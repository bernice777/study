. "$PSScriptRoot\_common.ps1"
Load-Env
docker compose --profile cms up -d --build xe rb
if ($LASTEXITCODE -ne 0) { throw "CMS startup failed" }
$xePort = $global:EnvMap['XE_PORT']
$rbPort = $global:EnvMap['RB_PORT']
if (-not $xePort) { $xePort = '8003' }
if (-not $rbPort) { $rbPort = '8004' }
Write-Host "XE: http://127.0.0.1:$xePort/xe/"
Write-Host "RB: http://127.0.0.1:$rbPort/rb/"
Write-Host 'First installation: docs/CMS_SETUP.md. Existing data is preserved.'
