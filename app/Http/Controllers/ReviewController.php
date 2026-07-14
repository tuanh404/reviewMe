<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReviewController extends Controller
{
  // 1. Lấy danh sách review
  public function index(Request $request)
  {
    $sessionId = $request->query('session_id');

    $reviews = Review::where('is_approved', true)
      ->orWhere('session_id', $sessionId)
      ->get();

    return response()->json($reviews);
  }

  // 2. Thêm bài review mới
  public function store(Request $request)
  {
    // Kiểm tra dữ liệu đầu vào
    $validated = $request->validate([
      'reviewer_name' => 'nullable|string',
      'content'       => 'required|string',
      'rating'        => 'integer|min:1|max:5',
      'session_id'    => 'required|string'
    ]);

    // Mặc định bài mới chưa được duyệt
    $validated['is_approved'] = false;
    $validated['likes_count'] = 0;

    // Lưu vào DB
    $review = Review::create($validated);

    // THÊM ĐOẠN NÀY ĐỂ BÁO CÁO LÊN TELEGRAM:
    try {
      $telegramToken = env('TELEGRAM_BOT_TOKEN');
      $chatId = env('TELEGRAM_CHAT_ID');

      if ($telegramToken && $chatId) {
        $message = "🚨 <b>CÓ NHẬN XÉT MỚI CHỜ DUYỆT!</b>\n\n";
        $message .= "👤 <b>Người gửi:</b> " . $review->reviewer_name . "\n";
        $message .= "🏷 <b>Cảm xúc:</b> " . $review->tag . "\n";
        $message .= "💬 <b>Nội dung:</b> " . $review->content . "\n\n";
        $message .= "👉 <a href='" . url('/admin') . "'>Vào Web duyệt ngay</a>";

        // Bắn tin nhắn (Ép timeout 3s để nếu cáp quang đứt, khách ở web cũng ko bị đợi lâu)
        \Illuminate\Support\Facades\Http::timeout(3)
          ->withoutVerifying()
          ->post("https://api.telegram.org/bot{$telegramToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
          ]);
      }
    } catch (\Exception $e) {
      // Có lỗi mạng gửi Telegram thì kệ nó, âm thầm bỏ qua để web vẫn chạy mượt
    }

    return response()->json($review, 201);
  }

  // 3. Thả tim
  public function like(Request $request, $id)
  {
    // Tìm quả bóng review
    $review = \App\Models\Review::find($id);
    if (!$review) {
      return response()->json(['error' => 'Không tìm thấy đánh giá'], 404);
    }

    // Bắt lấy cái mã ẩn danh của khách
    $sessionId = $request->input('session_id');
    if (!$sessionId) {
      return response()->json(['error' => 'Bắt buộc phải có session_id'], 400);
    }

    // Tạo ra một cái chìa khóa trí nhớ duy nhất (Ví dụ: review_1_liked_by_guest-xyz)
    $cacheKey = "review_{$id}_liked_by_{$sessionId}";

    // BẬT CHẾ ĐỘ CÔNG TẮC (TOGGLE)
    if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
      // NẾU TRONG TRÍ NHỚ ĐÃ CÓ -> TỨC LÀ ĐÃ LIKE RỒI -> RÚT LẠI TIM (UNLIKE)
      $review->decrement('likes_count');
      \Illuminate\Support\Facades\Cache::forget($cacheKey); // Xóa khỏi trí nhớ

      return response()->json([
        'message' => 'Đã rút lại tim (Unlike)',
        'likes_count' => $review->likes_count
      ], 200);
    } else {
      // NẾU TRÍ NHỚ CHƯA CÓ -> TỨC LÀ LẦN ĐẦU BẤM -> THẢ TIM (LIKE)
      $review->increment('likes_count');
      \Illuminate\Support\Facades\Cache::forever($cacheKey, true); // Khắc cốt ghi tâm vĩnh viễn

      return response()->json([
        'message' => 'Đã thả tim (Like)',
        'likes_count' => $review->likes_count
      ], 200);
    }
  }

  // 4. Admin duyệt bài
  public function approved($id)
  {
    $review = Review::findOrFail($id);
    $review->update(['is_approved' => true]);

    return response()->json(['message' => 'Duyệt bài thành công']);
  }

  public function generateAiReview(Request $request)
  {
    $keyword = $request->input('keyword', '');

    $promptText = "Hãy viết một lời nhận xét ngắn gọn (khoảng 2-3 câu), mang phong cách '{$keyword}', để khen ngợi một dự án web siêu mượt. Không dùng ngoặc kép.";

    try {
      $apiKey = trim(env('GEMINI_API_KEY'));

      // GỌI LÊN MÁY CHỦ GOOGLE (Đã nới lỏng cho AI suy nghĩ trong 60 giây)
      $response = \Illuminate\Support\Facades\Http::timeout(60)
        ->withoutVerifying()
        ->post(
          'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . $apiKey,
          [
            'contents' => [['parts' => [['text' => $promptText]]]]
          ]
        );

      // Nếu Google phản hồi thành công
      if ($response->successful()) {
        $data = $response->json();
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
          $aiText = $data['candidates'][0]['content']['parts'][0]['text'];
          return response()->json(['generated_content' => trim($aiText)], 200);
        }
      }

      // Nếu nó trả mã 200 nhưng không có chữ bên trong -> Nhảy xuống Kế hoạch B
      throw new \Exception("Google không trả về chữ");
    } catch (\Exception $e) {
      // KẾ HOẠCH B: AI LỖI / CHẬM -> BỐC CÂU CÓ SẴN ĐỂ CHỮA CHÁY
      $backupTexts = [
        "Hài hước" => ["Trải nghiệm mượt đến mức tôi tưởng chuột của mình tự chạy!", "Web xịn thế này chắc Dev phải truyền thái y mấy lần!"],
        "Chuyên nghiệp" => ["Cấu trúc dự án rất rõ ràng, logic xử lý mượt mà và giao diện chỉn chu."],
        "Quạt" => ["Thực sự ấn tượng! Mình chính thức trở thành fan cứng của dự án này."],
        "Động viên" => ["Bạn đang đi rất đúng hướng, hãy tiếp tục phát huy nhé!"]
      ];

      // Tìm câu theo tag, nếu không có tag thì lấy câu chung chung
      $fallbackPool = isset($backupTexts[$keyword]) ? $backupTexts[$keyword] : [
        "Dự án rất ấn tượng, giao diện đẹp và các chức năng hoạt động cực kỳ trơn tru."
      ];

      $randomFallbackText = $fallbackPool[array_rand($fallbackPool)];

      // Trả về câu dự phòng nhưng báo mã 200 (FE vẫn nghĩ là AI tự viết)
      return response()->json(['generated_content' => $randomFallbackText], 200);
    }
  }

  // ==========================================
  // CÁC HÀM XỬ LÝ DÀNH CHO TRANG ADMIN
  // ==========================================

  public function getAdminReviews()
  {
    // Lấy tất tần tật bóng, xếp mới nhất lên đầu
    $reviews = \App\Models\Review::orderBy('created_at', 'desc')->get();
    return response()->json($reviews);
  }

  public function approve($id)
  {
    $review = \App\Models\Review::find($id);

    if ($review) {
      $review->is_approved = true;
      $review->save();
      return response()->json(['message' => 'Đã cho bóng lên sóng thành công!']);
    }

    return response()->json(['error' => 'Không tìm thấy quả bóng này!'], 404);
  }

  public function destroy($id)
  {
    $review = \App\Models\Review::find($id);

    if ($review) {
      $review->delete();
      return response()->json(['message' => 'Đã nổ bóng thành công!']);
    }

    return response()->json(['error' => 'Không tìm thấy quả bóng này!'], 404);
  }
}
