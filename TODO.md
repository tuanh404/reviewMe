# TODO - Sửa ReviewController

## Bước 1: Nghiên cứu & đối chiếu
- [x] Đọc routes/api.php
- [x] Đọc migration reviews & ai_prompts
- [x] Kiểm tra nội dung app/Http/Controllers/ReviewController.php (hiện đang là HTML/JS sai chỗ)

## Bước 2: Chỉnh backend API
- [x] Viết lại app/Http/Controllers/ReviewController.php thành PHP controller
- [x] Thêm show route: GET /api/reviews/{id}
- [x] Tạo migration bảng review_likes lưu theo (review_id, session_id)
- [x] Implement index/store/show/like/approve/generateAiReview

## Bước 3: Test
- [ ] Chạy migrate
- [ ] Kiểm tra:
  - Gửi review -> pending
  - Vào detail -> owner xem được khi pending
  - Like toggle theo session -> likes_count cập nhật
  - Approve -> approved có thể xem công khai

