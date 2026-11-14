#!/bin/sh
set -eu

port="${PORT:-10000}"
sed -i "s|Listen 80|Listen ${port}|" /etc/apache2/ports.conf
sed -i "s|<VirtualHost \\*:80>|<VirtualHost *:${port}>|" /etc/apache2/sites-available/000-default.conf

php artisan migrate --force
php artisan fitledger:bootstrap-production-accounts --from-environment || true

# Sync designated production account emails
php artisan tinker --execute="
    \App\Models\User::where('is_platform_admin', true)->update(['email' => 'adriandadoviernes@mail.com']);
    \App\Models\User::where('is_platform_admin', false)->update(['email' => '09262874496adrian@gmail.com']);
"

exec apache2-foreground
