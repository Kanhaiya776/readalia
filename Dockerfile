#
# NOTE: THIS DOCKERFILE IS GENERATED VIA "apply-templates.sh"
#
# PLEASE DO NOT EDIT IT DIRECTLY.
#

# from https://www.drupal.org/docs/system-requirements/php-requirements
FROM php:8.3-apache-bullseye

# install the PHP extensions we need
RUN set -eux; \
	\
	if command -v a2enmod; then \
		a2enmod rewrite headers ssl expires; \
	fi; \
	\
	savedAptMark="$(apt-mark showmanual)"; \
	\
	apt-get update; \
	apt-get install -y --no-install-recommends \
		libfreetype6-dev \
		libjpeg-dev \
		libpng-dev \
		libpq-dev \
		libwebp-dev \
		libzip-dev \
		git \
		default-mysql-client \
                ssl-cert \
                nano \
	; \
	\
	docker-php-ext-configure gd \
		--with-freetype \
		--with-jpeg=/usr \
		--with-webp \
	; \
	\
	docker-php-ext-install -j "$(nproc)" \
		gd \
		opcache \
		pdo_mysql \
		pdo_pgsql \
		zip \
	; \
	\
# reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
	apt-mark auto '.*' > /dev/null; \
	apt-mark manual $savedAptMark; \
	ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
		| awk '/=>/ { so = $(NF-1); if (index(so, "/usr/local/") == 1) { next }; gsub("^/(usr/)?", "", so); print so }' \
		| sort -u \
		| xargs -r dpkg-query -S \
		| cut -d: -f1 \
		| sort -u \
		| xargs -rt apt-mark manual; \
	\
	rm -rf /var/lib/apt/lists/*

# set recommended PHP.ini settings
RUN { \
                echo 'upload_max_filesize=20M'; \
           } > /usr/local/etc/php/conf.d/php_override.ini

# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=60'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /opt/drupal

RUN mkdir /opt/drupal/web
COPY ./project /opt/drupal

RUN set -eux; \
        export COMPOSER_HOME="$HOME/.config/composer"; \
#	export COMPOSER_HOME="$(mktemp -d)"; \
#	mkdir -p vendor/bin; \
#        chmod -R 777 vendor; \
      	composer install --no-scripts --ignore-platform-reqs --prefer-dist; \
#        chown -R www-data:www-data docroot/sites/default/files; \
#	chown -R www-data:www-data web/sites web/modules web/themes; \
	rmdir /var/www/html; \
	ln -sf /opt/drupal/web /var/www/html; \
	ln -sf /opt/drupal/vendor/drush/drush/drush /usr/local/bin/drush; \
        make-ssl-cert generate-default-snakeoil; \
        a2ensite default-ssl.conf; \
#        drush cr; \
	apt-get clean; \
	echo ${COMPOSER_HOME}

# vim:set ft=dockerfile:
