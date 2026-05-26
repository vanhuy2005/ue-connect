---
title: "Community Flow"
module: "03-product/user-flow"
product: "UEConnect"
version: "1.0"
status: "draft"
priority: "P2"
actors:
  - Student
  - Club Manager
  - Community Manager
  - Admin
related_use_cases:
  - COM-CRE-001
  - COM-MEM-001
  - COM-POST-001
---

# Community Flow

## 1. Purpose

Community Flow mô tả cách UEConnect hỗ trợ CLB, lớp, nhóm học tập và cộng đồng sinh viên.

Community là module quan trọng nhưng không nên làm quá phức tạp ở MVP. Có thể học Discord về khái niệm community/channel/role, nhưng UEConnect cần đơn giản hơn để không biến app thành mê cung sidebar. Discord làm tốt, nhưng copy Discord nguyên con là cách nhanh nhất để sinh viên năm nhất đóng tab.

---

## 2. Actors

| Actor             | Role                          |
| ----------------- | ----------------------------- |
| Student           | Tham gia, đọc, đăng, chat     |
| Club Manager      | Quản lý CLB                   |
| Community Manager | Quản lý nhóm/lớp/cộng đồng    |
| Admin             | Duyệt và kiểm duyệt community |

---

## 3. Entry Points

- Nav `Cộng đồng` hoặc `CLB`.
- Home feed community suggestion.
- Profile club badge.
- Search.
- Shared invite.
- Event card.
- Notification.

---

## 4. High-level Flow

```txt
User opens Clubs/Community
→ Browse communities
→ Open community detail
→ Join/request to join
→ View posts
→ Participate in discussion/chat
→ Receive notifications
```

## 5. Community Types

Type Description Priority
Club CLB chính thức hoặc bán chính thức P2
Class Lớp học hoặc nhóm lớp P2
Study Group Nhóm học tập theo môn/chủ đề P2
Faculty Community Cộng đồng theo khoa P2
Event Community Nhóm tạm thời cho sự kiện P3
Alumni Community Cộng đồng cựu sinh viên P3

## 6. Browse Communities

Open community page
→ Load list
→ Filter by type/faculty/topic
→ Open community
Community Card Must Show
Name.
Avatar/cover.
Type.
Member count.
Short description.
Join status.
Verification/official badge if applicable.

## 7. Join Community

User clicks Join
→ If public, join immediately
→ If approval required, request pending
→ User notified when approved/rejected
Join States
State Meaning
Not joined Can join
Pending Waiting approval
Joined Can participate
Rejected Cannot join now
Banned Cannot access
Private Need invite or approval

## 8. Community Detail

Community detail should include:

Header.
Description.
Members.
Rules.
Pinned posts.
Feed/posts.
Chat entry if enabled.
Events if enabled.
Resource library if enabled later.

## 9. Community Posting

Joined user opens composer
→ Select community visibility
→ Write post
→ Submit
→ Post appears in community feed
Permission Rules
Role Permission
Owner Manage all
Moderator Moderate posts/members
Member Post/comment depending on setting
Visitor View public info
Banned No access

## 10. Community Chat

User opens community chat
→ Load recent messages
→ Send message
→ Receive realtime messages

MVP chat có thể đơn giản:

Một chat room cho mỗi community.
Không cần nhiều channel ở đầu.
Channel theo topic để P3.

## 11. Events

Manager creates event
→ Admin review if needed
→ Members see event
→ User registers
→ Event reminder

Events là P2/P3, không chen vào MVP nếu core social chưa ổn.

## 12. Alternative Flows

### 12.1. Community Pending Approval

User creates community
→ Admin review
→ Pending state
→ Approved/rejected

### 12.2. User Not Member

User opens private community
→ Show limited preview
→ CTA join/request

### 12.3. Content Violates Rule

Post/comment reported
→ Community mod or admin reviews
→ Hide/delete/warn

## 13. Required Pages

Page Purpose
clubs.md Community list
club-detail.md Community profile/feed
community-chat.md Community chat
community-channel.md Future channel
events.md Community events
resource-library.md Future resources
safety-reporting.md Report community content
admin/community-management.md Admin review

## 14. Required Components

Community card.
Community header.
Join button.
Member list.
Role badge.
Pinned post.
Community composer.
Community chat.
Event card.
Rules panel.
Report action.
Empty state.

## 15. Required States

State Description
Loading Fetch communities
Empty No communities
Joined User is member
Pending Join request pending
Private Limited preview
Banned Access denied
No permission Cannot post/chat
Under review Community waiting admin
Moderated Content hidden

## 16. Desktop Layout

Left Nav
Community Feed / Detail Center
Right Panel: rules, members, events

## 17. Mobile Layout

Community Header
Tabs: Posts / Chat / Members / Events
Bottom Nav
Action Sheet for community actions

## 18. UX Checklist

- Community không quá phức tạp như Discord ngay từ đầu.
- Join state rõ.
- Rules dễ thấy.
- Role/permission rõ.
- Chat không lấn feed chính.
- Report/moderation có sẵn.
- Empty state hướng dẫn tạo/tham gia community.
