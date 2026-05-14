FROM php:8.1-apache

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تثبيت امتدادات PHP الضرورية (لقاعدة البيانات وcURL وJSON وغيرها)
RUN apt-get update && apt-get install -y \
        libcurl4-openssl-dev \
        libssl-dev \
        libzip-dev \
        zip \
        unzip \
    && docker-php-ext-install pdo pdo_mysql curl json mbstring zip

# نسخ ملفات المشروع
COPY . /var/www/html/

# تشغيل Composer لتثبيت المكتبات (مع تجاهل متطلبات بيئة التطوير)
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader --no-interaction

# صلاحيات مجلد الرفع
RUN chown -R www-data:www-data /var/www/html/uploads

# تفعيل mod_rewrite
RUN a2enmod rewrite

EXPOSE 80
CMD ["apache2-foreground"]
