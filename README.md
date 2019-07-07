# bet365
parsing-api-sync bet system
install docker (check google...)

docker:
 - start PHP+APACHE+MYSQL {sudo} docker-compose up
 - stop: {sudo} docker ps => check the containers id
 - - run {sudo} docker stop SPECIFIC_CONTAINER_ID

curl -L https://github.com/laravel/laravel/archive/v5.3.16.tar.gz | tar xz
https://medium.com/@shakyShane/laravel-docker-part-1-setup-for-development-e3daaefaf3c
https://www.ionos.com/community/hosting/php/install-and-use-php-composer-on-ubuntu-1604/

What is this project about:

This is build system for processing api every minute using bash that is running from cron. This bash initing laravel cron command which manage all the laravel commands. In current web version there is only one command that is running every minute check new updates with using bet 365 api about new coefficients and send telegram message to users which are subscribed to the bot with specific telegram key. All keys are saved in .env file are not provided here.

This system is completely web-application, this is not web-site or something like that. This an example of API sync system with direct communication with every user.