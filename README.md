## Important!
jika mendapat error
Composer require runs out of memory. PHP Fatal error: Allowed memory size
ketika install laravel/passport

maka coba ubah memory limit php di php.ini menjadi : 
- memory limit = -1 (option 1)
- php composer.phar COMPOSER_MEMORY_LIMIT=-1 require laravel/passport (option2)


dan jika mendapatkan error 
[Composer\Downloader\TransportException]
  The "https://packagist.phpindonesia.id/p/provider-2020-01%246a2c2d17cda9f275893297acfedfd228b0db8cb4d68be0e098350687ab351b04.json" file could not be downloaded (HTTP/1.1 404 Not Found)

maka coba update composer ke version 2
- composer self-update --update-keys
- composer self-update


## How to install
enter the root project
run `` composer install `` on your terminal

run executable script in root project
`` ./start.sh ``
for running :
- composer dump-autoload
- php artisan migrate
- php artisan passport:install
- php artisan db:seed
- php artisan key:generate

before execute script `` ./start.sh `` make sure for running `` sudo chmod +x ./start.sh `` to add permission exec file.

Anyway Environtment yang saya gunakan adalah :
- PHP 7.3.5
- Mysql version 5.7

## Postman Collection
Postman collection sudah saya taruh di root project dengan nama file `` test-tonase.postman_collection.json `` .
