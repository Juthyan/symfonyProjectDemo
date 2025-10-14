#!/usr/bin/env sh
set -e

Ce script est l'ENTRYPOINT/CMD du conteneur.
Il démarre PHP-FPM en arrière-plan et Nginx en premier plan.
1. Démarrage de PHP-FPM en mode daemon (-D)
echo "Démarrage de PHP-FPM..."

Lancement de PHP-FPM, qui écoute sur le port 9000
php-fpm -D

2. Démarrage de Nginx en premier plan
echo "Démarrage de Nginx sur le port 8080..."

Le flag 'daemon off;' est CRUCIAL pour que le conteneur reste en vie
exec nginx -g "daemon off;"