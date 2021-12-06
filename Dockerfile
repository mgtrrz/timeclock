FROM php:7.4-apache-buster

# Copy application source
COPY ./ /var/www/html
RUN chown -R www-data:www-data /var/www

CMD ["apache2-foreground"]
