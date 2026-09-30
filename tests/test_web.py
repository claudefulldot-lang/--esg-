import urllib.request
import urllib.parse
import http.cookiejar
import re

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# 1. Get login page
resp = opener.open('http://localhost/esg/login')
html = resp.read().decode('utf-8')
m = re.search(r'name="csrf_token"\s+value="([^"]+)"', html)
token = m.group(1) if m else ''
print('[1] Got Login Page & CSRF Token:', token[:10] + '...')

# 2. Login
data = urllib.parse.urlencode({'csrf_token': token, 'username': 'admin', 'password': 'admin123'}).encode()
resp = opener.open('http://localhost/esg/login', data=data)
print('[2] Login POST Status:', resp.status)

# 3. Test pages
pages = [
    ('/', 'ESG 永續戰情室'),
    ('/ghg', '溫室氣體活動數據盤查清冊'),
    ('/ghg/create', '新增溫室氣體盤查活動數據'),
    ('/factors', '碳排放係數庫管理'),
    ('/environment/water', '水資源與放流水質監控管理'),
    ('/environment/waste', '事業廢棄物處置管理'),
    ('/social', '社會責任管理'),
    ('/governance', '公司治理管理'),
    ('/workflow', '待辦任務與審批簽核中心'),
    ('/reports', '智慧報表與永續報告書產出中心'),
    ('/admin/users', '使用者帳號與權限角色管理'),
    ('/admin/orgs', '組織機構與廠區層級架構'),
    ('/admin/logs', '系統安全與操作審計日誌'),
    ('/admin/settings', '全域可延展性與可調整參數控制台'),
    ('/admin/ai-assistant', 'AI 客服小編設定'),
    ('/manual', '系統操作手冊與問題排解中心')
]

for url, expected_text in pages:
    r = opener.open('http://localhost/esg' + url)
    content = r.read().decode('utf-8')
    assert expected_text in content, f'Missing expected text on {url}'
    print(f'  [OK] http://localhost/esg{url} [HTTP {r.status} - Content verified]')

# 4. Test Excel Export
r = opener.open('http://localhost/esg/reports/export_iso14064?year=2025')
content = r.read()
print(f'[3] ISO 14064 Excel Exported: {len(content)} bytes, Content-Type: {r.headers.get("Content-Type")}')

# 6. Verify clean Chinese characters from Database
content_factors = opener.open('http://localhost/esg/factors').read().decode('utf-8')
assert '柴油 (固定發電機/鍋爐)' in content_factors, 'Factor name is garbled'
assert '台灣環境部 1.2版' in content_factors, 'Factor source is garbled'
assert '台電外購一般電力' in content_factors, 'Electricity factor is garbled'
print('  [OK] Database Chinese characters verified (No mojibake!)')

print('\nALL WEB ENDPOINTS & EXPORTS FUNCTIONING PERFECTLY!')
