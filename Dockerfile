# Use official PHP 8.2 with Apache
FROM php:8.2-apache

# Install Composer and required system packages
RUN apt-get update && apt-get install -y zip unzip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /var/lib/apt/lists/*

# Install the mysqli extension
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy composer files first (this is a Docker best practice for faster builds)
COPY composer.json ./

# Install PHP dependencies (THIS creates the missing vendor folder!)
RUN composer install --no-dev --optimize-autoloader

# Copy all your project files into the container
COPY . /var/www/html/

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]