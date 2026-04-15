# PACS Installation Script for Windows
# Run: powershell -ExecutionPolicy Bypass -File .\install.ps1

param(
    [string]$DockerRoot = "",
    [string]$ProjectPath = ""
)

$ErrorActionPreference = "Stop"
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path

# Default ProjectPath = parent of Deploy-Dev = project root (pacs2)
if (-not $ProjectPath) {
    $ProjectPath = Split-Path -Parent $ScriptDir
}

# Default DockerRoot = inside Deploy-Dev so it's contained in the project
if (-not $DockerRoot) {
    $DockerRoot = Join-Path $ScriptDir ".docker-data"
}

if (-not (Test-Path $ProjectPath)) {
    Write-Host "ERROR: Project path not found: $ProjectPath" -ForegroundColor Red
    exit 1
}

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  PACS Installation Script (Windows)" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan

# -----------------------------------------------
# 1. Check Docker
# -----------------------------------------------
try {
    docker --version | Out-Null
    Write-Host "Docker: $(docker --version)" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Docker is not installed. Install Docker Desktop first." -ForegroundColor Red
    exit 1
}

try {
    docker compose version | Out-Null
} catch {
    try { docker-compose --version | Out-Null } catch {
        Write-Host "ERROR: Docker Compose is not installed." -ForegroundColor Red
        exit 1
    }
}

# -----------------------------------------------
# 2. Check old installation
# -----------------------------------------------
if (Test-Path $DockerRoot) {
    $confirm = Read-Host "WARNING! Old installation at '$DockerRoot'. Remove and reinstall? [y/n]"
    if ($confirm -ne "y") {
        Write-Host "Aborted." -ForegroundColor Yellow
        exit 1
    }
    $containers = docker ps -a -q 2>$null
    if ($containers) {
        docker stop $containers 2>$null
        docker rm $containers 2>$null
    }
    Remove-Item -Recurse -Force "$DockerRoot\*" -ErrorAction SilentlyContinue
}

# -----------------------------------------------
# 3. User input
# -----------------------------------------------
$dbpass = Read-Host "Enter MariaDB password"
$dbpass2 = Read-Host "Confirm password"
if ($dbpass -ne $dbpass2) {
    Write-Host "ERROR: Passwords do not match!" -ForegroundColor Red
    exit 1
}

Write-Host "--------------------------------------------"
$pacsversion = Read-Host "PACS version (blank = latest)"
Write-Host "--------------------------------------------"
$zoneID = Read-Host "Enter zone ID"

# -----------------------------------------------
# 4. Create directories 
# -----------------------------------------------
Write-Host ""
Write-Host "Creating directories..." -ForegroundColor Yellow

# Volumes referenced in docker-compose.yml:
#   zookeeper:     /docker/local/zookeeper
#   kafka:         /docker/share/kafka/secrets, /docker/local/kafka
#   elasticsearch: /docker/local/elastic
#   mysql:         /docker/local/mysql, /docker/local/mysql-log
#   pacs:          /docker/share/pacs -> PROJECT SOURCE (mounted from $ProjectPath)
#                  /docker/share/kafka/secrets, /docker/local/pacs/server

$directories = @(
    "$DockerRoot\local\zookeeper",
    "$DockerRoot\local\kafka",
    "$DockerRoot\local\elastic",
    "$DockerRoot\local\mysql",
    "$DockerRoot\local\mysql-log",
    "$DockerRoot\local\pacs\server",
    "$DockerRoot\share\kafka\secrets"
)

foreach ($dir in $directories) {
    New-Item -ItemType Directory -Force -Path $dir | Out-Null
}
Write-Host "  Created $($directories.Count) directories" -ForegroundColor Green
Write-Host "  Project source: $ProjectPath -> /var/www/html (in pacs container)" -ForegroundColor Green

# Copy kafka config
$kafkaSource = Join-Path $ScriptDir "kafka"
if (Test-Path $kafkaSource) {
    Copy-Item -Recurse -Force "$kafkaSource\*" "$DockerRoot\share\kafka\"
    Write-Host "  Copied kafka config" -ForegroundColor Green
} else {
    Write-Host "  WARNING: kafka config not found at $kafkaSource" -ForegroundColor Yellow
}

# -----------------------------------------------
# 5. Generate .env (used by pacs container)
# -----------------------------------------------
Write-Host "Generating .env..." -ForegroundColor Yellow

