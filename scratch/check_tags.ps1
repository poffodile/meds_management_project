
$content = Get-Content "resources\views\frontEnd\common\header.blade.php" -Raw
$openCount = ([regex]::Matches($content, '<\?php')).Count
$closeCount = ([regex]::Matches($content, '\?>')).Count
Write-Host "Open: $openCount, Close: $closeCount"
