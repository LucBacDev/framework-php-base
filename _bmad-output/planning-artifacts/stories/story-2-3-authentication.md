# Story 2.3: Authentication System

**Epic:** Epic 2: User & Access Management

**User Story:**

As a User,
I want đăng nhập vào hệ thống,
So that truy cập các chức năng được phép.

**Acceptance Criteria:**

**Given** User nhập username/password
**When** Validate credentials với BCrypt hash
**Then** Tạo session hoặc JWT token
**And** Brute force protection sau 10 lần sai

**FRs Covered:** FR11, FR11a-FR11f

**Epic Goal:** Quản lý users, roles, permissions và authentication
