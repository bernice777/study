FROM php:8.2-apache

# mysqli 확장 + 헬스체크용 curl
RUN docker-php-ext-install mysqli \
 && apt-get update && apt-get install -y --no-install-recommends curl \
 && rm -rf /var/lib/apt/lists/*

# Apache 모듈/설정
RUN a2enmod rewrite headers
COPY docker/web/php.ini      /usr/local/etc/php/conf.d/zz-lab.ini
COPY docker/web/apache.conf  /etc/apache2/conf-enabled/zz-lab.conf

# 계측 키트(웹루트 밖) — auto_prepend 로 전역 로드
COPY docker/web/labkit.php   /opt/labkit/labkit.php

# 랜딩/헬스 (docroot 에 baked — 호스트 마운트로 노출하지 않음)
COPY www/                    /var/www/html/

# 로그 볼륨 소유권 교정 후 apache 기동
COPY docker/web/entrypoint.sh /usr/local/bin/lab-entrypoint
RUN chmod +x /usr/local/bin/lab-entrypoint

ENTRYPOINT ["lab-entrypoint"]
CMD ["apache2-foreground"]
