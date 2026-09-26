-- 005-stored-procedures.sql — 저장 프로시저 내부 동적 SQL (Level 14)
-- 취약 vs 안전 비교. SQL SECURITY INVOKER: 호출자(lab_app) 권한으로 실행.
USE `sbadmin`;

DROP PROCEDURE IF EXISTS `sp_search_member_vuln`;
DROP PROCEDURE IF EXISTS `sp_search_member_safe`;

DELIMITER //

-- 취약: 프로시저 내부에서 문자열을 조합한 뒤 PREPARE/EXECUTE
CREATE PROCEDURE `sp_search_member_vuln`(IN kw VARCHAR(128))
  SQL SECURITY INVOKER
BEGIN
  SET @q = CONCAT('SELECT id, username, nickname, role FROM ladder_members WHERE nickname LIKE ''%', kw, '%''');
  PREPARE st FROM @q;
  EXECUTE st;
  DEALLOCATE PREPARE st;
END //

-- 안전: 고정 SQL + 파라미터 바인딩 (프로시저 내부도 파라미터화)
CREATE PROCEDURE `sp_search_member_safe`(IN kw VARCHAR(128))
  SQL SECURITY INVOKER
BEGIN
  SET @needle = CONCAT('%', kw, '%');
  PREPARE st FROM 'SELECT id, username, nickname, role FROM ladder_members WHERE nickname LIKE ?';
  EXECUTE st USING @needle;
  DEALLOCATE PREPARE st;
END //

DELIMITER ;
