-- 003-users-and-privileges.sql — DB 계정 분리 + 최소권한 (§7)
-- ${...} 는 start.sh/reset.sh 가 .env 값으로 envsubst 치환한다. (평문 비밀 하드코딩 없음)
-- 멱등: DROP USER IF EXISTS 후 CREATE.

-- 웹 애플리케이션 계정: sbadmin 에서 SELECT/INSERT/UPDATE/EXECUTE 만. FILE/SUPER/CREATE USER 없음.
DROP USER IF EXISTS '${LAB_APP_USER}'@'%';
CREATE USER '${LAB_APP_USER}'@'%' IDENTIFIED BY '${LAB_APP_PASSWORD}';
GRANT SELECT, INSERT, UPDATE, EXECUTE ON `sbadmin`.* TO '${LAB_APP_USER}'@'%';

-- 제한 계정: 지정된 View 만 SELECT (Level 15 최소권한 비교)
DROP USER IF EXISTS '${LAB_LIMITED_USER}'@'%';
CREATE USER '${LAB_LIMITED_USER}'@'%' IDENTIFIED BY '${LAB_LIMITED_PASSWORD}';
-- (개별 View 권한은 004-views.sql 생성 후 부여)

-- 로그 조회 계정: 읽기 전용 (본 구현의 실습 로그는 JSONL 파일이며 Observer 가 읽음.
--   lab_observer 는 DB 측 읽기전용 역할로, sbadmin View 에 대한 SELECT 만 가진다.)
DROP USER IF EXISTS '${LAB_OBSERVER_USER}'@'%';
CREATE USER '${LAB_OBSERVER_USER}'@'%' IDENTIFIED BY '${LAB_OBSERVER_PASSWORD}';

-- 강사 관리 계정: sbadmin 전체 관리 (전역 관리자 아님)
DROP USER IF EXISTS '${LAB_INSTRUCTOR_USER}'@'%';
CREATE USER '${LAB_INSTRUCTOR_USER}'@'%' IDENTIFIED BY '${LAB_INSTRUCTOR_PASSWORD}';
GRANT ALL PRIVILEGES ON `sbadmin`.* TO '${LAB_INSTRUCTOR_USER}'@'%';

FLUSH PRIVILEGES;
