#!/usr/bin/env python3

import argparse
import logging
from os import path
import pymongo
import requests

import settings

alias_cache = {}


def get_client_from_cache(alias_name: str):
    return alias_cache.get(alias_name)


def set_client_to_cache(client):
    if client:
        alias_cache.update({client['login']: client})


def get_database(database_name=settings.DATABASE_NAME):
    mongo_client = get_mongo_client()
    return mongo_client[database_name]


def get_client_from_db(alias_name: str) -> dict:
    client = get_database().aliases.find_one(
        {"$or": [{"login": alias_name}, {"login_alias": alias_name}]})
    if client:
        del client['_id']
    return client


def get_client_from_billing(alias_name: str) -> dict:
    headers = {'Authorization': settings.TOKEN}
    response = requests.get(settings.ENDPOINT,
                            params={'login_or_alias': alias_name},
                            headers=headers)
    results = response.json()['results']
    if not len(results):
        raise ValueError('No such client {} in billing'.format(alias_name))
    return results[0]


def update_client_in_db(client_object: dict) -> None:
    client_login = client_object['login']
    saved_client = get_client_from_db(client_login)
    aliases_collection = get_database().aliases
    if saved_client:
        aliases_collection.delete_one({"login": saved_client['login']})
    aliases_collection.insert_one(client_object)


def invalidate(alias_name: str) -> None:
    client_object = get_client_from_billing(alias_name)
    update_client_in_db(client_object)
    set_client_to_cache(client_object)


def get_mongo_client():
    if not get_mongo_client.mongo_client:
        get_mongo_client.mongo_client = pymongo.MongoClient(
            settings.CONNECTION_STRING)
    return get_mongo_client.mongo_client


get_mongo_client.mongo_client = None


def create_logger() -> logging.Logger:
    if create_logger.logger:
        return create_logger.logger
    new_logger = logging.getLogger('check_alias_error')
    new_logger.setLevel(logging.DEBUG)
    formatter = logging.Formatter(
        '%(asctime)s - %(name)s - %(levelname)s - %(message)s')
    log_file = path.abspath(
        "{}/{}".format(
            path.abspath(__file__),
            '../../../var/clients/maxibooking/logs/check_alias_error.log'))
    fh = logging.FileHandler(log_file)
    fh.setFormatter(formatter)
    new_logger.addHandler(fh)
    create_logger.logger = new_logger
    return new_logger


create_logger.logger = None


def check(alias, action='get_alias'):
    logger = create_logger()
    logger.log(logging.INFO, 'start alias check with %(alias)'.format(alias))
    result = None
    try:
        if not alias:
            raise ValueError('Empty alias is error!')
        if action == 'get_alias':
            client = get_client_from_cache(alias)
            if not client:
                client = get_client_from_db(alias)
                set_client_to_cache(client)
            if not client:
                client = get_client_from_billing(alias)
                set_client_to_cache(client)
                update_client_in_db(client)
            result = client['login']
        if action == 'invalidate':
            invalidate(alias)
    except ValueError as e:
        logger.log(logging.WARNING, e)
        result = 'error'
    except Exception as e:
        logger.log(logging.CRITICAL, e)
        result = 'error'
    finally:
        get_mongo_client().close()

    return result


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Show real client name')
    parser.add_argument('--client', type=str)
    parser.add_argument('--mode', type=str, default='get_alias',
                        choices=['invalidate', 'get_alias'])
    args = parser.parse_args()

    alias = args.client
    mode = args.mode
    actual_alias = check(alias, mode)
    print(actual_alias)
