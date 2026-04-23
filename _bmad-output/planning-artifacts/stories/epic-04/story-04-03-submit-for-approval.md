---
epic: 4
story: "4.3"
title: "Staff gửi duyệt kết quả (IN_PROGRESS -> PENDING_APPROVAL)"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 4.3: Staff gửi duyệt kết quả (IN_PROGRESS -> PENDING_APPROVAL)

As a Staff,  
I want gửi duyệt kết quả của task,  
So that Manager có thể kiểm tra và phê duyệt theo quy trình.

## Acceptance Criteria

**Given** task đang ở trạng thái `Đang làm`  
**When** Staff gọi action “submit” kèm kết quả/ghi chú tổng kết tối thiểu  
**Then** hệ thống từ chối nếu thiếu nội dung tổng kết tối thiểu  
**And** nếu hợp lệ, task chuyển sang `Chờ duyệt` và ghi status log + audit log

## Diagrams

### Sequence

- **Actor**: Staff
- **Mục tiêu**: action `submit` chuyển `Đang làm -> Chờ duyệt`, bắt buộc có summary tối thiểu
- **Checks**: auth/site/privilege + assignee access + validate summary + guard
- **Writes**: status update + status log + audit log

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
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

  Staff->>UI: Nhập summary + click "Submit"
  UI->>API: POST /:siteID/rest/task/tasks/{taskID}/actions/submit (summary)
  API->>Auth: requireLogin + requireSite + requirePrivilege(submitTask)
  Auth-->>API: OK
  API->>Perm: checkAssigneeAccess(taskID, user)
  Perm-->>API: Allowed
  API->>Val: validate(summaryMin)
  alt Missing summary
    Val-->>API: error
    API-->>UI: result(false, {error:"Summary required"}, 400)
  else Has summary
    Val-->>API: OK
    API->>Task: loadTask(taskID)
    Task->>DB: SELECT task(status)
    DB-->>Task: status=IN_PROGRESS
    API->>Guard: assertAllowed(IN_PROGRESS, PENDING_APPROVAL)
    Guard-->>API: OK
    API->>Task: updateStatus(taskID, PENDING_APPROVAL)
    Task->>DB: UPDATE task
    API->>StatusLog: append(from=IN_PROGRESS,to=PENDING_APPROVAL)
    StatusLog->>DB: INSERT task_status_log
    API->>Audit: write(action="task.submit", before/after, summary)
    Audit->>DB: INSERT task_audit_log
    API-->>UI: result(true, {taskID, status:"Chờ duyệt"})
  end
```

