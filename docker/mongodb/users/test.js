db.createUser(
    {
        user: "test",
        pwd: "testpwd",
        roles: ["readWrite", "dbAdmin"]
    }
);