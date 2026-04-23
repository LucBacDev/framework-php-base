---
epic: 3
story: "3.1"
title: "Cập nhật tiến độ (0-100) và nội dung thực hiện"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 3.1: Cập nhật tiến độ (0-100) và nội dung thực hiện

As a Staff,  
I want cập nhật % tiến độ và nội dung thực hiện của task,  
So that Manager có thể theo dõi tình trạng công việc theo thời gian.

## Acceptance Criteria

**Given** Staff là người được gán task (cá nhân hoặc thuộc snapshot phòng ban) và có quyền truy cập task  
**When** gửi cập nhật tiến độ với `progress_percent` trong [0..100] và `note`  
**Then** hệ thống validate dữ liệu và lưu bản ghi log tiến độ (append-only) kèm actor + timestamp  
**And** Manager xem được tiến độ mới nhất và lịch sử thay đổi  
**And** ghi audit log cho sự kiện cập nhật tiến độ

## Diagrams

### Sequence

- **Actor**: Staff
- **Mục tiêu**: ghi log tiến độ append-only (`task_progress_log`), progress ∈ [0..100]
- **Checks**: auth/site/privilege + quyền là assignee (cá nhân hoặc thuộc snapshot phòng ban)
- **Writes**: progress log + audit log
- **Response**: `result(true, {progressVersion,...})`

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
  actor Staff
  participant UI as Web UI
  participant API as TaskCtrl (REST)
  participant Auth as Auth/Site/Privilege
  participant Perm as TaskPermissionService
  participant Val as Validator
  participant Prog as TaskProgressMapper
  participant Audit as TaskAuditLogMapper
  participant DB as MySQL

  Staff->>UI: Nhập progress_percent + note
  UI->>API: POST /:siteID/rest/task/tasks/{taskID}/progress
  API->>Auth: requireLogin + requireSite + requirePrivilege(updateProgress)
  Auth-->>API: OK
  API->>Perm: checkAssigneeAccess(taskID, user)
  Perm-->>API: Allowed
  API->>Val: validate(progress 0..100)
  alt Invalid
    Val-->>API: errors
    API-->>UI: result(false, errors, 400)
  else Valid
    Val-->>API: OK
    API->>Prog: appendProgressLog(taskID, user, percent, note)
    Prog->>DB: INSERT task_progress_log (append-only)
    DB-->>Prog: progressLogID/version
    API->>Audit: write(action="task.progress.update", before/after)
    Audit->>DB: INSERT task_audit_log
    API-->>UI: result(true, {taskID, progressPercent, version})
  end
```

