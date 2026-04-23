---
epic: 1
story: "1.5"
title: "Ràng buộc thao tác xóa task (soft delete/lock theo quyền)"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 1.5: Ràng buộc thao tác xóa task (soft delete/lock theo quyền)

As an Admin,  
I want kiểm soát việc xóa task theo phân quyền và trạng thái xử lý,  
So that dữ liệu và lịch sử nghiệp vụ không bị mất sai quy định.

## Acceptance Criteria

**Given** có request xóa task  
**When** actor là Staff cố gắng xóa task đã được giao  
**Then** hệ thống từ chối với lỗi phân quyền phù hợp  
**And** khi actor là Manager và task đã phát sinh xử lý thì hệ thống không cho phép xóa cứng (trừ quyền Admin)  
**And** khi thực hiện xóa logic (nếu được phép) thì bắt buộc có xác nhận + ghi audit log before/after (kèm reason nếu có)

## Diagrams

### Sequence

- **Actor**: Staff / Manager / Admin
- **Mục tiêu**: enforce rule xóa task (staff không được xóa; manager không được xóa cứng nếu đã phát sinh xử lý; admin có thể soft delete theo policy)
- **Checks**: auth/site/privilege + scope + rule theo role + trạng thái/đã phát sinh xử lý
- **Writes**: soft delete/lock (nếu cho phép) + audit log (before/after + reason)
- **Response**: `result(true, ...)` hoặc `result(false, ..., 403/409)`

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
  actor User as Staff/Manager/Admin
  participant UI as Web UI
  participant API as TaskCtrl (REST)
  participant Auth as Auth/Site/Privilege
  participant Perm as TaskPermissionService
  participant Task as TaskMapper
  participant Audit as TaskAuditLogMapper
  participant DB as MySQL

  User->>UI: Click "Delete task"
  UI->>API: DELETE /:siteID/rest/task/tasks/{taskID}?mode=soft
  API->>Auth: requireLogin + requireSite + requirePrivilege(deleteTask)
  Auth-->>API: OK
  API->>Perm: checkTaskScope(taskID, user, site)
  Perm-->>API: Allowed/Denied
  alt Denied by RBAC/scope
    API-->>UI: result(false, {error:"Forbidden"}, 403)
  else Allowed
    API->>Task: loadTask(taskID)
    Task->>DB: SELECT task
    DB-->>Task: task snapshot
    alt Staff attempts delete
      API-->>UI: result(false, {error:"Forbidden"}, 403)
    else Manager hard delete on processed task
      API-->>UI: result(false, {error:"Not allowed"}, 409)
    else Soft delete allowed (Admin/policy)
      API->>Task: softDelete(taskID)
      Task->>DB: UPDATE task SET deletedDate=now()
      API->>Audit: write(action="task.delete.soft", before/after, reason)
      Audit->>DB: INSERT task_audit_log
      API-->>UI: result(true, {taskID, deleted:true})
    end
  end
```

