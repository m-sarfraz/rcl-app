# MySQL 8.0 Root Password Reset Script for Windows
# Run this as Administrator in PowerShell

param(
    [string]$NewPassword = "rcl2024"
)

$mysqlBin  = "C:\Program Files\MySQL\MySQL Server 8.0\bin"
$mysqld    = "$mysqlBin\mysqld.exe"
$mysql     = "$mysqlBin\mysql.exe"
$dataDir   = "C:\ProgramData\MySQL\MySQL Server 8.0\Data"
$initFile  = "$env:TEMP\mysql_reset_init.sql"

Write-Host "`n=== MySQL 8.0 Root Password Reset ===" -ForegroundColor Cyan
Write-Host "New password will be: $NewPassword" -ForegroundColor Yellow

# Step 1: Stop MySQL service
Write-Host "`n[1/5] Stopping MySQL80 service..." -ForegroundColor White
Stop-Service -Name "MySQL80" -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 3
Write-Host "     Service stopped." -ForegroundColor Green

# Step 2: Create init SQL file
Write-Host "[2/5] Writing reset SQL..." -ForegroundColor White
$sql = @"
ALTER USER 'root'@'localhost' IDENTIFIED BY '$NewPassword';
FLUSH PRIVILEGES;
"@
$sql | Out-File -FilePath $initFile -Encoding utf8 -Force
Write-Host "     SQL written to $initFile" -ForegroundColor Green

# Step 3: Start MySQL with init file (runs SQL then exits)
Write-Host "[3/5] Starting MySQL with --init-file to reset password..." -ForegroundColor White
$proc = Start-Process -FilePath $mysqld -ArgumentList @(
    "--defaults-file=`"C:\ProgramData\MySQL\MySQL Server 8.0\my.ini`"",
    "--init-file=`"$initFile`"",
    "--console"
) -NoNewWindow -PassThru

Write-Host "     Waiting 8 seconds for MySQL to apply reset..." -ForegroundColor Yellow
Start-Sleep -Seconds 8

# Kill the temp instance
Stop-Process -Id $proc.Id -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2
Write-Host "     Init instance stopped." -ForegroundColor Green

# Step 4: Start MySQL service normally
Write-Host "[4/5] Restarting MySQL80 service..." -ForegroundColor White
Start-Service -Name "MySQL80"
Start-Sleep -Seconds 4
Write-Host "     Service started." -ForegroundColor Green

# Step 5: Test connection
Write-Host "[5/5] Testing new password..." -ForegroundColor White
$test = & "$mysql" -u root "--password=$NewPassword" -h 127.0.0.1 -e "SELECT VERSION();" 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅ SUCCESS! MySQL root password reset to: $NewPassword" -ForegroundColor Green
    Write-Host "   Next steps:" -ForegroundColor Cyan
    Write-Host "   1. Update your .env: DB_PASSWORD=$NewPassword" -ForegroundColor White
    Write-Host "   2. Run: php artisan migrate --seed" -ForegroundColor White
    Write-Host "   3. phpMyAdmin: http://phpmyadmin.test (login: root / $NewPassword)" -ForegroundColor White
} else {
    Write-Host "`n❌ Test failed — password may not have reset. Try manually." -ForegroundColor Red
    Write-Host "   $test" -ForegroundColor Red
}

# Cleanup
Remove-Item $initFile -Force -ErrorAction SilentlyContinue
