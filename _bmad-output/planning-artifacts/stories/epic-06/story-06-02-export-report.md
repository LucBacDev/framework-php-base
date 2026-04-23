---
epic: 6
story: "6.2"
title: "Xuất báo cáo theo kỳ (ngày/tuần/tháng)"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 6.2: Xuất báo cáo theo kỳ (ngày/tuần/tháng)

As a Manager,  
I want xuất báo cáo theo kỳ,  
So that tôi có thể chia sẻ số liệu và lưu trữ báo cáo định kỳ.

## Acceptance Criteria

**Given** Manager đang xem báo cáo theo bộ lọc thời gian  
**When** chọn xuất báo cáo theo kỳ (ngày/tuần/tháng)  
**Then** hệ thống tạo file export (ví dụ CSV) với dữ liệu đúng phạm vi phòng ban và thời gian  
**And** hành động export được ghi log/audit theo chuẩn hệ thống nếu yêu cầu

## Diagrams

### Sequence

- **Actor**: Manager
- **Mục tiêu**: export report theo kỳ (ngày/tuần/tháng) thành file (ví dụ CSV) đúng scope + time range
- **Checks**: auth/site/privilege + scope phòng ban
- **Reads**: report dataset theo filters
- **Writes**: file export (storage) + (optional) audit/log
- **Response**: `result(true, {downloadUrl|fileID})`

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
  actor Manager
  participant UI as Web UI
  participant API as TaskReportCtrl (REST)
  participant Auth as Auth/Site/Privilege
  participant Perm as TaskPermissionService
  participant Report as TaskReportService
  participant Export as ExportService (CSV)
  participant Store as File Storage
  participant Audit as TaskAuditLogMapper
  participant DB as MySQL

  Manager->>UI: Click "Export" (day/week/month)
  UI->>API: POST /:siteID/rest/task/reports/department/export (period, from,to)
  API->>Auth: requireLogin + requireSite + requirePrivilege(exportReports)
  Auth-->>API: OK
  API->>Perm: resolveManagerDeptScope(user)
  Perm-->>API: dept scope
  API->>Report: getDataset(scope, from,to, period)
  Report->>DB: SELECT dataset
  DB-->>Report: rows
  API->>Export: toCSV(rows)
  Export-->>API: csvBytes
  API->>Store: save(csvBytes)
  Store-->>API: fileID/downloadUrl
  API->>Audit: write(action="task.report.export", before/after minimal)
  Audit->>DB: INSERT task_audit_log
  API-->>UI: result(true, {downloadUrl})
```

