---
epic: 3
story: "3.2"
title: "Truy vấn lịch sử cập nhật tiến độ theo phiên bản"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 3.2: Truy vấn lịch sử cập nhật tiến độ theo phiên bản

As a Manager,  
I want xem lịch sử các lần cập nhật tiến độ của task,  
So that tôi có thể truy vết ai cập nhật gì và khi nào.

## Acceptance Criteria

**Given** Manager có quyền xem task trong scope phòng ban  
**When** mở lịch sử tiến độ của task  
**Then** hệ thống trả về danh sách phiên bản cập nhật (thời gian, người cập nhật, % tiến độ, nội dung)  
**And** dữ liệu trả về có thể phân trang nếu số lượng log lớn

## Diagrams

### Sequence

- **Actor**: Manager
- **Mục tiêu**: xem lịch sử progress theo phiên bản, có thể phân trang
- **Checks**: auth/site/privilege + scope phòng ban
- **Reads**: `task_progress_log` (append-only)
- **Response**: `result(true, {items, paging})`

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
  actor Manager
  participant UI as Web UI
  participant API as TaskCtrl (REST)
  participant Auth as Auth/Site/Privilege
  participant Perm as TaskPermissionService
  participant Prog as TaskProgressMapper
  participant DB as MySQL

  Manager->>UI: Mở "Progress history"
  UI->>API: GET /:siteID/rest/task/tasks/{taskID}/progress?limit&offset
  API->>Auth: requireLogin + requireSite + requirePrivilege(viewProgressHistory)
  Auth-->>API: OK
  API->>Perm: checkTaskScope(taskID, user, site)
  Perm-->>API: Allowed
  API->>Prog: listProgressLogs(taskID, paging)
  Prog->>DB: SELECT task_progress_log ORDER BY createdDate DESC LIMIT/OFFSET
  DB-->>Prog: rows[]
  API-->>UI: result(true, {items:rows, paging})
```

