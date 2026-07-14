# 1. Nâng cấp thẳng lên PHP 8.3 để đồng bộ với môi trường của bro
FROM php:8.3-apache

# 2. Cài đặt các công cụ và tiện ích mở rộng cốt lõi
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl \
    libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql zip mbstring exif pcntl bcmath gd

# 3. Cài Node.js (để chạy Vite nén giao diện)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 4. Cài Composer (quản lý thư viện PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Đặt thư mục làm việc mặc định
WORKDIR /var/www/html

# 6. Copy toàn bộ code vào trong container
COPY . .

# 7. Cấu hình máy chủ Apache trỏ đúng vào thư mục public của Laravel
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# 8. Cài thư viện chuẩn xác (BỎ cờ ép buộc để Composer chạy an toàn)
ENV COMPOSER_MEMORY_LIMIT=-1
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 9. Cài Node và nén giao diện
RUN npm install && npm run build

# 10. Cấp quyền cho thư mục chứa file cache và log
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 11. Mở cổng mạng 80
EXPOSE 80

# 12. Lệnh khởi động: Tự động chạy Database Migration rồi bật web
CMD php artisan migrate --force && apache2-foreground