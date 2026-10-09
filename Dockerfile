# Use official PHP 8.2 with Apache
FROM php:8.2-apache

# Install the mysqli extension
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite (good practice for clean URLs)
RUN a2enmod rewrite

# Set the working directory to the root of the project
WORKDIR /var/www/html

# Copy all your project files into the container
COPY . /var/www/html/

# Expose port 80 (Render will automatically map this)
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]