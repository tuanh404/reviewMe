<?php

use Illuminate\Support\Facades\Route;

// Thay vì chỉ trả về View rỗng, ta gói theo quà (data) cho Front-end
Route::get('/', function () {
    $reviews = \App\Models\Review::where('is_approved', true)
        ->orderBy('created_at', 'desc')
        ->get();

    return view('welcome', ['initialReviews' => $reviews]);
});

Route::get('/admin', function (\Illuminate\Http\Request $request) {
    // 1. Rút tài khoản và mật khẩu từ file .env
    // (Nếu .env không có, nó sẽ lấy giá trị dự phòng '' và '')
    $username = env('ADMIN_USERNAME', '');
    $password = env('ADMIN_PASSWORD', '');

    // 2. Kiểm tra xem khách đã nhập đúng chưa
    if ($request->getUser() !== $username || $request->getPassword() !== $password) {
        return response('Khu vực cấm! Vui lòng quay xe.', 401, ['WWW-Authenticate' => 'Basic']);
    }

    return view('admin');
});

Route::get('/test-ai', function () {
    // Rút chìa khóa từ két sắt
    $apiKey = env('GEMINI_API_KEY');

    // Nếu quên chưa cấu hình
    if (!$apiKey) {
        return "BRO ƠI, CHƯA CÓ GEMINI_API_KEY TRONG FILE .ENV!";
    }

    // Bắn thẳng lên Google bằng cấu hình tối giản nhất
    $response = \Illuminate\Support\Facades\Http::withoutVerifying()->post(
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . $apiKey,
        [
            'contents' => [['parts' => [['text' => 'Chào bạn, trả lời tôi 1 từ thôi nhé.']]]]
        ]
    );

    // In toàn bộ lời ruột gan của Google ra màn hình
    return $response->json();
});
