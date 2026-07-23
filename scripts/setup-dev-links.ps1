<#
.SYNOPSIS
    Local dev helper — makes legacy `/public/...` asset URLs resolve under `php artisan serve`.

.DESCRIPTION
    The legacy blade pages reference their CSS/JS at `/public/frontEnd/...`, but
    `php artisan serve` serves from *inside* `public/`, so that extra `/public/`
    segment 404s and the pages render unstyled.

    This script creates a real `public/public/` folder containing a JUNCTION to each
    asset sub-folder (frontEnd, backEnd, css, js, images, ...). So a request for
    `/public/frontEnd/x` resolves to `public/public/frontEnd` -> (junction) -> `public/frontEnd`.

    IMPORTANT: it links each sub-folder individually and NEVER links `public/public`
    to `public` itself — a self-referential link creates an infinite `public/public/public/...`
    path that makes Vite's file-watcher crash (ELOOP). Per-folder links can't loop.

    Idempotent and safe to re-run. Dev-only; not needed in production (a real web server
    with docroot at the project root resolves `/public/...` natively).

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts\setup-dev-links.ps1
    # or via npm:  npm run dev:links
#>

$ErrorActionPreference = 'Stop'

# Project root = parent of this script's folder.
$projectRoot = Split-Path -Parent $PSScriptRoot
$publicDir   = Join-Path $projectRoot 'public'
$outerDir    = Join-Path $publicDir  'public'

if (-not (Test-Path $publicDir)) { throw "public/ not found at $publicDir" }

# Ensure the outer wrapper folder exists (a REAL directory, not a link).
if (-not (Test-Path $outerDir)) {
    New-Item -ItemType Directory -Path $outerDir | Out-Null
    Write-Host "created public/public/"
}

# Link every asset sub-folder EXCEPT 'build' (Vite's own output) and 'public' (would loop).
$exclude = @('build', 'public')
$linked = 0; $skipped = 0

Get-ChildItem -Path $publicDir -Directory | Where-Object { $exclude -notcontains $_.Name } | ForEach-Object {
    $link = Join-Path $outerDir $_.Name
    if (Test-Path $link) {
        $skipped++
    } else {
        New-Item -ItemType Junction -Path $link -Target $_.FullName | Out-Null
        Write-Host ("linked public/public/{0} -> public/{0}" -f $_.Name)
        $linked++
    }
}

Write-Host ""
Write-Host ("Done. {0} linked, {1} already present." -f $linked, $skipped) -ForegroundColor Green
