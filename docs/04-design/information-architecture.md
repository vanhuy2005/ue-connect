---
title: "Information Architecture"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "source-of-truth"
priority: "P0"
last_updated: "2026-05-26"
owner: "Product Design / UX / Product"
depends_on:
  - "../03-product/sitemap.md"
  - "../03-product/product-overview.md"
  - "../03-product/feature-list.md"
  - "../03-product/feature-priority.md"
related:
  - "page-specs/page-spec-index.md"
  - "page-specs/onboarding.md"
  - "page-specs/home-feed.md"
  - "page-specs/discovery.md"
  - "page-specs/profile.md"
  - "page-specs/messaging.md"
  - "page-specs/notifications.md"
  - "page-specs/mentor.md"
  - "page-specs/clubs.md"
  - "page-specs/settings.md"
  - "page-specs/safety-reporting.md"
---

# Information Architecture

## 1. Purpose

This file defines the information architecture of UEConnect.

It is the source of truth for:

- Product content hierarchy.
- Top-level navigation.
- Route grouping.
- Screen grouping.
- How major product areas map to page-spec files.
- Navigation labels used across the PWA.

This file does not define visual styling, component details, design tokens, or page-level UI layout.

That belongs in:

```txt
page-specs/
component-primitives.md
component-variants.md
design-token-documentation.md
2. Design Decision

Keep information architecture separate from page specs and visual design files.

Reason:

IA answers: where does this screen belong?
Page spec answers: what is on this screen?
Component docs answer: how should UI pieces behave?
Token docs answer: what values should UI use?

Một file mà trả lời tất cả câu hỏi thường sẽ trả lời sai vài câu. Rất con người, nhưng ta tránh được.

3. Canonical Source Relationship
Decision Type	Source of Truth
Product scope	../03-product/product-overview.md
Feature priority	../03-product/feature-priority.md
Route/page map	../03-product/sitemap.md
IA hierarchy	information-architecture.md
Screen-level layout	page-specs/*.md
Component behavior	12-component-primitives.md, 13-component-variants.md
Tokens	19-design-token-documentation.md
4. Top-level Product Areas

UEConnect IA is grouped into these major areas:

Public
Account Gate
Main App
Profile
Discovery / Connection
Messaging
Community / Club
Mentor
Career Pathway
Search
Settings / Privacy
Safety
Admin
5. Navigation Model
5.1. Public Navigation
Landing
Login
Register
Forgot Password
5.2. Account Gate Navigation
Account Status
Verification
Profile Setup
Onboarding
Restricted Account
5.3. Main App Navigation

Primary mobile navigation should focus on high-frequency student actions:

Bảng tin
Khám phá
Tin nhắn
Cộng đồng
Hồ sơ

Mentor and Career Pathway may appear through:

Home shortcuts
Profile menu
More menu
Desktop sidebar
Search
Dedicated entry cards

If Mentor is prioritized in MVP navigation, the mobile nav may become:

Bảng tin
Khám phá
Tin nhắn
Mentor
Hồ sơ

Final navigation must stay aligned with sitemap.md.

5.4. Desktop App Navigation

Desktop app may use sidebar navigation:

Bảng tin
Khám phá
Tin nhắn
Cộng đồng
Mentor
Lộ trình
Thông báo
Hồ sơ
Cài đặt
5.5. Admin Navigation

Admin navigation:

Dashboard
Users
Verification
Reports
Moderation
Communities
Mentors
Permissions
Audit Log
Settings

Admin must only be visible to users with valid permissions.

6. Page Spec Mapping
IA Area	Page Specs
Account Gate	account-status.md, verification.md, profile-setup.md, onboarding.md
Auth	auth.md, account.md
Home	home-feed.md, post-detail.md, composer.md
Profile	profile.md, profile-edit.md, alumni-profile.md, mentor-profile.md, saved-profiles.md
Discovery / Connection	discovery.md, connection-management.md
Messaging	messaging.md, conversation.md
Notifications	notifications.md
Mentor	mentor.md, mentor-request.md, mentor-profile.md
Community / Club	clubs.md, club-detail.md, community-channel.md, community-chat.md, resource-library.md, events.md
Search	search.md
Settings / Privacy	settings.md, privacy.md, blocked-users.md, support.md
Safety	safety-reporting.md
Admin	admin/*
7. IA Rules
Any sitemap change must update this file.
Any top-level navigation change must update this file.
Any new major screen must have a matching page spec.
Any new feature area must map back to product scope.
Do not add component-level detail here.
Do not define color, typography, spacing, radius, motion, or component variants here.
Do not invent routes that are not supported by sitemap/product scope.
8. Route Grouping
Public
/
 /login
 /register
 /forgot-password
 /reset-password
Account Gate
/account/status
/account/restricted
/app/verification
/app/profile/setup
/app/onboarding
Main App
/app/home
/app/discovery
/app/connections
/app/messages
/app/messages/{conversation_id}
/app/notifications
/app/profile
/app/users/{user_id}
/app/search
/app/settings
Mentor
/app/mentor
/app/mentors/{mentor_id}
/app/mentors/{mentor_id}/request
/app/mentor-requests/{request_id}
Community
/app/clubs
/app/clubs/{club_id}
/app/communities/{community_id}
/app/communities/{community_id}/channels/{channel_id}
/app/communities/{community_id}/chat
/app/communities/{community_id}/resources
/app/communities/{community_id}/events
Admin
/admin
/admin/users
/admin/verification
/admin/reports
/admin/moderation
/admin/communities
/admin/mentors
/admin/permissions
/admin/audit-log
/admin/settings
9. QA Checklist
[ ] IA matches product scope.
[ ] IA matches sitemap.
[ ] Top-level navigation is clear.
[ ] Every major area maps to page specs.
[ ] No component-level details are included.
[ ] No design tokens are defined here.
[ ] Navigation labels use Vietnamese product language.
[ ] Admin routes are separated from student-facing app routes.
[ ] Mobile and desktop navigation are both considered.
[ ] No dating-app terminology appears.
10. AI Prompt Notes

When asking AI to work with UEConnect IA:

Use information-architecture.md as the source of truth for navigation hierarchy and page grouping.
Do not invent new top-level sections.
Do not move routes unless sitemap/product scope requires it.
Map every major screen to a page-spec file.
Use Vietnamese navigation labels.
Keep IA separate from visual component details.
