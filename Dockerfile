FROM php:8.3-apache

# Habilitar extensión mysqli
RUN docker-php-ext-install mysqli

# Crear carpeta uploads y asignar permisos
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod 775 /var/www/html/uploads

# Copiar archivos del proyecto
COPY . /var/www/html/

# Dar permisos correctos al resto
RUN chown -R www-data:www-data /var/www/html

# Railway usa la variable $PORT, Apache necesita escuchar en ese puerto
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

EXPOSE 80
