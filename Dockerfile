
FROM php:8.1-apache


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-install pdo pdo_mysql

COPY . /var/www/html/


RUN cd /var/www/html && composer install --no-dev --optimize-autoloader


RUN chown -R www-data:www-data /var/www/html/uploads

RUN a2enmod rewrite

EXPOSE 80
CMD ["apache2-foreground"]
