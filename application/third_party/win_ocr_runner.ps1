# Windows Native OCR Runner for Scanned/Vectorized PDFs & Images
param(
[Parameter(Mandatory=$true)]
[string]$FilePath,
[string]$FileType = '',
[int]$MaxPages = 30
)

[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

if (-not (Test-Path $FilePath)) {
Write-Error "File not found: $FilePath"
exit 1
}

Add-Type -AssemblyName "System.Runtime.WindowsRuntime"
$asTaskGeneric = [System.WindowsRuntimeSystemExtensions].GetMethods() | Where-Object { 
$_.Name -eq 'AsTask' -and $_.GetParameters().Count -eq 1 -and $_.GetParameters()[0].ParameterType.Name -eq 'IAsyncOperation`1' 
} | Select-Object -First 1

function Await-WinRt($asyncOp, $type) {
$method = $asTaskGeneric.MakeGenericMethod($type)
$task = $method.Invoke($null, @($asyncOp))
$task.Wait()
return $task.Result
}

[Windows.Storage.StorageFile, Windows.Foundation.UniversalApiContract, ContentType = WindowsRuntime] | Out-Null
[Windows.Graphics.Imaging.BitmapDecoder, Windows.Foundation.UniversalApiContract, ContentType = WindowsRuntime] | Out-Null
[Windows.Media.Ocr.OcrEngine, Windows.Foundation.UniversalApiContract, ContentType = WindowsRuntime] | Out-Null

$engine = [Windows.Media.Ocr.OcrEngine]::TryCreateFromUserProfileLanguages()
if ($null -eq $engine) {
$engine = [Windows.Media.Ocr.OcrEngine]::TryCreateFromLanguage([Windows.Globalization.Language]::new("en-US"))
}

function Do-OcrOnImage($imgPath) {
try {
$file = Await-WinRt ([Windows.Storage.StorageFile]::GetFileFromPathAsync($imgPath)) ([Windows.Storage.StorageFile])
$stream = Await-WinRt ($file.OpenAsync([Windows.Storage.FileAccessMode]::Read)) ([Windows.Storage.Streams.IRandomAccessStream])
$decoder = Await-WinRt ([Windows.Graphics.Imaging.BitmapDecoder]::CreateAsync($stream)) ([Windows.Graphics.Imaging.BitmapDecoder])
$bitmap = Await-WinRt ($decoder.GetSoftwareBitmapAsync()) ([Windows.Graphics.Imaging.SoftwareBitmap])
$result = Await-WinRt ($engine.RecognizeAsync($bitmap)) ([Windows.Media.Ocr.OcrResult])
return $result.Text
} catch {
return ""
}
}

$ext = [System.IO.Path]::GetExtension($FilePath).ToLower()
$isPdf = ($ext -eq '.pdf') -or ($FileType.ToLower() -eq 'pdf')

# Check magic bytes if extension is .tmp or generic
if (-not $isPdf) {
try {
$bytes = [System.IO.File]::ReadAllBytes($FilePath)
if ($bytes.Length -ge 4) {
$header = [System.Text.Encoding]::ASCII.GetString($bytes[0..3])
if ($header -eq '%PDF') {
$isPdf = $true
}
}
} catch {}
}

$tempDir = [System.IO.Path]::GetTempPath()
$sessionGuid = [System.Guid]::NewGuid().ToString()

if ($isPdf) {
# Check for pdftoppm.exe in project
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$popplerPpm = Join-Path $scriptDir "poppler-23.11.0-0\Library\bin\pdftoppm.exe"
if (-not (Test-Path $popplerPpm)) {
    $popplerPpm = "d:\xampp\htdocs\ibid\application\third_party\poppler-23.11.0-0\Library\bin\pdftoppm.exe"
}

if (Test-Path $popplerPpm) {
# pdftoppm works best when the file has a .pdf extension
$actualPdfPath = $FilePath
$tempPdfCopy = ""
if (-not ($FilePath.ToLower().EndsWith('.pdf'))) {
$tempPdfCopy = Join-Path $tempDir "input_${sessionGuid}.pdf"
Copy-Item -Path $FilePath -Destination $tempPdfCopy -Force
$actualPdfPath = $tempPdfCopy
}

$prefix = Join-Path $tempDir "ocr_${sessionGuid}_page"
& $popplerPpm -png -r 150 -f 1 -l $MaxPages "$actualPdfPath" "$prefix" 2>&1 | Out-Null

if ($tempPdfCopy -and (Test-Path $tempPdfCopy)) {
Remove-Item -Path $tempPdfCopy -Force -ErrorAction SilentlyContinue
}

$pageImages = Get-ChildItem -Path $tempDir -Filter "ocr_${sessionGuid}_page-*.png" | Sort-Object Name
$allPageTexts = @()
foreach ($img in $pageImages) {
$txt = Do-OcrOnImage $img.FullName
if ($txt.Trim().Length -gt 0) {
$allPageTexts += $txt.Trim()
}
Remove-Item -Path $img.FullName -Force -ErrorAction SilentlyContinue
}

if ($allPageTexts.Count -gt 0) {
$finalText = $allPageTexts -join "`n`n"
Write-Output $finalText
}
}
} else {
$txt = Do-OcrOnImage $FilePath
Write-Output $txt
}
