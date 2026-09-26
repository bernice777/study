# INSPECTION_REPORT — KSJ `sbadmin` 원본 분석

> 이 문서는 **원본 소스 실제 분석 결과**만 기록한다. 추측한 내용은 "추정"으로 명시한다.
> 대상: `original/sbadmin/` (읽기전용 보존본). 계측/실행본: `apps/ksj-sbadmin/`.

## 0. 아카이브 무결성

| 파일 | 크기(byte) | SHA-256 |
|---|---|---|
| source/sbadmin.tgz | 6209759 | `12f2196d8198eb1e2b4270e819f2882d324e178b0a47b7353ca5d6a34df4471a` |
| source/rb.tgz (추가·참고) | 2422292 | `e19e5eef1134de8eb4cfb1d87863d7b2ee631cfd24475f68d380ad9855698051` |
| source/xe.tgz (추가·참고) | 5162956 | `2c93a92636464abd282de6c89e0961b5deadd97aef3468d4c29cb492d042ec29` |

- `gzip -t` 무결성 OK.
- **traversal 안전성:** 절대경로(`/`) 항목 0개, `..` 포함 항목 0개, 단일 top-level `sbadmin/`. 안전하게 추출함.
- 추가 발견: `rb.tgz`, `xe.tgz` 는 스펙 "제공 파일" 목록에 없으나 `메모.txt`의 XE 회원가입 INSERT SQLi 흐름과 연관. **이번 구축의 주 소스는 `sbadmin`** 이며 xe/rb 는 메모 분석 참고용으로만 보존한다.

## 1. 원본 구조

- 실체: **Start Bootstrap "SB Admin 2"** 정적 템플릿(MIT) 위에 소규모 KSJ 실습용 PHP 앱을 얹은 형태.
- PHP 파일 15개. 나머지는 템플릿 자산(svg 1624, scss/js/css 등).
- 실행 관련 핵심 PHP:
  - `init.php` — 세션 시작, DB 접속, `isLogin()`
  - `index.php` — 프론트 컨트롤러(`?p=` 로 pages include)
  - `act/{login,register,write,download}.php` — 처리 로직
  - `pages/{login,logout,register,home,board,read,write}.php` — 화면
  - `theme/{head,foot}.php` — 레이아웃

## 2. PHP 버전 호환성

- `mysqli_*` 절차형 API 사용 → PHP 8.2 에서 정상 동작(권장 `web` 이미지: `php:8.2-apache` + `mysqli`).
- 단, PHP 8 에서 **정의되지 않은 배열 키 접근은 Warning** (예: `$_GET['t']`, `$_POST['type']`, `$_SESSION['uIdx']`). 원본은 `display_errors=on` 이라 경고가 화면에 노출될 수 있음. 이는 교육상 유용(에러 기반 관찰)하나, 계측본에서는 Trace/Observer 로 별도 수집한다. **원본 문자열 결합 로직은 변경하지 않는다.**
- `move_uploaded_file`, `file_get_contents` 사용(업로드/다운로드). SQLI 모드에서 차단 대상(§9).

## 3. DB 연결 방식 (원본)

```php
// init.php:6-7
$db = mysqli_connect("localhost", "root", "1234");
mysqli_select_db($db, "sbadmin");
```

- 하드코딩된 `localhost/root/1234/sbadmin`. **계측본에서는 환경변수 기반 연결로 교체**(§ORIGINAL_DIFF). root 대신 최소권한 `lab_app` 사용.
- 문자셋 지정 없음 → 계측본에서 `utf8mb4` 로 통일.

## 4. 발견한 SQL 실행 지점 (전수)

| # | 위치 | 최종 SQL(원본) | 컨텍스트/취약점 |
|---|---|---|---|
| 1 | `act/login.php:7` | `select * from users where email='{$email}' and password='{$password}'` | 문자열형 로그인 SQLi. 첫 행의 `idx` 존재 시 로그인 성립 |
| 2 | `act/register.php:7` | `insert into users (email, password) values ('{$email}', '{$password}')` | INSERT SQLi |
| 3 | `act/write.php:17` | `insert into {$type} (uIdx, title, contents, upPath, upName) values ('{$uIdx}','{$title}','{$contents}','{$upPath}','{$upName}')` | 테이블명(`$type`)+값 주입. 파일 업로드 동반(범위 외) |
| 4 | `act/download.php:7` | `select * from {$type} where idx={$idx}` | 숫자형 SQLi + 이후 `file_get_contents($row['upPath'])` 임의 파일 읽기(범위 외) |
| 5 | `pages/board.php:25` | `select * from {$type} order by idx desc` | 테이블명 주입 |
| 6 | `pages/read.php:4` | `select * from {$type} where idx={$idx}` | **숫자형 SQLi (메모의 핵심 실습 지점)** |
| 6b | `pages/read.php:7` | `update {$type} set hit = hit + 1 where idx={$idx}` | 숫자형(부수) |
| 6c | `pages/read.php:9` | `select * from users where idx='{$row['uIdx']}'` | 2차성(저장값→쿼리) |
| 7 | `index.php:14` | `include("./pages/".$page)` | SQL 아님. LFI/파일 include(범위 외) |

