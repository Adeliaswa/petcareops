$ErrorActionPreference = 'Stop'
$bladePath = Join-Path (Get-Location).Path 'resources\views\booking\create.blade.php'
if (-not (Test-Path $bladePath)) { throw "Jalankan script dari root project Laravel. File Blade tidak ditemukan." }
$blade = Get-Content $bladePath -Raw
$pattern = '(?s)<a href="\{\{ route\(''home''\) \}\}"\s+class="booking-primary-button">\s*Lihat Appointment\s*</a>'
$replacement = '<a :href="bookingResult?.status_url || ''{{ route(''home'') }}''" class="booking-primary-button">Lihat Status Booking</a>'
$updated = [regex]::Replace($blade, $pattern, $replacement, 1)
if ($updated -eq $blade -and $blade -notmatch 'bookingResult\?\.status_url') {
    Write-Warning 'Tombol lama tidak ditemukan. Ubah tautan di .booking-success-card secara manual menjadi :href="bookingResult?.status_url".'
} elseif ($updated -ne $blade) {
    Set-Content -Path $bladePath -Value $updated -Encoding UTF8
    Write-Host 'Tombol hasil booking berhasil diarahkan ke status_url.'
} else {
    Write-Host 'Blade sudah memakai status_url.'
}
