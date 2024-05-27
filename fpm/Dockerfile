# Use the official PHP image with PHP-FPM
FROM php:8.2-fpm

# Disable proxy cache
RUN echo 'Acquire::http::Pipeline-Depth 0;\nAcquire::http::No-Cache true;\nAcquire::BrokenProxy true;' > /etc/apt/apt.conf.d/99fixbadproxy

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    libpq-dev \
    libonig-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    zip \
    pcntl \
    sockets

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory to /var/www
WORKDIR /data

# Define volume
VOLUME /data

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
