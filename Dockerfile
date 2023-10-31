# richarvey/nginx-php-fpmをベースとする
FROM richarvey/nginx-php-fpm:3.1.6

COPY . /application

WORKDIR /application

ADD conf/nginx-site.conf /etc/nginx/sites-available/default.conf
#ADD conf/nginx-site-ssl.conf /etc/nginx/sites-available/default-ssl.conf

RUN docker-php-ext-install bcmath

# Image config
ENV SKIP_COMPOSER 1
ENV WEBROOT /application/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Laravel config
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN composer install

RUN chown -Rf nginx:nginx ./

CMD ["/start.sh"]