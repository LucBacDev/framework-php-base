---
epic: 5
story: "5.2"
title: "Danh sách “My Tasks” và hàng chờ duyệt của Manager"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 5.2: Danh sách “My Tasks” và hàng chờ duyệt của Manager

As a Manager,  
I want xem danh sách task theo trạng thái và hàng chờ duyệt,  
So that tôi có thể duyệt nhanh và theo dõi tiến độ phòng ban.

## Acceptance Criteria

**Given** Manager có scope phòng ban  
**When** lọc danh sách theo `status`/`priority`/`overdue`/`dueSoon`  
**Then** hệ thống trả về danh sách phân trang + sort (nếu có) theo chuẩn API hiện hữu  
**And** chỉ hiển thị các task trong phạm vi phòng ban được phép

## Diagrams

### Sequence

- **Actor**: Manager
- **Mục tiêu**: truy vấn “My Tasks” + approval queue theo filter `status/priority/overdue/dueSoon`, có paging/sort
- **Checks**: auth/site/privilege + scope phòng ban
- **Reads**: tasks (server-side filtering)
- **Response**: `result(true, {items, paging, sort})`

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
  actor Manager
  participant UI as Web UI
  participant API as TaskCtrl/TaskApprovalCtrl (REST)
  participant Auth as Auth/Site/Privilege
  participant Perm as TaskPermissionService
  participant Query as TaskListQuery
  participant DB as MySQL

  Manager->>UI: Set filters + open list/queue
  UI->>API: GET /:siteID/rest/task/tasks?status&priority&overdue&dueSoon&limit&offset
  API->>Auth: requireLogin + requireSite + requirePrivilege(viewTasks)
  Auth-->>API: OK
  API->>Perm: resolveManagerDeptScope(user)
  Perm-->>API: dept scope
  API->>Query: queryTasks(filters, scope, paging, sort)
  Query->>DB: SELECT ... WHERE scope + filters LIMIT/OFFSET
  DB-->>Query: rows + total
  API-->>UI: result(true, {items, paging:{total,limit,offset}})
```

