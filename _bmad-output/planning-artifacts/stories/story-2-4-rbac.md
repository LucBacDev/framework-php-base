# Story 2.4: RBAC Implementation

**Epic:** Epic 2: User & Access Management

**User Story:**

As a System,
I want kiểm tra quyền truy cập dựa trên roles và privileges,
So that đảm bảo security.

**Acceptance Criteria:**

**Given** User gọi API endpoint
**When** Check privilege/role required
**Then** Allow nếu user có quyền
**And** Deny nếu không có quyền
**And** Fullcontrol bypasses all checks

**FRs Covered:** FR14, FR14a-FR14h

**Epic Goal:** Quản lý users, roles, permissions và authentication
