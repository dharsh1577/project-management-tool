# Use official PHP image with Apache
FROM php:8.2-apache

# Copy all project files to container
COPY . /var/www/html/

# Enable Apache mod_rewrite (important for routing)
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
