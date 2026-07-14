# 1. Dùng hệ điều hành nền có sẵn PHP 8.2 và máy chủ Apache
FROM php:8.2-apache

# 2. Cài đặt đầy đủ các gói mở rộng (extensions) mà Laravel yêu cầu
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

# 7. Cấu hình máy chủ Apache
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# 8. Bơm RAM vô hạn cho Composer và cài thư viện (bỏ qua rào cản nền tảng)
ENV COMPOSER_MEMORY_LIMIT=-1
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts

# 9. Cài Node và nén giao diện
RUN npm install && npm run build

# 10. Cấp quyền cho thư mục chứa file cache và log
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 11. Mở cổng mạng 80
EXPOSE 80

# 12. Lệnh khởi động
CMD php artisan migrate --force && apache2-foreground