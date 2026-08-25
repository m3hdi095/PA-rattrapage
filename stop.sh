#!/bin/bash
set -e

echo "Arret des conteneurs..."
docker compose down

echo "Conteneurs arretes. Les donnees sont conservees (relance avec install.sh)."
