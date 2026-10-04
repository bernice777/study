FROM php:5.6.40-apache

# The historical base predates newer signatures on Debian's archive metadata.
COPY --from=debian:bookworm-slim /usr/share/keyrings/debian-archive-keyring.gpg /etc/apt/trusted.gpg.d/current-debian.gpg

# The course applications use mysql_* and XE's Object class (PHP 5 only).
# Debian Stretch has moved to the official archive.
RUN printf 'deb http://archive.debian.org/debian stretch main\n' > /etc/apt/sources.list \
 && apt-get -o Acquire::Check-Valid-Until=false update \
 && apt-get install -y --no-install-recommends libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
 && docker-php-ext-install mysql mysqli mbstring gd \
 && rm -rf /var/lib/apt/lists/* \
 && a2enmod rewrite

COPY docker/legacy/archives/ /opt/ksj-sources/
COPY docker/legacy/php.ini /usr/local/etc/php/conf.d/zz-ksj.ini
COPY docker/legacy/apache.conf /etc/apache2/conf-enabled/zz-ksj.conf
COPY docker/legacy/entrypoint.sh /usr/local/bin/ksj-cms-entrypoint
RUN chmod +x /usr/local/bin/ksj-cms-entrypoint
ENTRYPOINT ["ksj-cms-entrypoint"]
CMD ["apache2-foreground"]
