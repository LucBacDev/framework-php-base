---
epic: 4
story: "4.4"
title: "Manager phê duyệt task (PENDING_APPROVAL -> DONE)"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 4.4: Manager phê duyệt task (PENDING_APPROVAL -> DONE)

As a Manager,  
I want phê duyệt kết quả task đang `Chờ duyệt`,  
So that task được kết thúc đúng quy trình và phản ánh vào báo cáo.

## Acceptance Criteria

**Given** task đang ở trạng thái `Chờ duyệt` và Manager có quyền duyệt  
**When** gọi action “approve”  
**Then** task chuyển sang `Hoàn thành`  
**And** hệ thống ghi status log + audit log  
**And** task `Hoàn thành` không cho phép sửa nội dung chính (theo rule) ngoài các ghi chú hệ thống nếu có

## Diagrams

### Sequence

- **Actor**: Manager
- **Mục tiêu**: action `approve` chuyển `Chờ duyệt -> Hoàn thành`
- **Checks**: auth/site/privilege + manager approval permission + guard
- **Writes**: status update + status log + audit log + (rule) khóa sửa nội dung chính sau DONE

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
  actor Manager
  participant UI as Web UI
  participant API as TaskWorkflowCtrl (REST)
  participant Auth as Auth/Site/Privilege
  participant Perm as TaskPermissionService
  participant Task as TaskMapper
  participant Guard as TaskWorkflowGuard
  participant StatusLog as TaskStatusLogMapper
  participant Audit as TaskAuditLogMapper
  participant DB as MySQL

  Manager->>UI: Click "Approve"
  UI->>API: POST /:siteID/rest/task/tasks/{taskID}/actions/approve
  API->>Auth: requireLogin + requireSite + requirePrivilege(approveTask)
  Auth-->>API: OK
  API->>Perm: checkManagerApproveScope(taskID, user)
  Perm-->>API: Allowed
  API->>Task: loadTask(taskID)
  Task->>DB: SELECT task(status)
  DB-->>Task: status=PENDING_APPROVAL
  API->>Guard: assertAllowed(PENDING_APPROVAL, DONE)
  Guard-->>API: OK
  API->>Task: updateStatus(taskID, DONE)
  Task->>DB: UPDATE task
  API->>StatusLog: append(from=PENDING_APPROVAL,to=DONE)
  StatusLog->>DB: INSERT task_status_log
  API->>Audit: write(action="task.approve", before/after)
  Audit->>DB: INSERT task_audit_log
  API-->>UI: result(true, {taskID, status:"Hoàn thành"})
```

