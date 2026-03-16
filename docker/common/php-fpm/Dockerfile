# Stage 1: Build environment and Composer dependencies
FROM php:8.4-fpm AS builder

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    unzip \
    libonig-dev \
    libssl-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    intl \
    zip \
    bcmath \
    gd \
    pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy the entire Laravel application code into the container
# We copy it FIRST so that any existing storage structure is brought in
COPY . /var/www/html

# Ensure storage directory and its children exist explicitly
# We remove any existing storage entry first because it might be a symlink
# to a path outside the container context, which blocks mkdir.
RUN rm -rf storage && mkdir -p storage/app/public \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/testing \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && chmod -R 775 storage

# Install Composer and dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist

# Stage 2: Production environment
FROM php:8.4-fpm AS production

# Install only runtime libraries needed in production
RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libpq-dev \
    libfcgi-bin \
    procps \
    && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copy PHP extensions from the builder stage
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --from=builder /usr/local/bin/docker-php-ext-* /usr/local/bin/

# Download and install php-fpm health check script
RUN curl -o /usr/local/bin/php-fpm-healthcheck \
    https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
    && chmod +x /usr/local/bin/php-fpm-healthcheck

# Copy the initialization script
COPY ./docker/production/php-fpm/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set working directory BEFORE copying files
WORKDIR /var/www/html

# Copy the initial storage structure and the app
# Using absolute paths to avoid any ambiguity
COPY --from=builder /var/www/html /var/www/html

# Create a pristine copy of storage for volume initialization
RUN cp -rp /var/www/html/storage /var/www/html/storage-init

# Use the recommended production PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Ensure correct permissions
RUN chown -R www-data:www-data /var/www/html

# Switch to the non-privileged user to run the application
USER www-data

# Change the default command to run the entrypoint script
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
