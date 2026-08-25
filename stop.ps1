Write-Host "Arret des conteneurs..."
docker compose down

if ($LASTEXITCODE -eq 0) {
    Write-Host "Conteneurs arretes. Les donnees sont conservees (relance avec install.ps1)." -ForegroundColor Green
} else {
    Write-Host "Echec de l'arret des conteneurs." -ForegroundColor Red
    exit 1
}
