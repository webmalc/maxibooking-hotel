#!/usr/bin/env bash
mongo admin < ./root.js
mongo test < ./test.js
mongo emplate_db_for_test < ./user_test_db.js