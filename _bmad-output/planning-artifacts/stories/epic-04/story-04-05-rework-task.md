---
epic: 4
story: "4.5"
title: "Manager yêu cầu làm lại (PENDING_APPROVAL -> REWORK -> IN_PROGRESS)"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 4.5: Manager yêu cầu làm lại (PENDING_APPROVAL -> REWORK -> IN_PROGRESS)

As a Manager,  
I want yêu cầu Staff làm lại kết quả task,  
So that chất lượng đầu ra được đảm bảo trước khi hoàn thành.

## Acceptance Criteria

**Given** task đang ở trạng thái `Chờ duyệt`  
**When** Manager gọi action “rework” kèm lý do  
**Then** task chuyển sang `Làm lại` và ghi status log + audit log  
**And** Staff có thể đưa task quay lại `Đang làm` theo workflow guard

## Diagrams

### Sequence

- **Actor**: Manager (rework) + Staff (resume)
- **Mục tiêu**: `Chờ duyệt -> Làm lại` (kèm lý do), sau đó Staff đưa về `Đang làm`
- **Checks**: auth/site/privilege + manager approve scope + guard + validate reason
- **Writes**: status update + status log + audit log

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
  actor Manager
  actor Staff
  participant UI as Web UI
  participant API as TaskWorkflowCtrl (REST)
  participant Auth as Auth/Site/Privilege
  participant Perm as TaskPermissionService
  participant Val as Validator
  participant Task as TaskMapper
  participant Guard as TaskWorkflowGuard
  participant StatusLog as TaskStatusLogMapper
  participant Audit as TaskAuditLogMapper
  participant DB as MySQL

  Manager->>UI: Nhập reason + click "Rework"
  UI->>API: POST /:siteID/rest/task/tasks/{taskID}/actions/rework (reason)
  API->>Auth: requireLogin + requireSite + requirePrivilege(reworkTask)
  Auth-->>API: OK
  API->>Perm: checkManagerApproveScope(taskID, user)
  Perm-->>API: Allowed
  API->>Val: validate(reason not empty)
  Val-->>API: OK
  API->>Task: loadTask(taskID)
  Task->>DB: SELECT status
  DB-->>Task: status=PENDING_APPROVAL
  API->>Guard: assertAllowed(PENDING_APPROVAL, REWORK)
  Guard-->>API: OK
  API->>Task: updateStatus(taskID, REWORK)
  Task->>DB: UPDATE task
  API->>StatusLog: append(from=PENDING_APPROVAL,to=REWORK)
  StatusLog->>DB: INSERT task_status_log
  API->>Audit: write(action="task.rework", before/after, reason)
  Audit->>DB: INSERT task_audit_log
  API-->>UI: result(true, {status:"Làm lại"})

  Staff->>UI: Click "Resume work"
  UI->>API: POST /:siteID/rest/task/tasks/{taskID}/actions/start
  API->>Task: (guard) REWORK -> IN_PROGRESS + logs + audit
```

