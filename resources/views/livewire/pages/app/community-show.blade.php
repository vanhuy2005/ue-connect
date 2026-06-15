<?php

use App\Actions\Community\ApproveJoinRequestAction;
use App\Actions\Community\CreateCommunityEventAction;
use App\Actions\Community\CreateCommunityPostAction;
use App\Actions\Community\GrantClubManagerAction;
use App\Actions\Community\LeaveCommunityAction;
use App\Actions\Community\RejectJoinRequestAction;
use App\Actions\Community\RequestJoinCommunityAction;
use App\Actions\Community\RevokeClubManagerAction;
use App\Actions\Community\RsvpCommunityEventAction;
use App\Actions\Community\SubmitCommunityResourceAction;
use App\Actions\Messaging\FindOrCreateDirectConversation;
use App\Actions\Messaging\SendSharedPostMessage;
use App\Enums\CommentStatus;
use App\Enums\CommunityEventRsvpStatus;
use App\Enums\CommunityJoinPolicy;
use App\Enums\CommunityMemberRole;
use App\Enums\CommunityMemberStatus;
use App\Enums\CommunityResourceType;
use App\Enums\CommunityStatus;
use App\Enums\CommunityType;
use App\Enums\CommunityVisibility;
use App\Enums\ConnectionStatus;
use App\Enums\PostStatus;
use App\Models\Community;
use App\Models\CommunityEvent;
use App\Models\CommunityEventRsvp;
use App\Models\CommunityJoinRequest;
use App\Models\CommunityMember;
use App\Models\Connection;
use App\Models\MediaFile;
use App\Models\Post;
use App\Models\User;
use App\Models\PermissionGrant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Actions\Media\StoreTemporaryMediaAction;
use App\Actions\Media\AttachMediaToModelAction;
use App\Actions\Media\DeleteMediaAction;
use App\Actions\Media\GenerateMediaUrlAction;
use App\Actions\Posts\DeletePost;
use App\Actions\Posts\UpdatePost;
use App\Actions\Posts\TogglePostLike;
use App\Actions\Posts\TogglePostSave;
use App\Actions\Posts\TogglePostRepost;
use App\Actions\Posts\HidePostFromFeed;
use App\Actions\Reports\CreateReport;
use App\Actions\Follows\FollowUser;
use App\Actions\Follows\UnfollowUser;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    use WithFileUploads, WithPagination;

    public Community $community;

    public string $activeTab = 'feed';

    // Community media uploads
    public $coverFile = null;
    public $avatarFile = null;

    // Post composer
    public bool $showPostComposer = false;

    public string $postBody = '';

    public string $postType = 'standard_post';

    // Resource submit modal
    public bool $showResourceModal = false;

    public string $resourceTitle = '';

    public string $resourceDescription = '';

    public string $resourceType = 'link';

    public string $resourceUrl = '';

    public $resourceFile = null;

    public bool $resourceCopyright = false;

    // Leave community modal
    public bool $showLeaveModal = false;

    // Event creation modal
    public bool $showEventModal = false;

    public string $eventTitle = '';

    public string $eventDescription = '';

    public string $eventType = 'in_person';

    public string $eventStatus = 'published';

    public string $eventStartsAt = '';

    public string $eventEndsAt = '';

    public string $eventLocation = '';

    public string $eventOnlineLink = '';

    public bool $eventRsvpRequired = true;

    public string $eventRsvpDeadline = '';

    public ?int $eventCapacity = null;

    public bool $eventWaitlistEnabled = false;

    // Join request reason
    public bool $showJoinModal = false;

    public string $joinReason = '';

    // Community Settings Form
    public string $settingsName = '';

    public string $settingsType = '';

    public string $settingsJoinPolicy = '';

    public string $settingsVisibility = '';

    public string $settingsStatus = '';

    public string $settingsRelatedFaculty = '';

    public string $settingsShortDescription = '';

    public string $settingsDescription = '';

    public string $settingsRules = '';

    // Rejection state
    public ?int $rejectionRequestId = null;

    public string $rejectionReason = '';

    // Manual member management
    public string $memberEmailToAdd = '';

    // Share & Invite modals
    public bool $showShareModal = false;

    public string $shareSearch = '';

    public ?int $selectedShareUserId = null;

    public string $shareOptionalMessage = '';

    public bool $showInviteModal = false;

    public string $inviteSearch = '';

    public array $selectedInviteUserIds = [];

    // Post actions & modal state properties
    public ?int $deletingPostId = null;
    public bool $showDeleteModal = false;

    public ?int $editingPostId = null;
    public string $editingBody = '';

    public ?Post $reportingPost = null;
    public string $reportReason = 'spam';
    public string $reportDescription = '';
    public bool $showReportModal = false;

    public array $locallyHiddenPostIds = [];

    public ?int $sharingPostId = null;
    public string $postShareSearch = '';
    public array $selectedShareUserIds = [];
    public string $postShareOptionalMessage = '';

    public bool $showQuickFollowModal = false;
    public ?int $quickFollowUserId = null;
    public bool $quickFollowCompleted = false;

    public ?string $feedbackMessage = null;

    // Member role management
    public bool $showChangeRoleModal = false;

    public ?int $selectedMemberIdForRole = null;

    public string $newRole = '';

    public function mount(Community $community): void
    {
        $this->authorize('view', $community);
        $this->community = $community->load(['creator', 'owner']);

        // Initialize settings properties
        $this->settingsName = $community->name;
        $this->settingsType = $community->type?->value ?? $community->type;
        $this->settingsJoinPolicy = $community->join_policy?->value ?? $community->join_policy;
        $this->settingsVisibility = $community->visibility?->value ?? $community->visibility;
        $this->settingsStatus = $community->status?->value ?? $community->status;
        $this->settingsRelatedFaculty = $community->related_faculty ?? '';
        $this->settingsShortDescription = $community->short_description ?? '';
        $this->settingsDescription = $community->description ?? '';
        $this->settingsRules = $community->rules ?? '';
    }

    public function setActiveTab(string $tab): void
    {
        if (! in_array($tab, $this->availableTabs(), true)) {
            return;
        }

        $this->activeTab = $tab;
        $this->closeTransientUi();

        match ($tab) {
            'feed' => $this->resetPage('feedPage'),
            'resources' => $this->resetPage('resourcesPage'),
            'members' => $this->resetPage('membersPage'),
            default => null,
        };
    }

    /**
     * @return list<string>
     */
    private function availableTabs(): array
    {
        return ['feed', 'resources', 'events', 'members', 'about', 'settings'];
    }

    public function closeTransientUi(): void
    {
        $this->showPostComposer = false;
        $this->showResourceModal = false;
        $this->showLeaveModal = false;
        $this->showEventModal = false;
        $this->showJoinModal = false;
        $this->showShareModal = false;
        $this->showInviteModal = false;
        $this->rejectionRequestId = null;

        $this->reset([
            'joinReason',
            'rejectionReason',
            'shareSearch',
            'selectedShareUserId',
            'shareOptionalMessage',
            'inviteSearch',
            'selectedInviteUserIds',
        ]);
    }

    #[Computed]
    public function membership(): ?CommunityMember
    {
        return CommunityMember::where('community_id', $this->community->id)
            ->where('user_id', auth()->id())
            ->first();
    }

    #[Computed]
    public function isActiveMember(): bool
    {
        if (auth()->check() && $this->community->isOwnedBy(auth()->user())) {
            return true;
        }

        return $this->membership?->status === CommunityMemberStatus::Active;
    }

    #[Computed]
    public function hasPendingRequest(): bool
    {
        return CommunityJoinRequest::where('community_id', $this->community->id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->exists();
    }

    #[Computed]
    public function feedPosts()
    {
        return Post::inCommunity($this->community->id)
            ->whereIn('status', [PostStatus::PUBLISHED->value, PostStatus::EDITED->value])
            ->whereNotIn('id', $this->locallyHiddenPostIds)
            ->with(['user.profile', 'media.variants', 'community'])
            ->withCount([
                'likes',
                'comments as published_comments_count' => function ($query): void {
                    $query->where('status', CommentStatus::PUBLISHED->value);
                },
                'likes as liked_by_current_user_count' => function ($query): void {
                    $query->where('user_id', auth()->id());
                },
                'saves as saved_by_current_user_count' => function ($query): void {
                    $query->where('user_id', auth()->id());
                },
            ])
            ->latest('published_at')
            ->paginate(15, pageName: 'feedPage');
    }

    #[Computed]
    public function upcomingEvents()
    {
        $query = CommunityEvent::where('community_id', $this->community->id)
            ->upcoming()
            ->with(['rsvps' => fn ($query) => $query->where('user_id', auth()->id())])
            ->orderBy('starts_at')
            ->take(10);

        if (! $this->canCreateEvents) {
            $query->published();
        }

        return $query->get();
    }

    #[Computed]
    public function publishedResources()
    {
        return $this->community->publishedResources()
            ->with(['submitter', 'mediaFile'])
            ->latest()
            ->paginate(12, pageName: 'resourcesPage');
    }

    #[Computed]
    public function members()
    {
        return $this->community->activeMembers()
            ->with('user.profile')
            ->paginate(15, pageName: 'membersPage');
    }

    #[Computed]
    public function canCreateEvents(): bool
    {
        return auth()->check()
            && $this->community->isActive()
            && Gate::allows('create', [CommunityEvent::class, $this->community]);
    }

    // ─── Join ─────────────────────────────────────────────────────────────────

    public function openJoinModal(): void
    {
        $this->authorize('join', $this->community);
        $this->showJoinModal = true;
    }

    public function confirmJoin(RequestJoinCommunityAction $action): void
    {
        $action->execute(auth()->user(), $this->community, ['join_reason' => $this->joinReason]);
        $this->showJoinModal = false;
        $this->reset('joinReason');
        $this->dispatch('notify', type: 'success', message: $this->community->requiresApproval()
            ? 'Yêu cầu tham gia đã được gửi.'
            : 'Bạn đã tham gia cộng đồng!');
    }

    // ─── Leave ────────────────────────────────────────────────────────────────

    public function openLeaveModal(): void
    {
        $this->showLeaveModal = true;
    }

    public function openResourceModal(): void
    {
        $this->authorize('create', [App\Models\CommunityResource::class, $this->community]);
        $this->showResourceModal = true;
    }

    public function confirmLeave(LeaveCommunityAction $action): void
    {
        $action->execute(auth()->user(), $this->community);
        $this->showLeaveModal = false;
        $this->dispatch('notify', type: 'success', message: 'Bạn đã rời cộng đồng.');
    }

    // ─── Post ─────────────────────────────────────────────────────────────────

    public function openPostComposer(): void
    {
        $this->showPostComposer = true;
    }

    public function createPost(CreateCommunityPostAction $action): void
    {
        $this->validate([
            'postBody' => ['required', 'string', 'min:1', 'max:10000'],
        ]);

        $action->execute(auth()->user(), $this->community, [
            'body' => $this->postBody,
            'community_post_type' => $this->postType,
        ]);

        $this->showPostComposer = false;
        $this->reset(['postBody', 'postType']);
        $this->dispatch('notify', type: 'success', message: 'Bài đăng đã được tạo.');
    }

    // ─── Resource ─────────────────────────────────────────────────────────────

    public function submitResource(SubmitCommunityResourceAction $action): void
    {
        $rules = [
            'resourceTitle' => ['required', 'string', 'min:3', 'max:200'],
            'resourceType' => ['required', 'string', 'in:'.implode(',', array_column(CommunityResourceType::cases(), 'value'))],
            'resourceCopyright' => ['accepted'],
        ];

        $type = CommunityResourceType::tryFrom($this->resourceType);

        if ($type?->requiresUrl()) {
            $rules['resourceUrl'] = ['required', 'url', 'max:2000'];
        } else {
            $rules['resourceUrl'] = ['nullable', 'url', 'max:2000'];
        }

        if ($type?->requiresFile()) {
            $rules['resourceFile'] = match ($type) {
                CommunityResourceType::Image => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
                CommunityResourceType::Document => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,md', 'max:10240'],
                CommunityResourceType::Template => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,md,zip', 'max:10240'],
                default => ['required', 'file', 'max:10240'],
            };
        } else {
            $rules['resourceFile'] = ['nullable', 'file', 'max:10240'];
        }

        $this->validate($rules, [
            'resourceTitle.required' => 'Vui lòng nhập tiêu đề tài nguyên.',
            'resourceTitle.min' => 'Tiêu đề phải có ít nhất 3 ký tự.',
            'resourceUrl.required' => 'Đường dẫn URL là bắt buộc đối với loại tài nguyên này.',
            'resourceUrl.url' => 'Đường dẫn URL không hợp lệ.',
            'resourceFile.required' => 'Tệp tin đính kèm là bắt buộc đối với loại tài nguyên này.',
            'resourceFile.max' => 'Kích thước tệp tin không được vượt quá 10MB.',
            'resourceFile.mimes' => 'Định dạng tệp không phù hợp với loại tài nguyên đã chọn.',
            'resourceFile.image' => 'Loại hình ảnh yêu cầu một tệp ảnh hợp lệ.',
            'resourceCopyright.accepted' => 'Bạn cần xác nhận cam kết bản quyền.',
        ]);

        $fileId = null;
        if ($this->resourceFile) {
            $path = $this->resourceFile->store('communities/'.$this->community->id.'/resources', 'public');
            $mediaFile = MediaFile::create([
                'owner_id' => auth()->id(),
                'disk' => 'public',
                'path' => $path,
                'original_name' => $this->resourceFile->getClientOriginalName(),
                'mime_type' => $this->resourceFile->getMimeType(),
                'extension' => $this->resourceFile->getClientOriginalExtension(),
                'size_bytes' => $this->resourceFile->getSize(),
                'visibility' => 'public',
                'file_category' => 'community_resource',
            ]);
            $fileId = $mediaFile->id;
        }

        $resource = $action->execute(auth()->user(), $this->community, [
            'title' => $this->resourceTitle,
            'description' => $this->resourceDescription,
            'resource_type' => $this->resourceType,
            'url' => $this->resourceUrl ?: null,
            'file_id' => $fileId,
            'copyright_attestation' => $this->resourceCopyright,
        ]);

        $this->showResourceModal = false;
        $isPublished = $resource->status === App\Enums\CommunityResourceStatus::Published;
        $this->reset(['resourceTitle', 'resourceDescription', 'resourceType', 'resourceUrl', 'resourceFile', 'resourceCopyright']);
        $this->dispatch('notify', type: 'success', message: $isPublished ? 'Tài nguyên đã được đăng thành công.' : 'Tài nguyên đã được gửi để xét duyệt.');
    }

    // ─── Event RSVP ───────────────────────────────────────────────────────────

    public function rsvpEvent(int $eventId, string $status, RsvpCommunityEventAction $action): void
    {
        $this->authorize('view', $this->community);

        if (! $this->isActiveMember) {
            $this->dispatch('notify', type: 'error', message: 'Bạn cần tham gia cộng đồng này để có thể đăng ký tham gia sự kiện.');

            return;
        }

        $event = CommunityEvent::findOrFail($eventId);
        $rsvpStatus = CommunityEventRsvpStatus::from($status);

        $action->execute(auth()->user(), $event, $rsvpStatus);

        $this->dispatch('notify', type: 'success', message: 'Cập nhật trạng thái đăng ký sự kiện thành công.');
    }

    public function openEventModal(): void
    {
        $this->authorize('create', [CommunityEvent::class, $this->community]);

        if (! $this->community->isActive()) {
            $this->dispatch('notify', type: 'error', message: 'Cộng đồng cần ở trạng thái Hoạt động trước khi tạo sự kiện.');

            return;
        }

        $this->eventStartsAt = now()->addDay()->format('Y-m-d\TH:i');
        $this->showEventModal = true;
    }

    public function submitEvent(CreateCommunityEventAction $action): void
    {
        $this->authorize('create', [CommunityEvent::class, $this->community]);

        $this->validate([
            'eventTitle' => ['required', 'string', 'min:3', 'max:200'],
            'eventDescription' => ['nullable', 'string', 'max:5000'],
            'eventType' => ['required', 'string', 'in:in_person,online,hybrid'],
            'eventStatus' => ['required', 'string', 'in:draft,published'],
            'eventStartsAt' => ['required', 'date', 'after_or_equal:now'],
            'eventEndsAt' => ['nullable', 'date', 'after:eventStartsAt'],
            'eventLocation' => [$this->eventType === 'online' ? 'nullable' : 'required', 'string', 'max:500'],
            'eventOnlineLink' => [$this->eventType === 'in_person' ? 'nullable' : 'required', 'url', 'max:2000'],
            'eventRsvpDeadline' => ['nullable', 'date', 'before_or_equal:eventStartsAt'],
            'eventCapacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'eventWaitlistEnabled' => ['boolean'],
        ], [
            'eventTitle.required' => 'Vui lòng nhập tên sự kiện.',
            'eventTitle.min' => 'Tên sự kiện phải có ít nhất 3 ký tự.',
            'eventStartsAt.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'eventStartsAt.after_or_equal' => 'Thời gian bắt đầu không được nằm trong quá khứ.',
            'eventEndsAt.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'eventLocation.required' => 'Sự kiện trực tiếp hoặc kết hợp cần có địa điểm.',
            'eventOnlineLink.required' => 'Sự kiện online hoặc kết hợp cần có đường dẫn tham gia.',
            'eventOnlineLink.url' => 'Đường dẫn tham gia không hợp lệ.',
            'eventRsvpDeadline.before_or_equal' => 'Hạn đăng ký phải trước hoặc bằng thời gian bắt đầu.',
        ]);

        $createdStatus = $this->eventStatus;

        $action->execute(auth()->user(), $this->community, [
            'title' => $this->eventTitle,
            'description' => $this->eventDescription ?: null,
            'event_type' => $this->eventType,
            'status' => $this->eventStatus,
            'visibility' => 'community_members',
            'starts_at' => $this->eventStartsAt,
            'ends_at' => $this->eventEndsAt ?: null,
            'location' => $this->eventLocation ?: null,
            'online_link' => $this->eventOnlineLink ?: null,
            'rsvp_required' => $this->eventRsvpRequired,
            'rsvp_deadline' => $this->eventRsvpDeadline ?: null,
            'capacity' => $this->eventCapacity,
            'waitlist_enabled' => $this->eventWaitlistEnabled,
        ]);

        $this->showEventModal = false;
        $this->reset([
            'eventTitle',
            'eventDescription',
            'eventType',
            'eventStatus',
            'eventStartsAt',
            'eventEndsAt',
            'eventLocation',
            'eventOnlineLink',
            'eventRsvpRequired',
            'eventRsvpDeadline',
            'eventCapacity',
            'eventWaitlistEnabled',
        ]);
        $this->eventType = 'in_person';
        $this->eventStatus = 'published';
        $this->eventRsvpRequired = true;
        $this->dispatch('notify', type: 'success', message: $createdStatus === 'draft'
            ? 'Sự kiện đã được lưu nháp.'
            : 'Sự kiện đã được tạo và công bố.');
    }

    // ─── Owner Management ──────────────────────────────────────────────────────

    #[Computed]
    public function canManage(): bool
    {
        if (! auth()->check()) {
            return false;
        }
        $user = auth()->user();

        return $user->can('update', $this->community)
            || $user->can('manageMember', $this->community);
    }

    #[Computed]
    public function canManuallyAddMembers(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $joinPolicy = $this->community->join_policy?->value ?? $this->community->join_policy;

        if ($joinPolicy === CommunityJoinPolicy::Closed->value) {
            return false;
        }

        if ($joinPolicy === CommunityJoinPolicy::AdminOnly->value) {
            return auth()->user()->hasRole('admin') || auth()->user()->can('manage_communities');
        }

        return auth()->user()->can('manageMember', $this->community);
    }

    public function visibilityBehavior(?string $value = null): array
    {
        return match ($value ?? $this->settingsVisibility) {
            CommunityVisibility::Restricted->value => [
                'title' => 'Hạn chế',
                'description' => 'Cộng đồng vẫn xuất hiện ở trang khám phá, nhưng người mới phải đi qua chính sách tham gia đang chọn.',
                'discovery' => 'Hiển thị trên trang Cộng đồng & CLB.',
                'detail' => 'Người đã đăng nhập có thể mở trang chi tiết để đọc phần giới thiệu.',
            ],
            CommunityVisibility::Private->value => [
                'title' => 'Riêng tư',
                'description' => 'Không đưa cộng đồng ra trang khám phá. Owner, quản lý, admin và thành viên đang hoạt động vẫn truy cập được.',
                'discovery' => 'Không hiển thị trong danh sách khám phá.',
                'detail' => 'Chỉ owner, quản lý, admin hoặc thành viên được thêm mới mở được trang.',
            ],
            CommunityVisibility::Hidden->value => [
                'title' => 'Ẩn',
                'description' => 'Dùng khi cần bảo trì hoặc cất cộng đồng khỏi người dùng thông thường.',
                'discovery' => 'Không hiển thị ở khám phá.',
                'detail' => 'Chỉ owner, admin hoặc người có quyền quản lý cộng đồng truy cập được.',
            ],
            CommunityVisibility::OfficialOnly->value => [
                'title' => 'Chỉ quản trị viên',
                'description' => 'Dùng cho không gian nội bộ của hệ thống. Admin, owner và người có quyền quản lý cộng đồng mới truy cập được.',
                'discovery' => 'Không hiển thị với người dùng thông thường.',
                'detail' => 'Chỉ nhóm quản trị hoặc owner xem được.',
            ],
            default => [
                'title' => 'Công khai',
                'description' => 'Cộng đồng xuất hiện ở trang khám phá và trang giới thiệu mở cho người dùng hợp lệ.',
                'discovery' => 'Hiển thị trên trang Cộng đồng & CLB.',
                'detail' => 'Người dùng hợp lệ có thể mở trang chi tiết.',
            ],
        };
    }

    public function joinPolicyBehavior(?string $value = null): array
    {
        return match ($value ?? $this->settingsJoinPolicy) {
            CommunityJoinPolicy::ApprovalRequired->value => [
                'title' => 'Yêu cầu xét duyệt',
                'description' => 'Người dùng gửi yêu cầu tham gia. Owner hoặc quản lý duyệt trong tab Cài đặt.',
                'action' => 'Nút tham gia hiển thị là Gửi yêu cầu tham gia.',
            ],
            CommunityJoinPolicy::InviteOnly->value => [
                'title' => 'Chỉ theo lời mời',
                'description' => 'Người dùng không tự gửi yêu cầu. Owner hoặc quản lý thêm thành viên bằng email.',
                'action' => 'Không hiển thị nút tham gia công khai.',
            ],
            CommunityJoinPolicy::AdminOnly->value => [
                'title' => 'Chỉ admin thêm',
                'description' => 'Chỉ tài khoản admin hoặc người có quyền manage_communities được thêm thành viên.',
                'action' => 'Owner thường không thêm được thành viên ở chính sách này.',
            ],
            CommunityJoinPolicy::Closed->value => [
                'title' => 'Không nhận thành viên',
                'description' => 'Khóa tuyển thành viên mới. Dùng khi cộng đồng đã kết thúc hoặc tạm ngừng nhận thêm người.',
                'action' => 'Không hiển thị nút tham gia và form thêm thành viên bị khóa.',
            ],
            default => [
                'title' => 'Mở',
                'description' => 'Người dùng đủ điều kiện có thể tham gia ngay, không cần owner duyệt.',
                'action' => 'Nút tham gia hiển thị là Tham gia.',
            ],
        };
    }

    public function statusBehavior(?string $value = null): array
    {
        return match ($value ?? $this->settingsStatus) {
            CommunityStatus::Draft->value => [
                'title' => 'Bản nháp',
                'description' => 'Dùng khi đang dựng cộng đồng. Owner và admin vẫn sửa được, nhưng người mới chưa thể tham gia.',
                'operations' => 'Chặn tham gia, đăng bài, gửi tài nguyên và tạo sự kiện công khai.',
            ],
            CommunityStatus::Inactive->value => [
                'title' => 'Ngưng hoạt động',
                'description' => 'Dùng khi cộng đồng cần tạm dừng vận hành nhưng vẫn muốn giữ dữ liệu.',
                'operations' => 'Không cho người mới tham gia và hạn chế các hành động tạo nội dung.',
            ],
            default => [
                'title' => 'Hoạt động',
                'description' => 'Cộng đồng đang vận hành bình thường.',
                'operations' => 'Cho phép tham gia, đăng bài, tài nguyên và sự kiện theo quyền của từng vai trò.',
            ],
        };
    }

    #[Computed]
    public function behaviorPreview(): array
    {
        $visibility = $this->visibilityBehavior();
        $joinPolicy = $this->joinPolicyBehavior();
        $status = $this->statusBehavior();

        return [
            ['label' => 'Trang khám phá', 'value' => $visibility['discovery']],
            ['label' => 'Trang chi tiết', 'value' => $visibility['detail']],
            ['label' => 'Nút tham gia', 'value' => $joinPolicy['action']],
            ['label' => 'Vận hành', 'value' => $status['operations']],
        ];
    }

    public function addMemberByEmail(): void
    {
        $this->authorize('manageMember', $this->community);

        if (! $this->canManuallyAddMembers) {
            $this->dispatch('notify', type: 'error', message: 'Chính sách tham gia hiện tại không cho tài khoản này thêm thành viên thủ công.');

            return;
        }

        $this->validate([
            'memberEmailToAdd' => ['required', 'email', 'exists:users,email'],
        ], [
            'memberEmailToAdd.required' => 'Vui lòng nhập email thành viên.',
            'memberEmailToAdd.email' => 'Email không hợp lệ.',
            'memberEmailToAdd.exists' => 'Không tìm thấy tài khoản với email này.',
        ]);

        $target = User::where('email', $this->memberEmailToAdd)->firstOrFail();

        if ($this->community->isOwnedBy($target)) {
            $this->dispatch('notify', type: 'error', message: 'Chủ sở hữu đã có quyền quản lý cộng đồng này.');

            return;
        }

        $existing = CommunityMember::where('community_id', $this->community->id)
            ->where('user_id', $target->id)
            ->first();

        $wasActive = $existing?->status === CommunityMemberStatus::Active;

        CommunityMember::updateOrCreate([
            'community_id' => $this->community->id,
            'user_id' => $target->id,
        ], [
            'role' => CommunityMemberRole::Member->value,
            'status' => CommunityMemberStatus::Active->value,
            'joined_at' => $existing?->joined_at ?? now(),
            'left_at' => null,
            'removed_at' => null,
            'removed_by' => null,
            'remove_reason' => null,
        ]);

        if (! $wasActive) {
            $this->community->increment('members_count');
        }

        $this->reset('memberEmailToAdd');
        $this->community->refresh();
        $this->resetPage('membersPage');
        $this->dispatch('notify', type: 'success', message: 'Đã thêm thành viên vào cộng đồng.');
    }

    public function saveSettings(): void
    {
        $this->authorize('update', $this->community);

        $this->validate([
            'settingsName' => ['required', 'string', 'min:3', 'max:160'],
            'settingsType' => ['required', 'string', 'in:'.implode(',', array_column(CommunityType::cases(), 'value'))],
            'settingsJoinPolicy' => ['required', 'string', 'in:'.implode(',', array_column(CommunityJoinPolicy::cases(), 'value'))],
            'settingsVisibility' => ['required', 'string', 'in:'.implode(',', array_column(CommunityVisibility::cases(), 'value'))],
            'settingsStatus' => ['required', 'string', 'in:'.implode(',', [
                CommunityStatus::Draft->value,
                CommunityStatus::Active->value,
                CommunityStatus::Inactive->value,
            ])],
            'settingsRelatedFaculty' => ['nullable', 'string', 'max:160'],
            'settingsShortDescription' => ['nullable', 'string', 'max:300'],
            'settingsDescription' => ['nullable', 'string', 'max:5000'],
            'settingsRules' => ['nullable', 'string', 'max:5000'],
        ], [
            'settingsName.required' => 'Tên cộng đồng không được để trống.',
            'settingsName.min' => 'Tên cộng đồng phải có ít nhất 3 ký tự.',
        ]);

        $this->community->update([
            'name' => $this->settingsName,
            'type' => $this->settingsType,
            'join_policy' => $this->settingsJoinPolicy,
            'visibility' => $this->settingsVisibility,
            'status' => $this->settingsStatus,
            'related_faculty' => $this->settingsRelatedFaculty ?: null,
            'short_description' => $this->settingsShortDescription ?: null,
            'description' => $this->settingsDescription ?: null,
            'rules' => $this->settingsRules ?: null,
        ]);

        $this->dispatch('notify', type: 'success', message: 'Cập nhật cài đặt cộng đồng thành công!');
        $this->community->refresh();
    }

    #[Computed]
    public function pendingJoinRequests()
    {
        if (! $this->canManage) {
            return collect();
        }

        return CommunityJoinRequest::where('community_id', $this->community->id)
            ->where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();
    }

    public function approveJoinRequest(int $requestId, ApproveJoinRequestAction $action): void
    {
        $request = CommunityJoinRequest::findOrFail($requestId);
        $this->authorize('review', $request);

        $action->execute(auth()->user(), $request);
        $this->dispatch('notify', type: 'success', message: 'Đã duyệt yêu cầu tham gia.');
        $this->community->refresh();
    }

    public function startRejection(int $requestId): void
    {
        $request = CommunityJoinRequest::findOrFail($requestId);
        $this->authorize('review', $request);

        $this->rejectionRequestId = $requestId;
        $this->rejectionReason = '';
    }

    public function cancelRejection(): void
    {
        $this->rejectionRequestId = null;
        $this->rejectionReason = '';
    }

    public function confirmRejectJoinRequest(RejectJoinRequestAction $action): void
    {
        if (! $this->rejectionRequestId) {
            abort(403);
        }

        $this->validate([
            'rejectionReason' => ['required', 'string', 'min:3', 'max:255'],
        ], [
            'rejectionReason.required' => 'Vui lòng cung cấp lý do từ chối.',
            'rejectionReason.min' => 'Lý do từ chối phải có ít nhất 3 ký tự.',
        ]);

        $request = CommunityJoinRequest::findOrFail($this->rejectionRequestId);
        $this->authorize('review', $request);

        $action->execute(auth()->user(), $request, $this->rejectionReason);

        $this->rejectionRequestId = null;
        $this->rejectionReason = '';
        $this->dispatch('notify', type: 'success', message: 'Đã từ chối yêu cầu tham gia.');
        $this->community->refresh();
    }

    #[Computed]
    public function selectedMemberForRole(): ?CommunityMember
    {
        return $this->selectedMemberIdForRole
            ? CommunityMember::with('user')->find($this->selectedMemberIdForRole)
            : null;
    }

    public function openChangeRoleModal(int $memberId): void
    {
        $this->authorize('manageMemberRoles', $this->community);
        $member = CommunityMember::findOrFail($memberId);

        if ($member->role === CommunityMemberRole::Owner) {
            $this->dispatch('notify', type: 'error', message: 'Không thể thay đổi quyền của chủ sở hữu hiện tại.');

            return;
        }

        $this->selectedMemberIdForRole = $memberId;
        $this->newRole = $member->role->value;
        $this->showChangeRoleModal = true;
    }

    public function confirmChangeRole(
        GrantClubManagerAction $grantAction,
        RevokeClubManagerAction $revokeAction
    ): void {
        $this->authorize('manageMemberRoles', $this->community);

        if (! $this->selectedMemberIdForRole) {
            return;
        }

        $member = CommunityMember::findOrFail($this->selectedMemberIdForRole);
        $targetUser = $member->user;
        $oldRole = $member->role;
        $requestedRole = CommunityMemberRole::from($this->newRole);

        if ($oldRole === $requestedRole) {
            $this->showChangeRoleModal = false;

            return;
        }

        DB::transaction(function () use ($member, $targetUser, $oldRole, $requestedRole, $grantAction, $revokeAction) {
            $actor = auth()->user();

            // 1. If old role was Manager, revoke manager permission grant
            if ($oldRole === CommunityMemberRole::Manager) {
                $revokeAction->execute($actor, $this->community, $targetUser, 'Thu hồi quyền quản lý do thay đổi vai trò.');
            }

            // 2. If old role was Moderator, revoke moderator permission grant
            if ($oldRole === CommunityMemberRole::Moderator) {
                PermissionGrant::where('user_id', $targetUser->id)
                    ->where('permission_key', 'moderate_community_posts')
                    ->where('scope_type', 'community')
                    ->where('scope_id', $this->community->id)
                    ->where('status', 'active')
                    ->update([
                        'status' => 'revoked',
                        'revoked_at' => now(),
                        'revoked_by' => $actor->id,
                        'reason' => 'Thu hồi quyền kiểm duyệt do thay đổi vai trò.',
                    ]);
            }

            // 3. If new role is Owner (Transfer ownership)
            if ($requestedRole === CommunityMemberRole::Owner) {
                // Update community owner
                $this->community->update([
                    'owner_id' => $targetUser->id,
                ]);

                // Set new owner member role
                $member->update([
                    'role' => CommunityMemberRole::Owner->value,
                    'role_label' => 'Chủ sở hữu',
                ]);

                // Demote old owner (actor) to Manager
                $oldOwnerMember = CommunityMember::where('community_id', $this->community->id)
                    ->where('user_id', $actor->id)
                    ->first();

                if ($oldOwnerMember) {
                    $oldOwnerMember->update([
                        'role' => CommunityMemberRole::Manager->value,
                        'role_label' => 'Quản lý cộng đồng',
                    ]);

                    // Grant manager permissions to old owner
                    $grantAction->execute($actor, $this->community, $actor, 'Chuyển giao quyền sở hữu cộng đồng.');
                }

                $this->dispatch('notify', type: 'success', message: 'Đã chuyển giao quyền sở hữu nhóm thành công.');
            }
            // 4. If new role is Manager
            elseif ($requestedRole === CommunityMemberRole::Manager) {
                $grantAction->execute($actor, $this->community, $targetUser, 'Được bổ nhiệm làm quản lý bởi chủ cộng đồng.');
                $this->dispatch('notify', type: 'success', message: 'Đã thăng chức thành Quản lý cộng đồng.');
            }
            // 5. If new role is Moderator
            elseif ($requestedRole === CommunityMemberRole::Moderator) {
                // Create a PermissionGrant for moderate_community_posts
                PermissionGrant::create([
                    'user_id' => $targetUser->id,
                    'permission_key' => 'moderate_community_posts',
                    'scope_type' => 'community',
                    'scope_id' => $this->community->id,
                    'granted_by' => $actor->id,
                    'reason' => 'Bổ nhiệm kiểm duyệt viên cộng đồng.',
                    'starts_at' => now(),
                    'status' => 'active',
                ]);

                $member->update([
                    'role' => CommunityMemberRole::Moderator->value,
                    'role_label' => 'Kiểm duyệt viên',
                ]);

                $this->dispatch('notify', type: 'success', message: 'Đã thăng chức thành Kiểm duyệt viên.');
            }
            // 6. If new role is Member
            elseif ($requestedRole === CommunityMemberRole::Member) {
                $member->update([
                    'role' => CommunityMemberRole::Member->value,
                    'role_label' => null,
                ]);

                $this->dispatch('notify', type: 'success', message: 'Đã chuyển vai trò về Thành viên.');
            }
        });

        $this->showChangeRoleModal = false;
        $this->selectedMemberIdForRole = null;
        $this->community->refresh();
        $this->resetPage('membersPage');
    }

    // ─── Persistent Left Sidebar Data ───────────────────────────────────────

    public function getJoinedCommunitiesProperty()
    {
        $userId = auth()->id();
        $memberIds = CommunityMember::where('user_id', $userId)
            ->where('status', 'active')
            ->pluck('community_id');

        return Community::query()
            ->where(function ($query) use ($memberIds, $userId) {
                $query->where('owner_id', $userId)
                    ->orWhereIn('id', $memberIds);
            })
            ->latest('updated_at')
            ->get();
    }

    // ─── Group Invite & Share Logic ──────────────────────────────────────────

    public function startInvite(): void
    {
        $this->inviteSearch = '';
        $this->selectedInviteUserIds = [];
        $this->showInviteModal = true;
    }

    public function toggleInviteUser(int $userId): void
    {
        if (in_array($userId, $this->selectedInviteUserIds)) {
            $this->selectedInviteUserIds = array_diff($this->selectedInviteUserIds, [$userId]);
        } else {
            $this->selectedInviteUserIds[] = $userId;
        }
    }

    public function getInviteConnectionsProperty(): Collection
    {
        $userId = auth()->id();
        $search = trim($this->inviteSearch);

        $query = Connection::where(function ($q) use ($userId) {
            $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
        })
            ->where('status', ConnectionStatus::ACTIVE)
            ->with(['userOne.profile', 'userTwo.profile']);

        $connections = $query->get()->map(function ($connection) use ($userId) {
            return $connection->user_one_id === $userId ? $connection->userTwo : $connection->userOne;
        });

        // Filter out existing members
        $existingMemberUserIds = CommunityMember::where('community_id', $this->community->id)
            ->where('status', 'active')
            ->pluck('user_id')
            ->toArray();

        $connections = $connections->reject(function ($user) use ($existingMemberUserIds) {
            return in_array($user->id, $existingMemberUserIds);
        });

        if (! empty($search)) {
            $connections = $connections->filter(function ($user) use ($search) {
                return Str::contains(strtolower($user->name), strtolower($search));
            });
        }

        return $connections->values();
    }

    public function sendInvites(): void
    {
        if (empty($this->selectedInviteUserIds)) {
            $this->dispatch('notify', type: 'error', message: 'Vui lòng chọn ít nhất một người để mời.');

            return;
        }

        foreach ($this->selectedInviteUserIds as $friendId) {
            $friend = User::findOrFail($friendId);
            $conversation = app(FindOrCreateDirectConversation::class)->execute(auth()->user(), $friend);

            $shareLink = route('community.show', $this->community->id);
            $messageBody = "Chào {$friend->name}! Mình muốn mời bạn tham gia cộng đồng: {$this->community->name}\nTham gia tại đây: {$shareLink}";

            $message = $conversation->messages()->create([
                'sender_id' => auth()->id(),
                'body' => $messageBody,
            ]);
            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => $message->created_at ?? now(),
            ]);
            app(\App\Support\Navigation\UserNavigationMetrics::class)->forgetForUser(auth()->id());
            app(\App\Support\Navigation\UserNavigationMetrics::class)->forgetForUser($friend);
        }

        $this->showInviteModal = false;
        $this->selectedInviteUserIds = [];
        $this->dispatch('notify', type: 'success', message: 'Đã gửi lời mời tham gia cộng đồng.');
    }

    public function startShareCommunity(): void
    {
        $this->shareSearch = '';
        $this->selectedShareUserId = null;
        $this->shareOptionalMessage = '';
        $this->showShareModal = true;
    }

    public function executeShareCommunity(
        SendSharedPostMessage $sendSharedPostMessage,
        FindOrCreateDirectConversation $findOrCreateDirectConversation
    ): void {
        if (! $this->selectedShareUserId) {
            return;
        }

        try {
            $recipient = User::findOrFail($this->selectedShareUserId);
            $conversation = $findOrCreateDirectConversation->execute(auth()->user(), $recipient);

            $shareLink = route('community.show', $this->community->id);
            $messageBody = "Mình muốn chia sẻ cộng đồng này với bạn: {$this->community->name}\nLiên kết: {$shareLink}";
            if ($this->shareOptionalMessage) {
                $messageBody .= "\n\nLời nhắn: ".$this->shareOptionalMessage;
            }

            $message = $conversation->messages()->create([
                'sender_id' => auth()->id(),
                'body' => $messageBody,
            ]);
            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => $message->created_at ?? now(),
            ]);
            app(\App\Support\Navigation\UserNavigationMetrics::class)->forgetForUser(auth()->id());
            app(\App\Support\Navigation\UserNavigationMetrics::class)->forgetForUser($recipient);

            $this->showShareModal = false;
            $this->dispatch('notify', type: 'success', message: 'Đã chia sẻ cộng đồng qua tin nhắn thành công.');
        } catch (Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function getShareConnectionsProperty(): Collection
    {
        $userId = auth()->id();
        $search = trim($this->shareSearch);

        $query = Connection::where(function ($q) use ($userId) {
            $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
        })
            ->where('status', ConnectionStatus::ACTIVE)
            ->with(['userOne.profile', 'userTwo.profile']);

        $connections = $query->get()->map(function ($connection) use ($userId) {
            return $connection->user_one_id === $userId ? $connection->userTwo : $connection->userOne;
        });

        if (! empty($search)) {
            $connections = $connections->filter(function ($user) use ($search) {
                return Str::contains(strtolower($user->name), strtolower($search));
            });
        }

        return $connections->values();
    }

    /**
     * Handle community cover uploads.
     */
    public function updatedCoverFile(): void
    {
        try {
            $this->validate([
                'coverFile' => 'image|max:5120', // 5MB limit
            ]);

            $storeAction = app(StoreTemporaryMediaAction::class);
            $attachAction = app(AttachMediaToModelAction::class);
            $deleteAction = app(DeleteMediaAction::class);

            if (! $this->canManage) {
                $this->dispatch('notify', type: 'error', message: 'Bạn không có quyền quản lý cộng đồng này.');
                return;
            }

            $oldCover = $this->community->cover()->first();

            // Store new temporary media (public visibility)
            $media = $storeAction->execute(auth()->user(), $this->coverFile, 'community_cover', ['visibility' => 'public']);

            // Attach to the Community model if it is different
            if (!$oldCover || $oldCover->id !== $media->id) {
                if ($oldCover) {
                    $deleteAction->execute($oldCover);
                }
                $attachAction->execute(auth()->user(), $this->community, [$media->id], 'community_cover');
            }

            $this->community->load('media');
            $this->dispatch('notify', type: 'success', message: 'Cập nhật ảnh bìa cộng đồng thành công.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('notify', type: 'error', message: $e->validator->errors()->first());
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Lỗi tải ảnh lên: ' . $e->getMessage());
        }
    }

    /**
     * Handle community avatar uploads.
     */
    public function updatedAvatarFile(): void
    {
        try {
            $this->validate([
                'avatarFile' => 'image|max:5120', // 5MB limit
            ]);

            $storeAction = app(StoreTemporaryMediaAction::class);
            $attachAction = app(AttachMediaToModelAction::class);
            $deleteAction = app(DeleteMediaAction::class);

            if (! $this->canManage) {
                $this->dispatch('notify', type: 'error', message: 'Bạn không có quyền quản lý cộng đồng này.');
                return;
            }

            $oldAvatar = $this->community->avatar()->first();

            // Store new temporary media (public visibility)
            $media = $storeAction->execute(auth()->user(), $this->avatarFile, 'community_avatar', ['visibility' => 'public']);

            // Attach to the Community model if it is different
            if (!$oldAvatar || $oldAvatar->id !== $media->id) {
                if ($oldAvatar) {
                    $deleteAction->execute($oldAvatar);
                }
                $attachAction->execute(auth()->user(), $this->community, [$media->id], 'community_avatar');
            }

            $this->community->load('media');
            $this->dispatch('notify', type: 'success', message: 'Cập nhật ảnh đại diện cộng đồng thành công.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('notify', type: 'error', message: $e->validator->errors()->first());
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Lỗi tải ảnh lên: ' . $e->getMessage());
        }
    }

    /**
     * Get safe cover URL.
     */
    public function getCoverUrlProperty(): ?string
    {
        $media = $this->community->cover()->first();
        return \App\Support\Media\MediaUrlResolver::publicUrl($media, 'desktop');
    }

    /**
     * Get safe avatar URL.
     */
    public function getAvatarUrlProperty(): ?string
    {
        $media = $this->community->avatar()->first();
        return \App\Support\Media\MediaUrlResolver::publicUrl($media, 'display');
    }

    public function toggleLike(int $postId, TogglePostLike $togglePostLike): void
    {
        try {
            $post = Post::findOrFail($postId);
            $togglePostLike->execute(Auth::user(), $post);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    public function toggleSave(int $postId, TogglePostSave $togglePostSave): void
    {
        try {
            $post = Post::findOrFail($postId);
            $togglePostSave->execute(Auth::user(), $post);
            $this->dispatch('notify', type: 'success', message: 'Đã cập nhật trạng thái lưu bài viết.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    public function toggleRepost(int $postId, TogglePostRepost $togglePostRepost): void
    {
        $post = Post::findOrFail($postId);
        try {
            $isReposted = $togglePostRepost->execute(Auth::user(), $post);
            $this->dispatch('notify', type: 'success', message: $isReposted ? 'Đã đăng lại bài viết.' : 'Đã hủy đăng lại bài viết.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    public function startEdit(int $postId): void
    {
        $post = Post::findOrFail($postId);
        if (! Gate::allows('update', $post)) {
            $this->dispatch('notify', type: 'error', message: 'Bạn không có quyền chỉnh sửa bài viết này.');
            return;
        }
        $this->editingPostId = $postId;
        $this->editingBody = $post->body;
    }

    public function saveEdit(UpdatePost $updatePost): void
    {
        if (! $this->editingPostId) {
            return;
        }
        try {
            $post = Post::findOrFail($this->editingPostId);
            $this->validate([
                'editingBody' => 'required|string|max:3000',
            ]);
            $updatePost->execute(Auth::user(), $post, [
                'body' => $this->editingBody,
            ]);
            $this->editingPostId = null;
            $this->editingBody = '';
            $this->dispatch('notify', type: 'success', message: 'Đã cập nhật bài viết thành công.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('editingBody', $e->getMessage());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
            $this->editingPostId = null;
            $this->editingBody = '';
        }
    }

    public function cancelEdit(): void
    {
        $this->editingPostId = null;
        $this->editingBody = '';
    }

    public function openDeleteModal(int $postId): void
    {
        $this->deletingPostId = $postId;
        $this->showDeleteModal = true;
    }

    public function executeDelete(DeletePost $deletePost): void
    {
        if (! $this->deletingPostId) {
            return;
        }
        try {
            $post = Post::findOrFail($this->deletingPostId);
            $deletePost->execute(Auth::user(), $post);
            $this->dispatch('notify', type: 'success', message: 'Đã xóa bài viết thành công.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
        $this->deletingPostId = null;
        $this->showDeleteModal = false;
    }

    public function openReport(int $postId): void
    {
        $this->reportingPost = Post::findOrFail($postId);
        $this->reportReason = 'spam';
        $this->reportDescription = '';
        $this->showReportModal = true;
        $this->resetErrorBag();
    }

    public function submitReport(CreateReport $createReport): void
    {
        if (! $this->reportingPost) {
            return;
        }
        try {
            $createReport->execute(Auth::user(), $this->reportingPost, [
                'reason' => $this->reportReason,
                'description' => $this->reportDescription,
            ]);
            $this->showReportModal = false;
            $this->reportingPost = null;
            $this->dispatch('notify', type: 'success', message: 'Báo cáo của bạn đã được gửi. Cảm ơn bạn đã góp phần xây dựng cộng đồng HCMUE an toàn.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('report', $e->getMessage());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->showReportModal = false;
            $this->reportingPost = null;
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function closeReport(): void
    {
        $this->showReportModal = false;
        $this->reportingPost = null;
    }

    public function hidePost(int $postId, HidePostFromFeed $hidePostFromFeed): void
    {
        try {
            $post = Post::findOrFail($postId);
            $hidePostFromFeed->execute(Auth::user(), $post);
            $this->locallyHiddenPostIds[] = $postId;
            $this->dispatch('notify', type: 'success', message: 'Đã ẩn bài viết khỏi bảng tin của bạn.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function undoHidePost(int $postId): void
    {
        $user = Auth::user();
        \App\Models\PostHide::where('post_id', $postId)
            ->where('user_id', $user->id)
            ->delete();
        $this->locallyHiddenPostIds = array_diff($this->locallyHiddenPostIds, [$postId]);
        $this->dispatch('notify', type: 'success', message: 'Đã hoàn tác ẩn bài viết.');
    }

    public function hidePostGlobally(int $postId): void
    {
        if (! Auth::user()->hasPermissionTo('moderate_content')) {
            $this->dispatch('notify', type: 'error', message: 'Bạn không có quyền thực hiện hành động này.');
            return;
        }
        $post = Post::findOrFail($postId);
        $post->status = PostStatus::HIDDEN_BY_MODERATION;
        $post->save();
        $this->dispatch('notify', type: 'success', message: 'Đã ẩn bài viết khỏi cộng đồng.');
    }

    public function copyLinkFeedback(): void
    {
        $this->dispatch('notify', type: 'success', message: 'Đã sao chép liên kết bài viết vào bộ nhớ tạm.');
    }

    public function toggleFollow(int $userId, FollowUser $followUser, UnfollowUser $unfollowUser): void
    {
        try {
            $targetUser = User::findOrFail($userId);
            $currentUser = Auth::user();
            if ($currentUser->id === $targetUser->id) {
                $this->dispatch('notify', type: 'error', message: 'Bạn không thể tự theo dõi chính mình.');
                return;
            }
            $isFollowing = \App\Models\UserFollow::where('follower_id', $currentUser->id)
                ->where('following_id', $targetUser->id)
                ->exists();
            if ($isFollowing) {
                $unfollowUser->execute($currentUser, $targetUser);
                $this->dispatch('notify', type: 'success', message: 'Đã bỏ theo dõi ' . $targetUser->name . '.');
            } else {
                $followUser->execute($currentUser, $targetUser);
                $this->dispatch('notify', type: 'success', message: 'Đã theo dõi ' . $targetUser->name . '.');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function openQuickFollowModal(int $authorId): void
    {
        $author = User::findOrFail($authorId);
        $currentUser = Auth::user();
        if ($author->id === $currentUser->id) {
            $this->dispatch('notify', type: 'error', message: 'Bạn không thể tự theo dõi chính mình.');
            return;
        }
        $isFriend = Connection::where(function ($query) use ($currentUser, $author) {
                $query->where('user_one_id', $currentUser->id)->where('user_two_id', $author->id);
            })
            ->orWhere(function ($query) use ($currentUser, $author) {
                $query->where('user_one_id', $author->id)->where('user_two_id', $currentUser->id);
            })
            ->where('status', ConnectionStatus::ACTIVE)
            ->exists();
        if ($isFriend) {
            $this->dispatch('notify', type: 'error', message: 'Người dùng này đã kết nối bạn bè với bạn.');
            return;
        }
        $this->quickFollowUserId = $author->id;
        $this->quickFollowCompleted = \App\Models\UserFollow::where('follower_id', $currentUser->id)
            ->where('following_id', $author->id)
            ->exists();
        $this->showQuickFollowModal = true;
    }

    public function confirmQuickFollow(FollowUser $followUser): void
    {
        if (! $this->quickFollowUserId) {
            return;
        }
        $author = User::findOrFail($this->quickFollowUserId);
        try {
            $followUser->execute(Auth::user(), $author);
            $this->quickFollowCompleted = true;
            $this->dispatch('notify', type: 'success', message: 'Đã theo dõi ' . $author->name . '.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('notify', type: 'error', message: collect($e->errors())->flatten()->first() ?: 'Không thể theo dõi người dùng này.');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function confirmQuickUnfollow(UnfollowUser $unfollowUser): void
    {
        if (! $this->quickFollowUserId) {
            return;
        }
        $author = User::findOrFail($this->quickFollowUserId);
        try {
            $unfollowUser->execute(Auth::user(), $author);
            $this->quickFollowCompleted = false;
            $this->dispatch('notify', type: 'success', message: 'Đã bỏ theo dõi ' . $author->name . '.');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function closeQuickFollowModal(): void
    {
        $this->showQuickFollowModal = false;
        $this->quickFollowUserId = null;
        $this->quickFollowCompleted = false;
    }

    public function startShare(int $postId): void
    {
        $post = Post::findOrFail($postId);
        if (! Gate::allows('share', $post)) {
            $this->dispatch('notify', type: 'error', message: 'Bạn không có quyền chia sẻ bài viết này.');
            return;
        }
        $this->sharingPostId = $postId;
        $this->postShareSearch = '';
        $this->selectedShareUserIds = [];
        $this->postShareOptionalMessage = '';
        $this->showShareModal = true;
    }

    public function toggleShareRecipient(int $userId): void
    {
        $selectedUserIds = collect($this->selectedShareUserIds)
            ->map(fn ($selectedUserId) => (int) $selectedUserId)
            ->unique()
            ->values();
        if ($selectedUserIds->contains($userId)) {
            $this->selectedShareUserIds = $selectedUserIds
                ->reject(fn ($selectedUserId) => $selectedUserId === $userId)
                ->values()
                ->all();
            return;
        }
        $this->selectedShareUserIds = $selectedUserIds
            ->push($userId)
            ->unique()
            ->values()
            ->all();
    }

    public function executeShare(
        SendSharedPostMessage $sendSharedPostMessage,
        FindOrCreateDirectConversation $findOrCreateDirectConversation
    ): void {
        $selectedUserIds = collect($this->selectedShareUserIds)
            ->map(fn ($selectedUserId) => (int) $selectedUserId)
            ->filter()
            ->unique()
            ->values();
        if (! $this->sharingPostId || $selectedUserIds->isEmpty()) {
            return;
        }
        $post = Post::findOrFail($this->sharingPostId);
        $sentCount = 0;
        $failedRecipients = [];
        foreach ($selectedUserIds as $recipientId) {
            try {
                $recipient = User::findOrFail($recipientId);
                $conversation = $findOrCreateDirectConversation->execute(Auth::user(), $recipient);
                $sendSharedPostMessage->execute(Auth::user(), $conversation, $post, [
                    'body' => $this->postShareOptionalMessage ?: null,
                ]);
                $sentCount++;
            } catch (\Exception $e) {
                $failedRecipients[] = User::find($recipientId)?->name ?? "ID {$recipientId}";
            }
        }
        if ($sentCount > 0) {
            $this->showShareModal = false;
            $this->sharingPostId = null;
            $this->selectedShareUserIds = [];
            $this->postShareOptionalMessage = '';
            $this->postShareSearch = '';
            if ($failedRecipients === []) {
                $this->dispatch('notify', type: 'success', message: "Đã chia sẻ bài viết qua tin nhắn cho {$sentCount} người nhận.");
                return;
            }
            $this->dispatch('notify', type: 'warning', message: "Đã gửi cho {$sentCount} người nhận. Không gửi được cho: ".implode(', ', $failedRecipients).'.');
            return;
        }
        $this->dispatch('notify', type: 'error', message: 'Không gửi được bài viết cho người nhận đã chọn: '.implode(', ', $failedRecipients).'.');
    }

    public function getSharePostConnections(): Collection
    {
        $userId = Auth::id();
        $search = trim($this->postShareSearch);
        $query = Connection::where(function ($q) use ($userId) {
                $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
            })
            ->where('status', ConnectionStatus::ACTIVE)
            ->with(['userOne.profile', 'userTwo.profile']);
        $connections = $query->get()->map(function ($connection) use ($userId) {
            return $connection->user_one_id === $userId ? $connection->userTwo : $connection->userOne;
        });
        if (! empty($search)) {
            $connections = $connections->filter(function ($user) use ($search) {
                return Str::contains(strtolower($user->name), strtolower($search)) ||
                       ($user->profile && Str::contains(strtolower($user->profile->display_name), strtolower($search)));
            });
        }
        return $connections->values();
    }
};
?>

<div class="flex flex-col lg:flex-row min-h-screen bg-white w-full">
    
    {{-- 1. Persistent Left Sidebar (matching communities index for consistent UX) --}}
    <aside class="hidden lg:flex flex-col w-80 bg-white border-r border-slate-200 flex-shrink-0 p-4 sticky top-0 h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Cộng đồng & CLB</h1>
            <a href="{{ route('community.index') }}" class="text-xs font-semibold text-slate-400 hover:text-ue-brand transition">Thoát</a>
        </div>

        <nav class="space-y-1 mb-6">
            <a href="{{ route('community.index') }}?subTab=feed"
                class="ue-sidebar-subnav-link">
                <x-ui.icon name="message-square" size="xs" />
                <span>Bảng feed của bạn</span>
            </a>
            <a href="{{ route('community.index') }}?subTab=discover"
                class="ue-sidebar-subnav-link">
                <x-ui.icon name="users" size="xs" />
                <span>Khám phá</span>
            </a>
            <a href="{{ route('community.index') }}?subTab=mine"
                class="ue-sidebar-subnav-link">
                <x-ui.icon name="user" size="xs" />
                <span>Nhóm của bạn</span>
            </a>
        </nav>

        <hr class="border-slate-200 mb-4" />

        {{-- Persistent Joined Groups List --}}
        <div class="flex-1 overflow-y-auto">
            <div class="flex items-center justify-between mb-3 px-2">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Nhóm bạn đã tham gia</h2>
            </div>
            
            <div class="space-y-1">
                @foreach ($this->joinedCommunities as $c)
                    <a href="{{ route('community.show', $c->id) }}" wire:navigate
                        class="flex items-center gap-3 p-2 rounded-xl transition group {{ $c->id === $community->id ? 'bg-ue-brand-soft text-ue-brand' : 'hover:bg-slate-100' }}">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-ue-brand/20 to-ue-brand/5 border border-slate-150 flex items-center justify-center text-ue-brand flex-shrink-0">
                            <x-ui.icon name="users" size="sm" class="text-ue-brand" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold truncate {{ $c->id === $community->id ? 'text-ue-brand' : 'text-slate-800 group-hover:text-ue-brand' }}">{{ $c->name }}</p>
                            <p class="text-[10px] text-slate-400 font-medium truncate mt-0.5">{{ number_format($c->members_count) }} thành viên</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </aside>

    {{-- 2. Detail Content Layout --}}
    <main class="flex-1 overflow-y-auto">
        
        {{-- Detail Header Card --}}
        <header class="bg-white border-b border-slate-200 shadow-2xs">
            {{-- Cover Photo Section --}}
            <div class="relative h-44 sm:h-56 md:h-64 lg:h-72 bg-gradient-to-br from-[#0A3761] via-[#124874] to-[#4BB7E8] flex items-center justify-center overflow-hidden">
                @if ($this->coverUrl)
                    <img src="{{ $this->coverUrl }}" class="w-full h-full object-cover absolute inset-0" alt="{{ $community->name }}">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <x-ui.icon name="users" class="w-24 h-24 text-white/10 select-none" />
                    </div>
                @endif
                
                @if ($this->canManage)
                    <label class="absolute bottom-4 right-4 bg-white/80 backdrop-blur-xs text-slate-800 text-xs font-bold p-2 sm:px-3 sm:py-1.5 rounded-lg border border-slate-250 hover:bg-white transition flex items-center gap-1.5 shadow-sm cursor-pointer z-10">
                        <x-ui.icon name="camera" size="xs" />
                        <span class="hidden sm:inline">Chỉnh sửa ảnh bìa</span>
                        <input type="file" wire:model="coverFile" class="hidden" accept="image/jpeg,image/png,image/webp">
                    </label>
                    @error('coverFile')
                        <span class="absolute bottom-16 right-4 bg-red-500 text-white text-[10px] font-semibold px-2 py-1 rounded-md shadow-sm z-20">
                            {{ $message }}
                        </span>
                    @enderror
                @endif
            </div>

            {{-- Info block --}}
            <div class="max-w-5xl mx-auto px-4 sm:px-6 py-5 flex flex-col md:flex-row md:items-end justify-between gap-5">
                {{-- Overlapping Avatar & details --}}
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 -mt-16 sm:-mt-20 md:-mt-24 relative z-10">
                    <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-2xl bg-ue-brand text-white border-4 border-white flex items-center justify-center font-black text-3xl sm:text-4xl shadow-md select-none overflow-hidden relative group">
                        @if ($this->avatarUrl)
                            <img src="{{ $this->avatarUrl }}" class="w-full h-full object-cover" alt="{{ $community->name }}">
                        @else
                            <x-ui.icon name="users" class="w-12 h-12 sm:w-16 sm:h-16 text-white" />
                        @endif

                        @if ($this->canManage)
                            <label class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center cursor-pointer text-white text-xs font-bold">
                                <x-ui.icon name="camera" size="sm" class="text-white" />
                                <input type="file" wire:model="avatarFile" class="hidden" accept="image/jpeg,image/png,image/webp">
                            </label>
                        @endif

                        @error('avatarFile')
                            <div class="absolute inset-0 bg-red-950/85 text-white text-[10px] font-semibold flex flex-col items-center justify-center p-2 text-center z-20">
                                <x-ui.icon name="alert-triangle" size="xs" class="text-red-400 mb-1" />
                                <span class="leading-tight">{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <div class="text-center sm:text-left pt-2 sm:pt-14 md:pt-20">
                        <div class="flex items-center justify-center sm:justify-start gap-2">
                            <h2 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight leading-tight">{{ $community->name }}</h2>
                            <x-ui.icon name="check-circle" size="xs" class="text-ue-brand fill-ue-brand flex-shrink-0" title="Cộng đồng xác thực" />
                        </div>

                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 text-xs text-slate-600 font-semibold mt-1">
                            <span class="flex items-center gap-1">
                                <x-ui.icon name="{{ $community->visibility?->value === 'public' ? 'unlock' : 'lock' }}" size="2xs" />
                                {{ $community->visibility?->label() }}
                            </span>
                            <span>·</span>
                            <span>{{ number_format($community->members_count) }} thành viên</span>
                            @if ($community->related_faculty)
                                <span>·</span>
                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-md text-[10px] font-bold">{{ $community->related_faculty }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- CTAs row --}}
                <div class="flex flex-wrap items-center justify-center gap-2 relative z-10 pt-2 md:pt-0">
                    
                    @if ($community->isOwnedBy(auth()->user()))
                        <span class="px-3.5 py-2 bg-amber-50 text-amber-700 border border-amber-200 rounded-xl text-xs font-bold flex items-center gap-1 shadow-2xs">
                            <x-ui.icon name="shield" size="xs" />
                            <span>Chủ sở hữu</span>
                        </span>
                    @elseif ($this->isActiveMember)
                        <button wire:click="openLeaveModal"
                            class="px-3.5 py-2 border border-slate-250 text-slate-700 hover:bg-slate-50 hover:text-red-700 rounded-xl text-xs font-bold transition">
                            Đã tham gia
                        </button>
                    @elseif ($this->hasPendingRequest)
                        <span class="px-3.5 py-2 bg-yellow-50 text-yellow-700 border border-yellow-250 rounded-xl text-xs font-bold shadow-2xs">
                            Đang chờ duyệt
                        </span>
                    @elseif ($community->allowsJoin())
                        <button wire:click="openJoinModal"
                            class="px-4 py-2 bg-ue-brand hover:bg-opacity-95 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            {{ $community->requiresApproval() ? 'Gửi yêu cầu' : 'Tham gia' }}
                        </button>
                    @endif

                    @if ($this->isActiveMember)
                        <button wire:click="startInvite"
                            class="px-3.5 py-2 bg-ue-brand hover:bg-opacity-95 text-white rounded-xl text-xs font-bold transition shadow-2xs flex items-center gap-1">
                            <x-ui.icon name="user-plus" size="xs" />
                            Mời bạn
                        </button>
                    @endif

                    <button wire:click="startShareCommunity"
                        class="px-3.5 py-2 border border-slate-250 bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-2xs">
                        <x-ui.icon name="send" size="xs" />
                        Chia sẻ
                    </button>

                    {{-- More Menu Options dropdown --}}
                    <div class="relative" x-data="{ openMenu: false }" @click.away="openMenu = false">
                        <button @click="openMenu = !openMenu"
                            class="p-2 border border-slate-250 bg-white hover:bg-slate-50 text-slate-700 rounded-xl transition shadow-2xs flex items-center justify-center">
                            <x-ui.icon name="more-horizontal" size="xs" />
                        </button>
                        <div x-show="openMenu" x-transition class="absolute right-0 mt-1.5 w-48 bg-white border border-slate-200 rounded-xl shadow-lg py-1.5 z-40 text-xs font-bold text-slate-700" style="display:none;">
                            <button type="button" @click="navigator.clipboard.writeText('{{ route('community.show', $community->id) }}'); alert('Đã sao chép liên kết vào bộ nhớ tạm!'); openMenu = false"
                                class="w-full text-left px-4 py-2 hover:bg-slate-100 flex items-center gap-2 text-black"
                                style="color: #000000 !important;">
                                <x-ui.icon name="send" size="xs" class="text-slate-400" style="color: #000000 !important;" />
                                <span style="color: #000000 !important;">Sao chép liên kết</span>
                            </button>
                            <button type="button" @click="alert('Đã bật thông báo từ nhóm này.'); openMenu = false"
                                class="w-full text-left px-4 py-2 hover:bg-slate-100 flex items-center gap-2 text-black"
                                style="color: #000000 !important;">
                                <x-ui.icon name="bell" size="xs" class="text-slate-400" style="color: #000000 !important;" />
                                <span style="color: #000000 !important;">Bật thông báo</span>
                            </button>
                            @if ($this->isActiveMember && ! $community->isOwnedBy(auth()->user()))
                                <button type="button" wire:click="openLeaveModal" @click="openMenu = false"
                                    class="w-full text-left px-4 py-2 hover:bg-red-50 flex items-center gap-2 border-t border-slate-100 text-red-600"
                                    style="color: #dc2626 !important;">
                                    <x-ui.icon name="log-out" size="xs" class="text-red-500" style="color: #ef4444 !important;" />
                                    <span style="color: #dc2626 !important;">Rời nhóm</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Horizontal Navigation Tabs --}}
            <div class="border-t border-slate-200 sticky top-0 bg-white/95 backdrop-blur-xs z-30">
                <div class="max-w-5xl mx-auto px-2 sm:px-6 flex gap-1.5 overflow-x-auto">
                    @php
                        $tabs = [
                            ['key' => 'feed', 'label' => 'Bảng tin'],
                            ['key' => 'resources', 'label' => 'Tài nguyên'],
                            ['key' => 'events', 'label' => 'Sự kiện'],
                            ['key' => 'members', 'label' => 'Thành viên'],
                            ['key' => 'about', 'label' => 'Giới thiệu'],
                        ];
                        if ($this->canManage) {
                            $tabs[] = ['key' => 'settings', 'label' => 'Cài đặt quản trị'];
                        }
                    @endphp
                    @foreach ($tabs as $tab)
                        <button wire:click="setActiveTab('{{ $tab['key'] }}')"
                            class="px-4 py-3.5 text-xs font-bold border-b-3 transition whitespace-nowrap {{ $activeTab === $tab['key'] ? 'border-ue-brand text-ue-brand' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                            {{ $tab['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>
        </header>

        {{-- Active View Rendering --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Main Left Block --}}
                <div class="lg:col-span-2 space-y-4">
                    
                    {{-- 2.1 Tab Bảng tin --}}
                    @if ($activeTab === 'feed')
                        {{-- Composer Box --}}
                        @if ($this->isActiveMember && ($community->isActive() || $this->canManage))
                            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs flex gap-3">
                                <x-ui.avatar :user="auth()->user()" size="md" />
                                <button wire:click="openPostComposer"
                                    class="flex-1 text-left px-4 py-3 bg-slate-100 rounded-2xl text-slate-500 text-xs font-semibold hover:bg-slate-150 transition">
                                    Chia sẻ điều gì đó với mọi người trong cộng đồng...
                                </button>
                            </div>
                        @endif

                        {{-- Feed list --}}
                        <div class="space-y-4">
                            @forelse ($this->feedPosts as $post)
                                @if (in_array($post->id, $locallyHiddenPostIds))
                                    <div class="bg-white border border-slate-200 rounded-2xl p-4 flex items-center justify-between gap-4 shadow-sm" wire:key="hidden-post-{{ $post->id }}">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-slate-105 border border-slate-200 flex items-center justify-center text-slate-400">
                                                <x-ui.icon name="eye-off" size="xs" />
                                            </div>
                                            <div class="text-left">
                                                <h4 class="text-xs font-bold text-slate-800">Đã ẩn bài viết</h4>
                                                <p class="text-[10px] text-slate-500">Bài viết đã được ẩn khỏi bảng tin của bạn.</p>
                                            </div>
                                        </div>
                                        <button type="button" wire:click="undoHidePost({{ $post->id }})"
                                            class="px-3 py-1.5 text-xs font-bold text-ue-brand bg-ue-brand-soft rounded-xl hover:bg-ue-brand hover:text-white transition">
                                            Hoàn tác
                                        </button>
                                    </div>
                                @else
                                    <article class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" wire:key="detail-post-{{ $post->id }}">
                                        <x-ui.post-card
                                            :post="$post"
                                            :currentUser="auth()->user()"
                                            :isSaved="(int) $post->saved_by_current_user_count > 0"
                                            :isLiked="(int) $post->liked_by_current_user_count > 0"
                                            :likeCount="(int) $post->likes_count"
                                            :commentCount="(int) $post->published_comments_count"
                                            :editingPostId="$editingPostId"
                                            :editingBody="$editingBody"
                                        />
                                    </article>
                                @endif
                            @empty
                                <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-450 italic text-sm">
                                    Chưa có bài đăng nào trong nhóm. Hãy bắt đầu cuộc thảo luận đầu tiên!
                                </div>
                            @endforelse

                            @if ($this->feedPosts->isNotEmpty() && $this->feedPosts->hasPages())
                                <div class="pt-4">{{ $this->feedPosts->links() }}</div>
                            @endif
                        </div>
                    @endif

                    {{-- 2.2 Tab Tài nguyên --}}
                    @if ($activeTab === 'resources')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                                <h3 class="text-sm font-extrabold text-slate-800">Kho tài liệu, liên kết & học liệu</h3>
                                @if ($this->isActiveMember)
                                    <button wire:click="openResourceModal"
                                        class="px-4 py-2 bg-ue-brand hover:bg-opacity-95 text-white rounded-xl text-xs font-bold transition shadow-2xs">
                                        + Đăng tài nguyên
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @forelse ($this->publishedResources as $res)
                                    <div class="bg-white border border-slate-200 rounded-2xl p-4 flex flex-col justify-between hover:shadow-2xs transition">
                                        <div>
                                            <div class="flex items-start justify-between gap-3">
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 border border-slate-150 rounded-md text-[9px] font-bold uppercase tracking-wider select-none">
                                                    {{ $res->resource_type?->label() }}
                                                </span>
                                                <span class="text-[10px] text-slate-400 font-semibold">{{ $res->created_at->format('d/m/Y') }}</span>
                                            </div>

                                            <h4 class="font-bold text-xs text-slate-800 mt-2.5 leading-normal line-clamp-1">{{ $res->title }}</h4>
                                            
                                            @if ($res->description)
                                                <p class="text-[11px] text-slate-500 mt-1 line-clamp-2 leading-relaxed">{{ $res->description }}</p>
                                            @else
                                                <p class="text-[11px] text-slate-350 italic mt-1">Không có mô tả bổ sung.</p>
                                            @endif
                                        </div>

                                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                                            <span class="text-[10px] text-slate-500 font-bold truncate max-w-[120px]">
                                                Đăng: {{ $res->submitter?->name }}
                                            </span>

                                            <div class="flex gap-1.5 flex-shrink-0">
                                                @if ($res->mediaFile)
                                                    <a href="{{ Storage::disk($res->mediaFile->disk)->url($res->mediaFile->path) }}" download="{{ $res->mediaFile->original_name }}"
                                                        class="px-2.5 py-1.5 bg-ue-brand text-white rounded-lg text-[10px] font-bold hover:bg-opacity-95 transition flex items-center gap-1">
                                                        <x-ui.icon name="download" size="2xs" />
                                                        <span>Tải xuống</span>
                                                    </a>
                                                @endif
                                                @if ($res->url)
                                                    <a href="{{ $res->url }}" target="_blank" rel="noopener noreferrer"
                                                        class="px-2.5 py-1.5 bg-slate-100 border border-slate-200 text-slate-700 rounded-lg text-[10px] font-bold hover:bg-slate-200 transition flex items-center gap-1">
                                                        <x-ui.icon name="external-link" size="2xs" />
                                                        <span>Mở</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-500 italic text-sm shadow-2xs">
                                        Kho tài nguyên chưa có dữ liệu chia sẻ.
                                    </div>
                                @endforelse
                            </div>

                            @if ($this->publishedResources->isNotEmpty() && $this->publishedResources->hasPages())
                                <div class="pt-4">{{ $this->publishedResources->links() }}</div>
                            @endif
                        </div>
                    @endif

                    {{-- 2.3 Tab Sự kiện --}}
                    @if ($activeTab === 'events')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                                <h3 class="text-sm font-extrabold text-slate-800">Sự kiện sắp tới</h3>
                                @if ($this->canCreateEvents)
                                    <button wire:click="openEventModal"
                                        class="px-4 py-2 bg-ue-brand hover:bg-opacity-95 text-white rounded-xl text-xs font-bold transition shadow-2xs">
                                        + Tạo sự kiện
                                    </button>
                                @endif
                            </div>

                            <div class="space-y-4">
                                @forelse ($this->upcomingEvents as $evt)
                                    @php $rsvpStatus = $evt->rsvps->first()?->status?->value; @endphp
                                    <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row gap-4 hover:shadow-2xs transition">
                                        
                                        {{-- Calendar Block --}}
                                        <div class="w-16 h-16 rounded-xl bg-ue-brand-soft border border-ue-brand-border flex flex-col items-center justify-center flex-shrink-0">
                                            <span class="text-2xl font-black text-ue-brand leading-none">{{ $evt->starts_at->format('d') }}</span>
                                            <span class="text-[9px] font-bold text-ue-brand uppercase mt-0.5 tracking-wider">{{ $evt->starts_at->translatedFormat('M') }}</span>
                                        </div>

                                        {{-- Text Column --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="font-extrabold text-slate-800 text-sm leading-tight truncate">{{ $evt->title }}</h4>
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-md text-[9px] font-bold uppercase select-none">
                                                    {{ $evt->event_type === 'online' ? 'Online' : ($evt->event_type === 'hybrid' ? 'Kết hợp' : 'Trực tiếp') }}
                                                </span>
                                            </div>

                                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-slate-500 text-xxs font-semibold mt-1">
                                                <span class="flex items-center gap-1">
                                                    <x-ui.icon name="clock" size="xs" />
                                                    {{ $evt->starts_at->format('H:i') }} - {{ $evt->ends_at ? $evt->ends_at->format('H:i') : 'Trong ngày' }}
                                                </span>
                                                @if ($evt->location)
                                                    <span>·</span>
                                                    <span class="truncate max-w-[150px]">{{ $evt->location }}</span>
                                                @endif
                                            </div>

                                            @if ($evt->description)
                                                <p class="text-xs text-slate-500 mt-2 whitespace-pre-line leading-normal">{{ $evt->description }}</p>
                                            @endif

                                            <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xxs font-bold text-slate-400">
                                                <div class="flex gap-3">
                                                    <span>{{ $evt->going_count }} tham gia</span>
                                                    <span>{{ $evt->interested_count }} quan tâm</span>
                                                </div>

                                                {{-- RSVP Buttons --}}
                                                @if ($this->isActiveMember && $evt->isPublished())
                                                    <div class="flex gap-1.5 flex-shrink-0">
                                                        <button wire:click="rsvpEvent({{ $evt->id }}, 'going')"
                                                            class="px-2.5 py-1 text-[10px] font-bold rounded-lg border transition {{ $rsvpStatus === 'going' ? 'bg-green-600 text-white border-green-600' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                                            Sẽ tham gia
                                                        </button>
                                                        <button wire:click="rsvpEvent({{ $evt->id }}, 'interested')"
                                                            class="px-2.5 py-1 text-[10px] font-bold rounded-lg border transition {{ $rsvpStatus === 'interested' ? 'bg-ue-brand text-white border-ue-brand' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                                            Quan tâm
                                                        </button>
                                                        <button wire:click="rsvpEvent({{ $evt->id }}, 'declined')"
                                                            class="px-2.5 py-1 text-[10px] font-bold rounded-lg border transition {{ $rsvpStatus === 'declined' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                                            Từ chối
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-500 italic text-sm shadow-2xs">
                                        Chưa có sự kiện nào được công bố.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    {{-- 2.4 Tab Thành viên --}}
                    @if ($activeTab === 'members')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                                <h3 class="text-sm font-extrabold text-slate-800">Danh sách thành viên ({{ number_format($community->members_count) }})</h3>
                            </div>

                            {{-- Invite box for managers --}}
                            @if ($this->canManage && $this->canManuallyAddMembers)
                                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex flex-col md:flex-row items-end gap-3 shadow-2xs">
                                    <div class="flex-1 w-full">
                                        <label class="block text-xs font-bold text-slate-600 mb-1">Thêm nhanh thành viên bằng email</label>
                                        <input type="email" wire:model.blur="memberEmailToAdd" placeholder="email@hcmue.edu.vn"
                                            class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200 bg-white" />
                                        @error('memberEmailToAdd') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                    </div>
                                    <button wire:click="addMemberByEmail"
                                        class="px-4 py-2 bg-ue-brand text-white text-xs font-bold rounded-xl transition shadow-2xs whitespace-nowrap">
                                        Thêm thành viên
                                    </button>
                                </div>
                            @endif

                            {{-- Members Row list --}}
                            <div class="bg-white border border-slate-200 rounded-2xl divide-y divide-slate-100 overflow-hidden shadow-2xs">
                                @forelse ($this->members as $m)
                                    <div class="p-4 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <x-ui.avatar :user="$m->user" size="sm" />
                                            <div>
                                                <h4 class="text-xs font-bold text-slate-800">{{ $m->user?->name }}</h4>
                                                <p class="text-[10px] text-slate-400 mt-0.5">Đã gia nhập: {{ $m->joined_at ? $m->joined_at->diffForHumans() : 'gần đây' }}</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider
                                                {{ $m->role?->value === 'owner' ? 'bg-amber-100 text-amber-800 border border-amber-200' : '' }}
                                                {{ $m->role?->value === 'manager' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : '' }}
                                                {{ $m->role?->value === 'moderator' ? 'bg-purple-100 text-purple-800 border border-purple-200' : '' }}
                                                {{ $m->role?->value === 'member' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}">
                                                {{ $m->role?->label() }}
                                            </span>

                                            @if (auth()->check() && auth()->user()->can('manageMemberRoles', $community) && $m->role?->value !== 'owner')
                                                <button type="button" wire:click="openChangeRoleModal({{ $m->id }})"
                                                    class="p-1 hover:bg-slate-150 rounded text-slate-400 hover:text-ue-brand transition flex items-center justify-center"
                                                    title="Cấp quyền">
                                                    <x-ui.icon name="settings" size="2xs" />
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-slate-450 italic text-xs">
                                        Nhóm chưa có thành viên nào.
                                    </div>
                                @endforelse
                            </div>

                            @if ($this->members->isNotEmpty() && $this->members->hasPages())
                                <div class="pt-4">{{ $this->members->links() }}</div>
                            @endif
                        </div>
                    @endif

                    {{-- 2.5 Tab Giới thiệu --}}
                    @if ($activeTab === 'about')
                        <div class="space-y-4">
                            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs">
                                <h3 class="font-extrabold text-slate-800 mb-2">Giới thiệu cộng đồng</h3>
                                @if ($community->short_description)
                                    <p class="text-xs font-semibold text-slate-600 mb-3">{{ $community->short_description }}</p>
                                @endif
                                <p class="text-xs text-slate-500 whitespace-pre-line leading-relaxed">
                                    {{ $community->description ?: 'Không có thông tin mô tả chi tiết.' }}
                                </p>
                            </div>

                            @if ($community->rules)
                                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs">
                                    <h3 class="font-extrabold text-slate-800 mb-2">Nội quy hoạt động</h3>
                                    <p class="text-xs text-slate-500 whitespace-pre-line leading-relaxed">{{ $community->rules }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- 2.6 Tab Cài đặt --}}
                    @if ($activeTab === 'settings' && $this->canManage)
                        <div class="space-y-6">
                            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs">
                                <h3 class="font-extrabold text-slate-800 mb-4">Thiết lập chung</h3>
                                
                                <form wire:submit.prevent="saveSettings" class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-bold text-slate-600 mb-1">Tên cộng đồng <span class="text-red-500">*</span></label>
                                            <input type="text" wire:model.blur="settingsName"
                                                class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                                            @error('settingsName') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-slate-600 mb-1">Phân loại <span class="text-red-500">*</span></label>
                                            <select wire:model="settingsType" class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                                                @foreach (CommunityType::cases() as $t)
                                                    <option value="{{ $t->value }}">{{ $t->label() }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-slate-600 mb-1">Chính sách tham gia <span class="text-red-500">*</span></label>
                                            <select wire:model="settingsJoinPolicy" class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                                                @foreach (CommunityJoinPolicy::cases() as $policy)
                                                    <option value="{{ $policy->value }}">{{ $policy->label() }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-slate-600 mb-1">Hiển thị <span class="text-red-500">*</span></label>
                                            <select wire:model="settingsVisibility" class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                                                @foreach (CommunityVisibility::cases() as $vis)
                                                    <option value="{{ $vis->value }}">{{ $vis->label() }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-slate-600 mb-1">Trạng thái vận hành <span class="text-red-500">*</span></label>
                                            <select wire:model="settingsStatus" class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                                                <option value="draft">Bản nháp</option>
                                                <option value="active">Hoạt động</option>
                                                <option value="inactive">Ngưng hoạt động</option>
                                            </select>
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-bold text-slate-600 mb-1">Khoa phụ trách</label>
                                            <input type="text" wire:model.blur="settingsRelatedFaculty"
                                                class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-bold text-slate-600 mb-1">Mô tả ngắn</label>
                                            <input type="text" wire:model.blur="settingsShortDescription"
                                                class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-bold text-slate-600 mb-1">Mô tả chi tiết</label>
                                            <textarea wire:model.blur="settingsDescription" rows="3"
                                                class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200 resize-none"></textarea>
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-bold text-slate-600 mb-1">Nội quy nhóm</label>
                                            <textarea wire:model.blur="settingsRules" rows="3"
                                                class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200 resize-none"></textarea>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-3 border-t border-slate-100">
                                        <button type="submit" wire:loading.attr="disabled" wire:target="saveSettings" class="px-5 py-2 bg-ue-brand hover:bg-opacity-95 text-white text-xs font-bold rounded-xl transition shadow-2xs disabled:opacity-60 disabled:cursor-not-allowed">
                                            <span wire:loading.remove wire:target="saveSettings">Lưu cài đặt</span>
                                            <span wire:loading wire:target="saveSettings">Đang lưu...</span>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- Settings Behavior Preview --}}
                            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs">
                                <h3 class="font-extrabold text-slate-800 mb-3">Preview hành vi sau khi lưu</h3>
                                <div class="space-y-2 text-xs text-slate-600">
                                    @if ($settingsVisibility === 'private')
                                        <p class="flex items-center gap-2 text-amber-600 font-semibold">
                                            <x-ui.icon name="eye-off" size="xs" />
                                            <span>Không đưa cộng đồng ra trang khám phá</span>
                                        </p>
                                    @endif
                                    @if ($settingsJoinPolicy === 'invite_only')
                                        <p class="flex items-center gap-2 text-blue-600 font-semibold">
                                            <x-ui.icon name="mail" size="xs" />
                                            <span>Owner hoặc quản lý thêm thành viên bằng email</span>
                                        </p>
                                    @endif
                                    @if ($settingsStatus === 'active')
                                        <p class="flex items-center gap-2 text-green-600 font-semibold">
                                            <x-ui.icon name="check-circle" size="xs" />
                                            <span>Cộng đồng đang vận hành bình thường</span>
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Join Requests review --}}
                            @if ($community->requiresApproval())
                                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs">
                                    <h3 class="font-extrabold text-slate-800 mb-3">Yêu cầu tham gia chờ duyệt ({{ $this->pendingJoinRequests->count() }})</h3>
                                    
                                    <div class="divide-y divide-slate-100">
                                        @forelse ($this->pendingJoinRequests as $req)
                                            <div class="py-3 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                                                <div>
                                                    <h4 class="font-bold text-xs text-slate-800">{{ $req->user?->name }}</h4>
                                                    <span class="text-[10px] text-slate-400 mt-0.5 block">Yêu cầu gửi: {{ $req->created_at->diffForHumans() }}</span>
                                                    @if ($req->join_reason)
                                                        <p class="text-xs bg-slate-50 border border-slate-150 p-2 rounded-xl mt-1.5 text-slate-600 leading-normal italic">
                                                            "{{ $req->join_reason }}"
                                                        </p>
                                                    @endif
                                                </div>
                                                <div class="flex gap-1.5 flex-shrink-0 self-end sm:self-center">
                                                    <button wire:click="approveJoinRequest({{ $req->id }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="approveJoinRequest({{ $req->id }})"
                                                        class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-[10px] font-bold transition disabled:opacity-60">
                                                        <span wire:loading.remove wire:target="approveJoinRequest({{ $req->id }})">Duyệt</span>
                                                        <span wire:loading wire:target="approveJoinRequest({{ $req->id }})">Đang duyệt...</span>
                                                    </button>
                                                    <button wire:click="startRejection({{ $req->id }})"
                                                        class="px-3 py-1.5 border border-red-200 text-red-600 hover:bg-red-50 rounded-lg text-[10px] font-bold transition">
                                                        Từ chối
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-xs text-slate-500 italic py-4">Không có yêu cầu nào đang chờ xét duyệt.</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Sidebar Right Block --}}
                <div class="space-y-4">
                    <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-2xs">
                        <h3 class="font-extrabold text-slate-800 mb-3">Giới thiệu nhóm</h3>
                        
                        <div class="space-y-3.5 text-xs text-slate-500 font-semibold">
                            <div class="flex justify-between border-b border-slate-100 pb-2">
                                <span class="text-slate-400">Hình thức:</span>
                                <strong class="text-slate-700">{{ $community->type?->label() }}</strong>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-2">
                                <span class="text-slate-400">Quyền tham gia:</span>
                                <strong class="text-slate-700">{{ $community->join_policy?->label() }}</strong>
                            </div>
                            @if ($community->owner)
                                <div class="flex justify-between border-b border-slate-100 pb-2">
                                    <span class="text-slate-400">Chủ sở hữu:</span>
                                    <strong class="text-slate-700 truncate max-w-[120px]">{{ $community->owner->name }}</strong>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-slate-400">Thành lập:</span>
                                <strong class="text-slate-700">{{ $community->created_at->format('d/m/Y') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- MODALS --}}

    {{-- Post Composer Modal --}}
    @if ($showPostComposer)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden flex flex-col animate-fade-in">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-slate-805 flex items-center gap-2">
                        <x-ui.icon name="edit-3" size="xs" class="text-ue-brand" />
                        Tạo bài viết mới
                    </h3>
                    <button type="button" wire:click="closeTransientUi" class="text-slate-400 hover:text-slate-650 transition">
                        <x-ui.icon name="x" size="xs" />
                    </button>
                </div>

                <form wire:submit.prevent="createPost" class="p-5 space-y-4">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <x-ui.avatar :user="auth()->user()" size="md" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <label for="modal-post-body" class="sr-only">Nội dung bài viết</label>
                            <textarea
                                id="modal-post-body"
                                wire:model.live="postBody"
                                placeholder="Có gì mới trong cộng đồng của bạn hôm nay?"
                                rows="5"
                                class="w-full border-0 focus:ring-0 p-0 text-slate-700 placeholder-slate-400 text-sm resize-none bg-transparent focus:outline-none"
                                maxlength="10000"
                            ></textarea>
                            @error('postBody')
                                <p class="text-xs text-red-650 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <span class="text-xxs text-slate-400 font-semibold">
                            {{ mb_strlen($postBody) }}/10000
                        </span>

                        <div class="flex items-center justify-end gap-2.5">
                            <button 
                                type="button" 
                                wire:click="closeTransientUi" 
                                class="px-4 py-2 border border-slate-250 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-xl transition"
                            >
                                Hủy bỏ
                            </button>
                            <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="createPost"
                                class="px-5 py-2 bg-ue-brand text-white text-xs font-bold rounded-xl hover:bg-opacity-95 transition shadow-sm disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-1.5">
                                <x-ui.icon name="send" size="xs" wire:loading.remove wire:target="createPost" />
                                <span wire:loading.remove wire:target="createPost">Đăng bài</span>
                                <span wire:loading wire:target="createPost">Đang đăng...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Join Modal --}}
    @if ($showJoinModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-5 space-y-4">
                <h3 class="text-sm font-extrabold text-slate-800">Gửi yêu cầu tham gia</h3>
                @if ($community->requiresApproval())
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-600">Lý do mong muốn gia nhập (tùy chọn)</label>
                        <textarea wire:model="joinReason" rows="3" maxlength="255"
                            class="w-full px-3 py-2 text-xs border border-slate-200 focus:outline-none focus:ring-2 focus:ring-ue-brand rounded-xl resize-none"
                            placeholder="Giới thiệu bản thân và lý do bạn quan tâm..."></textarea>
                    </div>
                @endif
                <div class="flex justify-end gap-2 pt-2">
                    <button wire:click="closeTransientUi"
                        class="px-4 py-2 border border-slate-250 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-xl transition">Hủy</button>
                    <button wire:click="confirmJoin"
                        wire:loading.attr="disabled"
                        wire:target="confirmJoin"
                        class="px-5 py-2 bg-ue-brand text-white text-xs font-bold rounded-xl hover:bg-opacity-95 transition shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="confirmJoin">Gửi yêu cầu</span>
                        <span wire:loading wire:target="confirmJoin">Đang gửi...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Leave Modal --}}
    @if ($showLeaveModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-5 space-y-4">
                <h3 class="text-sm font-extrabold text-slate-800">Rời khỏi cộng đồng?</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Bạn sẽ không còn nhìn thấy các bài thảo luận, tài nguyên hoặc sự kiện nội bộ của nhóm này trên Bảng feed của mình nữa.</p>
                
                <div class="flex justify-end gap-2 pt-2">
                    <button wire:click="closeTransientUi"
                        class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-xl transition">Quay lại</button>
                    <button wire:click="confirmLeave"
                        wire:loading.attr="disabled"
                        wire:target="confirmLeave"
                        class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl transition shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="confirmLeave">Rời nhóm</span>
                        <span wire:loading wire:target="confirmLeave">Đang xử lý...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Submit Resource Modal --}}
    @if ($showResourceModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-5 space-y-4">
                <h3 class="text-sm font-extrabold text-slate-800">Đăng tài nguyên mới</h3>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Tiêu đề tài nguyên <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="resourceTitle"
                            class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                        @error('resourceTitle') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Loại tài nguyên <span class="text-red-500">*</span></label>
                            <select wire:model="resourceType" class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                                @foreach (CommunityResourceType::cases() as $rType)
                                    <option value="{{ $rType->value }}">{{ $rType->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Mô tả ngắn gọn</label>
                        <textarea wire:model="resourceDescription" rows="2" maxlength="500"
                            class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200 resize-none"></textarea>
                    </div>

                    @if (CommunityResourceType::tryFrom($resourceType)?->requiresUrl())
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Đường dẫn URL liên kết <span class="text-red-500">*</span></label>
                            <input type="url" wire:model="resourceUrl" placeholder="https://..."
                                class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                            @error('resourceUrl') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if (CommunityResourceType::tryFrom($resourceType)?->requiresFile())
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Tải tệp đính kèm <span class="text-red-500">*</span></label>
                            <input type="file" wire:model="resourceFile"
                                class="w-full px-3 py-2 text-xs border rounded-xl border-slate-200 focus:outline-none focus:ring-2 focus:ring-ue-brand">
                            <p class="text-[10px] text-slate-450 mt-1">Hỗ trợ tệp tin định dạng tài liệu, hình ảnh, zip tối đa 10MB.</p>
                            <div wire:loading wire:target="resourceFile" class="text-[10px] text-ue-brand font-bold mt-1">Đang xử lý tệp...</div>
                            @error('resourceFile') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <label class="flex items-start gap-2 text-xs text-slate-600 mt-2">
                        <input type="checkbox" wire:model="resourceCopyright" class="mt-0.5 text-ue-brand focus:ring-ue-brand">
                        <span>Tôi xác nhận tài liệu này thuộc quyền sở hữu hoặc được phép chia sẻ tự do trong nhà trường. <span class="text-red-500">*</span></span>
                    </label>
                    @error('resourceCopyright') <p class="text-red-500 text-[10px] font-semibold">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" wire:click="closeTransientUi"
                        class="px-4 py-2 border border-slate-250 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-xl transition">Hủy</button>
                    <button type="button" wire:click="submitResource" wire:loading.attr="disabled" wire:target="submitResource,resourceFile"
                        class="px-5 py-2 bg-ue-brand text-white text-xs font-bold rounded-xl hover:bg-opacity-95 transition shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="submitResource">Đăng tài nguyên</span>
                        <span wire:loading wire:target="submitResource">Đang đăng...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Event creation Modal --}}
    @if ($showEventModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-2xl w-full p-5 space-y-4 max-h-[90vh] overflow-y-auto">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-800">Tạo sự kiện cộng đồng mới</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Sắp xếp các hoạt động giao lưu, học tập hoặc workshop trực thuộc nhóm của bạn.</p>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Tên sự kiện <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.blur="eventTitle" placeholder="VD: Workshop chia sẻ kinh nghiệm học tập"
                            class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                        @error('eventTitle') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Hình thức <span class="text-red-500">*</span></label>
                            <select wire:model="eventType" class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                                <option value="in_person">Trực tiếp</option>
                                <option value="online">Trực tuyến (Online)</option>
                                <option value="hybrid">Kết hợp (Hybrid)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Chế độ xuất bản <span class="text-red-500">*</span></label>
                            <select wire:model="eventStatus" class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                                <option value="published">Công bố công khai</option>
                                <option value="draft">Lưu nháp</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Thời gian bắt đầu <span class="text-red-500">*</span></label>
                            <input type="datetime-local" wire:model.blur="eventStartsAt"
                                class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                            @error('eventStartsAt') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Thời gian kết thúc</label>
                            <input type="datetime-local" wire:model.blur="eventEndsAt"
                                class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                            @error('eventEndsAt') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @if ($eventType !== 'online')
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Địa điểm cụ thể <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.blur="eventLocation" placeholder="VD: Hội trường A, HCMUE"
                                class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                            @error('eventLocation') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if ($eventType !== 'in_person')
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Link phòng họp trực tuyến <span class="text-red-500">*</span></label>
                            <input type="url" wire:model.blur="eventOnlineLink" placeholder="https://meet.google.com/..."
                                class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                            @error('eventOnlineLink') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Mô tả sự kiện</label>
                        <textarea wire:model.blur="eventDescription" rows="3" maxlength="5000"
                            class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200 resize-none"
                            placeholder="Mục đích, thông tin diễn giả & quyền lợi tham gia..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Giới hạn số chỗ tham dự</label>
                            <input type="number" min="1" wire:model.blur="eventCapacity" placeholder="Không giới hạn nếu trống"
                                class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Hạn đăng ký RSVP</label>
                            <input type="datetime-local" wire:model.blur="eventRsvpDeadline"
                                class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200">
                            @error('eventRsvpDeadline') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex gap-4 pt-1.5 text-xs text-slate-600 font-semibold select-none">
                        <label class="flex items-center gap-1.5">
                            <input type="checkbox" wire:model="eventRsvpRequired" class="text-ue-brand focus:ring-ue-brand">
                            <span>Bắt buộc đăng ký trước</span>
                        </label>
                        <label class="flex items-center gap-1.5">
                            <input type="checkbox" wire:model="eventWaitlistEnabled" class="text-ue-brand focus:ring-ue-brand">
                            <span>Bật danh sách chờ khi đầy chỗ</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" wire:click="closeTransientUi"
                        class="px-4 py-2 border border-slate-250 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-xl transition">Hủy</button>
                    <button type="button" wire:click="submitEvent"
                        wire:loading.attr="disabled"
                        wire:target="submitEvent"
                        class="px-5 py-2 bg-ue-brand text-white text-xs font-bold rounded-xl hover:bg-opacity-95 transition shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="submitEvent">Đăng sự kiện</span>
                        <span wire:loading wire:target="submitEvent">Đang đăng...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Reject request reason Modal --}}
    @if ($rejectionRequestId)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-5 space-y-4">
                <h3 class="text-sm font-extrabold text-slate-800">Từ chối yêu cầu tham gia</h3>
                
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-600">Lý do từ chối (gửi tới tài khoản) <span class="text-red-500">*</span></label>
                    <textarea wire:model.blur="rejectionReason" rows="3" maxlength="255"
                        class="w-full px-3 py-2 text-xs border border-slate-200 focus:outline-none focus:ring-2 focus:ring-ue-brand rounded-xl resize-none"
                        placeholder="VD: Thông tin đăng ký không khớp..."></textarea>
                    @error('rejectionReason') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="cancelRejection"
                        class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-xl transition">Hủy</button>
                    <button type="button" wire:click="confirmRejectJoinRequest"
                        wire:loading.attr="disabled"
                        wire:target="confirmRejectJoinRequest"
                        class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl transition shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="confirmRejectJoinRequest">Xác nhận từ chối</span>
                        <span wire:loading wire:target="confirmRejectJoinRequest">Đang xử lý...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Invite Friends Modal --}}
    @if ($showInviteModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden flex flex-col max-h-[85vh]"
                x-data="{
                    selectedIds: @entangle('selectedInviteUserIds')
                }">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                        <x-ui.icon name="user-plus" size="xs" class="text-ue-brand" />
                        Mời bạn bè tham gia
                    </h3>
                    <button type="button" wire:click="closeTransientUi" class="text-slate-400 hover:text-slate-700 transition">
                        <x-ui.icon name="x" size="xs" />
                    </button>
                </div>

                <div class="p-5 flex-1 overflow-y-auto space-y-4">
                    {{-- Search --}}
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-ui.icon name="search" size="xs" class="text-slate-450" />
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="inviteSearch" placeholder="Tìm kiếm bạn bè..."
                            class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-ue-brand transition" />
                    </div>

                    {{-- Quick Select Actions --}}
                    @if ($this->inviteConnections->isNotEmpty())
                        <div class="flex items-center justify-between text-[11px] px-2.5 py-1.5 bg-slate-50/70 border border-slate-150 rounded-xl">
                            <span class="text-slate-500 font-medium">Chọn nhanh kết quả:</span>
                            <div class="flex gap-2.5">
                                <button type="button" 
                                    @click="
                                        let visibleIds = [{{ $this->inviteConnections->pluck('id')->join(',') }}];
                                        visibleIds.forEach(id => {
                                            if (!selectedIds.includes(id)) selectedIds.push(id);
                                        });
                                    "
                                    class="text-ue-brand hover:underline font-bold transition">
                                    Chọn tất cả
                                </button>
                                <span class="text-slate-300">|</span>
                                <button type="button" 
                                    @click="
                                        let visibleIds = [{{ $this->inviteConnections->pluck('id')->join(',') }}];
                                        selectedIds = selectedIds.filter(id => !visibleIds.includes(id));
                                    "
                                    class="text-slate-500 hover:underline font-semibold transition">
                                    Bỏ chọn
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Connections list --}}
                    <div class="space-y-1.5 max-h-60 overflow-y-auto pr-1">
                        @forelse ($this->inviteConnections as $friend)
                            <label class="flex items-center justify-between p-2.5 hover:bg-slate-50 rounded-xl cursor-pointer border transition"
                                :class="selectedIds.includes({{ $friend->id }}) ? 'border-ue-brand bg-ue-brand-soft/20' : 'border-slate-150'">
                                <div class="flex items-center gap-2.5">
                                    <x-ui.avatar :user="$friend" size="xs" />
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 block leading-tight">{{ $friend->name }}</span>
                                        @if ($friend->profile && $friend->profile->faculty)
                                            <span class="text-[9px] text-slate-400 mt-0.5 block">{{ $friend->profile->faculty }}</span>
                                        @endif
                                    </div>
                                </div>
                                <input type="checkbox" :value="{{ $friend->id }}" x-model.number="selectedIds"
                                    class="text-ue-brand rounded focus:ring-ue-brand cursor-pointer" />
                            </label>
                        @empty
                            <p class="text-[10px] text-slate-400 italic text-center py-6">Không tìm thấy bạn bè nào phù hợp hoặc tất cả bạn bè đã ở trong nhóm.</p>
                        @endforelse
                    </div>

                    <div x-show="selectedIds.length > 0" class="text-[11px] font-bold text-ue-brand" x-cloak>
                        Đã chọn: <span x-text="selectedIds.length"></span> người bạn
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-5 py-3.5 bg-slate-50 border-t border-slate-100">
                    <button type="button" wire:click="closeTransientUi" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 transition">
                        Hủy
                    </button>
                    <button type="button" wire:click="sendInvites" wire:loading.attr="disabled" wire:target="sendInvites"
                        :disabled="selectedIds.length === 0"
                        class="px-5 py-2 bg-ue-brand text-white text-xs font-bold rounded-xl transition shadow-2xs disabled:opacity-60 disabled:cursor-not-allowed"
                        :class="selectedIds.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-opacity-95'">
                        <span wire:loading.remove wire:target="sendInvites">Gửi lời mời</span>
                        <span wire:loading wire:target="sendInvites">Đang gửi...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Share Community Modal --}}
    @if ($showShareModal && !$sharingPostId)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-slate-805 flex items-center gap-2">
                        <x-ui.icon name="send" size="xs" class="text-ue-brand" />
                        Chia sẻ nhóm
                    </h3>
                    <button type="button" wire:click="closeTransientUi" class="text-slate-400 hover:text-slate-650 transition">
                        <x-ui.icon name="x" size="xs" />
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    {{-- Copy Link --}}
                    @if ($community->visibility?->value !== 'private' && $community->visibility?->value !== 'hidden')
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-150 flex items-center justify-between gap-3">
                            <span class="text-[11px] font-semibold text-slate-550 select-none">Liên kết công khai của nhóm</span>
                            <button type="button" @click="navigator.clipboard.writeText('{{ route('community.show', $community->id) }}'); alert('Đã sao chép liên kết vào bộ nhớ tạm!');"
                                class="px-3 py-1.5 bg-ue-brand text-white text-[10px] font-bold rounded-lg hover:bg-opacity-95 transition">
                                Sao chép
                            </button>
                        </div>
                    @else
                        <div class="p-3 bg-amber-50 text-amber-800 text-[11px] font-semibold rounded-2xl border border-amber-200 flex items-start gap-2">
                            <x-ui.icon name="alert-triangle" size="xs" class="mt-0.5 text-amber-600 flex-shrink-0" />
                            <span>Cộng đồng này đang ở chế độ riêng tư. Chỉ thành viên hoặc người được mời mới có thể xem.</span>
                        </div>
                    @endif

                    {{-- Direct Message --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600">Gửi tin nhắn trực tiếp cho Bạn bè</label>
                        <input type="text" wire:model.live.debounce.300ms="shareSearch" placeholder="Tìm kiếm bạn bè..."
                            class="w-full px-3 py-2 border rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200" />
                    </div>

                    <div class="max-h-40 overflow-y-auto space-y-1.5 pr-1 border border-slate-100 p-2 rounded-2xl bg-slate-50/50">
                        @forelse ($this->shareConnections as $conn)
                            <label class="flex items-center justify-between p-2 hover:bg-white rounded-xl cursor-pointer border border-transparent hover:border-slate-150 transition">
                                <div class="flex items-center gap-2.5">
                                    <x-ui.avatar :user="$conn" size="xs" />
                                    <span class="text-xs font-bold text-slate-800">{{ $conn->name }}</span>
                                </div>
                                <input type="radio" wire:model="selectedShareUserId" value="{{ $conn->id }}" class="text-ue-brand focus:ring-ue-brand" />
                            </label>
                        @empty
                            <p class="text-[10px] text-slate-400 italic text-center py-4">Chưa kết nối với bạn bè nào phù hợp.</p>
                        @endforelse
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-600">Lời nhắn đi kèm (tùy chọn)</label>
                        <textarea wire:model="shareOptionalMessage" rows="2" maxlength="255"
                            class="w-full px-3 py-2 border rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200 resize-none"
                            placeholder="Nhập nội dung tin nhắn đính kèm..."></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-5 py-3.5 bg-slate-50 border-t border-slate-100">
                    <button type="button" wire:click="closeTransientUi" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 transition">
                        Hủy
                    </button>
                    <button type="button" wire:click="executeShareCommunity" wire:loading.attr="disabled" wire:target="executeShareCommunity" @disabled(!$selectedShareUserId)
                        class="px-4 py-2 bg-ue-brand text-white text-xs font-bold rounded-xl transition hover:bg-opacity-95 disabled:opacity-50 disabled:cursor-not-allowed shadow-2xs">
                        <span wire:loading.remove wire:target="executeShareCommunity">Gửi tin nhắn</span>
                        <span wire:loading wire:target="executeShareCommunity">Đang gửi...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Change Member Role Modal --}}
    @if ($showChangeRoleModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-slate-805 flex items-center gap-2">
                        <x-ui.icon name="shield" size="xs" class="text-ue-brand" />
                        Cấp quyền thành viên
                    </h3>
                    <button type="button" wire:click="$set('showChangeRoleModal', false)" class="text-slate-400 hover:text-slate-650 transition">
                        <x-ui.icon name="x" size="xs" />
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    @if ($this->selectedMemberForRole)
                        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-150">
                            <x-ui.avatar :user="$this->selectedMemberForRole->user" size="sm" />
                            <div>
                                <h4 class="text-xs font-bold text-slate-800 leading-tight">{{ $this->selectedMemberForRole->user?->name }}</h4>
                                <p class="text-[10px] text-slate-400 mt-1 font-semibold">Vai trò hiện tại: {{ $this->selectedMemberForRole->role?->label() }}</p>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600">Chọn vai trò mới</label>
                            <select wire:model.live="newRole" class="w-full px-3 py-2 text-xs border rounded-xl focus:outline-none focus:ring-2 focus:ring-ue-brand border-slate-200 bg-white">
                                <option value="member">Thành viên (Member)</option>
                                <option value="moderator">Kiểm duyệt viên (Moderator)</option>
                                <option value="manager">Quản lý cộng đồng (Manager / Phó nhóm)</option>
                                <option value="owner">Chủ sở hữu (Owner / Trưởng nhóm)</option>
                            </select>
                        </div>

                        @if ($newRole === 'owner')
                            <div class="p-3 bg-amber-50 border border-amber-250 rounded-2xl text-[10px] text-amber-800 font-semibold space-y-1 flex items-start gap-2">
                                <x-ui.icon name="alert-triangle" size="xs" class="mt-0.5 text-amber-600 flex-shrink-0" />
                                <div>
                                    <p class="font-bold">⚠️ Cảnh báo chuyển giao quyền sở hữu:</p>
                                    <p class="mt-0.5 leading-relaxed">Hành động này sẽ chuyển quyền Trưởng nhóm cho {{ $this->selectedMemberForRole->user?->name }}. Bạn sẽ trở thành vai trò Quản lý cộng đồng (Phó nhóm).</p>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="flex items-center justify-end gap-2 px-5 py-3.5 bg-slate-50 border-t border-slate-100">
                    <button type="button" wire:click="$set('showChangeRoleModal', false)" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 transition">
                        Hủy
                    </button>
                    <button type="button" wire:click="confirmChangeRole" wire:loading.attr="disabled" wire:target="confirmChangeRole"
                        class="px-5 py-2 bg-ue-brand text-white text-xs font-bold rounded-xl transition hover:bg-opacity-95 shadow-2xs">
                        <span wire:loading.remove wire:target="confirmChangeRole">Xác nhận</span>
                        <span wire:loading wire:target="confirmChangeRole">Đang cập nhật...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- CUSTOM DELETE CONFIRMATION MODAL --}}
    @if ($showDeleteModal && $deletingPostId)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
            <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden">
                <div class="p-6 text-center space-y-4">
                    <div class="w-12 h-12 rounded-full bg-red-50 border border-red-100 flex items-center justify-center mx-auto text-red-650">
                        <x-ui.icon name="trash" size="md" />
                    </div>
                    <div class="space-y-2">
                        <h3 id="delete-modal-title" class="text-base font-bold text-slate-800">Xóa bài viết?</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Bài viết sẽ không còn hiển thị trong bảng tin. Bạn không thể hoàn tác thao tác này trong phiên bản hiện tại.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button type="button" wire:click="$set('showDeleteModal', false)" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 transition">
                        Hủy
                    </button>
                    <x-ui.button
                        type="button"
                        wire:click="executeDelete"
                        variant="danger"
                        size="sm"
                        icon="trash"
                        wire:loading.attr="disabled"
                        wire:target="executeDelete"
                    >
                        <span wire:loading.remove wire:target="executeDelete">Xóa bài viết</span>
                        <span wire:loading wire:target="executeDelete">Đang xóa...</span>
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif

    {{-- REPORT POST MODAL --}}
    @if ($showReportModal && $reportingPost)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" role="dialog" aria-modal="true" aria-labelledby="report-modal-title">
            <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 id="report-modal-title" class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <x-ui.icon name="alert-triangle" size="xs" class="text-red-500" />
                        Báo cáo bài viết vi phạm
                    </h3>
                    <button type="button" wire:click="closeReport" class="text-slate-400 hover:text-slate-600 transition">
                        <x-ui.icon name="x" size="xs" />
                    </button>
                </div>

                <form wire:submit.prevent="submitReport">
                    <div class="p-6 space-y-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-500">Lý do báo cáo</label>
                            <select wire:model="reportReason" class="w-full rounded-xl border border-slate-200 text-xs p-3 bg-slate-50 text-slate-700 focus:outline-none focus:ring-1 focus:ring-ue-brand/40">
                                <option value="spam">Thư rác (Spam)</option>
                                <option value="harassment">Quấy rối hoặc bắt nạt</option>
                                <option value="hate_speech">Ngôn từ kích động thù hận</option>
                                <option value="inappropriate">Nội dung không phù hợp</option>
                                <option value="other">Lý do khác</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-500">Chi tiết thêm (tùy chọn)</label>
                            <textarea wire:model="reportDescription" placeholder="Cung cấp thêm chi tiết để giúp ban kiểm duyệt xử lý..." rows="3"
                                class="w-full rounded-xl border border-slate-200 text-xs p-3 bg-slate-50 text-slate-700 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 resize-none" maxlength="500"></textarea>
                        </div>

                        @error('report')
                            <p class="text-xs font-bold text-red-650">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                        <button type="button" wire:click="closeReport" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 transition">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-ue-brand hover:bg-ue-brand-dark rounded-xl shadow-2xs transition disabled:opacity-60"
                            wire:loading.attr="disabled" wire:target="submitReport">
                            <span wire:loading.remove wire:target="submitReport">Gửi báo cáo</span>
                            <span wire:loading wire:target="submitReport">Đang gửi...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- SHARE POST MODAL --}}
    @if ($showShareModal && $sharingPostId)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" role="dialog" aria-modal="true" aria-labelledby="share-modal-title">
            <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
                    <h3 id="share-modal-title" class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <x-ui.icon name="send" size="xs" class="text-ue-brand" />
                        Chia sẻ bài viết qua tin nhắn
                    </h3>
                    <button type="button" wire:click="$set('showShareModal', false)" class="text-slate-400 hover:text-slate-655 transition">
                        <x-ui.icon name="x" size="xs" />
                    </button>
                </div>

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-500">Tìm kiếm người nhận (Bạn bè)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-ui.icon name="search" size="xs" class="text-slate-400" />
                            </span>
                            <input type="text" wire:model.live.debounce.150ms="postShareSearch" placeholder="Nhập tên bạn bè..."
                                class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 bg-slate-50 placeholder-slate-400 text-slate-700" />
                        </div>
                    </div>

                    <div class="space-y-1 max-h-48 overflow-y-auto border border-slate-100 rounded-xl divide-y divide-slate-50">
                        @php $sharePostConnections = $this->getSharePostConnections(); @endphp
                        @forelse ($sharePostConnections as $connUser)
                            @php $isSelectedShareRecipient = in_array($connUser->id, $selectedShareUserIds, true); @endphp
                            <button type="button" wire:click="toggleShareRecipient({{ $connUser->id }})"
                                class="w-full text-left p-3 hover:bg-slate-50 flex items-center justify-between transition {{ $isSelectedShareRecipient ? 'bg-slate-50' : '' }}">
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :user="$connUser" size="xs" />
                                    <div>
                                        <p class="text-xs font-bold text-slate-800 leading-tight">{{ $connUser->name }}</p>
                                        @if ($connUser->profile && $connUser->profile->faculty)
                                            <p class="text-[9px] text-slate-400 font-semibold leading-none mt-0.5">{{ $connUser->profile->faculty }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if ($isSelectedShareRecipient)
                                    <x-ui.icon name="check" size="xs" class="text-ue-brand" />
                                @endif
                            </button>
                        @empty
                            <div class="p-4 text-center text-xs text-slate-400 italic">
                                Không tìm thấy bạn bè phù hợp. Hãy chắc chắn bạn đã kết nối bạn bè với người nhận.
                            </div>
                        @endforelse
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-500">Tin nhắn kèm theo (không bắt buộc)</label>
                        <textarea wire:model="postShareOptionalMessage" placeholder="Nhập nội dung tin nhắn gửi kèm..." rows="2"
                            class="w-full rounded-xl border border-slate-200 text-xs p-3 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 resize-none bg-slate-50 placeholder-slate-400 text-slate-700" maxlength="200"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100 flex-shrink-0">
                    <p class="mr-auto text-xs font-semibold text-slate-400">
                        Đã chọn {{ count($selectedShareUserIds) }} người nhận
                    </p>
                    <button type="button" wire:click="$set('showShareModal', false)" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 transition">
                        Hủy bỏ
                    </button>
                    <button type="button" wire:click="executeShare" @disabled(empty($selectedShareUserIds))
                        class="px-4 py-2 text-xs font-bold text-white bg-ue-brand hover:bg-ue-brand-dark rounded-xl shadow-2xs transition disabled:opacity-50 flex items-center gap-1.5"
                        wire:loading.attr="disabled" wire:target="executeShare">
                        <span wire:loading.remove wire:target="executeShare" class="flex items-center gap-1.5">
                            <x-ui.icon name="send" size="xs" />
                            Gửi chia sẻ
                        </span>
                        <span wire:loading wire:target="executeShare" class="flex items-center gap-1.5">
                            <span class="animate-spin rounded-full h-3 w-3 border border-white border-t-transparent"></span>
                            Đang gửi...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- QUICK FOLLOW MODAL --}}
    @if ($showQuickFollowModal && $quickFollowUserId)
        @php $quickFollowUser = \App\Models\User::find($quickFollowUserId); @endphp
        @if ($quickFollowUser)
            @php
                $quickFollowFollowersCount = \App\Models\UserFollow::where('following_id', $quickFollowUser->id)->count();
                $quickFollowUserFaculty = $quickFollowUser->profile?->faculty;
                $quickFollowDisplayName = $quickFollowUser->profile?->display_name ?? $quickFollowUser->name;
                $quickFollowUsername = $quickFollowUser->username ?? \Illuminate\Support\Str::slug($quickFollowUser->name, '');
            @endphp
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" role="dialog" aria-modal="true" aria-labelledby="follow-modal-title">
                <div class="bg-white rounded-2xl max-w-sm w-full border border-slate-200 shadow-2xl overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Thông tin người dùng</span>
                        <button type="button" wire:click="closeQuickFollowModal" class="text-slate-400 hover:text-slate-600 transition p-1 rounded-lg hover:bg-slate-50">
                            <x-ui.icon name="x" size="xs" />
                        </button>
                    </div>

                    <div class="p-6 flex flex-col items-center text-center">
                        <x-ui.avatar :user="$quickFollowUser" size="xl" class="mb-4 shadow-sm" />
                        
                        <h3 id="follow-modal-title" class="text-lg font-bold text-slate-800 flex items-center gap-1.5 justify-center leading-tight">
                            <span>{{ $quickFollowDisplayName }}</span>
                            @if ($quickFollowUser->isActive())
                                <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                            @endif
                        </h3>
                        
                        <p class="text-xs text-slate-400 font-semibold mt-1">
                            @<span>{{ $quickFollowUsername }}</span>
                        </p>

                        @if ($quickFollowUserFaculty)
                            <p class="text-[10px] text-slate-500 font-bold bg-slate-50 border border-slate-150 px-2 py-0.5 rounded-full mt-2.5">
                                Khoa {{ $quickFollowUserFaculty }}
                            </p>
                        @endif

                        <div class="mt-4 flex items-center gap-1 text-xs text-slate-500 font-medium">
                            <span class="font-bold text-slate-800">{{ $quickFollowFollowersCount }}</span>
                            <span>người theo dõi</span>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col gap-2">
                        @if ($quickFollowCompleted)
                            <button type="button" wire:click="confirmQuickUnfollow" class="w-full py-2.5 px-4 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-xl transition flex items-center justify-center gap-1.5"
                                wire:loading.attr="disabled" wire:target="confirmQuickUnfollow">
                                <span wire:loading.remove wire:target="confirmQuickUnfollow" class="flex items-center gap-1.5 justify-center w-full">
                                    Bỏ theo dõi
                                </span>
                                <span wire:loading wire:target="confirmQuickUnfollow" class="flex items-center gap-1.5 justify-center w-full">
                                    <span class="animate-spin rounded-full h-3 w-3 border border-slate-500 border-t-transparent"></span>
                                    Đang xử lý...
                                </span>
                            </button>
                        @else
                            <button type="button" wire:click="confirmQuickFollow" class="w-full py-2.5 px-4 text-xs font-bold text-white bg-ue-brand hover:bg-ue-brand-dark rounded-xl shadow-2xs transition flex items-center justify-center gap-1.5"
                                wire:loading.attr="disabled" wire:target="confirmQuickFollow">
                                <span wire:loading.remove wire:target="confirmQuickFollow" class="flex items-center gap-1.5 justify-center w-full">
                                    Theo dõi
                                </span>
                                <span wire:loading wire:target="confirmQuickFollow" class="flex items-center gap-1.5 justify-center w-full">
                                    <span class="animate-spin rounded-full h-3 w-3 border border-white border-t-transparent"></span>
                                    Đang xử lý...
                                </span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

