# Используем официальный образ PHP
FROM php:8.1-cli

# Устанавливаем необходимые расширения PHP
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Устанавливаем cron
RUN apt-get update && apt-get install -y cron

# Устанавливаем MySQL client
RUN apt-get update && apt-get install -y default-mysql-client

# Копируем исходный код приложения в контейнер
COPY . /var/www

# Устанавливаем рабочую директорию
WORKDIR /var/www

# Устанавливаем зависимости Composer
RUN composer install

# Устанавливаем Supervisor
RUN apt-get install -y supervisor

# Копируем конфигурацию Supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Копируем скрипт для настройки crontab
COPY setup-cron.sh /usr/local/bin/setup-cron.sh

# Делаем скрипт исполняемым
RUN chmod +x /usr/local/bin/setup-cron.sh

# Выполняем скрипт для настройки crontab
RUN /usr/local/bin/setup-cron.sh

# Убедитесь, что cron запускается при старте контейнера
RUN touch /var/log/laravel_tasks.log && chmod 0644 /var/log/laravel_tasks.log

# Команда для запуска Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]


