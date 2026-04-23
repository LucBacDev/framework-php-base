---
epic: 5
story: "5.1"
title: "API dashboard cá nhân (filters theo role/status/priority)"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 5.1: API dashboard cá nhân (filters theo role/status/priority)

As a User (Manager/Staff),  
I want xem dashboard cá nhân tổng hợp task theo vai trò,  
So that tôi biết ngay việc nào cần xử lý trước.

## Acceptance Criteria

**Given** user đã đăng nhập và có role hợp lệ  
**When** gọi API dashboard cá nhân  
**Then** hệ thống trả về tối thiểu: việc cần làm ngay, ưu tiên cao, sắp quá hạn/quá hạn  
**And** nếu user là Manager thì có thêm khối “đang chờ duyệt”  
**And** bộ lọc theo trạng thái/ưu tiên hoạt động đúng và dữ liệu khớp nguồn task

## Diagrams

### Sequence

- **Actor**: Manager/Staff
- **Mục tiêu**: lấy dashboard counters/sections theo role (bao gồm pending approval cho Manager)
- **Checks**: auth/site/role + scope phòng ban (Manager) / assignee scope (Staff)
- **Reads**: task list + aggregates (có thể cache Redis theo architecture)
- **Response**: `result(true, {sections...})`

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
  actor User as Manager/Staff
  participant UI as Web UI
  participant API as TaskDashboardCtrl (REST)
  participant Auth as Auth/Site
  participant Perm as TaskPermissionService
  participant Cache as Redis Cache
  participant Query as TaskDashboardQuery
  participant DB as MySQL

  User->>UI: Mở Dashboard
  UI->>API: GET /:siteID/rest/task/dashboard
  API->>Auth: requireLogin + requireSite
  Auth-->>API: OK (role)
  API->>Perm: resolveScope(user, role)
  Perm-->>API: scope
  API->>Cache: GET task:{siteID}:dashboard:{userID}
  alt Cache hit
    Cache-->>API: dashboard payload
    API-->>UI: result(true, payload)
  else Cache miss
    Cache-->>API: null
    API->>Query: computeSections(scope, role)
    Query->>DB: SELECT aggregates (todo/highPriority/dueSoon/overdue + pendingApproval for Manager)
    DB-->>Query: aggregates
    Query-->>API: payload
    API->>Cache: SET payload (short TTL)
    API-->>UI: result(true, payload)
  end
```

