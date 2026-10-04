# 폴더 정리 기록

정리일: 2026-10-03

첫 정리에서는 `/home/bernice/study` 내부를 정리했고, 후속 요청에 따라 홈의 `sql-injection-lab/`과 `study-upload.zip`도 study 안으로 이동했습니다. CTF 및 리눅스 커널 익스 공부 폴더는 변경하지 않았습니다.

## 옮긴 파일

| 이전 위치 | 현재 위치 |
|---|---|
| `Dockerfile` | `blind-sqli/Dockerfile` |
| `run-local.sh` | `blind-sqli/run-local.sh` |
| `deploy/` | `blind-sqli/deploy/` |
| `original/` | `blind-sqli/original/` |
| `.dockerignore` | `blind-sqli/.dockerignore` |

기존 README의 Blind SQLi 안내를 `blind-sqli/README.md`로 분리하고 실행 경로를 수정했습니다.
루트 README는 프로젝트 안내로 정리했으며, `docs/README.md`에 수업·분석 자료의 링크를 모았습니다.
`ksj-lab/` 내부는 그대로 보존했습니다. 과거 `STUDY_IMPORT.md`의 루트 경로 설명은 당시 상태를 기록한 것입니다.

## 보존과 검증

기존 파일을 삭제하지 않았습니다. 루트 README를 제외한 기존 파일은 이동 전후 SHA-256을 비교해 내용 보존을 확인합니다.
정리 전 파일 경로와 SHA-256은 `organization-before-sha256.json`에 보관합니다.
실행 코드와 프로젝트 내부 상대 경로는 그대로이며, Blind SQLi Docker 빌드 문맥만 `blind-sqli/`로 이동했습니다.
서비스 기동·종료·초기화, Git 커밋·푸시는 수행하지 않았습니다.

## 이전 배치로 되돌리기

위 표의 오른쪽 항목을 왼쪽 위치로 되돌리면 기존 실행 파일 배치로 복원됩니다.
같은 이름의 파일이나 폴더가 이미 생겼다면 덮어쓰지 말고 먼저 비교하세요.
정리 직전 루트 README는 `README.before-organization.md`에 보존했습니다.

## 후속 정리: 누락한 스터디 부산물 이동

- `/home/bernice/sql-injection-lab/` → `/home/bernice/study/sql-injection-lab/`
- `/home/bernice/study-upload.zip` → `/home/bernice/study/study-upload.zip`

폴더·파일을 그대로 이동했고 내부 내용과 Git 이력을 수정하지 않았습니다. 대상 inode가 유지됨을 확인했습니다. 기존 `ksj-lab/`과 병합하지 않았습니다. study의 `.gitignore`에 두 항목을 추가했습니다. 기존 절대 경로를 사용하는 외부 설정과 실행 중인 서비스는 변경하지 않았습니다.
