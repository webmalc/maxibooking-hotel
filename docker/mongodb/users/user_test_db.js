db.createUser(
    {
        user: "user_test_db",
        pwd: "testpwd",
        roles: ["readWrite", "dbAdmin"]
    }
);