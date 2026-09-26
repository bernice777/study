# TEACHING_FLOW — 약 150분 수업 흐름

## 0~20분 — 개념 + 환경 (전원 공통)
- Client/Web/WAS/DB 관계, HTTP GET·POST, 쿠키·세션, HTML Form/파라미터.
- 환경 실행(`start.sh`), `/health` 확인, Burp 프록시 설정, **Level 0** 로 요청 관찰 + Trace ID.

## 20~55분 — 입문 SQLi (전원 공통 완료)
- **L1 문자열 로그인** → **L2 숫자형** → 참·거짓/주석. Escape 로 못 막는 이유(L2, L9/escape).

## 55~95분 — UNION/스키마 (전원 공통)
- **L3 ORDER BY/UNION**(8컬럼) → **L4 information_schema**(db→table→column, group_concat).
- **L5 변경된 로그인 루틴**: SQL 결과 + 후속 PHP 조건 함께 분석.

## 95~120분 — Blind/Error (강사 시연 + 실습)
- **L6 Boolean** / **L7 Error-based**(DBMS 호환성 설명) / **L8 Time-based**(동시요청 제한 안내).
- Blind 는 학생 모드에서 SQL 이 숨겨짐을 강조(Observer 비교).

## 120~150분 — 방어 사다리 + PS 경계 (강사 시연 중심)
- **L9 필터 사다리**(none→prepared) → **Roadmap** 표로 정리.
- **L10~L12** Prepared/식별자/가짜 PS, **L13 2차** , **L14 SP** , **L15 최소권한**.
- 마무리: 근본 방어 = 바인딩 · 식별자 화이트리스트 · 최소권한. WAF/에러숨김은 보조.

## 빠른 학생 심화
- L4 group_concat 로 전체 계정 1회 추출, L6/L8 로 secret 완전 추출, L11 식별자 우회 시도.

## 수업 후 과제
- `docs/SQLMAP_REPORT_GUIDE.md` 기반 sqlmap **소스코드 한 기법 흐름** 분석(로컬 대상만).
- 각 레벨 실습 보고서 제출.
