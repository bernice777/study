# 공통 PowerShell 함수
$ErrorActionPreference = "Stop"
# Windows PowerShell 5.1에서도 SQL의 한글을 UTF-8로 전달합니다.
$OutputEncoding = New-Object System.Text.UTF8Encoding($false)
$Root = Split-Path -Parent $PSScriptRoot
Set-Location $Root

# WSL+Docker Desktop credsStore(desktop.exe) 대비: 공개 이미지 전용 프로젝트-로컬 config
if (-not $env:DOCKER_CONFIG) {
  $cfg = Join-Path $HOME ".docker/config.json"
  if ((Test-Path $cfg) -and (Select-String -Path $cfg -Pattern 'desktop.exe' -Quiet)) {
    $env:DOCKER_CONFIG = Join-Path $Root ".docker"
    New-Item -ItemType Directory -Force -Path $env:DOCKER_CONFIG | Out-Null
    if (-not (Test-Path (Join-Path $env:DOCKER_CONFIG "config.json"))) { '{}' | Set-Content (Join-Path $env:DOCKER_CONFIG "config.json") }
  }
}
$SubstVars = @('LAB_APP_USER','LAB_APP_PASSWORD','LAB_LIMITED_USER','LAB_LIMITED_PASSWORD','LAB_OBSERVER_USER','LAB_OBSERVER_PASSWORD','LAB_INSTRUCTOR_USER','LAB_INSTRUCTOR_PASSWORD')

function Load-Env {
  if (-not (Test-Path ".env")) { Copy-Item ".env.example" ".env"; Write-Host "[!] .env 생성됨(.env.example). 비밀값을 변경하세요." }
  $global:EnvMap = @{}
  Get-Content ".env" -Encoding UTF8 | ForEach-Object {
    if ($_ -match '^\s*([^#=]+)=(.*)$') { $global:EnvMap[$matches[1].Trim()] = $matches[2].Trim() }
  }
}
function Wait-Db {
  Write-Host "[*] DB healthy 대기..." -NoNewline
  for ($i=0; $i -lt 60; $i++) {
    docker compose exec -T db healthcheck.sh --connect *> $null
    if ($LASTEXITCODE -eq 0) { Write-Host " ok"; return }
    Write-Host "." -NoNewline; Start-Sleep 2
  }
  throw "DB healthy timeout"
}
function Apply-Sql {
  Write-Host "[*] db/init/*.sql 적용 (envsubst)"
  Get-ChildItem "db/init/0*.sql" | Sort-Object Name | ForEach-Object {
    Write-Host "    - $($_.Name)"
    $sql = Get-Content $_.FullName -Raw -Encoding UTF8
    foreach ($v in $SubstVars) { $sql = $sql.Replace('${'+$v+'}', $global:EnvMap[$v]) }
    $sql | docker compose exec -T db mariadb -uroot -p"$($global:EnvMap['MARIADB_ROOT_PASSWORD'])"
    if ($LASTEXITCODE -ne 0) { throw "SQL initialization failed: $($_.Name)" }
  }
}
