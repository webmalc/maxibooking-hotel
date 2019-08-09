#!/usr/bin/env bash
mongo --port 27018 admin < ./root.js
mongo --port 27018 test < ./test.js
mongo --port 27018 maxibooking < ./maxibooking.js
mongo --port 27018 template_db_for_test < ./user_test_db.js