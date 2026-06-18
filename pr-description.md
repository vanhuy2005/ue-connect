# PR Description: UI Improvements & Audit Log Filtering

## 📝 Description
This Pull Request introduces significant enhancements to the user interface (UI) and user experience (UX) across the application, specifically focusing on the **Admin Audit Logs**, **Community Details**, and **Connections** pages. Additionally, it refactors the web command runner to run non-migration/seeding commands safely.

---

## 🚀 Key Changes

### 1. 🛡️ Admin Audit Logs Page (`audit-logs.blade.php`)
* **Filtering Capabilities**: Added a new filter panel allowing administrators to search logs by **Actor ID** and **Action**.
* **Visual Polish**:
  * Integrated icons (`clock`, `user`, `key`, `tag`, `info`) into table headers for better clarity.
  * Replaced text actor representation with user avatars alongside their names and IDs.
  * Added dynamic status badges for actions categorized into color variants (`success`, `danger`, `warning`, `info`, and `neutral`).
  * Styled targets cleanly in a compact `[Target Type | #ID]` badge format.
  * Truncated long log reasons using CSS line-clamping and added a full tooltip hover.
* **UX Adjustments**: Provided a stylized empty state illustration and enabled pagination only when records span multiple pages.

### 2. 👥 Community Detail Page (`community-show.blade.php`)
* **Brand Alignment**: Updated hardcoded generic blue styles (`blue-600`, `blue-50`, etc.) to the unified HCMUE theme variables (`ue-brand`, `ue-brand-soft`, `ue-brand-active`, `ue-brand-hover`).
* **Member Directory Enhancements**:
  * Improved the members list layout, incorporating distinct color-coded badges for member roles (`owner`, `manager`, `moderator`, `member`).
  * Enabled the management action dropdown/settings trigger for authorized roles.
  * Added pagination rendering to handle large community membership bases.
* **Mobile Layout Optimization**: Fixed overlap issues on smaller screens by adjusting the sidebar top margins (`mt-6 lg:mt-0`).

### 3. 🌐 Connections Page (`connections.blade.php`)
* **Responsive Layout**: Added margin spacing adjustments (`mt-6 lg:mt-0`) on the right sidebar widget column to improve responsiveness and prevent overlaps on mobile devices.

### 4. ⚙️ Web Command Runner (`routes/web.php`)
* **Safe CLI Invocation**: Updated parameters so that the `--force` flag is strictly applied to `migrate` and `db:seed` commands, preventing execution errors on commands that do not support it.

---

## 🧪 Verification & Testing
* **Responsive Checks**: Layout spacing verified across mobile, tablet, and desktop viewports.
* **Component Testing**: Verified role management modals and pagination triggers on the community pages.
* **Audit Filtering**: Confirmed filter inputs correctly refine queries and clear buttons reset active parameters.
