---
title: "Messaging Flow"
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
  - STU-MSG-001
  - STU-MSG-004
  - STU-SOC-004
---

# Messaging Flow

## 1. Purpose

Messaging giúp UEers giao tiếp sau khi kết nối, gửi lời chào, tương tác qua feed, discovery hoặc mentor request.

MVP ưu tiên:

```txt
A. Chat cá nhân realtime
D. Threaded discussion qua post/comment
```

Group chat, community chat và channel có thể phát triển sau.

## 2. Actors

Actor Role
Student Chat cá nhân với UEers
Alumni Chat với student trong phạm vi cho phép
Mentor Chat sau khi request được chấp nhận
Admin/Moderator Xử lý report nếu có

## 3. Entry Points

Bottom nav Tin nhắn.
Profile CTA Nhắn tin.
Discovery greeting accepted.
Notification message.
Mentor request accepted.
Shared post send action.

## 4. High-level Flow

User opens inbox
→ System loads conversation list
→ User selects conversation
→ System loads messages
→ User sends message
→ Message delivered realtime
→ User can report/block/mute

## 5. Permission Rules

Messaging không nên mở tự do cho mọi user ngay từ đầu.

Possible permission models:

Model Description
Connection required Chỉ nhắn tin sau khi đã kết nối
Greeting request Gửi lời chào trước, được chấp nhận mới chat
Mentor request accepted Mentor chat chỉ mở sau khi mentor đồng ý
Admin/system message System có thể gửi thông báo riêng

MVP khuyến nghị:

Student-to-student: cần greeting accepted hoặc connection.
Student-to-mentor: cần mentor request accepted.
Admin/system: one-way hoặc restricted.

## 6. Main Flow: Inbox

Open messaging
→ Show conversation skeleton
→ Load conversation list
→ Show latest message, unread count, timestamp
→ User selects conversation
Conversation List Item
Avatar.
Name.
Verified/role badge nếu cần.
Last message preview.
Timestamp.
Unread count.
Muted state.
Online/recent active optional.

## 7. Main Flow: Conversation

Open conversation
→ Load message history
→ User reads messages
→ User types message
→ Send
→ Optimistic UI
→ Delivered / failed state
Message Bubble Rules
Own message: brand blue solid.
Other message: neutral surface.
No gradient bubble.
Timestamp muted.
Failed state có retry.
Long message wrap tốt.
Link preview có thể P2.

## 8. Main Flow: Send Message

User types
→ Send button enabled
→ Submit
→ Message appears as sending
→ Server confirms
→ Message becomes delivered
Failure
Submit
→ Network error
→ Message state failed
→ User can retry/delete

Không được làm mất message khi fail. Mất tin nhắn là một loại tội ác UX nhỏ nhưng dai dẳng.

## 9. Main Flow: Start New Message

User opens profile
→ Click Nhắn tin / Gửi lời chào
→ If permission ok, open conversation
→ Else show request/greeting flow

## 10. Safety Flow

Open conversation
→ More menu
→ Report / Block / Mute
→ Confirm
→ Apply action
Safety Actions
Action Result
Mute Stop notifications
Block Prevent messaging
Report Send to moderation queue
Delete conversation Hide locally if supported

## 11. Alternative Flows

### 11.1. Empty Inbox

No conversations
→ Show empty state
→ Suggest discovery or feed interaction
Empty Copy
Bạn chưa có cuộc trò chuyện nào.
Gửi lời chào đến một UEer để bắt đầu kết nối.

### 11.2. Permission Denied

User tries to message someone
→ No connection/request
→ Show greeting request requirement

### 11.3. User Blocked

Conversation opened
→ User is blocked or has blocked other
→ Disable composer
→ Show safety message

## 12. Required Pages

Page Purpose
messaging.md Inbox
conversation.md Chat detail
profile.md Start message
discovery.md Greeting entry
mentor-request.md Mentor permission
safety-reporting.md Report/block

## 13. Required Components

Conversation list.
Conversation item.
Message bubble.
Message composer.
Attachment button.
Send button.
Typing indicator.
Read receipt.
Empty inbox.
Failed message state.
More menu.
Report/block modal.

## 14. Required States

State Description
Loading inbox Conversation skeleton
Empty inbox No conversation
Loading conversation Message skeleton
Sending Optimistic message
Delivered Sent successfully
Failed Retry available
Offline Disable sending or queue
Muted Conversation muted
Blocked Composer disabled
Permission denied Cannot message yet
Reported Report submitted

## 15. Desktop Layout

Conversation List: 320px
Message Panel: flexible
Optional Profile Panel: 280px

## 16. Mobile Layout

Inbox Screen
→ Conversation Screen
→ Back to Inbox

Mobile không nên ép inbox và conversation cùng lúc. Đừng nhét desktop vào điện thoại như nhét đồ vào vali quá cân.

## 17. Success Metrics

New conversations.
Message sent success rate.
Failed message rate.
Time to first reply.
Block/report rate.
Active conversations per user.
Mentor conversation start rate.

## 18. UX Checklist

- Inbox có empty state tốt.
- Message permission rõ.
- Failed message có retry.
- Composer không bị layout shift.
- Block/report dễ tìm.
- Own/other bubble dễ phân biệt.
- Không dùng gradient bubble.
- Mobile conversation mượt.
