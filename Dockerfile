FROM php:8.0-apache

WORKDIR /var/www/html

COPY . .

RUN apt-get update && \
    apt-get install -y \
    git \
    unzip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP extensions required by your application
RUN docker-php-ext-install pdo pdo_mysql

# Install application dependencies using Composer
RUN composer install --no-interaction --optimize-autoloader

# Set up Apache virtual host

RUN a2enmod rewrite

# Start Apache server
CMD ["apache2-foreground"]
