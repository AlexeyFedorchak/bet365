while [ true ]
do
	php /var/www/html/artisan check:scores:live --verbose --no-interaction &
	sleep 1
done
