# Lumen PHP Framework

# Set up
composer i
php artisan migrate:fresh --seed

# Run
php -S localhost:8000 -t public

# Set telegram web hook 
https://api.telegram.org/bot{TOKEN}/setWebhook?url={URL}
