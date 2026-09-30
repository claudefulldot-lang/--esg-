import urllib.request
import urllib.parse
import http.cookiejar
import re

def test_role_access(username, password, expect_access):
    cj = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))
    resp = opener.open('http://localhost/esg/login')
    token = re.search(r'name="csrf_token"\s+value="([^"]+)"', resp.read().decode('utf-8')).group(1)
    opener.open('http://localhost/esg/login', data=urllib.parse.urlencode({'csrf_token': token, 'username': username, 'password': password}).encode())
    
    try:
        r = opener.open('http://localhost/esg/admin/settings')
        content = r.read().decode('utf-8', errors='replace')
        if expect_access:
            assert '全域可延展性與可調整參數控制台' in content, f'{username} failed to see settings'
            print(f'[PASS] {username} (Super Admin): Successfully accessed settings page (HTTP 200)!')
        else:
            assert False, f'{username} should NOT have accessed settings page!'
    except urllib.error.HTTPError as e:
        if not expect_access and e.code == 403:
            print(f'[PASS] {username} (Restricted Role): Correctly intercepted with HTTP 403 Forbidden!')
        else:
            raise

print("=== 測試可延展性設定僅限 Super Admin 存取權限 ===")
test_role_access('admin', 'admin123', True)
test_role_access('cso_chen', 'admin123', False)
test_role_access('dept_approver', 'admin123', False)
test_role_access('submitter_hc', 'admin123', False)
test_role_access('auditor_wang', 'admin123', False)
print("=== 權限隔離驗證 100% 通過 ===")
