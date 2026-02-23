# ---------- Frontend build (Node 22) ----------
FROM node:22-bookworm AS nodebuild
WORKDIR /app

# Copiamos solo lo necesario primero para cache
COPY package*.json ./
RUN npm ci || npm install

# Copiamos el resto y construimos Vite
COPY . .
RUN npm run build


# ---------- PHP runtime (Laravel) ----------
FROM php:8.3-cli AS app
WORKDIR /app

# Dependencias del sistema + extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev \
  && docker-php-ext-install pdo_mysql zip \
  && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiamos el proyecto
COPY . .

# Copiamos assets ya construidos por Vite
COPY --from=nodebuild /app/public/build /app/public/build

# Instalar dependencias PHP (producción)
RUN composer install --no-dev --optimize-autoloader

# Opcional: optimizaciones (no fallar si no existe cache)
RUN php artisan config:cache || true \
 && php artisan route:cache || true \
 && php artisan view:cache || true

# Puerto (EasyPanel suele inyectar PORT)
EXPOSE 8080
CMD ["bash", "-lc", "php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
