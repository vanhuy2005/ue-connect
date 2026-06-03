document.addEventListener("alpine:init", () => {
    window.Alpine.data("identityCameraUpload", () => ({
        state: "idle",
        capturedData: "",
        errorMessage: "",
        stream: null,
        cleanupCallbacks: [],

        init() {
            const cleanupBeforeUnload = () => this.stopCamera();
            const cleanupBeforeNavigate = () => this.stopCamera();

            window.addEventListener("beforeunload", cleanupBeforeUnload);
            document.addEventListener("livewire:navigating", cleanupBeforeNavigate);

            this.cleanupCallbacks.push(() => {
                window.removeEventListener("beforeunload", cleanupBeforeUnload);
                document.removeEventListener("livewire:navigating", cleanupBeforeNavigate);
            });
        },

        destroy() {
            this.stopCamera();
            this.cleanupCallbacks.forEach((cleanup) => cleanup());
            this.cleanupCallbacks = [];
        },

        get isLocalhost() {
            return ["localhost", "127.0.0.1", "::1"].includes(window.location.hostname);
        },

        get isCameraContextAllowed() {
            return window.isSecureContext || this.isLocalhost;
        },

        async startCamera() {
            this.errorMessage = "";
            this.capturedData = "";

            if (!this.isCameraContextAllowed) {
                this.state = "error";
                this.errorMessage = "Camera chỉ hoạt động trên HTTPS. Trên môi trường phát triển, hãy dùng localhost.";
                return;
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.state = "error";
                this.errorMessage = "Trình duyệt của bạn không hỗ trợ camera. Hãy thử Chrome/Firefox mới nhất hoặc dùng upload file.";
                return;
            }

            this.state = "loading";

            try {
                this.stopCamera();

                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: { ideal: "environment" },
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                    },
                    audio: false,
                });

                await this.$nextTick();

                const video = this.$refs.videoEl;
                if (!video) {
                    this.stopCamera();
                    this.state = "error";
                    this.errorMessage = "Không tìm thấy vùng hiển thị camera. Vui lòng tải lại trang và thử lại.";
                    return;
                }

                video.srcObject = this.stream;

                await new Promise((resolve, reject) => {
                    let settled = false;
                    const finish = () => {
                        if (!settled) {
                            settled = true;
                            resolve();
                        }
                    };

                    video.onloadedmetadata = () => {
                        video.play().then(finish).catch(reject);
                    };
                    video.onloadeddata = finish;
                    video.onerror = reject;
                    setTimeout(finish, 3000);
                });

                this.state = "camera_ready";
            } catch (error) {
                this.stopCamera();
                this.state = "error";

                if (error.name === "NotAllowedError" || error.name === "PermissionDeniedError") {
                    this.errorMessage = "Bạn đã từ chối quyền truy cập camera. Hãy cấp quyền trong cài đặt trình duyệt rồi thử lại.";
                } else if (error.name === "NotFoundError" || error.name === "DevicesNotFoundError") {
                    this.errorMessage = "Không tìm thấy camera. Hãy kết nối camera và thử lại, hoặc dùng upload file.";
                } else if (error.name === "NotReadableError" || error.name === "TrackStartError") {
                    this.errorMessage = "Camera đang được ứng dụng khác sử dụng. Hãy đóng ứng dụng đó rồi thử lại.";
                } else if (error.name === "SecurityError") {
                    this.errorMessage = "Trình duyệt chặn camera vì kết nối không an toàn. Vui lòng dùng HTTPS hoặc localhost.";
                } else {
                    this.errorMessage = `Không thể khởi động camera: ${error.message || "lỗi không xác định"}. Hãy thử upload file thay thế.`;
                }
            }
        },

        capturePhoto() {
            const video = this.$refs.videoEl;
            const canvas = this.$refs.canvasEl;

            if (!video || !canvas) {
                this.state = "error";
                this.errorMessage = "Không tìm thấy video hoặc canvas camera. Vui lòng tải lại trang và thử lại.";
                return;
            }

            if (!video.videoWidth || !video.videoHeight) {
                this.state = "error";
                this.errorMessage = "Camera chưa sẵn sàng. Vui lòng thử bật lại camera.";
                return;
            }

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const context = canvas.getContext("2d");
            if (!context) {
                this.state = "error";
                this.errorMessage = "Trình duyệt không thể tạo ảnh từ camera. Hãy thử trình duyệt khác hoặc dùng upload file.";
                return;
            }

            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            this.capturedData = canvas.toDataURL("image/jpeg", 0.9);
            this.state = "capture_preview";
        },

        async retakePhoto() {
            this.capturedData = "";
            this.errorMessage = "";
            this.$wire?.set("capturedImageData", "", false);

            if (this.stream && this.stream.getVideoTracks().some((track) => track.readyState === "live")) {
                await this.$nextTick();
                if (this.$refs.videoEl && this.$refs.videoEl.srcObject !== this.stream) {
                    this.$refs.videoEl.srcObject = this.stream;
                }
                this.state = "camera_ready";
                return;
            }

            await this.startCamera();
        },

        confirmPhoto() {
            if (!this.capturedData) {
                this.state = "error";
                this.errorMessage = "Chưa có ảnh để sử dụng. Vui lòng chụp lại ảnh thẻ sinh viên.";
                return;
            }

            this.$wire?.set("capturedImageData", this.capturedData, false);
            this.stopCamera();
            this.state = "confirmed";
        },

        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach((track) => track.stop());
                this.stream = null;
            }

            if (this.$refs.videoEl) {
                this.$refs.videoEl.pause();
                this.$refs.videoEl.srcObject = null;
            }
        },
    }));
});