@"
PACS_MYSQL_HOST=mysql:3306
PACS_MYSQL_USER=pacs
PACS_MYSQL_PASS=$dbpass
PACS_DATABASE_NAME=pacsdb
PACS_ELASTIC_HOSTS=http://elastic:MyElasticPassword_678@elasticsearch:9200
PACS_REDIS_HOST=redis
PACS_REDIS_PORT=6379
PACS_REDIS_PASS=Redis_92932382832
PACS_KAFKA_BROKER_LIST=kafka:9092
PACS_VERSION=$pacsversion
PACS_URL_CODE=http://support.cdhaviet.vn/pacs-cloud
PACS_ZONE_ID=$zoneID

# --- Dynamic paths for docker-compose.yml ---
DOCKER_ROOT=$DockerRoot
PROJECT_PATH=$ProjectPath

"@ | Out-File -FilePath (Join-Path $ScriptDir ".env") -Encoding utf8 -NoNewline

Write-Host "  .env created" -ForegroundColor Green

# Clean up old override file if it exists
$overridePath = Join-Path $ScriptDir "docker-compose.override.yml"
if (Test-Path $overridePath) {
    Remove-Item $overridePath -Force
}

# -----------------------------------------------
# 7. Start containers
# -----------------------------------------------
Write-Host ""
Write-Host "Starting Docker containers (pacs & dependencies)..." -ForegroundColor Yellow
Set-Location $ScriptDir

# Chỉ gọi đích danh 7 service cần thiết để bỏ qua các service RIS/nginx trong file gốc
$targetServices = "zookeeper kafka elasticsearch kibana redis mysql pacs"

# PowerShell treats STDERR from native commands as exceptions when ErrorActionPreference is "Stop".
# We use "Continue" from here on and manually check $LASTEXITCODE instead.
$ErrorActionPreference = "Continue"

try {
    Invoke-Expression "docker compose -f docker-compose.yml up -d $targetServices"
} catch {
    Invoke-Expression "docker-compose -f docker-compose.yml up -d $targetServices"
}

Write-Host "  Containers started" -ForegroundColor Green

# -----------------------------------------------
# 8. Wait for MySQL
# -----------------------------------------------
Write-Host "Waiting for MySQL..." -ForegroundColor Yellow
$maxRetries = 40
for ($i = 1; $i -le $maxRetries; $i++) {
    docker exec mysql mysqladmin ping > $null 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  MySQL is ready!" -ForegroundColor Green
        break
    }
    if ($i -eq $maxRetries) {
        Write-Host "ERROR: MySQL did not start in time!" -ForegroundColor Red
        exit 1
    }
    Write-Host "  Attempt $i/$maxRetries..." -ForegroundColor DarkGray
    Start-Sleep -Seconds 3
}
Start-Sleep -Seconds 10

# -----------------------------------------------
# 9. Create databases & users
# -----------------------------------------------
Write-Host "Creating databases..." -ForegroundColor Yellow

@(
    "create database pacsdb CHARACTER SET utf8 COLLATE utf8_unicode_ci;",
    "create database cloud_viewer CHARACTER SET utf8 COLLATE utf8_unicode_ci;",
    "grant all on pacsdb.* to 'pacs'@'%' identified by '$dbpass';",
    "grant all on cloud_viewer.* to 'pacs'@'%';"
) | ForEach-Object {
    docker exec mysql mysql -uroot -e "$_"
    if ($LASTEXITCODE -ne 0) {
        Write-Host "  ERROR: $_" -ForegroundColor Red
        exit 1
    }
}
Write-Host "  Databases created" -ForegroundColor Green

# -----------------------------------------------
# Done
# -----------------------------------------------
Write-Host ""
Write-Host "============================================" -ForegroundColor Green
Write-Host "  Installation Complete!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host ""
Write-Host "Services:" -ForegroundColor Cyan
Write-Host "  zookeeper      -> :2181"
Write-Host "  kafka          -> :9092"
Write-Host "  elasticsearch  -> :9200, :9300"
Write-Host "  kibana         -> :5601"
Write-Host "  redis          -> :6379"
Write-Host "  mysql          -> :3306"
Write-Host "  pacs           -> :8080, :4242, :2575, :9000"
Write-Host ""
Write-Host "Project source mounted from:" -ForegroundColor Cyan
Write-Host "  $ProjectPath -> pacs:/var/www/html"
Write-Host "  (Code changes are reflected immediately)" -ForegroundColor DarkGray
Write-Host ""
