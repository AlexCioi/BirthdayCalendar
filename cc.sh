php composer install
php bin/console cache:clear
php bin/console doctrine:cache:clear-metadata
php bin/console doctrine:schema:update --dump-sql
php bin/console cache:warm

php bin/console assets:install public

php composer dumpautoload -o
# yarn run encore dev
