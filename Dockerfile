FROM php:8.1-apache

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تثبيت الحزم اللازمة
RUN apt-get update && apt-get install -y libzip-dev unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# نسخ ملفات المشروع
COPY . /var/www/html/

# تشغيل Composer
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader --no-interaction

# إنشاء مجلدات الرفع والسجلات (لأنها محذوفة من Git)
RUN mkdir -p /var/www/html/uploads/avatars /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html/uploads /var/www/html/logs

# تفعيل mod_rewrite
RUN a2enmod rewrite

EXPOSE 80
CMD ["apache2-foreground"]
