<!DOCTYPE html>
<html lang="vi" class="bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReviewMe - Trạm Kiểm Duyệt (Admin)</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-8">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow-md">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">🛠️ Trạm Kiểm Duyệt Bóng (Admin)</h1>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-700">
                        <th class="p-3 border-b">ID</th>
                        <th class="p-3 border-b">Người gửi</th>
                        <th class="p-3 border-b w-1/3">Nội dung</th>
                        <th class="p-3 border-b">Cảm xúc</th>
                        <th class="p-3 border-b">Trạng thái</th>
                        <th class="p-3 border-b text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody id="reviews-table-body">
                    <tr><td colspan="6" class="p-4 text-center text-gray-500">Đang tải dữ liệu...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // 1. GỌI API LẤY TOÀN BỘ DANH SÁCH BÓNG
        async function loadReviews() {
            try {
                const response = await fetch('/api/admin/reviews', {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const reviews = await response.json();
                    renderTable(reviews);
                } else {
                    alert('Lỗi tải dữ liệu. API /api/admin/reviews đã chuẩn chưa bro?');
                }
            } catch (error) {
                console.error("Lỗi:", error);
            }
        }

        // 2. VẼ BẢNG RA MÀN HÌNH
        function renderTable(reviews) {
            const tbody = document.getElementById('reviews-table-body');
            tbody.innerHTML = '';

            if (reviews.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="p-4 text-center text-gray-500">Chưa có ai gửi bóng cả!</td></tr>';
                return;
            }

            reviews.forEach(rv => {
                const isApproved = rv.is_approved ? 
                    '<span class="px-2 py-1 bg-green-100 text-green-700 rounded-md text-xs font-bold">Đã lên sóng</span>' : 
                    '<span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-md text-xs font-bold">Đang chờ</span>';

                const approveBtn = !rv.is_approved ? 
                    `<button onclick="approveReview(${rv.id})" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded shadow-sm text-sm mr-2 transition">Duyệt ✅</button>` : '';

                const deleteBtn = `<button onclick="deleteReview(${rv.id})" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded shadow-sm text-sm transition">Xóa 🗑️</button>`;

                tbody.innerHTML += `
                    <tr class="hover:bg-gray-50 border-b">
                        <td class="p-3">#${rv.id}</td>
                        <td class="p-3 font-semibold">${rv.reviewer_name || 'Khách'}</td>
                        <td class="p-3 text-gray-600 text-sm">${rv.content}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 bg-pink-100 text-pink-700 rounded-full text-xs">${rv.tag || 'Khác'}</span>
                        </td>
                        <td class="p-3">${isApproved}</td>
                        <td class="p-3 text-center">${approveBtn} ${deleteBtn}</td>
                    </tr>
                `;
            });
        }

        // 3. GỌI API DUYỆT BÓNG
        async function approveReview(id) {
            if (!confirm('Bro chắc chắn muốn cho quả bóng này lên sóng chứ?')) return;
            try {
                const res = await fetch(`/api/admin/reviews/${id}/approve`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) loadReviews();
            } catch (error) {
                console.error("Lỗi:", error);
            }
        }

        // 4. GỌI API XÓA BÓNG
        async function deleteReview(id) {
            if (!confirm('Xóa vĩnh viễn quả bóng này nhé?')) return;
            try {
                const res = await fetch(`/api/admin/reviews/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) loadReviews();
            } catch (error) {
                console.error("Lỗi:", error);
            }
        }

        // Tự động tải dữ liệu khi mở trang
        document.addEventListener('DOMContentLoaded', loadReviews);
    </script>
</body>
</html>