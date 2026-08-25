$ErrorActionPreference = "Stop"

Write-Host "Verification de Docker..."
docker --version | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Docker n'est pas installe ou n'est pas dans le PATH." -ForegroundColor Red
    exit 1
}

Write-Host "Construction et demarrage des conteneurs (base, API, site)..."
docker compose up -d --build
if ($LASTEXITCODE -ne 0) {
    Write-Host "Echec du demarrage des conteneurs." -ForegroundColor Red
    exit 1
}

Write-Host "Attente que la base de donnees soit prete..."
$dbReady = $false
for ($i = 0; $i -lt 30; $i++) {
    $health = docker compose ps mysql --format json | ConvertFrom-Json
    if ($health.Health -eq "healthy") {
        $dbReady = $true
        break
    }
    Start-Sleep -Seconds 2
}
if (-not $dbReady) {
    Write-Host "La base de donnees n'a pas demarre a temps. Verifie 'docker compose logs mysql'." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "=== Creation du premier compte super_admin ===" -ForegroundColor Cyan
$email = Read-Host "Email"
$password = Read-Host "Mot de passe" -AsSecureString
$plainPassword = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto(
    [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($password)
)
$nom = Read-Host "Nom"
$prenom = Read-Host "Prenom"

docker compose exec api /server -create-super-admin `
    "-email=$email" "-password=$plainPassword" "-nom=$nom" "-prenom=$prenom"

if ($LASTEXITCODE -ne 0) {
    Write-Host "Echec de la creation du super admin (voir le message ci-dessus)." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "=== Installation terminee ===" -ForegroundColor Green
Write-Host "Site web      : http://localhost:8090"
Write-Host "API (debug)   : http://localhost:8082/health"
Write-Host "Connexion admin : http://localhost:8090/admin/index.php"
