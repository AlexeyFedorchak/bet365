while [ true ]
do
	php /var/www/html/artisan schedule:run
	sleep 60
done