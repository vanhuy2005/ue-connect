@props([
    'body' => '',
    'visibility' => 'verified_users',
    'selectedCommunityId' => null,
    'communities' => collect(),
    'selectedTags' => [],
    'composerImages' => [],
    'canPostExperience' => false,
    'canPostOpportunity' => false,
])

<div
    id="create-post-modal"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden ue-animate-fade-in"
    role="dialog"
    aria-modal="true"
    aria-labelledby="create-post-title"
    x-data="{
        visOpen: false,
        selectedVis: @js($visibility),
        tagOpen: false,
        localTags: @js($selectedTags),
        closeModal() {
            const textarea = document.getElementById('modal-post-body');
            if (textarea && textarea.value.trim() !== '') {
                if (!confirm('Bạn có muốn hủy bài viết này không? Nội dung chưa đăng sẽ bị mất.')) {
                    return;
                }
                $wire.set('body', '');
            }
            document.getElementById('create-post-modal').classList.add('hidden');
        },
        submitForm() {
            const textarea = document.getElementById('modal-post-body');
            if (textarea) {
                $wire.set('body', textarea.value).then(() => $wire.submitPost());
            } else {
                $wire.submitPost();
            }
        },
        reopenModal() {
            document.getElementById('create-post-modal').classList.remove('hidden');
        },
        closeVis() {
            this.visOpen = false;
            $wire.set('visibility', this.selectedVis).then(() => this.reopenModal());
        },
        closeTag() {
            this.tagOpen = false;
            $wire.set('selectedTags', this.localTags).then(() => this.reopenModal());
        }
    }"
    @keydown.escape.window="if (visOpen) { closeVis() } else if (tagOpen) { closeTag() } else { closeModal() }"
    @click.self="closeModal()"
    @post-created.window="document.getElementById('create-post-modal').classList.add('hidden')"
