<#
.SYNOPSIS
    Script de Automatización de Lanzamientos y Versionado Semántico (SemVer) para SGEN-Support.

.DESCRIPTION
    Realiza las verificaciones previas de calidad (tests y build), actualiza las versiones
    en los package.json de Desktop y Backend, actualiza el CHANGELOG.md, crea el commit
    convencional y genera la etiqueta Git (tag) que activa el flujo de CI/CD para compilación
    y distribución con actualización automática (electron-updater).

.PARAMETER Version
    Versión explícita a lanzar en formato SemVer (ej. 1.1.0).

.PARAMETER Bump
    Incremento automático de versión: 'patch', 'minor' o 'major'.

.EXAMPLE
    .\scripts\release.ps1 -Bump minor
    .\scripts\release.ps1 -Version 1.2.0
#>

[CmdletBinding()]
param (
    [string]$Version,
    [ValidateSet('patch', 'minor', 'major')]
    [string]$Bump = 'patch'
)

$ErrorActionPreference = 'Stop'
$RootDir = Split-Path -Parent $PSScriptRoot
$DesktopPkgPath = Join-Path $RootDir "desktop\package.json"
$BackendPkgPath = Join-Path $RootDir "sgen-backend\package.json"
$ChangelogPath = Join-Path $RootDir "CHANGELOG.md"

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " 🚀 SGEN-Support - Automated Enterprise Release Manager " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

# 1. Leer versión actual de desktop/package.json
if (-not (Test-Path $DesktopPkgPath)) {
    throw "No se encontró $DesktopPkgPath"
}

$desktopPkg = Get-Content $DesktopPkgPath -Raw | ConvertFrom-Json
$currentVersion = $desktopPkg.version
Write-Host "📌 Versión actual: v$currentVersion" -ForegroundColor Yellow

# 2. Calcular nueva versión si no fue suministrada
if ([string]::IsNullOrWhiteSpace($Version)) {
    $parts = $currentVersion.Split('.')
    [int]$major = $parts[0]
    [int]$minor = $parts[1]
    [int]$patch = $parts[2]

    switch ($Bump) {
        'major' { $major++; $minor = 0; $patch = 0 }
        'minor' { $minor++; $patch = 0 }
        'patch' { $patch++ }
    }
    $targetVersion = "$major.$minor.$patch"
} else {
    $targetVersion = $Version.TrimStart('v')
}

Write-Host "🎯 Nueva versión a desplegar: v$targetVersion" -ForegroundColor Green

# 3. Quality Gate: Pruebas Backend
Write-Host "`n🧪 [Quality Gate] Ejecutando pruebas automatizadas..." -ForegroundColor Cyan
Push-Location (Join-Path $RootDir "sgen-backend")
try {
    php artisan test --ansi
    if ($LASTEXITCODE -ne 0) {
        throw "Las pruebas automatizadas de PHP fallaron. Lanzamiento abortado."
    }
    Write-Host "✅ Pruebas de PHP aprobadas." -ForegroundColor Green

    Write-Host "`n📦 [Quality Gate] Verificando compilación de Vite..." -ForegroundColor Cyan
    npm run build
    if ($LASTEXITCODE -ne 0) {
        throw "La compilación de frontend falló. Lanzamiento abortado."
    }
    Write-Host "✅ Compilación de frontend exitosa." -ForegroundColor Green
} finally {
    Pop-Location
}

# 4. Actualizar package.json en Desktop
Write-Host "`n📝 Actualizando archivos de configuración..." -ForegroundColor Cyan
$desktopPkg.version = $targetVersion
$desktopPkgJson = ConvertTo-Json $desktopPkg -Depth 10
Set-Content -Path $DesktopPkgPath -Value $desktopPkgJson -Encoding utf8
Write-Host "  ✓ Actualizado desktop/package.json -> $targetVersion"

# 5. Actualizar package.json en sgen-backend
if (Test-Path $BackendPkgPath) {
    $backendPkg = Get-Content $BackendPkgPath -Raw | ConvertFrom-Json
    $backendPkg.version = $targetVersion
    $backendPkgJson = ConvertTo-Json $backendPkg -Depth 10
    Set-Content -Path $BackendPkgPath -Value $backendPkgJson -Encoding utf8
    Write-Host "  ✓ Actualizado sgen-backend/package.json -> $targetVersion"
}

# 6. Actualizar CHANGELOG.md si existe
$today = (Get-Date).ToString("yyyy-MM-dd")
if (Test-Path $ChangelogPath) {
    $changelogContent = Get-Content $ChangelogPath -Raw
    if ($changelogContent -match "## \[Unreleased\]") {
        $replacement = "## [Unreleased]`n`n## [$targetVersion] - $today"
        $newChangelog = $changelogContent -replace "## \[Unreleased\]", $replacement
        Set-Content -Path $ChangelogPath -Value $newChangelog -Encoding utf8
        Write-Host "  ✓ Actualizado CHANGELOG.md con la sección [$targetVersion] - $today"
    }
}

# 7. Git Commit & Tag
Push-Location $RootDir
try {
    Write-Host "`n🔖 Creando Git Commit y Tag..." -ForegroundColor Cyan
    git add desktop/package.json sgen-backend/package.json CHANGELOG.md
    git commit -m "chore(release): prepare v$targetVersion"
    git tag -a "v$targetVersion" -m "Release v$targetVersion"
    Write-Host "✅ Commit y Tag 'v$targetVersion' creados exitosamente." -ForegroundColor Green

    Write-Host "`n==========================================================" -ForegroundColor Yellow
    Write-Host "🎉 ¡Lanzamiento v$targetVersion preparado con éxito!" -ForegroundColor Yellow
    Write-Host "Para iniciar la compilación y distribución automática ejecuta:" -ForegroundColor White
    Write-Host "  git push origin main --tags" -ForegroundColor Green
    Write-Host "==========================================================" -ForegroundColor Yellow
} finally {
    Pop-Location
}
