---
epic: 2
story: "2.1"
title: "Cập nhật deadline task và hiển thị trạng thái sắp quá hạn/quá hạn"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 2.1: Cập nhật deadline task và hiển thị trạng thái sắp quá hạn/quá hạn

As a Manager,  
I want cập nhật deadline của task trước khi hoàn thành,  
So that tôi có thể điều chỉnh kế hoạch mà vẫn giữ đúng ràng buộc nghiệp vụ.

## Acceptance Criteria

**Given** task chưa ở trạng thái `Hoàn thành` và Manager có quyền trong scope  
**When** cập nhật `start_time`/`due_time`  
**Then** hệ thống validate `start_time <= due_time` và lưu thay đổi  
**And** ghi audit log (before/after) cho “đổi deadline”  
**And** task sắp quá hạn/quá hạn được đánh dấu rõ ràng (theo mốc cấu hình) trên dữ liệu trả về cho dashboard/list

## Diagrams

### Sequence

- **Actor**: Manager
- **Mục tiêu**: update `start_time`/`due_time` trước khi `Hoàn thành`, validate `start<=due`
- **Checks**: auth/site/privilege + scope + task.status != DONE
- **Writes**: `task` (deadline fields) + audit “đổi deadline”
- **Response**: `result(true, {taskID,...})`

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

  Manager->>UI: Sửa start_time/due_time
  UI->>API: PATCH /:siteID/rest/task/tasks/{taskID} (deadline fields)
  API->>Auth: requireLogin + requireSite + requirePrivilege(updateTask)
  Auth-->>API: OK
  API->>Perm: checkTaskScope(taskID, user, site)
  Perm-->>API: Allowed
  API->>Task: loadTask(taskID)
  Task->>DB: SELECT task
  DB-->>Task: task snapshot
  alt task.status == DONE
    API-->>UI: result(false, {error:"Task completed"}, 409)
  else not DONE
    API->>Val: validate(start<=due)
    alt Invalid
      Val-->>API: errors
      API-->>UI: result(false, errors, 400)
    else Valid
      Val-->>API: OK
      API->>Task: updateDeadline(taskID, start, due)
      Task->>DB: UPDATE task SET start_time,due_time
      API->>Audit: write(action="task.deadline.change", before/after)
      Audit->>DB: INSERT task_audit_log
      API-->>UI: result(true, {taskID, start, due})
    end
  end
```

