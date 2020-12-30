#!/bin/bash

#before run make sure u add permission to this file : like sudo chmod +x ./start.sh

composer dump-autoload
php artisan migrate
php artisan passport:install
php artisan db:seed
php artisan key:generate
