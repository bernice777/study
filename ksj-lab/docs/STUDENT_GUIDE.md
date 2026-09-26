# STUDENT_GUIDE — 학생 가이드

## 전제
- **Docker Desktop** 설치 및 실행. (Windows 는 WSL2 백엔드 권장)

## 환경 실행

최초 설치용입니다. 재실행은 `docker compose up -d`를 사용합니다.
`start` 스크립트는 실습 DB를 초기화합니다.

```bash
cp .env.example .env
./scripts/start.sh        # Windows: .\scripts\start.ps1
```
접속: 학생용 웹 http://127.0.0.1:8000/ , Observer http://127.0.0.1:8002/

## Burp Proxy 설정
1. Burp: Proxy > Options 리스너 `127.0.0.1:8080`.
2. 브라우저 프록시를 `127.0.0.1:8080` 으로. (또는 Burp 내장 브라우저)
3. `http://127.0.0.1:8000/` 요청을 Intercept/Repeater 로 관찰·변조.
4. 클라이언트(type=email/JS) 검증은 Burp 로 우회할 수 있음을 Level 9/client 에서 확인.

## 정상 요청 확인 (Level 0)
- `/labs/level0.php` 에서 GET/POST/Cookie 를 보내고 수신 파라미터를 확인.
- 응답 헤더 **`X-Lab-Trace-ID`** 값을 기록 → Observer 에서 같은 Trace 로 내 요청의 SQL 을 찾음.

## Trace ID 확인
- 각 실습 페이지 하단에 Trace ID 표시. Observer(http://127.0.0.1:8002/) 표의 Trace 열과 매칭.
- Blind 레벨(6/8)은 학생 모드에서 SQL 이 가려짐(🔒) — 화면 차이/응답시간으로 추론.

## 실습 보고서 양식
```
[레벨] Level N — 취약점명
[입력] (파라미터/페이로드)
[관찰] Trace ID / 화면 변화 / 응답시간
[최종 SQL] (강사 모드/비블라인드일 때)
[원리] 왜 취약한가
[근본 방어] 어떻게 막는가
```

## 종료
```bash
./scripts/stop.sh         # Windows: .\scripts\stop.ps1
```
