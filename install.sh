#!/bin/bash
set -e

echo "Verification de Docker..."
if ! command -v docker &> /dev/null; then
    echo "Docker n'est pas installe ou n'est pas dans le PATH." >&2
    exit 1
fi

echo "Construction et demarrage des conteneurs (base, API, site)..."
docker compose up -d --build

echo "Attente que la base de donnees soit prete..."
ready=false
for i in $(seq 1 30); do
    status=$(docker compose ps mysql --format json | grep -o '"Health":"[^"]*"' | cut -d'"' -f4)
    if [ "$status" = "healthy" ]; then
        ready=true
        break
    fi
    sleep 2
done
if [ "$ready" != "true" ]; then
    echo "La base de donnees n'a pas demarre a temps. Verifie 'docker compose logs mysql'." >&2
    exit 1
fi

echo ""
echo "=== Creation du premier compte super_admin ==="
read -rp "Email : " email
read -rsp "Mot de passe : " password
echo ""
read -rp "Nom : " nom
read -rp "Prenom : " prenom

docker compose exec api /server -create-super-admin \
    "-email=$email" "-password=$password" "-nom=$nom" "-prenom=$prenom"

echo ""
echo "=== Installation terminee ==="
echo "Site web        : http://localhost:8090"
echo "API (debug)     : http://localhost:8082/health"
echo "Connexion admin : http://localhost:8090/admin/index.php"
