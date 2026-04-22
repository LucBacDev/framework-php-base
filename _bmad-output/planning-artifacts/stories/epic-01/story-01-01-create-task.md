---
epic: 1
story: "1.1"
title: "Tạo task (API + validation + lưu DB)"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 1.1: Tạo task (API + validation + lưu DB)

As a Manager,  
I want tạo một task mới với tiêu đề/mô tả/ưu tiên/mốc thời gian cơ bản,  
So that tôi có thể giao việc và bắt đầu theo dõi công việc trong hệ thống.

## Acceptance Criteria

**Given** Manager đã đăng nhập, đã chọn `siteID`, và có quyền tạo task trong phạm vi phòng ban  
**When** gọi API tạo task với `title` (<= 200 ký tự), `description`, `priority` ∈ {Thấp, Vừa, Cao}, `start_time`, `due_time`  
**Then** hệ thống validate dữ liệu (bao gồm `start_time <= due_time`) và tạo bản ghi `task` với trạng thái mặc định `Mới`  
**And** response trả về theo wrapper chuẩn `result(true, data)`; task xuất hiện ngay trong danh sách của người tạo, và sẽ xuất hiện trong danh sách người nhận sau khi thực hiện thao tác gán người nhận  
**And** ghi audit log cho sự kiện “tạo task” (actor, timestamp, before/after phù hợp)

## Diagrams

### Sequence

- **Actor**: Manager
- **UI**: Task Create Form
- **API**: `TaskCtrl.createTask`
- **Checks**: `requireLogin()` → `requireSite(siteID)` → `requirePrivilege(createTask)` → scope theo phòng ban
- **Writes**: `task` + `task_audit_log`
- **Response**: `result(true, {taskID, status, ...})`

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
  actor Manager
  participant UI as Web UI
  participant API as TaskCtrl (REST)
  participant Auth as Auth/Site/Privilege
  participant Perm as TaskPermissionService
  participant Val as Validator
  participant Task as TaskMapper
  participant Audit as TaskAuditLogMapper
  participant DB as MySQL

  Manager->>UI: Nhập title/description/priority/start_time/due_time
  UI->>API: POST /:siteID/rest/task/tasks
  API->>Auth: requireLogin + requireSite + requirePrivilege(createTask)
  Auth-->>API: OK (user, site)
  API->>Perm: checkScope(user, site, dept)
  Perm-->>API: Allowed
  API->>Val: validate(title<=200, priority, start<=due)
  alt Invalid
    Val-->>API: errors
    API-->>UI: result(false, errors, 400)
  else Valid
    Val-->>API: OK
    API->>Task: create(status="Mới", payload)
    Task->>DB: INSERT task
    DB-->>Task: taskID
    API->>Audit: write(action="task.create", before=null, after=task snapshot)
    Audit->>DB: INSERT task_audit_log
    API-->>UI: result(true, {taskID, status="Mới"})
  end
```

