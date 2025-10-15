# Dockerfile para Nginx
FROM nginx:alpine

# Copiar configuração customizada do Nginx
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Definir diretório de trabalho
WORKDIR /var/www/html

EXPOSE 80
