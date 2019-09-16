FROM php-zendserver:7.0-php5.4

RUN apt-get update && apt-get install -y git

COPY ./overwrite_configuration.sh /

RUN rm -rf /var/www/html
RUN git clone https://github.com/CodingInfraviolet/Password-Generator.git /var/www/html

ENTRYPOINT /overwrite_configuration.sh /var/www/html/dbinfo.php && /usr/local/bin/run
