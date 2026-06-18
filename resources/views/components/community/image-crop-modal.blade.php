<!-- Community Image Crop Modal -->
<div x-data="communityImageCropper()" wire:ignore>
    <template x-teleport="body">
        <div
            x-on:open-community-avatar-cropper.window="openCropper($event.detail.files, 'avatar', $event.detail.input)"
            x-on:open-community-cover-cropper.window="openCropper($event.detail.files, 'cover', $event.detail.input)"
            x-show="open"
            class="fixed inset-0 z-modal overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs ue-animate-fade-in"
            role="dialog"
            aria-modal="true"
            aria-labelledby="crop-modal-title"
            style="display: none; z-index: 9999;"
            @keydown.escape.window="cancel()"
        >
            <!-- Dynamic styles for Circular/Rectangular Cropper box and zoom slider -->
            <style>
                /* Style only applied to avatar cropper */
                .cropper-avatar-mode .cropper-view-box,
                .cropper-avatar-mode .cropper-face {
                    border-radius: 50% !important;
                    outline: 2px solid #fff !important;
                    outline-color: rgba(255, 255, 255, 0.85) !important;
                }
                .cropper-modal {
                    background-color: rgba(0, 0, 0, 0.6) !important;
                }
                .cropper-line, .cropper-point {
                    display: none !important;
                }

                /* Custom styling for range input to match design system */
                .avatar-zoom-slider {
                    -webkit-appearance: none;
                    appearance: none;
                    width: 100%;
                    background: transparent !important;
                    height: 16px !important;
                    outline: none !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    border: none !important;
                }
                .avatar-zoom-slider::-webkit-slider-runnable-track {
                    width: 100%;
                    height: 4px !important;
                    background: #e2e8f0 !important;
                    border-radius: 9999px !important;
                    border: none !important;
                }
                .avatar-zoom-slider::-webkit-slider-thumb {
                    -webkit-appearance: none;
                    appearance: none;
                    width: 18px !important;
                    height: 18px !important;
                    border-radius: 50% !important;
                    background: #1877f2 !important;
                    border: 3.5px solid #ffffff !important;
                    cursor: pointer !important;
                    margin-top: -7px !important;
                    transition: transform 0.1s ease !important;
                    box-shadow: 0 1.5px 4px rgba(0, 0, 0, 0.18) !important;
                }
                .avatar-zoom-slider::-webkit-slider-thumb:hover {
                    transform: scale(1.15) !important;
                }
                .avatar-zoom-slider::-moz-range-track {
                    width: 100%;
                    height: 4px !important;
                    background: #e2e8f0 !important;
                    border-radius: 9999px !important;
                    border: none !important;
                }
                .avatar-zoom-slider::-moz-range-thumb {
                    width: 18px !important;
                    height: 18px !important;
                    border-radius: 50% !important;
                    background: #1877f2 !important;
                    border: 3.5px solid #ffffff !important;
                    cursor: pointer !important;
                    transition: transform 0.1s ease !important;
                    box-shadow: 0 1.5px 4px rgba(0, 0, 0, 0.18) !important;
                }
                .avatar-zoom-slider::-moz-range-thumb:hover {
                    transform: scale(1.15) !important;
                }
            </style>

            <div 
                class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl w-full max-w-lg overflow-hidden flex flex-col ue-animate-scale-in text-slate-800"
                :class="cropMode === 'avatar' ? 'cropper-avatar-mode' : 'cropper-cover-mode'"
                @click.away="if (!isUploading) cancel()"
            >
                <!-- Modal Header -->
                <div class="relative flex items-center justify-center px-6 py-4 border-b border-slate-100 bg-white">
                    <h3 id="crop-modal-title" class="text-sm font-extrabold text-slate-800" x-text="cropMode === 'avatar' ? 'Chọn ảnh đại diện' : 'Chọn ảnh bìa'">Chọn ảnh</h3>
                    <button type="button" @click="cancel()" class="absolute right-6 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors" :disabled="isUploading">
                        <x-ui.icon name="x" size="xs" />
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4 space-y-4 bg-white">
                    <!-- Crop Preview Area -->
                    <div class="relative w-full h-64 sm:h-80 bg-slate-50 flex items-center justify-center overflow-hidden rounded-2xl border border-slate-100 shadow-inner" wire:ignore>
                        <img x-ref="cropImage" :src="imageSrc" class="max-w-full max-h-full" alt="Crop Target" />

                        <!-- Loading overlay -->
                        <div
                            x-show="isUploading"
                            class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs flex flex-col items-center justify-center text-white z-10 transition-opacity"
                            style="display: none;"
                        >
                            <svg class="animate-spin h-8 w-8 text-white mb-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-xs font-bold">Đang tải lên (<span x-text="uploadProgress"></span>%)</span>
                        </div>
                    </div>

                    <!-- Error message box -->
                    <div x-show="errorMessage" class="p-3 bg-red-50 border border-red-200 text-red-800 text-xs rounded-xl font-semibold flex items-center gap-2" style="display: none;">
                        <x-ui.icon name="alert-triangle" size="xs" class="text-red-600 flex-shrink-0" />
                        <span x-text="errorMessage"></span>
                    </div>

                    <!-- Zoom Slider Control -->
                    <div class="flex items-center justify-center gap-3 max-w-sm mx-auto w-full px-4 pt-1.5 bg-white">
                        <button type="button" @click="zoomValue = Math.max(0, parseInt(zoomValue) - 5); zoom();" class="text-slate-400 hover:text-slate-600 transition-colors" :disabled="isUploading">
                            <x-ui.icon name="minus" size="xs" />
                        </button>
                        <input 
                            type="range" 
                            min="0" 
                            max="100" 
                            x-model="zoomValue" 
                            @input="zoom()" 
                            class="flex-1 avatar-zoom-slider cursor-pointer" 
                            :disabled="isUploading"
                        />
                        <button type="button" @click="zoomValue = Math.min(100, parseInt(zoomValue) + 5); zoom();" class="text-slate-400 hover:text-slate-600 transition-colors" :disabled="isUploading">
                            <x-ui.icon name="plus" size="xs" />
                        </button>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-white border-t border-slate-150 flex items-center justify-end gap-3">
                    <button type="button" @click="cancel()" class="text-xs font-bold text-slate-550 hover:text-slate-700 px-4 py-2 transition-colors" :disabled="isUploading">
                        Hủy
                    </button>
                    
                    <button 
                        type="button" 
                        @click="save()" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-200 disabled:text-slate-400 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5"
                        :disabled="isUploading"
                    >
                        <x-ui.icon name="check" size="xs" />
                        <span>Lưu</span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script>
        function communityImageCropper() {
            return {
                open: false,
                imageSrc: '',
                cropper: null,
                zoomValue: 0,
                initialRatio: null,
                cropMode: 'avatar',
                isUploading: false,
                uploadProgress: 0,
                errorMessage: '',
                inputEl: null,
                
                openCropper(files, mode, input) {
                    if (files && files.length > 0) {
                        const file = files[0];
                        
                        // Client-side file type verification
                        const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
                        if (!validTypes.includes(file.type)) {
                            alert('Định dạng ảnh không hợp lệ. Vui lòng chọn tệp tin JPG, JPEG, PNG hoặc WEBP.');
                            if (input) input.value = '';
                            return;
                        }

                        // Client-side file size verification (5MB)
                        if (file.size > 5 * 1024 * 1024) {
                            alert('Kích thước ảnh vượt quá giới hạn 5MB.');
                            if (input) input.value = '';
                            return;
                        }

                        this.cropMode = mode;
                        this.inputEl = input;
                        this.imageSrc = URL.createObjectURL(file);
                        this.open = true;
                        this.zoomValue = 0;
                        this.initialRatio = null;
                        this.errorMessage = '';
                        this.isUploading = false;
                        
                        this.$nextTick(() => {
                            this.initCropper();
                        });
                    }
                },
                
                initCropper() {
                    const image = this.$refs.cropImage;
                    if (this.cropper) {
                        this.cropper.destroy();
                    }
                    
                    if (typeof Cropper === 'undefined') {
                        this.errorMessage = 'Không thể tải thư viện cắt ảnh (Cropper.js). Vui lòng kiểm tra lại kết nối mạng.';
                        return;
                    }
                    
                    const isAvatar = this.cropMode === 'avatar';
                    
                    this.cropper = new Cropper(image, {
                        aspectRatio: isAvatar ? 1 : 3, // 1:1 for avatar, 3:1 for cover
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        restore: false,
                        guides: false,
                        center: false,
                        highlight: false,
                        cropBoxMovable: false,
                        cropBoxResizable: false,
                        toggleDragModeOnDblclick: false,
                        ready: () => {
                            const imageData = this.cropper.getImageData();
                            this.initialRatio = imageData.width / imageData.naturalWidth;
                            this.zoomValue = 0;
                        },
                        zoom: (e) => {
                            if (this.initialRatio) {
                                const ratio = e.detail.ratio;
                                let val = 100 * (ratio / this.initialRatio - 1) / 2;
                                val = Math.max(0, Math.min(100, val));
                                this.zoomValue = Math.round(val);
                            }
                        }
                    });
                },
                
                zoom() {
                    if (this.cropper && this.initialRatio) {
                        const val = parseFloat(this.zoomValue);
                        const targetRatio = this.initialRatio * (1 + (val / 100) * 2);
                        this.cropper.zoomTo(targetRatio);
                    }
                },
                
                cancel() {
                    this.open = false;
                    if (this.cropper) {
                        this.cropper.destroy();
                        this.cropper = null;
                    }
                    if (this.imageSrc) {
                        URL.revokeObjectURL(this.imageSrc);
                        this.imageSrc = '';
                    }
                    if (this.inputEl) {
                        this.inputEl.value = '';
                        this.inputEl = null;
                    }
                    this.errorMessage = '';
                },
                
                save() {
                    if (this.isUploading) return;
                    
                    if (typeof Cropper === 'undefined' || !this.cropper) {
                        this.errorMessage = 'Thư viện Cropper.js chưa sẵn sàng hoặc bị lỗi.';
                        return;
                    }
                    
                    this.isUploading = true;
                    this.uploadProgress = 0;
                    this.errorMessage = '';
                    
                    const handleUpload = (blob) => {
                        const isAvatar = this.cropMode === 'avatar';
                        const filename = isAvatar ? 'avatar.jpg' : 'cover.jpg';
                        const croppedFile = new File([blob], filename, { type: 'image/jpeg' });
                        
                        const wireProperty = isAvatar ? 'croppedAvatarFile' : 'croppedCoverFile';
                        const saveMethod = isAvatar ? 'saveCommunityAvatarCropped' : 'saveCommunityCoverCropped';
                        
                        this.$wire.upload(wireProperty, croppedFile,
                            () => {
                                // Upload temporary file completed, now trigger backend save
                                this.$wire[saveMethod]()
                                    .then(() => {
                                        this.isUploading = false;
                                        this.open = false;
                                        this.cancel();
                                    })
                                    .catch((err) => {
                                        this.isUploading = false;
                                        this.errorMessage = 'Lỗi lưu ảnh vào cơ sở dữ liệu. Vui lòng kiểm tra lại.';
                                    });
                            },
                            () => {
                                this.isUploading = false;
                                this.errorMessage = 'Lỗi tải ảnh lên server.';
                            },
                            (event) => {
                                this.uploadProgress = event.detail.progress;
                            }
                        );
                    };

                    this.cropper.getCroppedCanvas({
                        width: this.cropMode === 'avatar' ? 400 : 1200,
                        height: this.cropMode === 'avatar' ? 400 : 400,
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high'
                    }).toBlob(handleUpload, 'image/jpeg', 0.9);
                }
            };
        }
    </script>
@endpush
