FROM php:8.2-apache
RUN apt-get update && apt-get install -y --no-install-recommends curl && rm -rf /var/lib/apt/lists/*
COPY observer/ /var/www/html/
RUN printf 'ServerTokens Prod\nServerSignature Off\n<Directory /var/www/html>\n Options -Indexes\n</Directory>\n' > /etc/apache2/conf-enabled/zz-obs.conf
