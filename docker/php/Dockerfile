FROM php:7.4-apache

# Install extensions
RUN docker-php-ext-install pdo_mysql

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Host
COPY ./docker/php/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Copy project
COPY . /var/www/html/

EXPOSE 80

CMD ["apache2-foreground"]
