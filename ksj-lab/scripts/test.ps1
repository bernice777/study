. "$PSScriptRoot\_common.ps1"
Load-Env
Write-Host "[*] 자동 테스트 실행"
# Windows: WSL/git-bash 의 bash 로 tests 실행 (로컬 전용)
bash tests/run_tests.sh
