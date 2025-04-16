FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    default-mysql-client \
    sudo

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql zip

# Enable Apache modules
RUN a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create a non-root user with sudo access
RUN useradd -m devuser && echo "devuser ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/devuser

# Set proper permissions for Apache
RUN chown -R devuser:devuser /var/www/html && \
    chmod -R 755 /var/www/html

WORKDIR /var/www/html