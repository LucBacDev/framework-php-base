---
epic: 1
story: "1.3"
title: "Giao task cho phòng ban (snapshot thành viên)"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 1.3: Giao task cho phòng ban (snapshot thành viên)

As a Manager,  
I want giao task cho cả phòng ban,  
So that mọi thành viên hợp lệ trong phòng đều nhận được công việc tại thời điểm giao.

## Acceptance Criteria

**Given** task tồn tại và Manager có quyền giao việc trong phòng ban mục tiêu  
**When** chọn “Phòng ban” và cung cấp `departmentID` để giao task  
**Then** hệ thống resolve danh sách thành viên hợp lệ tại thời điểm giao (loại `Đã nghỉ việc`) và tạo snapshot người nhận  
**And** nếu không có thành viên hợp lệ thì hệ thống cảnh báo và từ chối thao tác  
**And** hệ thống lưu được chế độ giao việc + snapshot người nhận (phục vụ audit/truy vết)

## Diagrams

### Sequence

- **Actor**: Manager
- **Mục tiêu**: giao task cho `departmentID` và snapshot danh sách thành viên hợp lệ tại thời điểm giao
- **Checks**: auth/site/privilege + scope phòng ban mục tiêu + phòng ban phải có thành viên hợp lệ
- **Reads**: danh sách nhân sự phòng ban (loại `Đã nghỉ việc`)
- **Writes**: snapshot assignees + audit log
- **Response**: `result(true, {assigneeCount,...})` hoặc `result(false, ..., 400/403)`

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
  actor Manager
  participant UI as Web UI
  participant API as TaskCtrl (REST)
  participant Auth as Auth/Site/Privilege
  participant Perm as TaskPermissionService
  participant Org as Org/Department Service
  participant Assignee as TaskAssigneeMapper
  participant Audit as TaskAuditLogMapper
  participant DB as MySQL

  Manager->>UI: Chọn task + chọn phòng ban departmentID
  UI->>API: POST /:siteID/rest/task/tasks/{taskID}/assignees (mode=department, departmentID)
  API->>Auth: requireLogin + requireSite + requirePrivilege(assignTask)
  Auth-->>API: OK
  API->>Perm: checkDepartmentScope(departmentID, user, site)
  Perm-->>API: Allowed
  API->>Org: listActiveMembers(departmentID) (exclude resigned)
  alt No valid members
    Org-->>API: []
    API-->>UI: result(false, {error:"No valid members"}, 400)
  else Has members
    Org-->>API: memberIDs[]
    API->>Assignee: snapshotDepartmentAssignment(taskID, departmentID, memberIDs)
    Assignee->>DB: INSERT task_assignee rows (snapshot)
    API->>Audit: write(action="task.assignee.department.snapshot", before/after)
    Audit->>DB: INSERT task_audit_log
    API-->>UI: result(true, {taskID, departmentID, assigneeCount})
  end
```

