import checkalias
import pytest


def test_no_alias():
    result = checkalias.check(None, 'get_alias')
    assert result == 'error'


def test_wrong_alias():
    result = checkalias.check('wrong_alias', 'get_alias')
    assert result == 'error'


def test_alias():
    actual_alias = checkalias.check('piterprivet', 'get_alias')
    assert actual_alias == 'piterprivet'


def test_invalidate_no_alias():
    result = checkalias.check(None, 'invalidate')
    assert result == 'error'


def test_invalidate_alias():
    result = checkalias.check('piterprivet', 'invalidate')
    assert result is None


def test_invalidate_wrong_alias():
    result = checkalias.check('wrong_alias', 'invalidate')
    assert result == 'error'


def test_update_client_in_db():
    client = checkalias.get_client_from_billing('piterprivet')
    client['login_alias'] = 'test_login_alias'
    assert checkalias.update_client_in_db(client) is None
    database = checkalias.get_database()
    updated_client = database.aliases.find_one({"login": "piterprivet"})
    assert updated_client['login_alias'] == 'test_login_alias'


def test_invalidate():
    client = checkalias.get_client_from_db('piterprivet')
    assert client['login_alias'] == 'test_login_alias'
    checkalias.invalidate('piterprivet')
    client = checkalias.get_client_from_db('piterprivet')
    assert client['login_alias'] is None


def test_get_client_from_billing():
    client = checkalias.get_client_from_billing('piterprivet')
    assert client['login'] == 'piterprivet'
    assert client['login_alias'] is None


def test_wrong_client_from_billing():
    with pytest.raises(ValueError) as e:
        checkalias.get_client_from_billing('wrong_name')
    assert str(e.value) in 'No such client wrong_name in billing'


def test_get_client_from_cache():
    client = checkalias.get_client_from_billing('piterprivet')
    checkalias.alias_cache = {'piterprivet': client}
    client = checkalias.get_client_from_cache('piterprivet')
    assert client == client


def test_set_client_to_cache():
    client = checkalias.get_client_from_billing('piterprivet')
    checkalias.set_client_to_cache(client)
    assert checkalias.alias_cache['piterprivet'] == client


def test_check():
    client_name = 'piterprivet'
    client = checkalias.get_client_from_billing(client_name)
    result = checkalias.check(client_name)
    assert result == client_name
    assert checkalias.alias_cache[client_name] == client
    client_name2 = 'super-demo'
    result = checkalias.check(client_name)
    result2 = checkalias.check(client_name2)
    assert result == client_name
    assert result2 == 'online-demo'
    assert len(checkalias.alias_cache.keys()) == 2
