<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Design System — UEConnect (Dev Only)</title>
    <meta name="robots" content="noindex, nofollow">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Realtime Meta Config --}}
    @include('partials.realtime-meta')

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Preview page only styles */
        .ds-section { padding: 3rem 0; border-top: 1px solid var(--ue-border); }
        .ds-section:first-of-type { border-top: none; }
        .ds-label { font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ue-text-muted); margin-bottom: 0.75rem; }
        .ds-swatch { width: 64px; height: 64px; border-radius: 12px; border: 1px solid var(--ue-border); }
        .ds-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 1rem; }
        .ds-row { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; }
        .ds-code { font-family: var(--font-mono); font-size: 11px; background: var(--neutral-150); border-radius: 4px; padding: 2px 6px; color: var(--ue-text-secondary); }
    </style>
</head>
<body class="font-sans" style="background: var(--ue-bg); color: var(--ue-text);">

    {{-- Dev banner --}}
    <div style="background: var(--ue-brand); color: white; padding: 8px 24px; font-size: 12px; font-weight: 600; text-align: center; letter-spacing: 0.04em;">
        🎨 UEConnect Design System Preview — Chỉ hiển thị trong môi trường local
    </div>

    <div style="max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem;">

        {{-- Page title --}}
        <div style="padding: 2rem 0 1rem;">
            <h1 style="font-size: var(--text-4xl); font-weight: var(--font-bold); letter-spacing: var(--tracking-snug); margin-bottom: 0.5rem;">
                UEConnect Design System
            </h1>
            <p style="font-size: var(--text-base); color: var(--ue-text-secondary);">
                Token system, component primitives, brand identity. Nguồn chuẩn cho mọi màn hình product.
            </p>
        </div>

        {{-- ================================================================
             1. COLORS
             ================================================================ --}}
        <section class="ds-section">
            <h2 style="font-size: var(--text-2xl); font-weight: var(--font-bold); margin-bottom: 2rem;">1. Màu sắc</h2>

            <div class="ds-label">Brand Blue — HCMUE Cerulean</div>
            <div class="ds-grid" style="margin-bottom: 2rem;">
                @foreach([
                    ['--blue-50',  '#EEF7FF', 'blue-50'],
                    ['--blue-100', '#D9ECFF', 'blue-100'],
                    ['--blue-200', '#B7DDFF', 'blue-200'],
                    ['--blue-300', '#83C5FF', 'blue-300'],
                    ['--blue-400', '#4DA3FF', 'blue-400'],
                    ['--blue-500', '#2178D4', 'blue-500'],
                    ['--blue-600', '#124874', 'blue-600 ★'],
                    ['--blue-700', '#0E3A60', 'blue-700'],
                    ['--blue-800', '#0A2B49', 'blue-800'],
                    ['--blue-900', '#071F35', 'blue-900'],
                ] as [$var, $hex, $name])
                    <div style="display:flex;flex-direction:column;gap:0.35rem;">
                        <div class="ds-swatch" style="background: var({{ $var }});"></div>
                        <span style="font-size:11px;font-weight:600;color:var(--ue-text);">{{ $name }}</span>
                        <span style="font-size:11px;color:var(--ue-text-muted);">{{ $hex }}</span>
                    </div>
                @endforeach
            </div>

            <div class="ds-label">Semantic Colors</div>
            <div class="ds-row" style="margin-bottom: 2rem;">
                @foreach([
                    ['#16A34A', 'Success'],
                    ['#D97706', 'Warning'],
                    ['#DC2626', 'Danger'],
                    ['#2563EB', 'Info'],
                    ['#7C5CFF', 'Mentor'],
                    ['#CF373D', 'Heritage Red'],
                ] as [$hex, $name])
                    <div style="display:flex;flex-direction:column;gap:0.35rem;align-items:center;">
                        <div class="ds-swatch" style="background: {{ $hex }};"></div>
                        <span style="font-size:11px;font-weight:600;">{{ $name }}</span>
                        <span style="font-size:11px;color:var(--ue-text-muted);">{{ $hex }}</span>
                    </div>
                @endforeach
            </div>

            <div class="ds-label">Surface & Text Tokens</div>
            <div class="ds-row">
                @foreach([
                    ['--ue-bg',              'ue-bg'],
                    ['--ue-surface',         'ue-surface'],
                    ['--ue-surface-subtle',  'ue-surface-subtle'],
                    ['--ue-surface-hover',   'ue-surface-hover'],
                    ['--ue-surface-pressed', 'ue-surface-pressed'],
                ] as [$var, $name])
                    <div style="display:flex;flex-direction:column;gap:0.35rem;align-items:center;">
                        <div class="ds-swatch" style="background: var({{ $var }}); border: 1px solid var(--ue-border);"></div>
                        <span style="font-size:11px;font-weight:600;">{{ $name }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ================================================================
             2. TYPOGRAPHY
             ================================================================ --}}
        <section class="ds-section">
            <h2 style="font-size: var(--text-2xl); font-weight: var(--font-bold); margin-bottom: 2rem;">2. Typography</h2>

            <div class="ds-label" style="margin-bottom: 1rem;">Type Scale</div>
            <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:2rem;">
                @foreach([
                    ['40px', 'text-5xl', 'Display Hero — Onboarding'],
                    ['32px', 'text-4xl', 'Heading 4xl — Marketing'],
                    ['24px', 'text-3xl', 'Page Title — Trang chủ'],
                    ['20px', 'text-2xl', 'Section Title — Khám phá'],
                    ['18px', 'text-xl', 'Card Title — Mentor Name'],
                    ['16px', 'text-lg', 'Emphasized Body — Input'],
                    ['15px', 'text-base', 'Product Body — Post content'],
                    ['14px', 'text-md', 'Form Label — Metadata'],
                    ['13px', 'text-sm', 'Secondary Meta — Timestamp'],
                    ['12px', 'text-xs', 'Badge / Caption'],
                    ['11px', 'text-2xs', 'Bottom Nav Label — Counter'],
                ] as [$size, $token, $usage])
                    <div style="display:flex;align-items:baseline;gap:1rem;">
                        <span style="font-size:{{ $size }};font-weight:600;color:var(--ue-text);min-width:200px;">{{ $usage }}</span>
                        <span class="ds-code">{{ $token }}</span>
                        <span style="font-size:12px;color:var(--ue-text-muted);">{{ $size }}</span>
                    </div>
                @endforeach
            </div>

            <div class="ds-label" style="margin-bottom: 1rem;">Font Weights</div>
            <div class="ds-row" style="margin-bottom:1rem;">
                <span style="font-size:var(--text-base);font-weight:400;">Regular 400 — Post body, comment, message</span>
                <span style="font-size:var(--text-base);font-weight:500;">Medium 500 — Nav item, secondary emphasis</span>
                <span style="font-size:var(--text-base);font-weight:600;">Semibold 600 — Button, author name, label</span>
                <span style="font-size:var(--text-base);font-weight:700;">Bold 700 — Page title, heading</span>
            </div>
        </section>

        {{-- ================================================================
             3. LOGO
             ================================================================ --}}
        <section class="ds-section">
            <h2 style="font-size: var(--text-2xl); font-weight: var(--font-bold); margin-bottom: 2rem;">3. Logo</h2>

            <div class="ds-label">Variants</div>
            <div class="ds-row" style="align-items:center; gap: 2rem; margin-bottom: 1.5rem;">
                <div style="text-align:center;">
                    <x-brand.logo variant="horizontal" size="md" />
                    <div class="ds-code" style="margin-top:6px;">horizontal / md</div>
                </div>
                <div style="text-align:center;">
                    <x-brand.logo variant="mark" size="md" />
                    <div class="ds-code" style="margin-top:6px;">mark / md</div>
                </div>
                <div style="text-align:center;">
                    <x-brand.logo variant="mark" size="lg" />
                    <div class="ds-code" style="margin-top:6px;">mark / lg</div>
                </div>
                <div style="background:var(--ue-brand);padding:16px;border-radius:12px;text-align:center;">
                    <x-brand.logo variant="horizontal" size="md" tone="white" />
                    <div class="ds-code" style="margin-top:6px;color:#fff;">horizontal / white</div>
                </div>
            </div>

            <div class="ds-label">Verified Badge</div>
            <div class="ds-row">
                <x-brand.verified-badge />
                <x-brand.verified-badge size="sm" />
            </div>
        </section>

        {{-- ================================================================
             4. BUTTONS
             ================================================================ --}}
        <section class="ds-section">
            <h2 style="font-size: var(--text-2xl); font-weight: var(--font-bold); margin-bottom: 2rem;">4. Buttons</h2>

            <div class="ds-label">Variants</div>
            <div class="ds-row" style="margin-bottom:1.5rem;">
                <x-ui.button variant="primary">Tạo tài khoản</x-ui.button>
                <x-ui.button variant="secondary">Để sau</x-ui.button>
                <x-ui.button variant="outline">Hủy</x-ui.button>
                <x-ui.button variant="ghost">Tùy chọn</x-ui.button>
                <x-ui.button variant="danger">Xóa bài viết</x-ui.button>
                <x-ui.button variant="danger-outline">Từ chối</x-ui.button>
                <x-ui.button variant="link">Xem thêm</x-ui.button>
            </div>

            <div class="ds-label">Sizes (primary)</div>
            <div class="ds-row" style="margin-bottom:1.5rem;align-items:flex-end;">
                <x-ui.button size="xs">xs</x-ui.button>
                <x-ui.button size="sm">sm</x-ui.button>
                <x-ui.button size="md">md (default)</x-ui.button>
                <x-ui.button size="lg">lg</x-ui.button>
                <x-ui.button size="xl">xl</x-ui.button>
            </div>

            <div class="ds-label">With Icons</div>
            <div class="ds-row" style="margin-bottom:1.5rem;">
                <x-ui.button variant="primary" icon="send">Gửi lời chào</x-ui.button>
                <x-ui.button variant="secondary" icon="arrow-right" icon-position="right">Khám phá</x-ui.button>
                <x-ui.button variant="ghost" icon="plus">Thêm</x-ui.button>
            </div>

            <div class="ds-label">States</div>
            <div class="ds-row" style="margin-bottom:1.5rem;">
                <x-ui.button>Mặc định</x-ui.button>
                <x-ui.button :loading="true">Đang xử lý</x-ui.button>
                <x-ui.button :disabled="true">Không khả dụng</x-ui.button>
            </div>

            <div class="ds-label">Icon Buttons</div>
            <div class="ds-row">
                <x-ui.icon-button icon="more-horizontal" label="Mở menu" variant="ghost" />
                <x-ui.icon-button icon="search" label="Tìm kiếm" variant="soft" />
                <x-ui.icon-button icon="edit" label="Chỉnh sửa" variant="outline" />
                <x-ui.icon-button icon="shield" label="Xác thực" variant="brand" />
                <x-ui.icon-button icon="trash" label="Xóa" variant="danger" />
            </div>
        </section>

        {{-- ================================================================
             5. BADGES
             ================================================================ --}}
        <section class="ds-section">
            <h2 style="font-size: var(--text-2xl); font-weight: var(--font-bold); margin-bottom: 2rem;">5. Badges</h2>

            <div class="ds-label">Verification States</div>
            <div class="ds-row" style="margin-bottom:1.5rem;">
                <x-ui.badge variant="verified">Đã xác thực</x-ui.badge>
                <x-ui.badge variant="pending">Đang chờ duyệt</x-ui.badge>
                <x-ui.badge variant="rejected">Bị từ chối</x-ui.badge>
                <x-ui.badge variant="need-more-info">Cần bổ sung</x-ui.badge>
            </div>

            <div class="ds-label">Role Badges</div>
            <div class="ds-row" style="margin-bottom:1.5rem;">
                <x-ui.badge variant="student">Sinh viên</x-ui.badge>
                <x-ui.badge variant="alumni">Cựu sinh viên</x-ui.badge>
                <x-ui.badge variant="advisor">Cố vấn</x-ui.badge>
                <x-ui.badge variant="mentor">Mentor</x-ui.badge>
                <x-ui.badge variant="club">Câu lạc bộ</x-ui.badge>
                <x-ui.badge variant="system">Hệ thống</x-ui.badge>
            </div>

            <div class="ds-label">Semantic Badges</div>
            <div class="ds-row">
                <x-ui.badge variant="success">Hoàn tất</x-ui.badge>
                <x-ui.badge variant="warning">Cần chú ý</x-ui.badge>
                <x-ui.badge variant="danger">Lỗi</x-ui.badge>
                <x-ui.badge variant="info">Thông tin</x-ui.badge>
                <x-ui.badge variant="neutral">Trung tính</x-ui.badge>
            </div>
        </section>

        {{-- ================================================================
             6. CARDS
             ================================================================ --}}
        <section class="ds-section">
            <h2 style="font-size: var(--text-2xl); font-weight: var(--font-bold); margin-bottom: 2rem;">6. Cards</h2>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;">
                <x-ui.card>
                    <p class="ds-label">default</p>
                    <p style="font-size:var(--text-sm);color:var(--ue-text-secondary);">Thẻ nội dung tiêu chuẩn.</p>
                </x-ui.card>

                <x-ui.card variant="interactive">
                    <p class="ds-label">interactive</p>
                    <p style="font-size:var(--text-sm);color:var(--ue-text-secondary);">Hover vào thẻ này để thấy hiệu ứng.</p>
                </x-ui.card>

                <x-ui.card variant="elevated">
                    <p class="ds-label">elevated</p>
                    <p style="font-size:var(--text-sm);color:var(--ue-text-secondary);">Thẻ quan trọng, bóng đổ lớn hơn.</p>
                </x-ui.card>

                <x-ui.card variant="soft">
                    <p class="ds-label">soft</p>
                    <p style="font-size:var(--text-sm);color:var(--ue-text-secondary);">Nền xám nhẹ, không có shadow.</p>
                </x-ui.card>

                <x-ui.card variant="success">
                    <p class="ds-label">success</p>
                    <p style="font-size:var(--text-sm);">Hồ sơ đã được xác thực thành công.</p>
                </x-ui.card>

                <x-ui.card variant="warning">
                    <p class="ds-label">warning</p>
                    <p style="font-size:var(--text-sm);">Hồ sơ đang chờ xác thực. Vui lòng kiểm tra email.</p>
                </x-ui.card>

                <x-ui.card variant="danger">
                    <p class="ds-label">danger</p>
                    <p style="font-size:var(--text-sm);">Xác thực bị từ chối. Vui lòng cung cấp lại thông tin.</p>
                </x-ui.card>

                <x-ui.card>
                    <x-slot:header>
                        <p style="font-size:var(--text-md);font-weight:600;">Với header và footer</p>
                    </x-slot:header>
                    <p style="font-size:var(--text-sm);color:var(--ue-text-secondary);">Nội dung chính của thẻ.</p>
                    <x-slot:footer>
                        <div style="display:flex;gap:0.5rem;">
                            <x-ui.button size="sm" variant="primary">Lưu</x-ui.button>
                            <x-ui.button size="sm" variant="ghost">Hủy</x-ui.button>
                        </div>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </section>

        {{-- ================================================================
             7. FORM CONTROLS
             ================================================================ --}}
        <section class="ds-section">
            <h2 style="font-size: var(--text-2xl); font-weight: var(--font-bold); margin-bottom: 2rem;">7. Form Controls</h2>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;max-width:800px;">
                {{-- Input --}}
                <div>
                    <div class="ds-label">Input — Default</div>
                    <x-ui.label for="preview-email">Email HCMUE</x-ui.label>
                    <x-ui.input id="preview-email" name="preview-email" type="email" placeholder="ten.ban@hcmue.edu.vn" />
                </div>

                {{-- Input Error --}}
                <div>
                    <div class="ds-label">Input — Error</div>
                    <x-ui.label for="preview-email-err" :required="true">Email HCMUE</x-ui.label>
                    <x-ui.input id="preview-email-err" name="preview-email-err" type="email" placeholder="ten.ban@hcmue.edu.vn" :hasError="true" />
                    <x-ui.field-error message="Email phải thuộc miền hcmue.edu.vn." />
                </div>

                {{-- Textarea --}}
                <div>
                    <div class="ds-label">Textarea — Default</div>
                    <x-ui.label for="preview-bio">Giới thiệu bản thân</x-ui.label>
                    <x-ui.textarea id="preview-bio" name="preview-bio" placeholder="Nói một chút về bạn..." rows="3" />
                </div>

                {{-- Textarea Composer --}}
                <div>
                    <div class="ds-label">Textarea — Composer</div>
                    <x-ui.textarea name="preview-msg" variant="composer" placeholder="Viết lời chào..." :maxlength="300" :showCount="true" rows="3" />
                </div>

                {{-- Select --}}
                <div>
                    <div class="ds-label">Select</div>
                    <x-ui.label for="preview-faculty">Khoa</x-ui.label>
                    <x-ui.select id="preview-faculty" name="preview-faculty">
                        <option value="">Chọn khoa</option>
                        <option value="cntt">Công nghệ thông tin</option>
                        <option value="sp">Sư phạm Toán</option>
                        <option value="anh">Sư phạm Tiếng Anh</option>
                    </x-ui.select>
                </div>

                {{-- Input sizes --}}
                <div>
                    <div class="ds-label">Input — Sizes</div>
                    <div style="display:flex;flex-direction:column;gap:0.75rem;">
                        <x-ui.input name="sz-sm" size="sm" placeholder="sm — 36px" />
                        <x-ui.input name="sz-md" size="md" placeholder="md — 44px (default)" />
                        <x-ui.input name="sz-lg" size="lg" placeholder="lg — 52px" />
                    </div>
                </div>
            </div>
        </section>

        {{-- ================================================================
             8. ALERTS
             ================================================================ --}}
        <section class="ds-section">
            <h2 style="font-size: var(--text-2xl); font-weight: var(--font-bold); margin-bottom: 2rem;">8. Alerts</h2>

            <div style="display:flex;flex-direction:column;gap:1rem;max-width:640px;">
                <x-ui.alert variant="info" title="Xác thực tài khoản">
                    Vui lòng xác thực email HCMUE để sử dụng đầy đủ tính năng của UEConnect.
                </x-ui.alert>

                <x-ui.alert variant="success" title="Đã lưu thành công">
                    Thông tin hồ sơ của bạn đã được cập nhật.
                </x-ui.alert>

                <x-ui.alert variant="warning" title="Hồ sơ đang chờ duyệt">
                    Đang chờ duyệt — thường mất 1–2 ngày làm việc.
                </x-ui.alert>

                <x-ui.alert variant="danger" title="Không thể gửi">
                    Vui lòng thử lại sau ít phút.
                    <x-slot:actions>
                        <x-ui.button variant="outline" size="sm">Thử lại</x-ui.button>
                    </x-slot:actions>
                </x-ui.alert>

                <x-ui.alert variant="info" :dismissible="true">
                    Bạn có thể đóng thông báo này bằng nút bên phải.
                </x-ui.alert>
            </div>
        </section>

        {{-- ================================================================
             9. AVATAR
             ================================================================ --}}
        <section class="ds-section">
            <h2 style="font-size: var(--text-2xl); font-weight: var(--font-bold); margin-bottom: 2rem;">9. Avatar</h2>

            <div class="ds-label">Sizes (with initials fallback)</div>
            <div class="ds-row" style="align-items:flex-end;margin-bottom:1.5rem;">
                <div style="text-align:center;">
                    <x-ui.avatar fallback="NA" size="xs" />
                    <div class="ds-code" style="margin-top:4px;">xs</div>
                </div>
                <div style="text-align:center;">
                    <x-ui.avatar fallback="NA" size="sm" />
                    <div class="ds-code" style="margin-top:4px;">sm</div>
                </div>
                <div style="text-align:center;">
                    <x-ui.avatar fallback="NA" size="md" />
                    <div class="ds-code" style="margin-top:4px;">md</div>
                </div>
                <div style="text-align:center;">
                    <x-ui.avatar fallback="NA" size="lg" />
                    <div class="ds-code" style="margin-top:4px;">lg</div>
                </div>
                <div style="text-align:center;">
                    <x-ui.avatar fallback="NA" size="xl" />
                    <div class="ds-code" style="margin-top:4px;">xl</div>
                </div>
                <div style="text-align:center;">
                    <x-ui.avatar fallback="NA" size="2xl" />
                    <div class="ds-code" style="margin-top:4px;">2xl</div>
                </div>
            </div>

            <div class="ds-label">Shapes</div>
            <div class="ds-row">
                <div style="text-align:center;">
                    <x-ui.avatar fallback="UV" size="lg" shape="circle" />
                    <div class="ds-code" style="margin-top:4px;">circle (user)</div>
                </div>
                <div style="text-align:center;">
                    <x-ui.avatar fallback="CLB" size="lg" shape="rounded-square" />
                    <div class="ds-code" style="margin-top:4px;">rounded-square (community)</div>
                </div>
                <div style="text-align:center;">
                    <x-ui.avatar size="lg" />
                    <div class="ds-code" style="margin-top:4px;">icon fallback</div>
                </div>
            </div>
        </section>

        {{-- ================================================================
             10. EMPTY & LOADING STATES
             ================================================================ --}}
        <section class="ds-section">
            <h2 style="font-size: var(--text-2xl); font-weight: var(--font-bold); margin-bottom: 2rem;">10. Empty & Loading States</h2>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;">
                <x-ui.card>
                    <x-ui.empty-state
                        icon="inbox"
                        title="Chưa có nội dung"
                        description="Khi có hoạt động mới, nội dung sẽ xuất hiện tại đây."
                        action-label="Khám phá UEers"
                        action-href="#"
                    />
                </x-ui.card>

                <x-ui.card>
                    <div class="ds-label" style="padding: 1rem 0 0 1rem;">spinner</div>
                    <x-ui.loading-state variant="spinner" message="Đang tải..." />
                </x-ui.card>

                <x-ui.card>
                    <div class="ds-label" style="padding: 1rem 0 0 1rem;">skeleton</div>
                    <div style="padding: 1rem;">
                        <x-ui.loading-state variant="skeleton" />
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <div class="ds-label" style="padding: 1rem 0 0 1rem;">dots</div>
                    <x-ui.loading-state variant="dots" />
                </x-ui.card>
            </div>
        </section>

        {{-- ================================================================
             11. SHELL PREVIEW
             ================================================================ --}}
        <section class="ds-section">
            <h2 style="font-size: var(--text-2xl); font-weight: var(--font-bold); margin-bottom: 2rem;">11. App Shell (preview)</h2>

            <div class="ds-label">Desktop Sidebar Nav</div>
            <div style="width:280px;border:1px solid var(--ue-border);border-radius:16px;overflow:hidden;margin-bottom:2rem;">
                <div style="padding:1rem;background:var(--ue-surface);">
                    <x-brand.logo variant="horizontal" size="md" />
                </div>
                @foreach(['home:Trang chủ:true', 'users:Khám phá:false', 'message:Tin nhắn:false', 'community:Cộng đồng:false', 'graduation-cap:Mentor:false', 'bell:Thông báo:false'] as $item)
                    @php [$icon, $label, $active] = explode(':', $item); $isActive = $active === 'true'; @endphp
                    <a href="#" class="ue-nav-link mx-2 {{ $isActive ? 'active' : '' }}" @if($isActive) aria-current="page" @endif>
                        <x-ui.icon :name="$icon" size="md" aria-hidden="true" />
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </div>

            <div class="ds-label">Mobile Bottom Nav</div>
            <div style="width:390px;border:1px solid var(--ue-border);border-radius:16px;overflow:hidden;">
                <div style="display:flex;background:var(--ue-surface);height:64px;">
                    @foreach(['home:Trang chủ:true', 'users:Khám phá:false', 'message:Tin nhắn:false', 'community:Cộng đồng:false', 'user:Hồ sơ:false'] as $item)
                        @php [$icon, $label, $active] = explode(':', $item); $isActive = $active === 'true'; @endphp
                        <a href="#" style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;font-size:11px;font-weight:600;text-decoration:none;color:{{ $isActive ? 'var(--ue-brand)' : 'var(--ue-text-muted)' }};">
                            <x-ui.icon :name="$icon" size="md" aria-hidden="true" />
                            <span>{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================================================================
             12. FOCUS STATE DEMO
             ================================================================ --}}
        <section class="ds-section">
            <h2 style="font-size: var(--text-2xl); font-weight: var(--font-bold); margin-bottom: 1rem;">12. Focus State</h2>
            <p style="font-size:var(--text-sm);color:var(--ue-text-secondary);margin-bottom:1.5rem;">
                Tab qua các phần tử dưới đây để kiểm tra focus ring. Ring phải rõ và tuân theo brand blue.
            </p>
            <div class="ds-row">
                <x-ui.button>Tab đến đây</x-ui.button>
                <x-ui.button variant="secondary">Rồi sang đây</x-ui.button>
                <x-ui.icon-button icon="search" label="Tìm kiếm" />
                <x-ui.input name="focus-test" placeholder="Focus vào input này" style="max-width: 200px;" />
            </div>
        </section>

        {{-- Footer --}}
        <footer style="padding:3rem 0 2rem;border-top:1px solid var(--ue-border);margin-top:2rem;text-align:center;">
            <x-brand.logo variant="horizontal" size="sm" style="margin: 0 auto 1rem;" />
            <p style="font-size:var(--text-xs);color:var(--ue-text-muted);">
                UEConnect Design System — Local Preview Only<br>
                Không deploy page này ra production.
            </p>
        </footer>

    </div>

</body>
</html>
