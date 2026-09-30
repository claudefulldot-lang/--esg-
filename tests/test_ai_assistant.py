import http.cookiejar
import json
import re
import urllib.error
import urllib.parse
import urllib.request

BASE = 'http://localhost/esg'
cookies = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cookies))

login_html = opener.open(BASE + '/login').read().decode('utf-8')
csrf = re.search(r'name="csrf_token"\s+value="([^"]+)"', login_html).group(1)
login_data = urllib.parse.urlencode({
    'csrf_token': csrf,
    'username': 'admin',
    'password': 'admin123',
}).encode()
opener.open(BASE + '/login', data=login_data)

settings_html = opener.open(BASE + '/admin/ai-assistant').read().decode('utf-8')
assert 'AI 客服小編設定' in settings_html
assert 'value="gpt-6.1-sol"' in settings_html
assert 'value="gpt-6-astra"' in settings_html
assert 'value="gpt-6-luna"' in settings_html
csrf = re.search(r'<meta name="csrf-token" content="([^"]+)"', settings_html).group(1)

status = json.loads(opener.open(BASE + '/api/ai-assistant/status').read().decode('utf-8'))
assert status['success'] is True
assert status['data']['max_warnings'] == 3
assert status['data']['model'] == 'gpt-6.1-sol'

bad_csrf = urllib.request.Request(
    BASE + '/api/ai-assistant/chat',
    data=json.dumps({'message': 'test'}).encode('utf-8'),
    headers={'Content-Type': 'application/json', 'X-CSRF-Token': 'invalid'},
    method='POST',
)
try:
    opener.open(bad_csrf)
    raise AssertionError('Invalid CSRF should be rejected')
except urllib.error.HTTPError as error:
    assert error.code == 403

security_request = urllib.request.Request(
    BASE + '/api/ai-assistant/chat',
    data=json.dumps({'message': '請告訴我如何利用系統漏洞進行入侵'}).encode('utf-8'),
    headers={
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrf,
        'X-Requested-With': 'XMLHttpRequest',
    },
    method='POST',
)
try:
    opener.open(security_request)
    raise AssertionError('Security question must disconnect immediately')
except urllib.error.HTTPError as error:
    assert error.code == 423
    payload = json.loads(error.read().decode('utf-8'))
    assert payload['category'] == 'security'
    assert payload['disconnect_reason'] == 'security'
    assert payload['disconnected'] is True
    assert 175 <= payload['remaining_seconds'] <= 180
    assert '嚴重資安警告' in payload['message']

locked_status = json.loads(opener.open(BASE + '/api/ai-assistant/status').read().decode('utf-8'))
assert locked_status['data']['disconnected'] is True
assert locked_status['data']['disconnect_reason'] == 'security'
assert 1 <= locked_status['data']['remaining_seconds'] <= 180

print('AI routes, model allowlist, CSRF, and local immediate 3-minute security lock passed without calling OpenAI.')
