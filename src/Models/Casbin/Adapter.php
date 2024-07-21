<?php declare(strict_types = 1);

/**
 * Adapter.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           09.07.20
 */

namespace FastyBird\SimpleAuth\Models\Casbin;

use Casbin\Model as CasbinModel;
use Casbin\Persist as CasbinPersist;
use Doctrine\DBAL;
use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Models;
use FastyBird\SimpleAuth\Queries;
use FastyBird\SimpleAuth\Types\PolicyType;
use IPub\DoctrineCrud\Exceptions as DoctrineCrudExceptions;
use Nette\Utils\ArrayHash;
use TypeError;
use ValueError;
use function array_filter;
use function count;
use function implode;
use function intval;
use function is_file;
use function range;
use function strval;
use function trim;

/**
 * Casbin database adapter
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Models
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class Adapter implements CasbinPersist\Adapter
{

	use CasbinPersist\AdapterHelper;

	private CasbinPersist\Adapter|null $fallback;

	private bool $useFallback = false;

	public function __construct(
		private readonly Models\Policies\Repository $policiesRepository,
		private readonly Models\Policies\Manager $policiesManager,
		string|null $policyFile = null,
	)
	{
		$this->fallback = $policyFile !== null && is_file($policyFile)
			? new CasbinPersist\Adapters\FileAdapter($policyFile)
			: null;
	}

	/**
	 * @param array<string, string|null> $rule
	 *
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function savePolicyLine(string $ptype, array $rule): void
	{
		$data = [
			'type' => PolicyType::from($ptype),
		];

		foreach ($rule as $key => $value) {
			$data['v' . strval($key)] = $value;
		}

		$this->policiesManager->create(ArrayHash::from($data));
	}

	/**
	 * @throws Exceptions\InvalidState
	 */
	public function loadPolicy(CasbinModel\Model $model): void
	{
		try {
			$findPoliciesQuery = new Queries\FindPolicies();

			$policies = $this->policiesRepository->findAllBy($findPoliciesQuery);
		} catch (DBAL\Driver\Exception) {
			$this->fallback?->loadPolicy($model);

			$this->useFallback = true;

			return;
		}

		foreach ($policies as $policy) {
			$data = [
				$policy->getType()->value,
				$policy->getV0(),
				$policy->getV1(),
				$policy->getV2(),
				$policy->getV3(),
				$policy->getV4(),
				$policy->getV5(),
			];

			$line = implode(', ', array_filter($data, static fn ($val) => $val != '' && $val !== null));

			$this->loadPolicyLine(trim($line), $model);
		}
	}

	/**
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function savePolicy(CasbinModel\Model $model): void
	{
		if ($this->useFallback) {
			$this->fallback?->savePolicy($model);

			return;
		}

		foreach ($model['p'] ?? [] as $type => $ast) {
			foreach ($ast->policy as $rule) {
				$this->savePolicyLine($type, $rule);
			}
		}

		foreach ($model['g'] ?? [] as $type => $ast) {
			foreach ($ast->policy as $rule) {
				$this->savePolicyLine($type, $rule);
			}
		}
	}

	/**
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function addPolicy(string $sec, string $ptype, array $rule): void
	{
		if ($this->useFallback) {
			$this->fallback?->addPolicy($sec, $ptype, $rule);

			return;
		}

		$this->savePolicyLine($ptype, $rule);
	}

	/**
	 * @throws DoctrineCrudExceptions\InvalidArgumentException
	 * @throws Exceptions\InvalidState
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function removePolicy(string $sec, string $ptype, array $rule): void
	{
		if ($this->useFallback) {
			$this->fallback?->removePolicy($sec, $ptype, $rule);

			return;
		}

		$findPoliciesQuery = new Queries\FindPolicies();
		$findPoliciesQuery->byType(PolicyType::from($ptype));

		foreach ($rule as $key => $value) {
			$findPoliciesQuery->byValue(intval($key), $value);
		}

		$policies = $this->policiesRepository->findAllBy($findPoliciesQuery);

		foreach ($policies as $policy) {
			$this->policiesManager->delete($policy);
		}
	}

	/**
	 * @throws DoctrineCrudExceptions\InvalidArgumentException
	 * @throws Exceptions\InvalidState
	 * @throws TypeError
	 * @throws ValueError
	 */
	public function removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, string ...$fieldValues): void
	{
		if ($this->useFallback) {
			$this->fallback?->removeFilteredPolicy($sec, $ptype, $fieldIndex, ...$fieldValues);

			return;
		}

		$findPoliciesQuery = new Queries\FindPolicies();
		$findPoliciesQuery->byType(PolicyType::from($ptype));

		foreach (range(0, 5) as $value) {
			if ($fieldIndex <= $value && $value < $fieldIndex + count($fieldValues)) {
				if ($fieldValues[$value - $fieldIndex] != '') {
					$findPoliciesQuery->byValue(intval($value), $fieldValues[$value - $fieldIndex]);
				}
			}
		}

		$policies = $this->policiesRepository->findAllBy($findPoliciesQuery);

		foreach ($policies as $policy) {
			$this->policiesManager->delete($policy);
		}
	}

}
