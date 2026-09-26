. "$PSScriptRoot\_common.ps1"
Write-Host "[*] 컨테이너 중지(볼륨 보존)"
docker compose --profile instructor down
Write-Host "[OK] 중지 완료"
