<div>
    {{-- Backdrop --}}
    <div 
        x-show="showDrawer" 
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40"
        @click="showDrawer = false"
        x-cloak
    ></div>

    {{-- Drawer Container (Right side on desktop, Bottom sheet on mobile) --}}
    <div 
        x-show="showDrawer"
        x-transition:enter="transform transition ease-in-out duration-300 md:duration-300"
        x-transition:enter-start="translate-y-full md:translate-y-0 md:translate-x-full"
        x-transition:enter-end="translate-y-0 md:translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300 md:duration-300"
        x-transition:leave-start="translate-y-0 md:translate-x-0"
        x-transition:leave-end="translate-y-full md:translate-y-0 md:translate-x-full"
        class="fixed inset-x-0 bottom-0 md:inset-x-auto md:top-0 md:right-0 z-50 w-full md:w-[480px] h-[85vh] md:h-full bg-white rounded-t-3xl md:rounded-none md:border-l border-slate-200 shadow-2xl flex flex-col"
        x-cloak
    >
        {{-- Mobile Handle --}}
        <div class="md:hidden flex justify-center pt-3 pb-1" @click="showDrawer = false">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full"></div>
        </div>

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white shrink-0 md:pt-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-ue-brand/10 text-ue-brand flex items-center justify-center">
                    <x-ui.icon name="book" size="sm" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Chi tiết môn học</h2>
                    <p class="text-xs text-slate-500 font-mono" x-text="selectedCourse?.course?.code"></p>
                </div>
            </div>
            <button @click="showDrawer = false" class="p-2 rounded-full hover:bg-slate-100 text-slate-500 transition">
                <x-ui.icon name="x" size="sm" />
            </button>
        </div>

        {{-- Scrollable Content --}}
        <div class="flex-1 overflow-y-auto bg-white p-6">
            <template x-if="selectedCourse">
                <div class="space-y-6">
                    {{-- Basic Info --}}
                    <div>
                        <h1 class="text-2xl font-extrabold text-slate-800 mb-3" x-text="selectedCourse.course.name"></h1>
                        
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span 
                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold"
                                :class="selectedCourse.is_mandatory ? 'bg-ue-brand/10 text-ue-brand' : 'bg-amber-100 text-amber-700'"
                                x-text="selectedCourse.is_mandatory ? 'Bắt buộc' : 'Tự chọn'"
                            ></span>
                            <span 
                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold"
                                :class="!isNaN(parseFloat(selectedCourse.course.credits)) ? 'bg-slate-200 text-slate-700' : 'bg-amber-50 text-amber-600 border border-amber-100/50'"
                            >
                                <span x-text="!isNaN(parseFloat(selectedCourse.course.credits)) ? selectedCourse.course.credits + ' tín chỉ' : 'Chưa rõ tín chỉ'"></span>
                            </span>
                            <template x-if="selectedCourse.knowledge_block">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-600 text-xs font-medium" x-text="selectedCourse.knowledge_block"></span>
                            </template>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-3 flex items-center gap-2">
                            <x-ui.icon name="align-left" size="xs" class="text-slate-400" />
                            Mô tả học phần
                        </h3>
                        
                        <template x-if="selectedCourse.course.description">
                            <div class="prose prose-sm prose-slate max-w-none prose-p:leading-relaxed" x-html="selectedCourse.course.description"></div>
                        </template>
                        
                        <template x-if="!selectedCourse.course.description">
                            <div class="flex flex-col items-center justify-center py-6 text-center">
                                <div class="w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center text-amber-500 mb-3">
                                    <x-ui.icon name="file-minus" size="md" />
                                </div>
                                <h4 class="text-sm font-bold text-slate-800 mb-1">Chưa có mô tả</h4>
                                <p class="text-xs text-slate-500 max-w-[200px]">Nội dung chi tiết của môn học này chưa được cập nhật từ tài liệu nguồn.</p>
                            </div>
                        </template>
                    </div>

                    <form
                        method="POST"
                        x-bind:action="selectedCourse ? '/career-pathway/courses/' + selectedCourse.course.id + '/update-proposals' : '#'"
                        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        @csrf
                        <input type="hidden" name="program_course_id" x-bind:value="selectedCourse?.id ?? ''">

                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ue-brand-soft text-ue-brand-active">
                                <x-ui.icon name="edit-3" size="sm" />
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900">Đề xuất cập nhật dữ liệu chính thức</h3>
                                <p class="mt-1 text-xs font-medium leading-5 text-slate-500">Đề xuất của bạn được lưu riêng tư và cần quản trị viên duyệt trước khi cập nhật vào chương trình.</p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-3">
                            <div>
                                <label class="text-xs font-bold text-slate-700">Tên môn học</label>
                                <input name="name" type="text" x-bind:value="selectedCourse?.course?.name ?? ''" class="mt-1 w-full rounded-xl border border-ue-border bg-white px-3 py-2 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="text-xs font-bold text-slate-700">Số tín chỉ</label>
                                    <input name="credits" type="number" min="0" max="20" step="0.5" x-bind:value="selectedCourse?.course?.credits ?? ''" class="mt-1 w-full rounded-xl border border-ue-border bg-white px-3 py-2 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-700">Tính chất</label>
                                    <select name="is_mandatory" class="mt-1 w-full rounded-xl border border-ue-border bg-white px-3 py-2 text-sm font-bold text-slate-700 focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                                        <option value="1" x-bind:selected="selectedCourse?.is_mandatory">Bắt buộc</option>
                                        <option value="0" x-bind:selected="selectedCourse && !selectedCourse.is_mandatory">Tự chọn</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-700">Khối kiến thức</label>
                                <input name="knowledge_block" type="text" x-bind:value="selectedCourse?.knowledge_block ?? ''" placeholder="Ví dụ: Cơ sở ngành, Kiến thức chuyên ngành..." class="mt-1 w-full rounded-xl border border-ue-border bg-white px-3 py-2 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-700">Mô tả học phần đề xuất</label>
                                <textarea name="description" rows="4" x-text="selectedCourse?.course?.description ?? ''" class="mt-1 w-full rounded-xl border border-ue-border bg-white px-3 py-2 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15"></textarea>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-700">Lý do hoặc nguồn tham khảo</label>
                                <textarea name="reason" rows="3" required placeholder="Nêu tài liệu, học kỳ, hoặc lý do bạn đề xuất cập nhật dữ liệu này." class="mt-1 w-full rounded-xl border border-ue-border bg-white px-3 py-2 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15"></textarea>
                            </div>
                        </div>

                        <button type="submit" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-ue-brand px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-ue-brand-active">
                            <x-ui.icon name="send" size="sm" />
                            Gửi đề xuất chờ duyệt
                        </button>
                    </form>
                </div>
            </template>
        </div>
    </div>
</div>
