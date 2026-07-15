
tailwind.config = {
    theme: {
        extend: {
            colors: {
                mint: {
                    50: "#f4fcf9",
                    100: "#dff7ef",
                    200: "#c5eddf",
                    300: "#9edfcf",
                    400: "#6fcbb6",
                    500: "#42ae98"
                },
                ink: "#29433d",
                coral: "#ff9cae",
                yellow: "#ffd774"
            },
            fontFamily: {
                sans: ["Inter", "ui-sans-serif", "system-ui", "sans-serif"],
                serif: ["Georgia", "ui-serif", "serif"]
            },
            boxShadow: {
                soft: "0 18px 50px rgba(41, 67, 61, 0.13)",
                button: "0 5px 0 #29433d"
            }
        }
    }
};

(function () {
    "use strict";

    const $ = (id) => document.getElementById(id);

    // BẮT ĐÚNG ID GỐC CỦA BRO
    const openFormButton = $("open-form-button");
    const closeFormButton = $("close-form-button");
    const formPanel = $("review-form-panel");
    const form = $("review-form");
    const nameInput = $("reviewer-name");
    const messageInput = $("review-message");
    const characterCount = $("character-count");
    const reviewCount = $("review-count");
    const physicsWorld = $("physics-world");
    const aiSuggestButton = $("ai-suggest-button");
    const tagButtons = document.querySelectorAll(".review-tag");

    const dialog = $("review-dialog");
    const closeDialogButton = $("close-dialog-button");
    const detailStatus = $("detail-status");
    const detailTag = $("detail-tag");
    const detailAccessNote = $("detail-access-note");
    const detailUserId = $("detail-user-id");
    const detailNickname = $("detail-nickname");
    const detailMessage = $("detail-message");
    const detailTime = $("detail-time");
    const likeButton = $("like-button");
    const likeCount = $("like-count");
    const heartIcon = $("heart-icon");

    // ĐÃ XÓA SẠCH MOCKUP (aiPrompts và mockReviews) KHỎI ĐÂY!

    const reviews = new Map();
    const ballBodies = new Map();

    let selectedTag = null;
    let selectedReview = null;
    let engine = null;
    let render = null;
    let runner = null;
    let boundaries = [];
    let resizeTimer = null;

    // --- CÁC HÀM XỬ LÝ GIAO DIỆN (GIỮ NGUYÊN TỪ BẢN GỐC CỦA BRO) ---
    function setFormOpen(isOpen) {
        if (formPanel) formPanel.dataset.open = String(isOpen);
        if (openFormButton) openFormButton.setAttribute("aria-expanded", String(isOpen));
        if (isOpen && nameInput) setTimeout(() => nameInput.focus(), 220);
        else if (openFormButton) openFormButton.focus();
    }

    function setDialogOpen(isOpen) {
        if (dialog) dialog.dataset.open = String(isOpen);
        if (!isOpen) selectedReview = null;
    }

    if (openFormButton) openFormButton.addEventListener("click", () => setFormOpen(true));
    if (closeFormButton) closeFormButton.addEventListener("click", () => setFormOpen(false));
    if (closeDialogButton) closeDialogButton.addEventListener("click", () => setDialogOpen(false));

    if (dialog) dialog.addEventListener("click", function (event) {
        if (event.target === dialog) setDialogOpen(false);
    });

    document.addEventListener("keydown", function (event) {
        if (event.key !== "Escape") return;
        if (dialog && dialog.dataset.open === "true") setDialogOpen(false);
        else if (formPanel && formPanel.dataset.open === "true") setFormOpen(false);
    });

    function updateCharacterCount() {
        if (characterCount && messageInput) characterCount.textContent = messageInput.value.length + " / 300";
    }

    if (messageInput) messageInput.addEventListener("input", updateCharacterCount);

    if (tagButtons) {
        tagButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                const clickedTag = button.dataset.tag;
                selectedTag = selectedTag === clickedTag ? null : clickedTag;
                tagButtons.forEach(function (item) {
                    item.setAttribute("aria-pressed", String(item.dataset.tag === selectedTag));
                });
            });
        });
    }

    // 🔥 GỌI API AI GEMINI THẬT (Thay vì dùng aiPrompts tĩnh)
    if (aiSuggestButton) {
        aiSuggestButton.addEventListener("click", async function () {
            if (!messageInput) return;
            messageInput.value = "Đang kết nối với AI...";
            aiSuggestButton.disabled = true;

            try {
                const response = await fetch('/api/generate-review', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ keyword: selectedTag || "" })
                });

                if (response.ok) {
                    const data = await response.json();
                    messageInput.value = data.generated_content;
                } else {
                    messageInput.value = "";
                    alert("Máy chủ AI đang bận!");
                }
            } catch (error) {
                messageInput.value = "";
                alert("Không thể kết nối tới AI!");
            } finally {
                updateCharacterCount();
                messageInput.focus();
                aiSuggestButton.disabled = false;
            }
        });
    }

    function formatRelativeTime(timestamp) {
        const seconds = Math.max(0, Math.floor((Date.now() - timestamp) / 1000));
        if (seconds < 10) return "Vừa xong";
        if (seconds < 60) return seconds + " giây trước";
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return minutes + " phút trước";
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return hours + " giờ trước";
        return Math.floor(hours / 24) + " ngày trước";
    }

    function updateStatusView(review) {
        if (!detailStatus || !detailAccessNote) return;
        const isPublic = review.status === "public";
        detailStatus.textContent = isPublic ? "Public — Đã duyệt" : "Private — Chờ duyệt";
        detailStatus.className = isPublic ? "rounded-full bg-mint-200 px-3 py-1 text-xs font-extrabold" : "rounded-full bg-yellow-200 px-3 py-1 text-xs font-extrabold";
        detailAccessNote.textContent = isPublic ? "Nhận xét đã được duyệt và mọi người đều có thể xem." : "Nhận xét đang chờ duyệt và hiện chỉ người gửi có thể xem.";
    }

    function showReviewDetail(review) {
        selectedReview = review;
        if (detailUserId) detailUserId.value = review.userId;
        if (detailNickname) detailNickname.value = review.nickname;
        if (detailMessage) detailMessage.value = review.message;

        if (detailTag) {
            detailTag.textContent = review.tag || "Tự viết";
            const tagColors = { "Hài hước": "#ffabc7", "Chuyên nghiệp": "#ffd66b", "Quạt": "#9edfd0", "Động viên": "#e9d5ff" };
            detailTag.style.backgroundColor = tagColors[review.tag] || "#88e8f2";
        }

        if (detailTime) {
            detailTime.textContent = formatRelativeTime(review.createdAt);
            detailTime.dateTime = new Date(review.createdAt).toISOString();
        }

        updateStatusView(review);
        updateLikeView();
        setDialogOpen(true);
        if (closeDialogButton) setTimeout(() => closeDialogButton.focus(), 180);
    }

    // --- KHÔI PHỤC TÍNH NĂNG THẢ TIM ---
    function updateLikeView() {
        if (!selectedReview || !likeCount || !likeButton || !heartIcon) return;

        // Cập nhật số tim và trạng thái nút
        likeCount.textContent = String(selectedReview.likes || 0);
        likeButton.setAttribute("aria-pressed", String(selectedReview.liked));

        // Đổi màu icon trái tim
        heartIcon.setAttribute("fill", selectedReview.liked ? "currentColor" : "none");

        // Chỉ cho phép thả tim nếu bóng đã được duyệt (public)
        likeButton.disabled = selectedReview.status !== "public";
    }

    if (likeButton) {
        likeButton.addEventListener("click", async function () {
            if (!selectedReview || selectedReview.status !== "public") return;

            // 1. Áp dụng Optimistic UI: Đổi giao diện NGAY LẬP TỨC cho mượt
            const wasLiked = selectedReview.liked;
            selectedReview.liked = !wasLiked;
            selectedReview.likes += selectedReview.liked ? 1 : -1;
            updateLikeView(); // Vẽ lại tim lên màn hình

            // 1. Tạo hoặc lấy mã định danh "bất tử" cho người dùng này
            let guestSessionId = localStorage.getItem('guest_session_id');
            if (!guestSessionId) {
                // Nếu chưa có, tạo một chuỗi ngẫu nhiên (VD: guest_a1b2c3d4)
                guestSessionId = 'guest_' + Math.random().toString(36).substr(2, 9);
                localStorage.setItem('guest_session_id', guestSessionId);
            }

            try {
                // 2. Âm thầm gọi API Back-end của bro ở chế độ nền
                // (Giả sử route API của bro là POST /api/reviews/{id}/like)
                const response = await fetch(`/api/reviews/${selectedReview.id}/like`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') },
                    body: JSON.stringify({ session_id: guestSessionId })
                });

                if (!response.ok) {
                    throw new Error("API Back-end trả về lỗi");
                }
            } catch (error) {
                // 3. Nếu mạng lỗi hoặc Server sập -> Hoàn tác lại trái tim (Rollback)
                console.error("Lỗi thả tim:", error);
                selectedReview.liked = wasLiked;
                selectedReview.likes += selectedReview.liked ? 1 : -1;
                updateLikeView();
            }
        });
    }


    // --- LOGIC VẬT LÝ BÓNG RƠI (GIỮ NGUYÊN) ---
    function removeBoundaries() {
        boundaries.forEach(boundary => Matter.Composite.remove(engine.world, boundary));
        boundaries = [];
    }

    function buildBoundaries() {
        if (!engine || !physicsWorld) return;
        removeBoundaries();
        const width = physicsWorld.clientWidth;
        const height = physicsWorld.clientHeight;
        const floorY = height * 0.9;
        const options = { isStatic: true, restitution: 0.8, friction: 0.15, render: { visible: false } };
        boundaries = [
            Matter.Bodies.rectangle(width / 2, floorY, width, 20, options),
            Matter.Bodies.rectangle(-10, height / 2, 20, height * 2, options),
            Matter.Bodies.rectangle(width + 10, height / 2, 20, height * 2, options)
        ];
        Matter.Composite.add(engine.world, boundaries);
    }

    function calculateBallRadius(messageLength) {
        const safeLength = Math.min(Math.max(Number(messageLength) || 0, 0), 300);
        return 28 + (62 - 28) * (safeLength / 300);
    }

    function createReviewBall(review, delay) {
        if (!engine || ballBodies.has(review.id) || !physicsWorld) return;

        setTimeout(function () {
            const width = physicsWorld.clientWidth;
            let radius = calculateBallRadius(review.message.length);
            if (window.innerWidth < 768) radius = radius / 1.5;

            const minX = radius + 8;
            const maxX = Math.max(minX, width - radius - 8);
            const x = minX + Math.random() * (maxX - minX);

            const tagColors = { "Hài hước": "#ffabc7", "Chuyên nghiệp": "#ffd66b", "Quạt": "#9edfd0", "Động viên": "#e9d5ff" };
            const color = tagColors[review.tag] || "#88e8f2";

            const ball = Matter.Bodies.circle(x, radius + 4, radius, {
                restitution: 0.8, friction: 0.08, frictionAir: 0.004, density: 0.0018,
                render: { fillStyle: color, strokeStyle: "#29433d", lineWidth: 2 }
            });

            ball.plugin = { reviewId: review.id };
            Matter.Body.setVelocity(ball, { x: (Math.random() - 0.5) * 3, y: 0 });
            Matter.Body.setAngularVelocity(ball, (Math.random() - 0.5) * 0.1);

            ballBodies.set(review.id, ball);
            Matter.Composite.add(engine.world, ball);
        }, delay || 0);
    }

    function registerReview(review, delay) {
        reviews.set(String(review.id), review);
        createReviewBall(review, delay);
        if (reviewCount) reviewCount.textContent = reviews.size + " nhận xét";
    }

    // 🔥 GỌI API LẤY BÓNG THẬT TỪ DATABASE (Thay thế mảng mockReviews)
    // 🔥 PHIÊN BẢN TỐC ĐỘ ÁNH SÁNG: Lấy bóng thẳng từ HTML, không tốn 1 mili-giây gọi API
    function loadInitialReviews() {
        try {
            // Lấy cục data mà Laravel đã nhét sẵn ở thẻ <head>
            const data = window.SERVER_REVIEWS || [];

            if (data.length === 0) {
                console.log("Chưa có quả bóng nào!");
                return;
            }

            console.log("Đã tải siêu tốc " + data.length + " quả bóng từ Server!");

            data.forEach((rv, index) => {
                const review = {
                    id: rv.id,
                    userId: rv.session_id || "GUEST",
                    nickname: rv.reviewer_name || "Khách",
                    message: rv.content,
                    tag: rv.tag || "Tự viết",
                    likes: rv.likes || 0,
                    liked: false,
                    status: rv.is_approved ? "public" : "private",
                    createdAt: new Date(rv.created_at).getTime()
                };

                // Set độ trễ 50ms giữa mỗi quả bóng để nó rớt rào rào siêu nhanh
                registerReview(review, index * 50);
            });
        } catch (error) {
            console.error("Lỗi nạp bóng siêu tốc:", error);
        }
    }

    function resizePhysics() {
        if (!render || !engine || !physicsWorld) return;
        const width = physicsWorld.clientWidth;
        const height = physicsWorld.clientHeight;
        Matter.Render.setSize(render, width, height);
        Matter.Render.setPixelRatio(render, Math.min(window.devicePixelRatio || 1, 2));
        render.bounds.min.x = 0; render.bounds.min.y = 0;
        render.bounds.max.x = width; render.bounds.max.y = height;
        buildBoundaries();
    }

    function getCanvasPoint(event) {
        const rect = render.canvas.getBoundingClientRect();
        return {
            x: (event.clientX - rect.left) * (render.options.width / rect.width),
            y: (event.clientY - rect.top) * (render.options.height / rect.height)
        };
    }

    function findBallAtPoint(point) {
        return Matter.Query.point(Matter.Composite.allBodies(engine.world), point).find(body => body.plugin && body.plugin.reviewId) || null;
    }

    function initializePhysics() {
        if (!window.Matter || engine || !physicsWorld) return;
        const width = physicsWorld.clientWidth;
        const height = physicsWorld.clientHeight;

        if (!width || !height) {
            setTimeout(initializePhysics, 100);
            return;
        }

        engine = Matter.Engine.create();
        engine.gravity.x = 0; engine.gravity.y = 1; engine.gravity.scale = 0.001;

        render = Matter.Render.create({
            element: physicsWorld, engine: engine,
            options: { width: width, height: height, wireframes: false, background: "transparent", pixelRatio: Math.min(window.devicePixelRatio || 1, 2) }
        });

        runner = Matter.Runner.create();
        buildBoundaries();
        Matter.Render.run(render);
        Matter.Runner.run(runner, engine);

        render.canvas.addEventListener("pointerup", function (event) {
            const ball = findBallAtPoint(getCanvasPoint(event));
            if (!ball) return;
            const review = reviews.get(String(ball.plugin.reviewId));
            if (review) showReviewDetail(review);
        });

        loadInitialReviews();
    }

    // 🔥 FORM GỬI NHẬN XÉT: LƯU VÀO DATABASE THẬT
    if (form) {
        form.addEventListener("submit", async function (event) {
            event.preventDefault();
            if (!form.reportValidity() || !engine) return;

            const nickname = nameInput.value.trim() || "Khách ẩn danh";
            const message = messageInput.value.trim();
            if (!message) return;

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = "Đang gửi...";
            }

            try {
                let sessionId = localStorage.getItem('reviewme_session_id');
                if (!sessionId) {
                    sessionId = "guest-" + Math.random().toString(36).substr(2, 9);
                    localStorage.setItem('reviewme_session_id', sessionId);
                }

                const response = await fetch('/api/reviews', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        reviewer_name: nickname,
                        content: message,
                        rating: 5,
                        session_id: sessionId,
                        tag: selectedTag || "Tự viết"
                    })
                });

                if (response.ok) {
                    const savedData = await response.json();
                    const review = {
                        id: savedData.id || "RV-" + Date.now(),
                        userId: sessionId,
                        nickname: nickname,
                        message: message,
                        tag: selectedTag || "Tự viết",
                        likes: 0,
                        liked: false,
                        status: "private",
                        createdAt: Date.now()
                    };

                    registerReview(review, 0);
                    form.reset();
                    selectedTag = null;
                    if (tagButtons) tagButtons.forEach(button => button.setAttribute("aria-pressed", "false"));
                    updateCharacterCount();
                    setFormOpen(false);
                    if (document.activeElement) document.activeElement.blur();
                } else {
                    alert("Lỗi lưu Database!");
                }
            } catch (error) {
                alert("Mất kết nối máy chủ API!");
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = "Đăng Nhận Xét";
                }
            }
        });
    }

    window.addEventListener("resize", function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(resizePhysics, 120);
    });

    setInterval(function () {
        if (selectedReview && dialog && dialog.dataset.open === "true" && detailTime) {
            detailTime.textContent = formatRelativeTime(selectedReview.createdAt);
        }
    }, 10000);

    window.addEventListener("beforeunload", function () {
        if (render) Matter.Render.stop(render);
        if (runner) Matter.Runner.stop(runner);
        if (engine) Matter.Engine.clear(engine);
    });

    function initializeApplication() {
        updateCharacterCount();
        initializePhysics();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initializeApplication);
    } else {
        initializeApplication();
    }
})();