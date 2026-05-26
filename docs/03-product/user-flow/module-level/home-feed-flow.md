---
title: "Home Feed Flow"
module: "03-product/user-flow"
product: "UEConnect"
version: "1.0"
status: "draft"
priority: "P0"
actors:
  - Student
  - Alumni
  - Mentor
related_use_cases:
  - STU-FEED-001
  - STU-POST-001
  - STU-CMT-001
  - STU-SOC-001
---

# Home Feed Flow

## 1. Purpose

Home Feed là trung tâm social blogging của UEConnect.

Flow này mô tả cách user đọc bài, đăng bài, bình luận, tương tác và đi sâu vào profile, community hoặc messaging.

Home Feed nên học từ Threads ở sự tối giản và content-first, nhưng comment có thể học Facebook ở độ rõ ràng. Tuyệt đối không biến feed thành bảng thông báo trường có nút like, vì đó là cách làm user bỏ đi trong im lặng.

---

## 2. Actors

| Actor           | Role                                |
| --------------- | ----------------------------------- |
| Student         | Đọc, đăng, comment, tương tác       |
| Alumni          | Chia sẻ kinh nghiệm, mentor insight |
| Mentor          | Đăng bài hỗ trợ học tập/career      |
| Admin/Moderator | Kiểm duyệt nội dung bị report       |

---

## 3. Entry Points

- Sau login.
- Bottom nav `Trang chủ`.
- Desktop left nav `Trang chủ`.
- Notification click.
- Profile post click.
- Community post click.
- Shared post link.

---

## 4. High-level Flow

```txt
User opens Home Feed
→ System loads personalized feed
→ User reads post
→ User can like/comment/save/share
→ User can open post detail
→ User can create new post
→ User can open author profile
→ User can report/hide content
```

## 5. Main Flow: Reading Feed

Open home feed
→ Show skeleton loading
→ Load posts
→ Render post list
→ User scrolls
→ Load more posts
Feed Content Sources
Source Priority
Posts from connected UEers High
Posts from same faculty/cohort High
Community/club posts Medium
Mentor/alumni posts Medium
Recommended posts Medium
System announcements Low but important
UI Requirements
Feed column width dễ đọc.
Avatar, name, verified state rõ.
Metadata gồm khoa/khóa/time nếu phù hợp.
Post action là icon line, neutral.
Không dùng button màu lớn cho action phụ.
Divider nhẹ giữa post.

## 6. Main Flow: Create Post

User clicks composer
→ Composer expands or opens page/modal
→ User writes content
→ Optional add media/tag/visibility
→ Submit
→ Loading state
→ Post appears in feed
Post Types
Type Priority
Text post P0
Text + image P1
Question post P1
Mentor insight P1
Community post P2
Resource/document post P2
Poll P3
Composer Rules
Textarea phải dễ viết.
Có character guidance nếu cần.
Có warning nếu chia sẻ tài liệu liên quan bản quyền.
Có visibility selector.
Có loading state khi submit.
Không xóa draft nếu submit fail.

## 7. Main Flow: Comment

User opens post detail
→ User reads comments
→ User writes comment
→ Submit
→ Comment appears
Comment Model

MVP:

Post
→ Comment
→ Reply level 1

Không nên làm nested vô hạn. Nested vô hạn là địa ngục UI được bọc bằng indent.

Comment Requirements
Comment box rõ.
Reply action nhẹ.
Có report comment.
Có edit/delete own comment.
Có loading khi submit.
Có empty state nếu chưa có comment.

## 8. Main Flow: Social Actions

Action Result
Like Toggle liked state
Comment Open post detail or focus comment box
Save Save post to saved list
Share/Send Open share/send sheet
More Open action menu
Hide Hide post with undo
Report Open report flow
Action UI Rules
Default neutral.
Active like có thể dùng brand blue.
Không dùng nhiều màu cho từng reaction.
Touch target >= 44px trên mobile.
Icon-only phải có aria-label.

## 9. Alternative Flows

### 9.1. Empty Feed

User has no posts available
→ Show empty state
→ Suggest following UEers / completing profile / exploring communities
Empty State Copy
Feed của bạn đang hơi yên ắng.
Khám phá UEers cùng khoa hoặc tạo bài viết đầu tiên để bắt đầu.

### 9.2. Post Submit Failed

Submit post
→ Network/server error
→ Keep draft
→ Show retry

### 9.3. Content Moderated

Post is hidden by moderation
→ Show placeholder
→ Explain briefly
→ Provide appeal if owner

### 9.4. User Not Verified

Pending user opens feed
→ Redirect to account status

## 10. Required Pages

Page Purpose
home-feed.md Main feed
post-detail.md Post + comments
composer.md Create/edit post
profile.md Author profile
safety-reporting.md Report content
saved-posts.md Saved posts
notifications.md Entry from notification

## 11. Required Components

Feed shell.
Post card/feed item.
Avatar.
Verified badge.
Composer.
Icon button.
Comment list.
Comment input.
Media preview.
Action sheet.
Dropdown menu.
Skeleton post.
Empty state.
Report modal.
Toast.

## 12. Required States

State Description
Loading Initial feed skeleton
Loading more Infinite scroll
Empty No posts
Error Feed failed
Offline Cannot refresh/post
Posting Composer submit
Draft Unsaved content
Moderated Hidden content
Permission denied User cannot post
Deleted Post no longer exists

## 13. Desktop Layout

Left Nav
Center Feed 560–640px
Right Panel with suggestions/events/mentor

## 14. Mobile Layout

Top Bar
Feed
Floating/inline composer entry
Bottom Navigation
Action Sheet for More menu

Mobile không hiển thị right panel. Suggestion chuyển thành cards trong feed hoặc discovery.

## 15. Success Metrics

Feed daily active users.
Post creation rate.
Comment rate.
Like/save rate.
Feed scroll depth.
Report rate.
Hidden post rate.
Time to first post after onboarding.

## 16. UX Checklist

- Feed đọc tốt trên mobile.
- Post author identity rõ.
- Verified badge không quá lố.
- Composer không làm user sợ đăng bài.
- Comment dễ theo dõi.
- Action icon đủ touch target.
- Empty state có next action.
- Submit fail không mất draft.
- Report/hide dễ tìm.
- Không dùng gradient trong feed.
