# SQLMAP_REPORT_GUIDE — sqlmap 소스코드 분석 과제 가이드

> 이 환경의 중심은 **sqlmap 자동 공격**이 아니라 **원리 이해**입니다.
> 과제는 sqlmap **소스코드에서 한 기법의 흐름**을 읽고 정리하는 것입니다.

## 규칙
1. 공식 저장소 **`sqlmapproject/sqlmap`** 만 사용(포크/미러 금지).
2. 분석 시점의 **Commit Hash** 를 반드시 기록(`git rev-parse HEAD`).
3. 전체 코드가 아니라 **한 기법의 흐름**만 선택해 따라간다.
4. 실행 예시는 **반드시 `127.0.0.1` 실습환경만** 대상으로 한다. 외부 사이트/Dreamhack 등 자동 공격 스크립트 금지.

## 따라갈 흐름 (권장 순서)
1. **입력 옵션 처리** — 명령행 옵션 파싱/정규화.
2. **대상 URL·파라미터 등록** — 테스트 대상 파라미터 식별.
3. **취약점 판별** — 참/거짓·오류·시간 기반 판정 로직.
4. **페이로드 생성** — 기법별 payload 조립.
5. **응답 비교** — 참/거짓 응답 차이 측정.
6. **결과 출력** — 추출/리포트.
7. **자동화의 오탐·미탐 가능성** — 왜 사람의 검증이 필요한가.

## 분석 후보 파일
```
sqlmap.py
lib/core/option.py
lib/controller/controller.py
lib/controller/checks.py
lib/core/agent.py
lib/techniques/blind/
lib/techniques/union/
```
- 예: Boolean-blind 판별은 `lib/controller/checks.py` 의 비교 로직 + `lib/techniques/blind/` 흐름을 함께 본다.
- 페이로드 템플릿은 `data/xml/payloads/` 와 `lib/core/agent.py`(prefix/suffix 조립) 연계.

## 로컬 실습환경 대상 실행 예시 (허용)
```bash
# 숫자형(Level 2) — 이 저장소 로컬 환경만!
python sqlmap.py -u "http://127.0.0.1:8000/labs/level2.php?idx=1" -p idx --batch --flush-session

# 문자열 로그인(Level 1) POST
python sqlmap.py -u "http://127.0.0.1:8000/labs/level1.php" --data="email=a&password=b" -p email --batch
```
- `--technique`, `--dbms=MySQL` 등으로 특정 기법만 관찰하며 소스 흐름과 대조.
- 관찰 후 Observer(강사 모드)에서 sqlmap 이 실제로 보낸 SQL 을 Trace 로 확인하면 학습에 좋다.

## 보고서 양식
```
[대상 기법] (예: Boolean-based blind)
[Commit Hash]
[진입점→핵심 함수 경로] 파일:함수 순서
[페이로드 생성 지점]
[판별 로직 요약]
[오탐/미탐 시나리오]
[로컬 실행 로그 요약] (127.0.0.1 대상)
```
