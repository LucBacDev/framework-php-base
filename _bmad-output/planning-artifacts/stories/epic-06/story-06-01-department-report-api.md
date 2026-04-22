---
epic: 6
story: "6.1"
title: "API báo cáo tổng hợp theo phòng ban và thời gian"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 6.1: API báo cáo tổng hợp theo phòng ban và thời gian

As a Manager,  
I want xem báo cáo tiến độ phòng ban theo khoảng thời gian,  
So that tôi đánh giá hiệu suất và tình trạng công việc của phòng ban.

## Acceptance Criteria

**Given** Manager có quyền xem báo cáo phòng ban trong scope  
**When** gọi API report với bộ lọc thời gian (`fromDate`, `toDate`)  
**Then** hệ thống trả về các chỉ số tối thiểu: tổng task, tỷ lệ hoàn thành, số quá hạn, số chờ duyệt  
**And** số liệu trùng khớp dữ liệu task thực tế

## Diagrams

### Sequence

- **Actor**: Manager
- **Mục tiêu**: lấy metrics theo phòng ban + khoảng thời gian (total, completionRate, overdueCount, pendingApprovalCount)
- **Checks**: auth/site/privilege + scope phòng ban
- **Reads**: aggregates từ `task` (+ logs nếu cần), tối ưu query/cache theo architecture
- **Response**: `result(true, {metrics,...})`

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
  actor Manager
  participant UI as Web UI
  participant API as TaskReportCtrl (REST)
  participant Auth as Auth/Site/Privilege
  participant Perm as TaskPermissionService
  participant Cache as Redis Cache
  participant Report as TaskReportService
  participant DB as MySQL

  Manager->>UI: Chọn fromDate/toDate
  UI->>API: GET /:siteID/rest/task/reports/department?fromDate&toDate
  API->>Auth: requireLogin + requireSite + requirePrivilege(viewReports)
  Auth-->>API: OK
  API->>Perm: resolveManagerDeptScope(user)
  Perm-->>API: dept scope
  API->>Cache: GET task:{siteID}:report:{dept}:{from}:{to}
  alt Cache hit
    Cache-->>API: metrics
    API-->>UI: result(true, metrics)
  else Cache miss
    Cache-->>API: null
    API->>Report: computeMetrics(scope, from, to)
    Report->>DB: SELECT aggregates (total, done, overdue, pendingApproval)
    DB-->>Report: rows
    Report-->>API: metrics
    API->>Cache: SET metrics (short TTL)
    API-->>UI: result(true, metrics)
  end
```

