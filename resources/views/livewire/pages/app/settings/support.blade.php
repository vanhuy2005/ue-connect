<?php

use App\Models\SupportTicket;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public ?string $feedbackMessage = null;
    public ?string $errorMessage = null;

    // Form inputs
    public string $ticket_subject = '';
    public string $ticket_message = '';
    public string $ticket_category = 'account';

    public function submitTicket(): void
    {
        $this->validate([
            'ticket_subject' => 'required|string|max:255',
            'ticket_category' => 'required|string|in:account,technical,report,other',
            'ticket_message' => 'required|string|max:2000',
        ]);

        try {
            SupportTicket::create([
                'user_id' => Auth::id(),
                'subject' => $this->ticket_subject,
                'category' => $this->ticket_category,
                'message' => $this->ticket_message,
                'status' => 'open',
            ]);

            $this->reset(['ticket_subject', 'ticket_category', 'ticket_message']);
            $this->feedbackMessage = 'Yêu cầu hỗ trợ đã được gửi thành công.';
        } catch (\Exception $e) {
            $this->errorMessage = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
        }
    }

    public function getTicketsProperty()
    {
        return SupportTicket::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
    }
}; ?>

<div class="space-y-8">
    {{-- Toast feedback --}}
    @if ($feedbackMessage)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => { show = false; $wire.set('feedbackMessage', null); }, 3000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-20 left-4 right-4 md:left-auto md:right-8 md:w-96 z-50 bg-slate-900 text-white rounded-xl shadow-xl px-4 py-3 border border-slate-800 flex items-center gap-3"
        >
            <x-ui.icon name="shield-check" size="sm" class="text-emerald-500 flex-shrink-0" />
            <span class="text-xxs font-semibold flex-1 leading-normal">{{ $feedbackMessage }}</span>
            <button @click="show = false" class="text-slate-400 hover:text-white transition-colors">
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>
    @endif

    @if ($errorMessage)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => { show = false; $wire.set('errorMessage', null); }, 3000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-20 left-4 right-4 md:left-auto md:right-8 md:w-96 z-50 bg-red-900 text-white rounded-xl shadow-xl px-4 py-3 border border-red-800 flex items-center gap-3"
        >
            <x-ui.icon name="alert-triangle" size="sm" class="text-red-400 flex-shrink-0" />
            <span class="text-xxs font-semibold flex-1 leading-normal">{{ $errorMessage }}</span>
            <button @click="show = false" class="text-slate-400 hover:text-white transition-colors">
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>
    @endif

    <div>
        <h2 class="text-sm font-bold text-slate-800">Hỗ trợ & Trợ giúp</h2>
        <p class="text-xxs text-slate-400 font-medium mt-0.5">Gửi yêu cầu hỗ trợ đến Ban Quản trị hệ thống UEConnect.</p>
    </div>

    {{-- Send Ticket Form --}}
    <form wire:submit.prevent="submitTicket" class="bg-white border border-slate-150 rounded-2xl p-5 shadow-2xs space-y-4">
        <h3 class="text-xxs font-bold text-slate-850 uppercase tracking-wider text-[9px] border-b border-slate-100 pb-2">Tạo yêu cầu mới</h3>
        
        <div class="space-y-3 text-xxs font-medium text-slate-700">
            <div>
                <label class="block mb-1 font-bold text-slate-800">Chủ đề</label>
                <input type="text" wire:model="ticket_subject" class="w-full rounded-xl border-slate-200 text-xxs focus:ring-ue-brand focus:border-ue-brand" placeholder="Tóm tắt vấn đề của bạn..." required>
                @error('ticket_subject') <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block mb-1 font-bold text-slate-800">Phân loại</label>
                <select wire:model="ticket_category" class="w-full rounded-xl border-slate-200 text-xxs focus:ring-ue-brand focus:border-ue-brand">
                    <option value="account">Tài khoản & Xác thực</option>
                    <option value="technical">Lỗi kỹ thuật hệ thống</option>
                    <option value="report">Báo cáo vi phạm</option>
                    <option value="other">Vấn đề khác</option>
                </select>
                @error('ticket_category') <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block mb-1 font-bold text-slate-800">Mô tả chi tiết</label>
                <textarea wire:model="ticket_message" rows="4" class="w-full rounded-xl border-slate-200 text-xxs focus:ring-ue-brand focus:border-ue-brand" placeholder="Cung cấp chi tiết để BQT có thể hỗ trợ bạn tốt nhất..." required></textarea>
                @error('ticket_message') <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" wire:loading.attr="disabled" class="bg-ue-brand hover:bg-ue-brand-hover disabled:opacity-50 text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-xs transition-all flex items-center gap-2">
                <span wire:loading.remove wire:target="submitTicket">Gửi yêu cầu</span>
                <span wire:loading wire:target="submitTicket">Đang gửi...</span>
            </button>
        </div>
    </form>

    {{-- Ticket History --}}
    <div class="bg-white border border-slate-150 rounded-2xl shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-xxs font-bold text-slate-850 uppercase tracking-wider text-[9px]">Lịch sử hỗ trợ</h3>
        </div>
        
        <div class="divide-y divide-slate-100">
            @forelse($this->tickets as $ticket)
                <div class="p-4 flex flex-col gap-2">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h4 class="text-xxs font-bold text-slate-800">{{ $ticket->subject }}</h4>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">{{ $ticket->category }} &bull; {{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div>
                            @if($ticket->status === 'open')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-100">Đang xử lý</span>
                            @elseif($ticket->status === 'in_progress')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-100">Đang xem xét</span>
                            @elseif($ticket->status === 'closed')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Đã đóng</span>
                            @elseif($ticket->status === 'resolved')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">Đã giải quyết</span>
                            @endif
                        </div>
                    </div>
                    <p class="text-xxs text-slate-600 font-medium leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100 mt-1">
                        {{ $ticket->message }}
                    </p>
                    @if($ticket->admin_reply)
                        <div class="mt-2 pl-4 border-l-2 border-ue-brand space-y-1">
                            <span class="text-[9px] font-bold text-ue-brand uppercase tracking-wider block">Phản hồi từ BQT</span>
                            <p class="text-xxs text-slate-800 font-medium leading-relaxed">{{ $ticket->admin_reply }}</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center space-y-2">
                    <x-ui.icon name="inbox" size="md" class="text-slate-300 mx-auto" />
                    <p class="text-xxs text-slate-400 italic">Chưa có yêu cầu hỗ trợ nào.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
