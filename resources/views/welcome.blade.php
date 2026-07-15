<!DOCTYPE html>
<html lang="vi" class="bg-[#dff7ef]">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="description" content="ReviewMe — gửi lời nhận xét dưới dạng những quả bóng tương tác.">
    <meta name="theme-color" content="#dff7ef">
    <title>ReviewMe — Khu vườn lời nhận xét</title>

    <!-- 1. Bơm dữ liệu từ Laravel vào trước tiên -->
    <script>
        window.SERVER_REVIEWS = @json($initialReviews);
    </script>

    <!-- 2. Gọi thư viện ngoài -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/matter-js@0.20.0/build/matter.min.js"></script>

    <!-- 3. Cuối cùng mới gọi app.js (vì nó cần 2 thứ ở trên) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <main class="flex h-dvh min-h-[680px] flex-col overflow-hidden bg-mint-100">
        <!-- Khu vực điều khiển 30% phía trên -->
        <header
            class="relative z-30 flex h-[30dvh] min-h-[190px] shrink-0 items-center justify-center border-ink/10 bg-mint-100 px-4">
            <div class="flex flex-col items-center gap-4 text-center">
                <div class="flex items-center gap-2 text-ink/70">
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true">
                        <path d="m12 3-1.7 4.3L6 9l4.3 1.7L12 15l1.7-4.3L18 9l-4.3-1.7L12 3Z"></path>
                        <path d="m5 16-.9 2.1L2 19l2.1.9L5 22l.9-2.1L8 19l-2.1-.9L5 16Z"></path>
                    </svg>
                    <span class="text-sm font-extrabold tracking-widest uppercase">
                        ReviewMe
                    </span>
                </div>

                <h1 class="text-balance font-serif text-2xl font-bold md:text-3xl">
                    Thả một lời dễ thương vào khu vườn
                </h1>

                <button
                    id="open-form-button"
                    type="button"
                    class="flex min-h-14 items-center justify-center gap-2 rounded-full border-2 border-ink bg-coral px-8 text-base font-extrabold text-ink shadow-button transition hover:-translate-y-0.5 hover:bg-[#ffadbb] hover:shadow-[0_6px_0_#29433d] active:translate-y-1 active:shadow-none focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-coral/40"
                    aria-haspopup="dialog"
                    aria-controls="review-form-panel"
                    aria-expanded="false">
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"></path>
                    </svg>
                    Gửi lời nhận xét
                </button>
            </div>

            <!-- Form chỉ xuất hiện sau khi nhấn nút -->
            <div
                id="review-form-panel"
                class="form-panel absolute inset-x-4 top-4 z-40 mx-auto max-w-4xl"
                data-open="false"
                role="dialog"
                aria-modal="true"
                aria-labelledby="form-title">
                <form
                    id="review-form"
                    class="flex max-h-[calc(100dvh-2rem)] flex-col gap-4 overflow-y-auto rounded-3xl border-2 border-ink/15 bg-mint-50 p-5 shadow-soft md:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold text-ink/60">ReviewMe</p>
                            <h2 id="form-title" class="pt-5 font-serif text-2xl font-bold">
                                Gửi lời nhận xét
                            </h2>
                        </div>

                        <button
                            id="close-form-button"
                            type="button"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-ink/15 bg-mint-200 transition hover:border-ink focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-mint-300"
                            aria-label="Đóng biểu mẫu">
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                aria-hidden="true">
                                <path d="M18 6 6 18"></path>
                                <path d="m6 6 12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <label for="reviewer-name" class="text-sm font-bold">
                                Tên của bạn
                            </label>
                            <input
                                id="reviewer-name"
                                name="name"
                                type="text"
                                required
                                maxlength="50"
                                autocomplete="name"
                                placeholder="Ví dụ: Minh Anh"
                                class="h-12 rounded-2xl border-2 border-ink/15 bg-mint-100 px-4 text-base text-ink outline-none transition placeholder:text-ink/40 focus:border-mint-500 focus:ring-4 focus:ring-mint-300/50">
                        </div>

                        <fieldset class="flex flex-col gap-2">
                            <legend class="text-sm font-bold">Chọn cảm xúc để AI gợi ý nhận xét</legend>

                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    class="review-tag rounded-xl border-2 border-transparent bg-coral/70 px-3 py-2 text-sm font-bold transition"
                                    data-tag="Hài hước"
                                    aria-pressed="false">
                                    Hài hước
                                </button>

                                <button
                                    type="button"
                                    class="review-tag rounded-xl border-2 border-transparent bg-yellow/80 px-3 py-2 text-sm font-bold transition"
                                    data-tag="Chuyên nghiệp"
                                    aria-pressed="false">
                                    Chuyên nghiệp
                                </button>

                                <button
                                    type="button"
                                    class="review-tag rounded-xl border-2 border-transparent bg-mint-300 px-3 py-2 text-sm font-bold transition"
                                    data-tag="Quạt"
                                    aria-pressed="false">
                                    Quạt
                                </button>

                                <button
                                    type="button"
                                    class="review-tag rounded-xl border-2 border-transparent bg-purple-200 px-3 py-2 text-sm font-bold transition"
                                    data-tag="Động viên"
                                    aria-pressed="false">
                                    Động viên
                                </button>
                            </div>
                        </fieldset>
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <label for="review-message" class="text-sm font-bold">
                                    Nội dung nhận xét
                                </label>
                                <button
                                    id="ai-suggest-button"
                                    type="button"
                                    class="flex items-center gap-1 rounded-lg border border-ink/20 bg-mint-200 px-2 py-1 text-xs font-bold text-ink transition hover:bg-mint-300 active:scale-95"
                                    title="Để AI viết giúp bạn một đoạn nhận xét mẫu">
                                    ✨ Gợi ý AI
                                </button>
                            </div>
                            <span id="character-count" class="text-xs font-semibold text-ink/50">
                                0 / 300
                            </span>
                        </div>

                        <textarea
                            id="review-message"
                            name="review"
                            required
                            maxlength="300"
                            rows="3"
                            placeholder="Viết điều gì đó tuyệt vời..."
                            class="min-h-24 resize-none rounded-2xl border-2 border-ink/15 bg-mint-100 px-4 py-3 text-base leading-relaxed text-ink outline-none transition placeholder:text-ink/40 focus:border-mint-500 focus:ring-4 focus:ring-mint-300/50"></textarea>

                        <p class="text-xs leading-relaxed text-ink/60">
                            Nội dung càng dài, quả bóng nhận xét sẽ càng lớn.
                        </p>
                    </div>

                    <button
                        id="submit-button"
                        type="submit"
                        class="flex min-h-12 items-center justify-center gap-2 rounded-2xl border-2 border-ink bg-coral px-6 font-extrabold text-ink shadow-button transition hover:-translate-y-0.5 hover:bg-[#ffadbb] active:translate-y-1 active:shadow-none">
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="m22 2-7 20-4-9-9-4Z"></path>
                            <path d="M22 2 11 13"></path>
                        </svg>
                        Thả nhận xét
                    </button>
                </form>
            </div>
        </header>

        <!-- Khu vực vật lý 70% phía dưới -->
        <section
            class="relative min-h-[490px] flex-1 overflow-hidden bg-mint-100"
            aria-label="Khu vườn vật lý chứa các nhận xét">
            <div class="pointer-events-none absolute inset-x-0 top-5 z-10 flex flex-col items-center gap-1 px-4 text-center">
                <div class="rounded-full bg-mint-50/80 px-4 py-2 backdrop-blur-sm">
                    <p class="font-serif text-lg font-bold">Khu vườn lời nhận xét</p>
                    <p class="text-sm text-ink/60">
                        Chạm vào một quả bóng để đọc nội dung
                    </p>
                </div>
            </div>

            <output
                id="review-count"
                class="pointer-events-none absolute bottom-5 left-5 z-10 rounded-full border border-ink/10 bg-mint-50/80 px-4 py-2 text-sm font-bold shadow-soft backdrop-blur-sm"
                aria-live="polite">
                0 nhận xét
            </output>

            <div id="physics-world" class="absolute inset-0" aria-hidden="true"></div>
        </section>
    </main>

    <!-- Popup chi tiết nhận xét -->
    <div
        id="review-dialog"
        class="dialog-backdrop fixed inset-0 z-50 flex items-center justify-center bg-ink/30 p-4 backdrop-blur-sm"
        data-open="false"
        role="dialog"
        aria-modal="true"
        aria-labelledby="detail-heading">

        <article
            class="dialog-card flex max-h-[calc(100dvh-2rem)] w-full max-w-lg flex-col gap-5 overflow-y-auto rounded-3xl border-2 border-ink/15 bg-mint-50 p-5 shadow-soft md:p-6">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            id="detail-status"
                            class="rounded-full bg-mint-200 px-3 py-1 text-xs font-extrabold">
                            Public
                        </span>

                        <span
                            id="detail-tag"
                            class="rounded-full bg-yellow px-3 py-1 text-xs font-extrabold">
                            Tự viết
                        </span>
                    </div>

                    <h2
                        id="detail-heading"
                        class="mt-3 shrink-0 font-serif text-2xl font-bold">
                        Chi tiêt nhận xét
                    </h2>

                    <p
                        id="detail-access-note"
                        class="mt-1 text-sm text-ink/60">
                    </p>
                </div>

                <button
                    id="close-dialog-button"
                    type="button"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-ink/15 bg-mint-200 transition hover:border-ink focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-mint-300"
                    aria-label="Đóng chi tiết nhận xét">
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        aria-hidden="true">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <label for="detail-user-id" class="text-sm font-bold">
                        ID người dùng
                    </label>

                    <input
                        id="detail-user-id"
                        type="text"
                        readonly
                        class="h-12 rounded-2xl border-2 border-ink/10 bg-mint-100 px-4 font-mono text-sm text-ink/70 outline-none">
                </div>

                <div class="flex flex-col gap-2">
                    <label for="detail-nickname" class="text-sm font-bold">
                        Nickname
                    </label>

                    <input
                        id="detail-nickname"
                        type="text"
                        readonly
                        class="h-12 rounded-2xl border-2 border-ink/10 bg-mint-100 px-4 font-bold text-ink outline-none">
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <label for="detail-message" class="text-sm font-bold">
                    Nội dung nhận xét
                </label>

                <textarea
                    id="detail-message"
                    rows="5"
                    readonly
                    class="min-h-32 resize-none rounded-2xl border-2 border-ink/10 bg-mint-100 px-4 py-3 leading-relaxed text-ink outline-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                    <span class="text-sm font-bold">
                        Lượt like
                    </span>

                    <button
                        id="like-button"
                        type="button"
                        class="flex h-12 items-center justify-center gap-2 rounded-2xl border-2 border-ink/15 bg-mint-200 px-4 font-extrabold transition hover:border-coral hover:bg-coral/20 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                        aria-label="Thích nhận xét"
                        aria-pressed="false">
                        <svg
                            id="heart-icon"
                            class="h-5 w-5 text-coral"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"></path>
                        </svg>

                        <span id="like-count">0</span>
                    </button>
                </div>

                <div class="flex flex-col gap-2">
                    <span class="text-sm font-bold">
                        Thời gian gửi
                    </span>

                    <time
                        id="detail-time"
                        class="flex h-12 items-center justify-center rounded-2xl border-2 border-ink/10 bg-mint-100 px-3 text-center text-sm font-semibold text-ink/65">
                        Vừa xong
                    </time>
                </div>
            </div>
        </article>
    </div>


</body>

</html>