FROM php:8.1-apache

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تثبيت الحزم اللازمة لـ zip و pdo_mysql
RUN apt-get update && apt-get install -y libzip-dev unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# نسخ ملفات المشروع
COPY . /var/www/html/

# تشغيل Composer
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader --no-interaction

# صلاحيات
RUN chown -R www-data:www-data /var/www/html/uploads

# تفعيل mod_rewrite
RUN a2enmod rewrite

EXPOSE 80
CMD ["apache2-foreground"]
