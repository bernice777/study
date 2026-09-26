-- 002-seed.sql — 결정적 시드(모두 가짜 데이터). reset 시 항상 동일.
USE `sbadmin`;

-- users: admin idx=1, learner@example.com idx=6 (메모 근거). 평문 비밀번호(교육용).
INSERT INTO `users` (`idx`,`email`,`password`) VALUES
 (1,'admin@sbadmin.local','admin_pw_2024'),
 (2,'alice@example.com','alicepw'),
 (3,'bob@example.com','bobpw'),
 (4,'carol@example.com','carolpw'),
 (5,'dave@example.com','davepw'),
 (6,'learner@example.com','bughela_secret_pw'),   -- Level 5 목표 계정
 (7,'erin@example.com','erinpw');

-- freeboard: 정상글 여러 개 + UNION 출력 확인용. 첨부는 안전한 더미.
INSERT INTO `freeboard` (`idx`,`uIdx`,`title`,`contents`,`upPath`,`upName`,`regDt`,`hit`) VALUES
 (1,2,'첫 자유게시글','안녕하세요 자유게시판입니다.','','','2024-08-01 09:00:00',12),
 (2,3,'점심 뭐 먹지','오늘 점심 추천 받습니다.','','','2024-08-02 12:30:00',34),
 (3,4,'UNION 출력 확인용 글','이 글의 idx 로 union 실습을 해보세요.','','','2024-08-03 15:10:00',7),
 (4,5,'스터디 모집','SQLi 스터디 모집합니다.','','','2024-08-04 18:45:00',20),
 (5,2,'주말 계획','주말에 뭐하시나요','','','2024-08-05 10:00:00',3);

-- notice: 관리자(uIdx=1) 작성
INSERT INTO `notice` (`idx`,`uIdx`,`title`,`contents`,`upPath`,`upName`,`regDt`,`hit`) VALUES
 (1,1,'[공지] 실습 환경 안내','로컬 실습 환경입니다.','','','2024-08-01 08:00:00',100),
 (2,1,'[공지] 수업 규칙','외부 대상 공격 금지.','','','2024-08-02 08:00:00',88),
 (3,1,'[공지] 첨부 예시','더미 첨부','uploads/notice_sample.txt','notice_sample.txt','2024-08-03 08:00:00',15);

-- pds: 자료실 (첨부 더미값 — 실제 파일 없음, SQLI 모드에서 다운로드 차단)
INSERT INTO `pds` (`idx`,`uIdx`,`title`,`contents`,`upPath`,`upName`,`regDt`,`hit`) VALUES
 (1,1,'로고 파일','회사 로고입니다.','uploads/logo_w.png','logo_w.png','2024-08-01 09:30:00',9),
 (2,3,'가이드 문서','실습 가이드 초안','uploads/guide_v1.txt','guide_v1.txt','2024-08-02 11:00:00',5),
 (3,4,'예제 소스','예제 zip (더미)','uploads/example.zip','example.zip','2024-08-03 14:00:00',2);

-- Blind 용 가짜 secret (Level 6/7/8). 실제 개인정보 아님.
INSERT INTO `lab_secret` (`id`,`label`,`secret`) VALUES
 (1,'flag','LAB{bl1nd_sqli_ok}'),
 (2,'pin','8461');

-- 2차 SQLi 초기 멤버 (Level 13). admin 존재.
INSERT INTO `ladder_members` (`id`,`username`,`nickname`,`role`) VALUES
 (1,'admin','관리자','admin'),
 (2,'guest','손님','user');
