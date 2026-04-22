---
epic: 1
story: "1.4"
title: "Đính kèm tệp cho task"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 1.4: Đính kèm tệp cho task

As a Staff,  
I want đính kèm nhiều tệp vào task,  
So that tôi có thể bổ sung tài liệu/ket quả làm việc phục vụ xử lý và phê duyệt.

## Acceptance Criteria

**Given** người dùng có quyền truy cập task và hệ thống có cấu hình giới hạn tổng dung lượng đính kèm  
**When** upload/đính kèm một hoặc nhiều tệp vào task  
**Then** hệ thống lưu metadata tệp (tên, kích thước, loại, người upload, thời gian) và liên kết đúng với `task_id`  
**And** tổng dung lượng đính kèm vượt giới hạn cấu hình thì hệ thống từ chối với lỗi rõ ràng  
**And** ghi audit log cho thao tác thêm/xóa tệp đính kèm

## Diagrams

### Sequence

- **Actor**: Staff
- **Mục tiêu**: upload/attach nhiều file vào task, kiểm soát tổng dung lượng theo cấu hình
- **Checks**: auth/site/privilege + quyền truy cập task + quota tổng dung lượng
- **Writes**: `task_attachment` (metadata + link task) + audit log
- **Response**: `result(true, attachmentMeta[])` hoặc `result(false, ..., 400/403)`

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
  actor Staff
  participant UI as Web UI
  participant API as TaskCtrl (REST)
  participant Auth as Auth/Site/Privilege
  participant Perm as TaskPermissionService
  participant Cfg as Attachment Config
  participant Store as File Storage
  participant Att as TaskAttachmentMapper
  participant Audit as TaskAuditLogMapper
  participant DB as MySQL

  Staff->>UI: Chọn task + chọn file(s)
  UI->>API: POST /:siteID/rest/task/tasks/{taskID}/attachments (multipart)
  API->>Auth: requireLogin + requireSite + requirePrivilege(attachFile)
  Auth-->>API: OK
  API->>Perm: checkTaskAccess(taskID, user, site)
  Perm-->>API: Allowed
  API->>Cfg: getMaxTotalBytes()
  Cfg-->>API: maxBytes
  API->>Store: storeFiles(files)
  alt Exceed quota / invalid file
    Store-->>API: error
    API-->>UI: result(false, {error}, 400)
  else Stored
    Store-->>API: fileRefs[]
    API->>Att: insertAttachmentMeta(taskID, fileRefs)
    Att->>DB: INSERT task_attachment
    API->>Audit: write(action="task.attachment.add/remove", before/after)
    Audit->>DB: INSERT task_audit_log
    API-->>UI: result(true, {attachments:fileRefs})
  end
```

