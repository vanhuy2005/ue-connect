---
title: "Safety Reporting Flow"
module: "03-product/user-flow"
product: "UEConnect"
version: "1.0"
status: "draft"
priority: "P0"
actors:
  - Student
  - Alumni
  - Mentor
  - Admin
related_use_cases:
  - STU-SET-005
  - STU-SOC-012
  - ADM-MOD-001
---

# Safety Reporting Flow

## 1. Purpose

Safety Reporting Flow mô tả cách user báo cáo nội dung/người dùng và cách hệ thống xử lý trạng thái sau báo cáo.

Với một verified student social platform, safety không phải feature phụ. Nó là điều kiện tồn tại. Social app thiếu safety thì sớm muộn cũng thành bãi đáp của spam, drama và các quyết định tồi tệ của nhân loại.

---

## 2. Actors

| Actor           | Role                                  |
| --------------- | ------------------------------------- |
| Student         | Report/block nội dung hoặc người dùng |
| Alumni          | Report/block                          |
| Mentor          | Report request/message không phù hợp  |
| Admin/Moderator | Xử lý report                          |
| Reported User   | Người bị report                       |

---

## 3. Reportable Objects

User có thể report:

| Object                 | Priority |
| ---------------------- | -------- |
| User profile           | P0       |
| Post                   | P0       |
| Comment                | P0       |
| Message                | P0       |
| Discovery profile      | P0       |
| Mentor request         | P0       |
| Community post         | P1       |
| Community chat message | P1       |
| Club/community         | P2       |
| Event                  | P2       |
| Resource/document      | P2       |

---

## 4. Entry Points

- Post more menu.
- Comment more menu.
- Profile more menu.
- Message conversation menu.
- Discovery card menu.
- Mentor request detail.
- Community content menu.
- Settings > Safety.
- Help/support page.

---

## 5. High-level Report Flow

```txt
User clicks Report
→ Select report reason
→ Add optional detail
→ Submit
→ System confirms
→ Content optionally hidden from reporter
→ Admin moderation queue receives report
```

## 6. Report Reasons

MVP reasons:

Spam
Harassment
Impersonation
Inappropriate content
Privacy violation
Scam/fraud
Copyright/resource issue
Other

Vietnamese labels:

Spam hoặc quảng cáo
Quấy rối hoặc công kích
Giả mạo danh tính
Nội dung không phù hợp
Vi phạm quyền riêng tư
Lừa đảo
Vi phạm bản quyền/tài liệu
Lý do khác

## 7. Main Flow: Report Post

User opens post menu
→ Click Report
→ Select reason
→ Optional detail
→ Submit
→ Confirmation
→ User can hide post
UI Requirements
Modal/action sheet.
Clear reason list.
Optional text area.
Submit button.
Confirmation message.
Safety reassurance.

## 8. Main Flow: Report User

Open profile
→ More menu
→ Report user
→ Select reason
→ Optional detail
→ Submit
→ Offer block option

After report user, system should offer:

Bạn có muốn chặn người dùng này không?

## 9. Main Flow: Block User

Open profile/conversation
→ Block
→ Confirm
→ User cannot message/view certain interactions
Block Effects
Area Effect
Messaging Cannot send messages
Discovery Hidden from each other
Feed User content hidden if policy chooses
Profile Limited visibility
Notifications Stop interaction notifications

## 10. Admin Moderation Flow

Report submitted
→ Report enters moderation queue
→ Admin opens report detail
→ Reviews content/user/history
→ Choose action
→ Notify relevant user if needed
→ Log action
Admin Actions
Action Description
Dismiss Không vi phạm
Hide content Ẩn khỏi public
Delete content Xóa nội dung
Warn user Cảnh cáo
Suspend user Tạm khóa
Ban user Khóa nghiêm trọng
Request more info Cần thêm thông tin
Escalate Chuyển cấp xử lý

## 11. User Feedback After Report

Sau khi submit report:

Cảm ơn bạn đã báo cáo.
Chúng tôi sẽ xem xét nội dung này để giữ cộng đồng UEConnect an toàn.

Không hứa:

Chúng tôi chắc chắn sẽ xóa nội dung này.

Vì report không luôn đồng nghĩa vi phạm. Công lý cần review, dù UX thích giả vờ mọi thứ đơn giản.

## 12. Alternative Flows

### 12.1. Duplicate Report

User reports same object again
→ System says report already received
→ Allow update detail if needed

### 12.2. Network Error

Submit report
→ Error
→ Keep selected reason/detail
→ Allow retry

### 12.3. Reporter Wants Immediate Safety

Report user
→ Offer block immediately

### 12.4. Reported Content Already Deleted

Open report target
→ Content no longer exists
→ Show resolved/unavailable state

## 13. Required Pages

Page Purpose
safety-reporting.md Report flow
blocked-users.md Manage blocked users
settings.md Safety settings
admin/moderation-queue.md Admin report queue
admin/report-detail.md Admin review
admin/audit-log.md Action history
community-guidelines.md Policy

## 14. Required Components

Report modal.
Reason list.
Textarea.
Confirmation screen.
Block confirmation.
Safety toast.
Admin queue item.
Report detail card.
Moderation action buttons.
Audit log row.

## 15. Required States

State Description
Report form Selecting reason
Submitting Sending report
Submitted Success
Duplicate Already reported
Error Submit failed
Blocked User blocked
Hidden Content hidden from reporter
Under review Admin pending
Resolved Admin acted
Dismissed No violation

## 16. Safety UX Rules

Report action phải dễ tìm nhưng không gây sợ.
Không làm reporter phải viết quá nhiều.
Không tiết lộ quá trình xử lý chi tiết cho reporter nếu ảnh hưởng privacy.
Không thông báo cho reported user quá sớm nếu có nguy cơ retaliation.
Mọi admin action phải có audit log.
Block phải có confirmation.
Report message/profile/post đều dùng cùng pattern.

## 17. Success Metrics

Report submission rate.
False report rate.
Average moderation response time.
Block after report rate.
Repeat offender rate.
Appeal rate.
Moderator action consistency.

## 18. UX Checklist

- Report có trên post/comment/profile/message.
- Reason list rõ.
- Có optional detail.
- Submit fail không mất dữ liệu.
- Sau report có option block nếu liên quan user.
- Admin queue nhận đủ context.
- Mọi admin action có audit log.
- Copy không hứa xử lý tuyệt đối.
- Mobile dùng action sheet dễ thao tác.
