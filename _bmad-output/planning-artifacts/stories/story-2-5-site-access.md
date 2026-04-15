# Story 2.5: Site-based Access Control

**Epic:** Epic 2: User & Access Management

**User Story:**

As an Admin,
I want giới hạn user truy cập certain sites,
So that đảm bảo data isolation giữa các sites.

**Acceptance Criteria:**

**Given** User thuộc nhiều sites
**When** Gọi API với siteID
**Then** Check user có quyền truy cập site đó
**And** Fullcontrol bypasses site restriction

**FRs Covered:** FR15, FR15a-FR15e

**Epic Goal:** Quản lý users, roles, permissions và authentication
