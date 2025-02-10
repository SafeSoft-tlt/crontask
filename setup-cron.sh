#!/bin/bash
# Добавляем задачу в crontab
(crontab -l ; echo "* * * * * /usr/local/bin/php /var/www/artisan schedule:run >> /var/log/laravel_tasks.log 2>&1") | crontab -
