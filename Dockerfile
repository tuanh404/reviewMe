# 1. Dùng hệ điều hành nền có sẵn PHP 8.2 và máy chủ Apache
FROM php:8.2-apache

# 2. Cài đặt các công cụ cần thiết cho Laravel và Node.js
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl \
    && docker-php-ext-install pdo_mysql zip

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

# 8. Tự động cài đặt thư viện và nén giao diện
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# 9. Cấp quyền cho thư mục chứa file cache và log
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 10. Mở cổng mạng 80
EXPOSE 80

# 11. Lệnh khởi động: Tự động chạy Database Migration rồi bật web
CMD php artisan migrate --force && apache2-foreground