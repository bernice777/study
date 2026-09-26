. "$PSScriptRoot\_common.ps1"
Load-Env
Write-Host "[*] 이미지 빌드"; docker compose build web observer
if ($LASTEXITCODE -ne 0) { throw "Docker build failed" }
Write-Host "[*] DB 기동"; docker compose up -d db
if ($LASTEXITCODE -ne 0) { throw "DB startup failed" }
Wait-Db
Apply-Sql
Write-Host "[*] web/observer 기동"; docker compose up -d web observer
if ($LASTEXITCODE -ne 0) { throw "Web startup failed" }
Write-Host "`n[OK] 실행 완료"
Write-Host "  학생용 웹  : http://127.0.0.1:$($global:EnvMap['WEB_PORT'])/"
Write-Host "  KSJ        : http://127.0.0.1:$($global:EnvMap['WEB_PORT'])/ksj/"
Write-Host "  Labs       : http://127.0.0.1:$($global:EnvMap['WEB_PORT'])/labs/"
Write-Host "  Observer   : http://127.0.0.1:$($global:EnvMap['OBSERVER_PORT'])/"
Write-Host "  phpMyAdmin : docker compose --profile instructor up -d phpmyadmin  (http://127.0.0.1:$($global:EnvMap['PMA_PORT'])/)"
