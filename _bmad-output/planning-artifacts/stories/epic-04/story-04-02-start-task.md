---
epic: 4
story: "4.2"
title: "Staff bắt đầu làm task (NEW -> IN_PROGRESS)"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 4.2: Staff bắt đầu làm task (NEW -> IN_PROGRESS)

As a Staff,  
I want chuyển task sang trạng thái `Đang làm`,  
So that hệ thống ghi nhận tôi đã bắt đầu xử lý công việc.

## Acceptance Criteria

**Given** task đang ở trạng thái `Mới` và Staff là người được gán  
**When** gọi action “start”  
**Then** task chuyển sang `Đang làm` nếu hợp lệ theo workflow guard  
**And** hệ thống ghi status log + audit log

## Diagrams

### Sequence

- **Actor**: Staff
- **Mục tiêu**: action `start` chuyển `Mới -> Đang làm` qua guard, ghi status log + audit
- **Checks**: auth/site/privilege + assignee access + guard
- **Writes**: `task.status`, `task_status_log`, `task_audit_log`

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
  actor Staff
  participant UI as Web UI
  participant API as TaskWorkflowCtrl (REST)
  participant Auth as Auth/Site/Privilege
  participant Perm as TaskPermissionService
  participant Task as TaskMapper
  participant Guard as TaskWorkflowGuard
  participant StatusLog as TaskStatusLogMapper
  participant Audit as TaskAuditLogMapper
  participant DB as MySQL

  Staff->>UI: Click "Start"
  UI->>API: POST /:siteID/rest/task/tasks/{taskID}/actions/start
  API->>Auth: requireLogin + requireSite + requirePrivilege(startTask)
  Auth-->>API: OK
  API->>Perm: checkAssigneeAccess(taskID, user)
  Perm-->>API: Allowed
  API->>Task: loadTask(taskID)
  Task->>DB: SELECT task
  DB-->>Task: status=NEW
  API->>Guard: assertAllowed(NEW, IN_PROGRESS)
  Guard-->>API: OK
  API->>Task: updateStatus(taskID, IN_PROGRESS)
  Task->>DB: UPDATE task
  API->>StatusLog: append(from=NEW,to=IN_PROGRESS)
  StatusLog->>DB: INSERT task_status_log
  API->>Audit: write(action="task.start", before/after)
  Audit->>DB: INSERT task_audit_log
  API-->>UI: result(true, {taskID, status:"Đang làm"})
```

