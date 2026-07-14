<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ReviewController;

// Nhóm Route Public (Ai cũng vào được)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Nhóm Route Private (Bắt buộc phải có Token đăng nhập mới được gọi)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- SAU NÀY LÀM DỰ ÁN MỚI THÌ THÊM ROUTE VÀO DƯỚI DÒNG NÀY ---


});
// 1. Lấy danh sách review để rớt bong bóng (Chỉ lấy bài đã duyệt HOẶC bài của chính người vừa đăng)
Route::get('/reviews', [ReviewController::class, 'index']);

// 2. Người dùng gửi review mới (Mặc định is_approved = false)
Route::post('/reviews', [ReviewController::class, 'store']);

// 3. Người dùng thả tim vào bong bóng làm nó to lên
Route::post('/reviews/{id}/like', [ReviewController::class, 'like']);

// 4. Admin duyệt bài (Tạm thời mở tự do để bạn dễ test, dự án sau ta sẽ khóa bằng Token)
Route::post('/admin/reviews/{id}/approve', [ReviewController::class, 'approve']);

// API gọi AI sinh bài review tự động
Route::post('/generate-review', [ReviewController::class, 'generateAiReview']);

// --- KHU VỰC API DÀNH CHO ADMIN ---
// 1. Lấy toàn bộ bóng (cả chưa duyệt và đã duyệt)
Route::get('/admin/reviews', [\App\Http\Controllers\ReviewController::class, 'getAdminReviews']);

// 2. Duyệt bóng
Route::post('/admin/reviews/{id}/approve', [\App\Http\Controllers\ReviewController::class, 'approve']);

// 3. Xóa bóng
Route::delete('/admin/reviews/{id}', [\App\Http\Controllers\ReviewController::class, 'destroy']);