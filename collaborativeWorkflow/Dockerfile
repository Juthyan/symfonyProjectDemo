# -------------------------------------------------------------------
# STAGE 1: Builder – Composer dependencies
# -------------------------------------------------------------------
    FROM composer:2.7 AS composer_builder
    WORKDIR /app
    
    # Copy only composer files first for better caching
    COPY composer.json composer.lock ./
    
    # Install production dependencies
    RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-scripts
    
    # Copy the rest of the source code
    COPY . .
    RUN composer dump-autoload --optimize
    
    
    # -------------------------------------------------------------------
    # STAGE 2: Runtime – PHP-FPM + Nginx
    # -------------------------------------------------------------------
    FROM php:8.3-fpm-alpine
    
    # Install system dependencies and PHP extensions
    RUN apk update && apk add --no-cache \
        nginx \
        bash \
        curl \
        postgresql-client \
        postgresql-dev \
        mariadb-client \
        mariadb-dev \
        libxml2-dev \
        su-exec \
        $PHPIZE_DEPS && \
        docker-php-ext-install -j$(nproc) pdo pdo_mysql pdo_pgsql && \
        apk del $PHPIZE_DEPS && \
        rm -rf /var/cache/apk/*
    
    WORKDIR /app
    
    # Copy app and vendor from builder
    COPY . /app
    COPY --from=composer_builder /app/vendor /app/vendor
    
    # Create necessary Symfony directories with proper permissions
    RUN mkdir -p /app/var/cache /app/var/log /app/public/build && \
        chown -R www-data:www-data /app/var /app/public/build /app/vendor
    
    # -------------------------------------------------------------------
    # Nginx & PHP-FPM configuration
    # -------------------------------------------------------------------
    COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
    COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
    COPY docker/php-fpm-cloudrun.conf /usr/local/etc/php-fpm.d/zz-app.conf
    
    # Copy entrypoint
    COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
    RUN chmod +x /usr/local/bin/entrypoint.sh
    
    # -------------------------------------------------------------------
    # Cloud Run configuration
    # -------------------------------------------------------------------
    EXPOSE 8080
    
    ENV APP_ENV=prod \
        PORT=8080
    
    USER root
    
    ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]