>
    {{-- ============================================================ --}}
    {{-- White card — NO overflow-hidden to avoid clipping fixed overlays --}}
    {{-- ============================================================ --}}
    <div class="bg-white rounded-2xl max-w-lg w-full border border-slate-200 shadow-2xl ue-animate-scale-in">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between rounded-t-2xl overflow-hidden">
            <h3 id="create-post-title" class="text-sm font-bold text-slate-800">
                Tạo bài viết mới
            </h3>
            <button
                type="button"
                @click="closeModal()"
                class="text-slate-400 hover:text-slate-600 transition-colors"
                aria-label="Đóng hộp thoại"
            >
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>

        <form wire:submit.prevent="submitPost" @submit.prevent="submitForm()" class="p-6 space-y-4">

            {{-- Avatar + Name + Visibility button --}}
            <div class="flex gap-3">
                <div class="flex-shrink-0">
                    <x-ui.avatar :user="auth()->user()" size="md" />
                </div>
                <div class="flex-1 min-w-0 flex flex-wrap items-center gap-1.5">
                    <span class="text-sm font-bold text-slate-800">{{ auth()->user()->name }}</span>
                    <button
                        type="button"
                        @click.stop="visOpen = true"
                        class="flex items-center gap-1 px-2 py-1 bg-slate-100 text-slate-600 border border-slate-200 rounded-lg whitespace-nowrap flex-shrink-0 hover:bg-slate-200 transition-colors cursor-pointer"
                    >
                        <x-ui.icon name="shield-check" size="xs" class="text-ue-brand flex-shrink-0" x-show="selectedVis === 'verified_users'" />
                        <x-ui.icon name="users" size="xs" class="text-ue-brand flex-shrink-0" x-show="selectedVis === 'connections_only'" style="display:none;" />
                        <x-ui.icon name="globe" size="xs" class="text-ue-brand flex-shrink-0" x-show="selectedVis === 'community'" style="display:none;" />
                        <span
                            class="text-xxs font-bold"
                            x-text="selectedVis === 'connections_only' ? 'Chỉ bạn bè' : (selectedVis === 'community' ? 'Cộng đồng' : 'Chỉ sinh viên xác thực')"
                        ></span>
                        <x-ui.icon name="chevron-down" size="xs" class="text-slate-400 flex-shrink-0" />
                    </button>
                </div>
            </div>

            {{-- Textarea --}}
            <div class="flex items-start gap-2">
                <div class="flex-1 min-w-0 relative" x-data="mentionComposer({ textareaId: 'modal-post-body', wireModel: 'body' })">
                    <label for="modal-post-body" class="sr-only">Nội dung bài viết</label>
                    <textarea
                        id="modal-post-body"
                        wire:model.live.debounce.150ms="body"
                        @input="handleInput($event)"
                        @keydown.arrow-down.prevent="selectNext()"
                        @keydown.arrow-up.prevent="selectPrev()"
                        @keydown.enter="showDropdown ? ($event.preventDefault() || confirmSelection()) : true"
                        @keydown.escape="showDropdown ? ($event.preventDefault() || closeDropdown()) : true"
                        placeholder="Có gì mới trong cộng đồng HCMUE hôm nay?"
                        rows="4"
                        class="w-full border-0 focus:ring-0 p-0 text-slate-700 placeholder-slate-400 text-sm sm:text-base resize-none bg-transparent focus:outline-none"
                        maxlength="3000"
                    ></textarea>

                    {{-- Suggestion Dropdown --}}
                    <div 
                        x-show="showDropdown" 
                        x-transition
                        @click.outside="closeDropdown()"
                        class="absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg z-50 max-h-48 overflow-y-auto divide-y divide-slate-50"
                        style="display: none;"
                    >
                        <template x-for="(user, index) in suggestions" :key="user.id">
                            <button
                                type="button"
                                @click="insertMention(user)"
                                @mouseenter="selectedIndex = index"
                                class="w-full text-left px-4 py-2 flex items-center gap-3 transition-colors"
                                x-bind:class="selectedIndex === index ? 'bg-slate-50 text-ue-brand' : 'text-slate-700'"
                            >
                                <img :src="user.avatar_url || 'https://www.gravatar.com/avatar/' + user.id + '?d=mp&s=100'" class="w-6 h-6 rounded-full object-cover border border-slate-100" />
                                <div class="flex-1 min-w-0">
                                    <span class="text-xxs font-bold block truncate" x-text="user.display_name"></span>
                                    <span class="text-[9px] text-slate-400 block truncate" x-text="'@' + user.name + (user.role ? ' · ' + user.role : '')"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            @error('body')
                <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
            @enderror

            {{-- Image previews --}}
            @if (!empty($composerImages))
                <div class="grid grid-cols-4 gap-2 select-none">
                    @foreach ($composerImages as $index => $img)
                        <div class="relative aspect-square rounded-xl border border-slate-150 overflow-hidden bg-slate-50">
                            <img src="{{ $img['url'] }}" alt="Preview" class="object-cover w-full h-full" />
                            <button
                                type="button"
                                wire:click="removeComposerImage({{ $index }})"
                                class="absolute top-1 right-1 w-5 h-5 rounded-full bg-slate-900/60 hover:bg-slate-900/80 text-white flex items-center justify-center transition-colors"
                            >
                                <x-ui.icon name="x" size="xs" />
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Footer toolbar --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pt-4 border-t border-slate-100 gap-3">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-start">
                    <label class="flex items-center justify-center p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-lg cursor-pointer transition-colors shadow-2xs flex-shrink-0">
                        <x-ui.icon name="image" size="md" />
                        <input type="file" wire:model="imageFiles" multiple class="hidden" accept="image/*" />
                    </label>

                    @if ($canPostExperience)
                        <button
                            type="button"
                            @click.stop="tagOpen = true; localTags = [...$wire.selectedTags]"
                            class="relative flex items-center justify-center p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-lg cursor-pointer transition-colors shadow-2xs flex-shrink-0"
                            :class="localTags.length > 0 ? 'text-ue-brand border-ue-brand/30 bg-ue-brand-soft/30' : ''"
                            title="Gắn nhãn bài viết"
                        >
                            <x-ui.icon name="tag" size="md" />
                            <template x-if="localTags.length > 0">
                                <span class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-ue-brand text-white text-[9px] font-bold rounded-full flex items-center justify-center border-2 border-white" x-text="localTags.length"></span>
                            </template>
                        </button>
                    @endif

                    <span class="text-xxs text-slate-400 font-semibold">
                        {{ mb_strlen($body) }}/3000
                    </span>

                    {{-- Visibility chip --}}
                    @php
                        $visibilityLabel = match ($visibility) {
                            'connections_only' => 'Chỉ bạn bè',
                            'community' => 'Cộng đồng',
                            default => 'Chỉ sinh viên xác thực',
                        };
                    @endphp
                    <div class="relative">
                        <label for="modal-post-visibility" class="sr-only">Quyền xem</label>
                        <select
                            id="modal-post-visibility"
                            wire:model.live="visibility"
                            class="absolute inset-0 w-full h-full opacity-0 z-10 cursor-pointer"
                        >
                            <option value="verified_users">Chỉ sinh viên xác thực</option>
                            <option value="connections_only">Chỉ bạn bè</option>
                            <option value="community">Chỉ cộng đồng</option>
                        </select>
                        <div class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 text-slate-500 rounded-lg select-none pointer-events-none">
                            <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand/10" />
                            <span class="hidden sm:inline text-xxs font-bold">{{ $visibilityLabel }}</span>
                            <span class="sm:hidden text-[10px] font-bold">{{ $visibility === 'verified_users' ? 'Xác thực' : $visibilityLabel }}</span>
                            <x-ui.icon name="chevron-down" size="xs" class="text-slate-400" />
                        </div>
                    </div>

                    @if ($visibility === 'community')
                        <div>
                            <label for="modal-post-community" class="sr-only">Chọn cộng đồng</label>
                            <select
                                id="modal-post-community"
                                wire:model="selectedCommunityId"
                                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xxs font-bold text-slate-600 focus:border-ue-brand/40 focus:ring-ue-brand/20"
                            >
                                <option value="">Chọn cộng đồng</option>
                                @foreach ($communities as $community)
                                    <option value="{{ $community->id }}">{{ $community->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedCommunityId')
                                <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-2.5">
                    <x-ui.button
                        type="submit"
                        variant="primary"
                        size="sm"
                        icon="send"
                        wire:loading.attr="disabled"
                        wire:target="submitPost,imageFiles"
                        :disabled="trim($body) === ''"
                    >
                        <span wire:loading.remove wire:target="submitPost">Đăng bài</span>
                        <span wire:loading wire:target="submitPost">Đang đăng...</span>
                    </x-ui.button>
                </div>
            </div>
        </form>
    </div>

    {{-- ============================================================ --}}
    {{-- Visibility overlay — direct child of #create-post-modal     --}}
    {{-- (outside the white card to avoid transform/overflow clipping)--}}
    {{-- ============================================================ --}}
    <div
        x-show="visOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm"
        style="display: none;"
        @click.self.stop="closeVis()"
    >
        <div
            class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden ue-animate-scale-in"
            @click.stop
        >
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-slate-900">Ai có thể xem bài viết của bạn?</h3>
                </div>
                <button type="button" @click.stop="closeVis()"
                    class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer" aria-label="Đóng">
                    <x-ui.icon name="x" size="xs" />
                </button>
            </div>

            <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-100">
                <p class="text-xs text-slate-500 font-semibold leading-relaxed">Bài viết sẽ hiển thị cho những người được chọn.</p>
            </div>

            <div class="divide-y divide-slate-100">
                <button type="button" @click.stop="selectedVis = 'verified_users'"
                    class="w-full flex items-center justify-between px-5 py-4 hover:bg-slate-50 transition-colors"
                    :class="selectedVis === 'verified_users' ? 'bg-ue-brand-soft/40' : ''"
                >
                    <div class="text-left min-w-0">
                        <p class="text-sm font-bold text-slate-800">Sinh viên xác thực</p>
                        <p class="text-xs text-slate-500 mt-0.5">Chỉ sinh viên đã xác thực tài khoản mới thấy</p>
                    </div>
                    <div class="flex-shrink-0 ml-3">
                        <div x-show="selectedVis === 'verified_users'" class="w-5 h-5 rounded-full border-2 border-ue-brand bg-ue-brand flex items-center justify-center">
                            <div class="w-2 h-2 rounded-full bg-white"></div>
                        </div>
                        <div x-show="selectedVis !== 'verified_users'" class="w-5 h-5 rounded-full border-2 border-slate-300"></div>
                    </div>
                </button>

                <button type="button" @click.stop="selectedVis = 'connections_only'"
                    class="w-full flex items-center justify-between px-5 py-4 hover:bg-slate-50 transition-colors"
                    :class="selectedVis === 'connections_only' ? 'bg-ue-brand-soft/40' : ''"
                >
                    <div class="text-left min-w-0">
                        <p class="text-sm font-bold text-slate-800">Chỉ bạn bè</p>
                        <p class="text-xs text-slate-500 mt-0.5">Chỉ những người bạn đã kết nối mới thấy</p>
                    </div>
                    <div class="flex-shrink-0 ml-3">
                        <div x-show="selectedVis === 'connections_only'" class="w-5 h-5 rounded-full border-2 border-ue-brand bg-ue-brand flex items-center justify-center">
                            <div class="w-2 h-2 rounded-full bg-white"></div>
                        </div>
                        <div x-show="selectedVis !== 'connections_only'" class="w-5 h-5 rounded-full border-2 border-slate-300"></div>
                    </div>
                </button>

                <button type="button" @click.stop="selectedVis = 'community'"
                    class="w-full flex items-center justify-between px-5 py-4 hover:bg-slate-50 transition-colors"
                    :class="selectedVis === 'community' ? 'bg-ue-brand-soft/40' : ''"
                >
                    <div class="text-left min-w-0">
                        <p class="text-sm font-bold text-slate-800">Cộng đồng</p>
                        <p class="text-xs text-slate-500 mt-0.5">Chỉ thành viên cộng đồng được chọn mới thấy</p>
                    </div>
                    <div class="flex-shrink-0 ml-3">
                        <div x-show="selectedVis === 'community'" class="w-5 h-5 rounded-full border-2 border-ue-brand bg-ue-brand flex items-center justify-center">
                            <div class="w-2 h-2 rounded-full bg-white"></div>
                        </div>
                        <div x-show="selectedVis !== 'community'" class="w-5 h-5 rounded-full border-2 border-slate-300"></div>
                    </div>
                </button>
            </div>

            <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-end bg-slate-50">
                <button type="button" @click.stop="closeVis()"
                    class="px-5 py-2 rounded-xl bg-ue-brand hover:bg-ue-brand-dark text-white text-xs font-bold transition-colors cursor-pointer">
                    Xong
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Tag overlay — direct child of #create-post-modal            --}}
    {{-- ============================================================ --}}
    @if ($canPostExperience)
        <div
            x-show="tagOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm"
            style="display: none;"
            @click.self.stop="closeTag()"
        >
            <div
                class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden ue-animate-scale-in"
                @click.stop
            >
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-900">Chọn nhãn bài viết</h3>
                    </div>
                    <button type="button" @click.stop="closeTag()"
                        class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer" aria-label="Đóng">
                        <x-ui.icon name="x" size="xs" />
                    </button>
                </div>

                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-100">
                    <p class="text-xs text-slate-500 font-semibold leading-relaxed">Chọn các nhãn để người đọc dễ dàng lọc và tìm kiếm bài viết của bạn. Bạn có thể gắn nhiều nhãn cùng lúc.</p>
                </div>

                <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                    <label class="flex items-center justify-between px-5 py-4 hover:bg-slate-50/50 transition-colors cursor-pointer">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-800">Kinh nghiệm</p>
                            <p class="text-xs text-slate-400 mt-0.5">Chia sẻ kinh nghiệm học tập hoặc công việc</p>
                        </div>
                        <input type="checkbox" x-model="localTags" value="experience" @click.stop
                            class="w-5 h-5 rounded border-slate-300 text-ue-brand focus:ring-ue-brand/20 cursor-pointer flex-shrink-0 ml-3"
                        />
                    </label>

                    @if ($canPostOpportunity)
                        <label class="flex items-center justify-between px-5 py-4 hover:bg-slate-50/50 transition-colors cursor-pointer">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-800">Cơ hội</p>
                                <p class="text-xs text-slate-400 mt-0.5">Đăng cơ hội việc làm, học bổng hoặc sự kiện</p>
                            </div>
                            <input type="checkbox" x-model="localTags" value="opportunity" @click.stop
                                class="w-5 h-5 rounded border-slate-300 text-ue-brand focus:ring-ue-brand/20 cursor-pointer flex-shrink-0 ml-3"
                            />
                        </label>

                        <label class="flex items-center justify-between px-5 py-4 hover:bg-slate-50/50 transition-colors cursor-pointer">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-800">Sư phạm</p>
                                <p class="text-xs text-slate-400 mt-0.5">Nội dung thuộc khối ngành Sư phạm</p>
                            </div>
                            <input type="checkbox" x-model="localTags" value="pedagogy" @click.stop
                                class="w-5 h-5 rounded border-slate-300 text-ue-brand focus:ring-ue-brand/20 cursor-pointer flex-shrink-0 ml-3"
                            />
                        </label>
                    @endif
                </div>

                <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-end bg-slate-50">
                    <button type="button" @click.stop="closeTag()"
                        class="px-5 py-2 rounded-xl bg-ue-brand hover:bg-ue-brand-dark text-white text-xs font-bold transition-colors cursor-pointer">
                        Xong
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
