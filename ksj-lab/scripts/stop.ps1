. "$PSScriptRoot\_common.ps1"
Write-Host "[*] 컨테이너 중지(볼륨 보존)"
docker compose --profile instructor --profile extra --profile cms down
if ($LASTEXITCODE -ne 0) { throw "Docker shutdown failed" }
Write-Host "[OK] 중지 완료"
