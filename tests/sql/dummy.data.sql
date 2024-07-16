INSERT INTO fb_security_policies (policy_id, p_type, v0, v1, v2, v3, v4, v5, policy_parent, policy_type) VALUES
(X'272379D8835144B6AD8D73A0ABCB7F9C', 'p', '2784d750-f085-4580-8525-4d622face83d', 'data1', 'read', NULL, NULL, NULL, NULL, 'test_policy'),
(X'AB369E71ADA64D1AA5A8B6EE5CD58296', 'p', 'c450531d-0f10-4587-a0ce-42fb48a8a8ad', 'data2', 'write', NULL, NULL, NULL, NULL, 'test_policy'),
(X'89F4A14F7F78421699B8584AB9229F1C', 'p', 'visitor', 'data3', 'read', NULL, NULL, NULL, NULL, 'test_policy'),
(X'C74A16B167F44FFD812A9E5EC4BD5263', 'p', 'administrator', 'data1', 'read', NULL, NULL, NULL, NULL, 'test_policy'),
(X'D721529EDEC647C88035A3484070142B', 'p', 'administrator', 'data1', 'write', NULL, NULL, NULL, NULL, 'test_policy'),
(X'155534434564454DAF040DFEEF08AA96', 'p', 'administrator', 'data2', 'read', NULL, NULL, NULL, NULL, 'test_policy'),
(X'1D60090154E743EE8F5DA9E22663DDD7', 'p', 'administrator', 'data2', 'write', NULL, NULL, NULL, NULL, 'test_policy'),
(X'9d270db23bfc403f88cd27dbf49c9d49', 'p', 'administrator', 'data3', 'read', NULL, NULL, NULL, NULL, 'test_policy'),
(X'27e12426f3c54b6f9c86211d675be143', 'p', 'administrator', 'data3', 'write', NULL, NULL, NULL, NULL, 'test_policy'),
(X'5626E7A1C42C4A319B5D848E3CF0E82A', 'g', '2784d750-f085-4580-8525-4d622face83d', 'visitor', NULL, NULL, NULL, NULL, NULL, 'test_role'),
(X'9A91473298DC47F6BFD19D81CA9F8CB6', 'g', '5785924c-75a8-42ae-9bdd-a6ce5edbadac', 'administrator', NULL, NULL, NULL, NULL, NULL, 'test_role');