. "$PSScriptRoot\_common.ps1"
Load-Env
Write-Host "[*] 이 프로젝트 실습 스키마만 초기화(다른 볼륨 미접촉)"
docker compose up -d db | Out-Null
Wait-Db
Apply-Sql
docker compose exec -T web sh -c ": > /var/lab-logs/observer.jsonl" 2>$null
Write-Host "[OK] reset 완료: Seed/AUTO_INCREMENT 초기화, 로그 초기화"
