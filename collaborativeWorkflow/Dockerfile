# --- STAGE 1: BUILD (Compiles extensions and installs dependencies) ---
    FROM php:8.3-fpm-alpine AS build

    # Set up environment variables
    ENV SYMFONY_ENV=prod
    
    # Install system dependencies required for compiling PHP extensions (g++, make, postgresql-dev, icu-dev).
    # icu-dev is needed here to compile the intl PHP extension.
    RUN apk add --no-cache \
        git \
        unzip \
        g++ \
        make \
        autoconf \
        openssl-dev \
        postgresql-dev \
        libpq-dev \
        icu-dev \
        # Clean up after installation to keep the layer small
        && rm -rf /var/cache/apk/*
    
    # Install and compile required PHP extensions. This generates pdo_pgsql.so, intl.so, etc.
    RUN docker-php-ext-install \
        pdo \
        pdo_pgsql \
        intl \
        opcache
    
    # Copy Composer executable
    COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
    
    # Set working directory inside the container
    WORKDIR /app
    
    # Copy dependency files and install Composer dependencies
    COPY composer.json composer.lock ./
    COPY . /app
    
    # CRITICAL FIX (directory existence): Ensure required directories exist for permissions and runtime
    RUN mkdir -p var/cache var/log public/uploads
    
    # Install Composer dependencies (production environment, no dev packages)
    RUN rm -rf var/cache/*
    RUN APP_ENV=${SYMFONY_ENV} composer install --no-dev --prefer-dist --optimize-autoloader
    
    # Set up the custom entrypoint script
    COPY entrypoint.sh /usr/local/bin/entrypoint.sh
    RUN chmod +x /usr/local/bin/entrypoint.sh
    
    # Final cache clear
    RUN php bin/console cache:clear --env=${SYMFONY_ENV} --no-debug
    
    
    # --- STAGE 2: FINAL (Lightweight runtime image) ---
    FROM php:8.3-fpm-alpine
    
    # FIX: Install ONLY the necessary runtime libraries.
    # 1. postgresql-client & libpq for connecting to Postgres.
    # 2. icu-libs is now added to provide libicuio.so.76 needed by the intl PHP extension.
    RUN apk add --no-cache \
        postgresql-client \
        libpq \
        icu-libs
    
    # Set working directory
    WORKDIR /app
    
    # Copy the ENTIRE built application from the 'build' stage
    COPY --from=build /app /app
    
    # CRITICAL FIX 1: Explicitly copy PHP extension configuration files (.ini files).
    COPY --from=build /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
    
    # CRITICAL FIX 2: Explicitly copy PHP extension library files (.so files).
    COPY --from=build /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
    
    # Copy the entrypoint script
    COPY --from=build /usr/local/bin/entrypoint.sh /usr/local/bin/entrypoint.sh
    
    # Set the correct permissions
    RUN chown -R www-data:www-data var public/uploads var/log
    
    USER www-data
    
    ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
    EXPOSE 9000
    CMD ["php-fpm"]
    