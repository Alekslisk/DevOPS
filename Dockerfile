# Используем официальный образ PHP 8.2 с FPM
FROM php:8.2-fpm as php

# Отключаем интерактивный режим для apt
ENV DEBIAN_FRONTEND=noninteractive

# Устанавливаем системные пакеты и PHP-расширения
RUN apt-get update && apt-get install -y \
    nginx \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    ca-certificates \  
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd

# Копируем код приложения
WORKDIR /var/www/html
COPY . .

# Устанавливаем зависимости Composer (без dev-пакетов для продакшена)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

RUN rm -f /etc/nginx/sites-enabled/default && \
    rm -f /etc/nginx/conf.d/default.conf

RUN rm -rf public/storage \
    && php artisan storage:link \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache


# Копируем конфиг Nginx (создай его в папке docker/nginx.conf)
# COPY docker/nginx.conf /etc/nginx/nginx.conf

# Скрипт запуска (запускает php-fpm и nginx)
# COPY docker/start.sh /start.sh
# RUN chmod +x /start.sh



EXPOSE 9000


# На будущее после локальных тестов если отдельно делаешь nginx не забудь менять 
CMD ["php-fpm"]