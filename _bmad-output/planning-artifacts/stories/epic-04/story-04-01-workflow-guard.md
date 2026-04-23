---
epic: 4
story: "4.1"
title: "Workflow guard cho state machine task"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 4.1: Workflow guard cho state machine task

As a System,  
I want enforce state machine cho mọi chuyển trạng thái task qua một guard chung,  
So that không có trường hợp nhảy trạng thái bất hợp lệ qua API/UI.

## Acceptance Criteria

**Given** có request chuyển trạng thái task  
**When** thực hiện transition  
**Then** hệ thống chỉ cho phép các cạnh hợp lệ: `Mới -> Đang làm -> Chờ duyệt -> Hoàn thành` và `Chờ duyệt -> Làm lại -> Đang làm`  
**And** mọi transition đều đi qua một service/guard duy nhất (không hardcode rải rác)  
**And** mọi transition đều ghi status log + audit log (actor, timestamp)

## Diagrams

### Sequence

- **Actor**: API layer (System)
- **Mục tiêu**: mọi action transition phải gọi `TaskWorkflowGuard` để validate cạnh hợp lệ
- **Inputs**: `taskID`, `fromStatus`, `toStatus`, `actor`
- **Writes (on success)**: `task.status` + `task_status_log` + `task_audit_log`
- **Failure**: invalid transition → `result(false, ..., 400)`

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
  participant API as TaskWorkflowCtrl (REST)
  participant Auth as Auth/Site/Privilege
  participant Perm as TaskPermissionService
  participant Task as TaskMapper
  participant Guard as TaskWorkflowGuard
  participant StatusLog as TaskStatusLogMapper
  participant Audit as TaskAuditLogMapper
  participant DB as MySQL

  API->>Auth: requireLogin + requireSite + requirePrivilege(workflowAction)
  Auth-->>API: OK
  API->>Perm: checkTaskScope(taskID, user, site)
  Perm-->>API: Allowed
  API->>Task: loadTask(taskID)
  Task->>DB: SELECT task
  DB-->>Task: currentStatus
  API->>Guard: assertAllowed(currentStatus, targetStatus)
  alt Not allowed
    Guard-->>API: invalid transition
    API-->>API: result(false, {error:"Invalid transition"}, 400)
  else Allowed
    Guard-->>API: OK
    API->>Task: updateStatus(taskID, targetStatus)
    Task->>DB: UPDATE task SET status=targetStatus
    API->>StatusLog: append(taskID, from,to,actor)
    StatusLog->>DB: INSERT task_status_log
    API->>Audit: write(action="task.status.change", before/after)
    Audit->>DB: INSERT task_audit_log
  end
```

