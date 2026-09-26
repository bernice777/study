-- 004-views.sql — 최소권한 비교용 View + 제한계정 권한 부여
USE `sbadmin`;

-- 공개 View: 민감 컬럼(contents/작성자/첨부경로) 제외, 목록용 컬럼만 노출
CREATE OR REPLACE VIEW `v_freeboard_public` AS
  SELECT `idx`, `title`, `regDt`, `hit` FROM `freeboard`;

CREATE OR REPLACE VIEW `v_notice_public` AS
  SELECT `idx`, `title`, `regDt`, `hit` FROM `notice`;

-- 제한 계정: 위 View 만 SELECT (users/원본 테이블 접근 불가 → SQLi 있어도 피해 제한)
GRANT SELECT ON `sbadmin`.`v_freeboard_public` TO '${LAB_LIMITED_USER}'@'%';
GRANT SELECT ON `sbadmin`.`v_notice_public`   TO '${LAB_LIMITED_USER}'@'%';

-- 로그 조회 계정: View 읽기 전용
GRANT SELECT ON `sbadmin`.`v_freeboard_public` TO '${LAB_OBSERVER_USER}'@'%';
GRANT SELECT ON `sbadmin`.`v_notice_public`   TO '${LAB_OBSERVER_USER}'@'%';

FLUSH PRIVILEGES;