메모 검증: `?p=read.php&t=freeboard&i=0 union select 1,2,3,database(),5,6,7,8`, `order by 8` 성공/`9` 실패, `information_schema.tables/columns`, `group_concat(concat(email,':',password))`, `i=2 or 1=1` 등 → 위 6번 지점과 8컬럼 구조가 실제로 일치.

## 5. SQLi 외 취약 기능 (이번 범위 밖 — 원본 보존, SQLI 모드에서 차단)

- **LFI/파일 include:** `index.php` 의 `?p=` → `include("./pages/".$page)`. → 계측본: pages 화이트리스트.
- **임의 파일 다운로드/읽기:** `act/download.php` `file_get_contents($row['upPath'])`. → SQLI 모드 차단.
- **업로드 → 웹셸:** `act/write.php` 가 원본 파일명 그대로 `uploads/` 저장, 확장자 검증 없음. `uploads/` 에서 PHP 실행 가능하면 웹셸. → 계측본: `uploads/` PHP 실행 차단 + SQLI 모드 업로드 차단.
- **INTO OUTFILE / load_file:** 메모에 `load_file('/etc/passwd')` 언급. → DB 권한에서 `FILE` 미부여로 원천 차단.
- **저장형 XSS:** `board.php`/`read.php` 가 `title`,`contents`,`$_GET['t']` 을 escape 없이 출력. SQLi 실습에는 유지(교육), Observer 자체에는 XSS 방지(출력 escape).

## 6. DB 스키마 복원 근거

원본에 스키마 DDL은 없음. 아래는 **코드+메모에서 역산**한 복원 근거.

### users (컬럼 순서: idx, email, password)
- `act/register.php` INSERT: `(email, password)`.
- `act/login.php`: `where email=... and password=...`, 로그인 성공 판정에 `$row['idx']` 사용 → `idx` PK 존재.
- `board.php` 관리자 판정 `$_SESSION['uIdx']==1` → admin `idx=1`. 메모: `learner@example.com idx=6`.

### freeboard / notice / pds (컬럼 순서: idx, uIdx, title, contents, upPath, upName, regDt, hit) — 8컬럼
- `act/write.php` INSERT: `(uIdx, title, contents, upPath, upName)` + auto `idx`.
- `board.php`/`read.php` 출력: `idx, title, regDt, hit`, 첨부 `upName/upPath`, 작성자 `uIdx`.
- 메모: `union select 1..8` 성공, `order by 8` 성공/`9` 실패 → 정확히 8컬럼. 컬럼 순서는 UNION 실습(예: `1,2,email,4,...,password,8`)이 재현되도록 위 순서로 고정.

## 7. 원본에 없어서 추가한 실습 (스펙 요구)

원본 sbadmin 은 Level 1(문자열 로그인)·Level 2(숫자형)·Level 3~4(UNION/스키마)·"변경된 로그인 루틴"·2차성 조회를 자연히 포함한다. 나머지는 별도 `apps/sqli-ladder/` 에 신규 구현:
- Level 0 요청관찰, Level 6 Boolean-blind, Level 7 Error-based, Level 8 Time-based,
  Level 9 필터 사다리(none/client/escape/blacklist/prepared), Level 10 Prepared 전후,
  Level 11 식별자 화이트리스트, Level 12 가짜 PS, Level 13 2차 SQLi(저장→관리기능),
  Level 14 저장 프로시저 동적 SQL, Level 15 최소권한 비교.
- 근거: 이들은 원본 코드 범위를 넘어서므로 **원본을 수정하지 않고** 교육용 신규 앱으로 분리한다. 원본과 동일한 8컬럼 스키마/시드를 공유한다.

## 8. 요구사항과의 차이(보고)

- 스펙 "제공 파일"은 `sbadmin.tgz`+`메모.txt` 만 명시했으나 실제로는 `rb.tgz`,`xe.tgz` 도 함께 제공됨 → 참고 보존, 주 소스는 sbadmin(스펙 준수).
- 원본은 순수 PHP 앱이 아니라 SB Admin 2 템플릿 기반 → 실행에는 영향 없음(PHP 파일만 서버측 동작).
- 그 외 스펙과 원본 구조는 일치.
