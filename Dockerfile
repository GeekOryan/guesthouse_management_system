# Use official PHP 8.2 with Apache
FROM php:8.2-apache

# Install the mysqli extension (Fixes the undefined function error)
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy all your project files into the container
COPY . /var/www/html/

# Change Apache's DocumentRoot to the 'public' directory
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]