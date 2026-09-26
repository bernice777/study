-- 001-schema.sql — KSJ sbadmin 스키마 복원 + 실습 부가 테이블
-- 근거: docs/INSPECTION_REPORT.md §6. 컬럼 순서는 UNION 실습 재현을 위해 고정.
-- 멱등: DROP DATABASE 후 재생성 (reset 반복 시 항상 동일 결과 + AUTO_INCREMENT 초기화)

DROP DATABASE IF EXISTS `sbadmin`;
CREATE DATABASE `sbadmin` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sbadmin`;

-- users: 컬럼 순서 idx, email, password (원본 register/login 근거)
CREATE TABLE `users` (
  `idx`      INT AUTO_INCREMENT PRIMARY KEY,
  `email`    VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL         -- ⚠️ 교육용 평문. 실서비스는 password_hash 필수
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 게시판 3종: 8컬럼 (idx,uIdx,title,contents,upPath,upName,regDt,hit)
-- order by 8 성공 / 9 실패, union select 1..8 재현
CREATE TABLE `freeboard` (
  `idx`      INT AUTO_INCREMENT PRIMARY KEY,
  `uIdx`     INT NOT NULL DEFAULT 0,
  `title`    VARCHAR(255) NOT NULL DEFAULT '',
  `contents` TEXT,
  `upPath`   VARCHAR(255) NOT NULL DEFAULT '',
  `upName`   VARCHAR(255) NOT NULL DEFAULT '',
  `regDt`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hit`      INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `notice` (
  `idx`      INT AUTO_INCREMENT PRIMARY KEY,
  `uIdx`     INT NOT NULL DEFAULT 0,
  `title`    VARCHAR(255) NOT NULL DEFAULT '',
  `contents` TEXT,
  `upPath`   VARCHAR(255) NOT NULL DEFAULT '',
  `upName`   VARCHAR(255) NOT NULL DEFAULT '',
  `regDt`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hit`      INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `pds` (
  `idx`      INT AUTO_INCREMENT PRIMARY KEY,
  `uIdx`     INT NOT NULL DEFAULT 0,
  `title`    VARCHAR(255) NOT NULL DEFAULT '',
  `contents` TEXT,
  `upPath`   VARCHAR(255) NOT NULL DEFAULT '',
  `upName`   VARCHAR(255) NOT NULL DEFAULT '',
  `regDt`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hit`      INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 실습 부가 테이블 (원본에 없음, ladder 전용) ──────────────────
-- Blind(Level6/7/8) 용 가짜 secret. 실제 개인정보 아님.
CREATE TABLE `lab_secret` (
  `id`     INT AUTO_INCREMENT PRIMARY KEY,
  `label`  VARCHAR(64) NOT NULL,
  `secret` VARCHAR(128) NOT NULL            -- 가짜 플래그 문자열
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2차 SQLi(Level13) 용. nickname 을 저장 시점엔 안전히 넣지만,
-- 관리 기능이 저장값을 꺼내 동적 SQL 로 재조립하는 흐름을 재현.
CREATE TABLE `ladder_members` (
  `id`       INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(64) NOT NULL,
  `nickname` VARCHAR(128) NOT NULL DEFAULT '',
  `role`     VARCHAR(16)  NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
