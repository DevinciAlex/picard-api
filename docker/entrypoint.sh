#!/bin/sh
set -eu

mkdir -p config/jwt var/cache var/log

if [ ! -f config/jwt/private.pem ] || [ ! -f config/jwt/public.pem ]; then
    php bin/console lexik:jwt:generate-keypair --skip-if-exists
fi

php bin/console cache:clear --env=prod
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

exec "$@"
