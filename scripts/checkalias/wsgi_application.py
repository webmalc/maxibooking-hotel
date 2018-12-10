import checkalias
from urllib.parse import parse_qs



def application(env, start_response):
    query = parse_qs(env['QUERY_STRING'])
    alias = checkalias.check(query['client'][0], query['action'][0])
    if alias is not None:
        alias = alias.encode('utf-8')
    start_response('200 OK', [('Content-Type', 'text/html')])
    return [alias]
