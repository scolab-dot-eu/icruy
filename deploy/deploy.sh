#!/bin/sh
mkdir -p /usr/local/www/apache24/

# Copiar  la aplicación a la carpeta apropiada de Apache
cp -a icr/camineriauy/camineriauyadmin /usr/local/www/apache24/
cp -a icr/camineriauy/visor /usr/local/www/apache24/

# Ajustar propietarios y permisos:
chown -R www:www  /usr/local/www/apache24/
find /usr/local/www/apache24/camineriauyadmin -type d -exec chmod 555 {} \;
find /usr/local/www/apache24/camineriauyadmin -type f -exec chmod 444 {} \;
find /usr/local/www/apache24/camineriauyadmin/bootstrap/cache -type d -exec chmod 755 {} \;
find /usr/local/www/apache24/camineriauyadmin/bootstrap/cache -type f -exec chmod 644 {} \;
find /usr/local/www/apache24/camineriauyadmin/storage -type d -exec chmod 755 {} \;
find /usr/local/www/apache24/camineriauyadmin/storage -type f -exec chmod 644 {} \;
