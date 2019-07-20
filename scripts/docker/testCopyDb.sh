#!/usr/bin/env bash
echo 'db.dropDatabase()' | mongo --port 27018 --quiet test
echo "db.copyDatabase('template_db_for_test', 'test')" | mongo --port 27018 --quiet admin
