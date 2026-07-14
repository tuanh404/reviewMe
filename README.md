# 🎈 ReviewMe - Khu Vườn Nhận Xét Tương Tác

Một ứng dụng web thú vị cho phép người dùng để lại lời nhận xét và nhìn chúng rơi xuống màn hình dưới dạng những quả bóng vật lý sinh động. Tích hợp AI hỗ trợ viết lời khen và hệ thống duyệt bài tự động báo cáo qua điện thoại.

## ✨ Tính năng nổi bật

*   **Hiệu ứng vật lý chân thực:** Ứng dụng `Matter.js` để biến các lời nhận xét thành bóng rơi rớt, va chạm và tương tác mượt mà trên giao diện.
*   **Trợ lý AI Gemini:** Tích hợp API Google Gemini 1.5 Flash. Người dùng chỉ cần chọn cảm xúc (Hài hước, Chuyên nghiệp...), AI sẽ tự động sinh ra lời nhận xét cực chất. Có cơ chế Fallback (Dự phòng) đảm bảo web không bao giờ lỗi khi AI bận.
*   **Thông báo Bot Telegram:** Hệ thống tự động bắn thông báo tức thì về điện thoại Admin mỗi khi có người gửi bóng mới.
*   **Trạm kiểm duyệt an toàn:** Khu vực Admin được bảo vệ bằng cơ chế HTTP Basic Auth ẩn danh. Chỉ những quả bóng được Admin duyệt mới được phép hiển thị ra ngoài (Server-Side Injection).

## 🛠️ Công nghệ sử dụng

*   **Back-end:** Laravel 11, MySQL
*   **Front-end:** Vanilla JS, TailwindCSS, Matter.js
*   **Tích hợp API:** Google Gemini API, Telegram Bot API

## 🚀 Hướng dẫn cài đặt (Chạy Local)

**1. Tải dự án và cài đặt thư viện**
git clone https://github.com/ten-tai-khoan-cua-ban/reviewme.git
cd reviewme
composer install
npm install

**2. Cấu hình môi trường**
Tạo file `.env` (hoặc copy từ `.env.example`) và cập nhật các thông số bảo mật:

# Kết nối cơ sở dữ liệu
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ten_database_cua_ban
DB_USERNAME=root
DB_PASSWORD=

# Phân quyền Admin
ADMIN_USERNAME=admin
ADMIN_PASSWORD=mat_khau_admin

# API Keys
GEMINI_API_KEY=chia_khoa_google
TELEGRAM_BOT_TOKEN=token_bot_telegram
TELEGRAM_CHAT_ID=id_telegram_cua_ban

**3. Khởi tạo Database và Chạy máy chủ**
php artisan key:generate
php artisan migrate
php artisan optimize:clear

**4. Khởi động Web**
Mở 2 tab Terminal và chạy song song 2 lệnh:
npm run dev        # Khởi động Vite (Front-end)
php artisan serve  # Khởi động máy chủ ảo PHP (Back-end)

👉 Truy cập trang khách: http://localhost:8000
👉 Truy cập trang Admin: http://localhost:8000/admin
