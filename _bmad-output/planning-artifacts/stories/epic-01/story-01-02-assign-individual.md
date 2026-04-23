---
epic: 1
story: "1.2"
title: "Giao task cho cá nhân (assignee đơn)"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 1.2: Giao task cho cá nhân (assignee đơn)

As a Manager,  
I want giao task cho một nhân viên cụ thể,  
So that tôi có một người chịu trách nhiệm rõ ràng cho công việc đó.

## Acceptance Criteria

**Given** task tồn tại và Manager có quyền quản lý task trong phạm vi phòng ban  
**When** gán `assigneeID` cho task theo chế độ “Cá nhân”  
**Then** hệ thống từ chối nếu nhân sự có trạng thái `Đã nghỉ việc` hoặc không thuộc scope phòng ban cho phép  
**And** hệ thống lưu quan hệ gán vào `task_assignee` (hoặc cấu trúc dữ liệu tương đương theo module) và ghi audit “đổi người nhận”  
**And** danh sách người nhận có thể truy vết (kèm thời điểm gán) trong lịch sử task

## Diagrams

### Sequence

- **Actor**: Manager
- **Mục tiêu**: gán 1 `assigneeID` cho task (chế độ cá nhân)
- **Checks**: auth/site/privilege + scope theo phòng ban + trạng thái nhân sự (không `Đã nghỉ việc`)
- **Writes**: `task_assignee` + audit “đổi người nhận”
- **Response**: `result(true, ...)` hoặc `result(false, ..., 403/400)`

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
  actor Manager
  participant UI as Web UI
  participant API as TaskCtrl (REST)
  participant Auth as Auth/Site/Privilege
  participant Perm as TaskPermissionService
  participant Org as Org/Employee Service
  participant Assignee as TaskAssigneeMapper
  participant Audit as TaskAuditLogMapper
  participant DB as MySQL

  Manager->>UI: Chọn task + chọn nhân sự assigneeID
  UI->>API: POST /:siteID/rest/task/tasks/{taskID}/assignees (mode=individual)
  API->>Auth: requireLogin + requireSite + requirePrivilege(assignTask)
  Auth-->>API: OK
  API->>Perm: checkTaskScope(taskID, user, site)
  Perm-->>API: Allowed
  API->>Org: validateEmployee(assigneeID)
  alt Assignee nghỉ việc / ngoài scope
    Org-->>API: Not allowed
    API-->>UI: result(false, {error:"Invalid assignee"}, 400/403)
  else Valid assignee
    Org-->>API: OK
    API->>Assignee: upsertIndividualAssignment(taskID, assigneeID)
    Assignee->>DB: INSERT/UPDATE task_assignee
    API->>Audit: write(action="task.assignee.change", before/after)
    Audit->>DB: INSERT task_audit_log
    API-->>UI: result(true, {taskID, assigneeID})
  end
```

