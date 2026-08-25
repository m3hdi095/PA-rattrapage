#!/bin/bash
set -e

domains=(nomorewaste.xyz www.nomorewaste.xyz)
email="moussa.mehdi@hotmail.com"
rsa_key_size=4096
data_path="./certbot"
compose="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

if [ -d "$data_path/conf/live/${domains[0]}" ]; then
    read -p "Des certificats existent deja pour ${domains[0]}. Continuer et les remplacer ? (o/N) " decision
    if [ "$decision" != "o" ]; then
        exit
    fi
fi

echo "### Telechargement des parametres TLS recommandes ..."
mkdir -p "$data_path/conf"
docker run --rm -v "$(pwd)/$data_path/conf:/dest" alpine sh -c \
    "wget -q -O /dest/options-ssl-nginx.conf https://raw.githubusercontent.com/certbot/certbot/master/certbot-nginx/certbot_nginx/_internal/tls_configs/options-ssl-nginx.conf && \
     wget -q -O /dest/ssl-dhparams.pem https://raw.githubusercontent.com/certbot/certbot/master/certbot/certbot/ssl-dhparams.pem"

echo "### Creation d'un certificat factice pour ${domains[0]} ..."
path="/etc/letsencrypt/live/${domains[0]}"
mkdir -p "$data_path/conf/live/${domains[0]}"
$compose run --rm --entrypoint "\
    openssl req -x509 -nodes -newkey rsa:$rsa_key_size -days 1 \
        -keyout '$path/privkey.pem' \
        -out '$path/fullchain.pem' \
        -subj '/CN=localhost'" certbot

echo "### Demarrage de nginx ..."
$compose up -d nginx

echo "### Suppression du certificat factice ..."
$compose run --rm --entrypoint "\
    rm -Rf /etc/letsencrypt/live/${domains[0]} && \
    rm -Rf /etc/letsencrypt/archive/${domains[0]} && \
    rm -Rf /etc/letsencrypt/renewal/${domains[0]}.conf" certbot

echo "### Demande du vrai certificat ..."
domain_args=""
for domain in "${domains[@]}"; do
    domain_args="$domain_args -d $domain"
done

$compose run --rm --entrypoint "\
    certbot certonly --webroot -w /var/www/certbot \
        --email $email \
        $domain_args \
        --rsa-key-size $rsa_key_size \
        --agree-tos \
        --non-interactive" certbot

echo "### Rechargement de nginx ..."
$compose exec nginx nginx -s reload

echo "### Demarrage du service de renouvellement automatique ..."
$compose up -d certbot

echo "Termine."
