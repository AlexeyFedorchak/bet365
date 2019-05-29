while [ true ]
do
	sudo docker exec -it 147a72140ea6 php /var/www/html/artisan schedule:run
	sleep 60
done