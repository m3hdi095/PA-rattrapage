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

echo "### Generation des parametres TLS recommandes ..."
mkdir -p "$data_path/conf"
cat > "$data_path/conf/options-ssl-nginx.conf" << 'CONF'
ssl_session_cache shared:le_nginx_SSL:10m;
ssl_session_timeout 1440m;
ssl_session_tickets off;

ssl_protocols TLSv1.2 TLSv1.3;
ssl_prefer_server_ciphers off;

ssl_ciphers "ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA256";
CONF
docker run --rm -v "$(pwd)/$data_path/conf:/dest" alpine sh -c \
    "apk add --no-cache openssl >/dev/null && openssl dhparam -out /dest/ssl-dhparams.pem 2048"

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
