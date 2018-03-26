#!/usr/bin/python
from pymongo import MongoClient

c = MongoClient('mongodb://admin:maxibooking@127.0.0.1:27018/admin')

for db_name in (n for n in c.database_names() if n not in ('local','admin','test','maxibooking')):
    print("drop database {}".format(db_name))
    c.drop_database(db_name)

