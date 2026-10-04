# 스터디 자료 모음

웹해킹 스터디에서 만든 실습 환경과 수업 자료입니다. 프로젝트별로 실행 파일과 원본을 함께 보관합니다.

| 찾는 자료 | 위치 | 내용 |
|---|---|---|
| KSJ 사이트와 SQLi 단계별 실습 | [ksj-lab/](ksj-lab/README.md) | PHP/MySQL 복원 사이트, Level 0~15, SQL Observer |
| XE · KimsQ RB | [CMS 실행·설치 안내](ksj-lab/docs/CMS_SETUP.md) | 같은 Compose 프로젝트의 선택 서비스, 각각 별도 PHP·DB |
| 기존 로컬 SQL Injection 작업본 | `sql-injection-lab/` (Git 제외) | 기존 Git 이력·로컬 자료를 포함한 작업 폴더 전체 |
| 업로드용 ZIP 보관본 | `study-upload.zip` (Git 제외) | 기존 압축 파일 그대로 보관 |
| 독립 Blind SQLi 실습 | [blind-sqli/](blind-sqli/README.md) | Flask/MariaDB 실습, Docker 실행 방법 |
| 학생·강사 자료와 분석 기록 | [자료 길잡이](docs/README.md) | 학습 순서, 보고서, 원본 분석 문서 바로가기 |
| 이번 폴더 정리 내역 | [정리 기록](docs/ORGANIZATION.md) | 이전/현재 위치와 파일 보존 확인 |

## 어디서 시작하면 되나요?

- KSJ 실습: `cd study/ksj-lab` 후 [실행 가이드](ksj-lab/README.md)를 따릅니다. 기본 주소는 http://127.0.0.1:8000/ksj/ 입니다.
- Blind SQLi 실습: `cd study/blind-sqli` 후 [실행 가이드](blind-sqli/README.md)를 따릅니다. 기본 주소는 http://127.0.0.1:8010/ 입니다.
- 수업 자료만 찾을 때: [자료 길잡이](docs/README.md)에서 주제별 문서를 엽니다.

**Blind SQLi의 Docker 빌드 위치가 저장소 루트에서 `blind-sqli/`로 바뀌었습니다.**
`docker build -t blind-sqli-advanced:local .`은 해당 폴더 안에서 실행합니다.

## 폴더 구조

```text
study/
├── README.md                 # 전체 안내
├── docs/                     # 자료 길잡이와 폴더 정리 기록
├── sql-injection-lab/        # 홈에서 옮긴 기존 로컬 작업본
├── study-upload.zip          # 기존 업로드용 압축 파일
├── ksj-lab/                  # KSJ 실습 프로젝트
│   ├── apps/                 # 실행 코드와 SQLi 단계별 실습
│   ├── original/             # KSJ 원본 보존본
│   ├── docs/                 # 수업 자료와 복원·분석 기록
│   ├── db/                   # 스키마와 실습 데이터
│   ├── docker/               # 실행 이미지 설정
│   └── scripts/              # 실행·종료·초기화 스크립트
└── blind-sqli/               # 독립 Blind SQLi 실습 프로젝트
    ├── README.md             # 실행·종료·초기화 안내
    ├── Dockerfile            # 로컬 실행 이미지
    ├── run-local.sh          # 컨테이너 시작 스크립트
    ├── deploy/               # 배포받은 실습 코드
    └── original/             # 원본 Dockerfile 보존본
```

각 프로젝트의 `original/`은 비교·보존용입니다. 실행할 때는 해당 프로젝트 README를 참고하세요.

`sql-injection-lab/`은 기존 로컬 작업본이고, `ksj-lab/`은 이전에 study용으로 복사·정리한 프로젝트입니다. 두 폴더는 합치지 않고 각각 보존했습니다. 로컬 작업본과 ZIP은 study 저장소의 `.gitignore`에 추가해 자동 추적 대상에서 제외했습니다.
