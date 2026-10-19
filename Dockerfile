FROM php:8.2-apache

# Install Composer and system packages
RUN apt-get update && apt-get install -y zip unzip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /var/lib/apt/lists/*

# Install mysqli
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy composer files and install dependencies
COPY composer.json ./
RUN composer install --no-dev --optimize-autoloader

# Copy all project files
COPY . /var/www/html/

# EXPLICITLY force Apache's DocumentRoot to the project root
RUN echo "DocumentRoot /var/www/html" > /etc/apache2/sites-available/000-default.conf

EXPOSE 80
CMD ["apache2-foreground"]