---
stepsCompleted: ["step-01-init", "step-02-context", "step-03-starter", "step-04-decisions", "step-05-patterns", "step-06-structure", "step-07-validation", "step-08-complete"]
inputDocuments:
  - "_bmad-output/planning-artifacts/prd-task-management.md"
  - "docs/BRD Quản lý công việc doanh nghiệp.md"
  - "_bmad-output/planning-artifacts/ux-design-specification.md"
  - "_bmad-output/project-context.md"
workflowType: 'architecture'
project_name: 'pacs2'
user_name: 'USER'
date: '2026-04-22'
lastStep: 8
status: 'complete'
completedAt: '2026-04-22'
---

# Architecture Decision Document

_This document builds collaboratively through step-by-step discovery. Sections are appended as we work through each architectural decision together._

## Project Context Analysis

### Requirements Overview

**Functional Requirements:**
- Hệ thống tập trung vào phân hệ Task Management trong E-Office với luồng nghiệp vụ rõ: tạo task, giao việc cá nhân/phòng ban, theo dõi tiến độ, gửi duyệt, phê duyệt/làm lại.
- Có 9 nhóm FR trọng tâm (`FR-05` đến `FR-13`) bao phủ vòng đời công việc từ khởi tạo đến báo cáo và truy vết.
- Tác nhân nghiệp vụ và phạm vi quyền đã rõ: `Admin`, `Manager`, `Staff`, trong đó Manager/Staff là luồng thao tác chính.
- Có yêu cầu quan trọng về audit trail, kiểm soát thao tác xóa, và đồng bộ dashboard theo dữ liệu thực tế.

**Non-Functional Requirements:**
- **Security:** RBAC theo vai trò + phạm vi phòng ban, 100% API thao tác task phải kiểm tra quyền.
- **Performance:** Tìm kiếm/truy vấn task đạt P95 <= 2 giây.
- **Availability/Reliability:** 24/7, mục tiêu `RPO <= 5 phút`, `RTO <= 30 phút`, retry thông báo tối đa 3 lần.
- **Web constraints:** ưu tiên Chrome/Edge, responsive, mục tiêu accessibility tối thiểu WCAG 2.1 mức A cho màn hình chính.

**Scale & Complexity:**
- Primary domain: Web application nội bộ doanh nghiệp (enterprise operations).
- Complexity level: Medium (nghiệp vụ rõ nhưng có nhiều ràng buộc workflow + phân quyền + truy vết).
- Estimated architectural components: khoảng 8-12 thành phần chính (Task service, Workflow engine/rules, Assignment module, Notification, Dashboard query, Reporting, Audit logging, Auth/RBAC integration, File attachment).

### Technical Constraints & Dependencies

- Bối cảnh là **brownfield** trên nền hệ thống hiện có (`pacs2`) với stack PHP/Slim/module-based.
- Cần tái sử dụng pattern hiện hữu từ project context:
  - `makeInstance()` thay vì khởi tạo trực tiếp.
  - Kiểm tra auth/site/privilege nhất quán ở controller.
  - Chuẩn response/error handling thống nhất.
- Cần tương thích cấu trúc module và convention hiện tại để giảm xung đột khi nhiều AI agents cùng triển khai.
- Nhu cầu thông báo quá hạn/sắp quá hạn tạo phụ thuộc vào cơ chế scheduler/queue đang có.

### Cross-Cutting Concerns Identified

- **Authorization boundary:** kiểm soát quyền theo vai trò + cây phòng ban ở mọi endpoint task.
- **Workflow integrity:** bắt buộc state machine và chặn chuyển trạng thái bất hợp lệ.
- **Auditability:** ghi vết đầy đủ before/after cho thay đổi quan trọng.
- **Data consistency:** dashboard/report phải khớp dữ liệu giao dịch task.
- **Observability & reliability:** theo dõi retry thông báo, lỗi nghiệp vụ, và độ trễ truy vấn.

## Starter Template Evaluation

### Primary Technology Domain

Brownfield internal web app module extension (PHP backend + existing admin web UI), based on current pacs2 architecture.

### Starter Options Considered

1. New framework starter (không phù hợp):
- Gây lệch stack hiện tại và tăng chi phí tích hợp.
- Rủi ro phá vỡ convention/module lifecycle của hệ thống đang chạy.

2. Brownfield extension on existing stack (phù hợp):
- Tận dụng auth, RBAC, router, response/error conventions đã ổn định.
- Giảm rủi ro triển khai và đảm bảo tương thích với codebase hiện hữu.

### Selected Starter: No new starter (Brownfield Extension)

**Rationale for Selection:**
- Hệ thống đã vận hành production với convention rõ ràng.
- PRD yêu cầu mở rộng nghiệp vụ Task Management, không yêu cầu re-platform.
- Đảm bảo AI agents triển khai nhất quán theo cùng chuẩn module hiện có.

**Initialization Command:**
N/A - Không tạo project mới. Triển khai dưới dạng module/feature mới trong cấu trúc hiện tại.

**Architectural Decisions Provided by Existing Foundation:**

**Language & Runtime:**
- PHP (theo stack hiện hữu của dự án).

**Framework/Platform:**
- Slim-based routing và module lifecycle hiện hành.

**Data Layer Direction:**
- Kế thừa data access pattern hiện có (mapper + conventions hiện tại).
- Mở rộng schema cho task/audit/reporting theo module SQL pattern của dự án.

**Code Organization:**
- Tuân thủ module-based structure hiện tại (`Controller/`, `Model/`, `sql/`, `router.php`, `construct.php`).

**Development Experience:**
- Không thay đổi workflow cốt lõi; tập trung bổ sung chuẩn nghiệp vụ Task Management trên nền stack sẵn có.

**Note:** Story implementation đầu tiên nên là bootstrap module Task Management theo đúng convention hiện tại (route, controller, mapper, schema, privilege seed).

## Core Architectural Decisions

### Decision Priority Analysis

**Critical Decisions (Block Implementation):**
- Thiết kế schema và quan hệ dữ liệu task/assignment/progress/audit.
- State machine và enforcement rule cho luồng trạng thái task.
- Mô hình phân quyền theo role + phạm vi phòng ban.
- API contract cho task lifecycle, dashboard, report.
- Cơ chế thông báo deadline + retry policy.

**Important Decisions (Shape Architecture):**
- Chiến lược query cho dashboard/reporting (read model, index, cache).
- Quy ước soft delete/lock task và bảo toàn lịch sử nghiệp vụ.
- Tổ chức module và boundary giữa task core, notification, reporting.
- Logging/monitoring cho audit + operational metrics.

**Deferred Decisions (Post-MVP):**
- Sub-task hierarchy.
- SLA theo loại task/phòng ban.
- Tích hợp thông báo đa kênh ngoài hệ thống (email/Zalo/Teams).

### Data Architecture

- **Database:** MySQL hiện hữu, mở rộng theo module SQL convention.
- **Core Entities đề xuất:**
  - `task`
  - `task_assignee`
  - `task_progress_log`
  - `task_status_log`
  - `task_attachment`
  - `task_audit_log`
- **Data Rules:**
  - `task.status` tuân thủ state machine.
  - `task_progress_log.progress_percent` trong [0..100].
  - Task giao phòng ban snapshot danh sách thành viên tại thời điểm giao.
- **Caching Strategy:**
  - Redis cache cho dashboard counters/query aggregate ngắn hạn.
  - Invalidate cache khi có thay đổi trạng thái/tiến độ.
- **Migration Approach:**
  - File SQL theo module (`sql/module_entity.table.sql`) + install/upgrade scripts.

### Authentication & Security

- **Authentication:** kế thừa login/session/JWT hiện tại.
- **Authorization:**
  - RBAC theo `Admin/Manager/Staff`.
  - Enforce thêm boundary theo phòng ban quản lý.
- **Security Middleware:**
  - Mọi endpoint task bắt buộc `requireLogin()` + site/privilege checks.
- **Data Protection:**
  - Không cho Staff xóa task đã giao.
  - Xóa logic (nếu có) bắt buộc audit before/after.
- **Audit:**
  - Ghi log tạo/sửa/chuyển trạng thái/duyệt/làm lại/đổi deadline/đổi người nhận.

### API & Communication Patterns

- **API Style:** REST endpoints trong module task.
- **Response Standard:** dùng chuẩn `result()` nhất quán toàn hệ thống.
- **Error Handling:** dùng `Company\Exception\*` map sang mã HTTP phù hợp.
- **Key Endpoint Groups:**
  - Task CRUD + assignment
  - Workflow transition (`start`, `submit`, `approve`, `rework`)
  - Progress updates
  - Dashboard queries
  - Department reports
  - Audit trail queries
- **Notification Communication:**
  - Job-based async notifications cho cảnh báo sắp quá hạn/quá hạn.
  - Retry: 1m, 5m, 15m (tối đa 3 lần).

### Frontend Architecture

- **UI Foundation:** giữ current admin UI pattern (brownfield).
- **Main Screens:**
  - Task List / My Tasks
  - Task Detail + Timeline/Audit
  - Manager Approval Queue
  - Dashboard cá nhân
  - Báo cáo phòng ban
- **State Strategy:**
  - Server-driven lists + filter/pagination.
  - Polling hoặc refresh event cho cập nhật tiến độ gần real-time.
- **UX Constraints:**
  - Chrome/Edge ưu tiên; responsive cho thiết bị di động.
  - Keyboard flow cho thao tác cốt lõi theo mục tiêu WCAG A.

### Infrastructure & Deployment

- **Runtime:** theo hạ tầng hiện tại (PHP app + existing services).
- **Background Processing:**
  - Dùng cơ chế queue/job hiện có cho notifications và deadline scan.
- **Observability:**
  - Metrics: số task quá hạn, approval pending, notification success rate.
  - Log tập trung cho API errors + workflow violations + retry failures.
- **Resilience Targets:**
  - Hỗ trợ mục tiêu RPO <= 5 phút, RTO <= 30 phút theo NFR.

### Decision Impact Analysis

**Implementation Sequence:**
1. Schema + migration + privilege seed.
2. Workflow engine + transition guard.
3. Core task APIs + assignment.
4. Progress + approval APIs.
5. Notification job + retry.
6. Dashboard/report queries + cache.
7. UI screens + filters.
8. Audit trail views + validation hardening.

**Cross-Component Dependencies:**
- Workflow engine phụ thuộc RBAC checks và schema logs.
- Dashboard/report phụ thuộc dữ liệu chuẩn từ task/progress/status logs.
- Notification retry phụ thuộc queue và trạng thái deadline chính xác.

## Implementation Patterns & Consistency Rules

### Pattern Categories Defined

**Critical Conflict Points Identified:**
10 nhóm xung đột tiềm ẩn: naming DB/API/code, cấu trúc module, format response/error, workflow transition guard, logging/audit payload, dashboard query conventions, notification retry behavior, quyền truy cập theo phòng ban, soft delete semantics, caching invalidation.

### Naming Patterns

**Database Naming Conventions:**
- Table: `task_*` theo snake_case, ví dụ: `task`, `task_assignee`, `task_status_log`.
- PK: `id` (UUID/VARCHAR theo chuẩn hệ thống hiện có).
- FK: `{entity}ID` hoặc `{entity}FK` theo convention hiện hữu của repo (chọn 1 và áp dụng nhất quán trong module task).
- Timestamp fields: `createdDate`, `updatedDate`, `deletedDate` (nếu dùng soft delete).

**API Naming Conventions:**
- Base path: `/:siteID/rest/task/*`
- Resource plural: `/tasks`, `/tasks/{taskID}/assignees`, `/tasks/{taskID}/progress`
- Workflow actions: `/tasks/{taskID}/actions/start|submit|approve|rework`
- Query params: camelCase nhất quán (`status`, `priority`, `assigneeID`, `fromDate`, `toDate`)

**Code Naming Conventions:**
- Controller: `TaskCtrl`, `TaskApprovalCtrl`, `TaskReportCtrl`
- Mapper: `TaskMapper`, `TaskProgressMapper`, `TaskAuditMapper`
- Methods: camelCase động từ rõ nghĩa (`createTask`, `submitForApproval`, `approveTask`)
- Constants: UPPER_SNAKE_CASE (`TASK_STATUS_NEW`, `TASK_PRIORITY_HIGH`)

### Structure Patterns

**Project Organization:**
- `Module/company/task/Controller/`
- `Module/company/task/Model/`
- `Module/company/task/Lib/`
- `Module/company/task/sql/`
- `Module/company/task/router.php`
- `Module/company/task/construct.php`
- UI side theo module hiện hữu tại `Module/companyui/task/`

**File Structure Patterns:**
- 1 endpoint group/1 controller responsibility.
- Mapper tách rõ entity chính và log/audit entity.
- SQL schema tách theo từng bảng, không dồn một file lớn khó review.

### Format Patterns

**API Response Formats:**
- Luôn dùng `result(true|false, dataOrError, code?)`.
- Không trả raw array/object tùy biến ngoài wrapper chuẩn.
- Danh sách luôn có metadata phân trang khi có paging.

**Error Format:**
- Exception chuẩn từ `Company\Exception\*`.
- Mapping lỗi nghiệp vụ:
  - Invalid transition -> 400
  - Forbidden by RBAC/scope -> 403
  - Not found -> 404
  - Conflict workflow/data version -> 409

**Date/Time Formats:**
- API input/output dùng ISO8601.
- DB lưu theo timezone chuẩn hệ thống (UTC hoặc timezone chuẩn đã cấu hình), không trộn lẫn.

### Communication Patterns

**Event/Job Patterns (Notifications):**
- Job names: `task.deadline.reminder`, `task.deadline.overdue`, `task.approval.pending`.
- Payload tối thiểu: `taskID`, `siteID`, `recipientIDs`, `triggerAt`, `retryCount`.
- Retry policy cố định: 1m -> 5m -> 15m, max 3.

**State Management Patterns (UI):**
- Filter state lưu trên URL query để share/reload được.
- Danh sách dùng server-side filtering/sorting nhất quán.
- Trạng thái loading/success/error hiển thị theo cùng mẫu toàn module.

### Process Patterns

**Workflow Transition Guard:**
- Chỉ cho phép các cạnh:
  - `NEW -> IN_PROGRESS -> PENDING_APPROVAL -> DONE`
  - `PENDING_APPROVAL -> REWORK -> IN_PROGRESS`
- Mọi transition đi qua 1 service guard chung, không hardcode rải rác ở nhiều controller.

**Permission Check Order:**
1. `requireLogin()`
2. `requireSite()/setSiteID()`
3. `requirePrivilege()`
4. Scope check theo phòng ban/task ownership

**Audit Logging Pattern:**
- Mọi action quan trọng ghi `before/after`, actor, timestamp, source.
- Staff không có quyền sửa/xóa audit.
- Delete logic phải có `reason` và record xác nhận.

**Caching/Invalidation Pattern:**
- Cache key có prefix `task:{siteID}:...`
- Invalidate bắt buộc khi create/update/transition/approve/rework.
- Không cache dữ liệu detail có tính nhất quán cao nếu chưa có invalidation rõ.

### Enforcement Guidelines

**All AI Agents MUST:**
- Tuân thủ state machine và transition guard chung.
- Tuân thủ response wrapper `result()` và exception chuẩn.
- Không bypass permission chain + department scope checks.
- Ghi audit log cho mọi thao tác nằm trong FR-12/FR-13.
- Tuân thủ naming/structure conventions của module task.

**Pattern Enforcement:**
- PR checklist bắt buộc: naming, permission, workflow, audit, cache invalidation.
- Unit/integration tests tối thiểu cho: transition guard, RBAC scope, audit creation.
- Reject PR nếu endpoint mới không có permission + audit phù hợp.

### Pattern Examples

**Good Examples:**
- `POST /:siteID/rest/tasks/{taskID}/actions/approve`:
  - check login/site/privilege/scope
  - validate current status = `PENDING_APPROVAL`
  - update status -> `DONE`
  - write status log + audit log
  - invalidate dashboard/report cache
  - return `result(true, payload)`

**Anti-Patterns:**
- Cập nhật trực tiếp `task.status` ở nhiều nơi không qua guard.
- Endpoint task không check scope phòng ban.
- Trả response custom làm lệch format.
- Không ghi audit cho approve/rework/delete logic.

## Project Structure & Boundaries

### Complete Project Directory Structure

```text
pacs2/
├── Module/
│   ├── company/
│   │   └── task/
│   │       ├── Controller/
│   │       │   ├── TaskCtrl.php
│   │       │   ├── TaskWorkflowCtrl.php
│   │       │   ├── TaskDashboardCtrl.php
│   │       │   └── TaskReportCtrl.php
│   │       ├── Model/
│   │       │   ├── TaskMapper.php
│   │       │   ├── TaskAssigneeMapper.php
│   │       │   ├── TaskProgressMapper.php
│   │       │   ├── TaskStatusLogMapper.php
│   │       │   ├── TaskAttachmentMapper.php
│   │       │   └── TaskAuditLogMapper.php
│   │       ├── Lib/
│   │       │   ├── TaskWorkflowGuard.php
│   │       │   ├── TaskPermissionService.php
│   │       │   ├── TaskNotificationService.php
│   │       │   └── TaskReportService.php
│   │       ├── Exec/
│   │       │   ├── deadlineReminderJob.php
│   │       │   └── overdueScanJob.php
│   │       ├── sql/
│   │       │   ├── task.table.sql
│   │       │   ├── task_assignee.table.sql
│   │       │   ├── task_progress_log.table.sql
│   │       │   ├── task_status_log.table.sql
│   │       │   ├── task_attachment.table.sql
│   │       │   └── task_audit_log.table.sql
│   │       ├── router.php
│   │       ├── construct.php
│   │       ├── install.php
│   │       └── composer.json
│   └── companyui/
│       └── task/
│           ├── public/
│           │   └── view/
│           │       ├── TaskList.js
│           │       ├── TaskDetail.js
│           │       ├── TaskApproval.js
│           │       ├── TaskDashboard.js
│           │       └── TaskReport.js
│           ├── lang/
│           │   ├── vi.json
│           │   └── en.json
│           ├── router.php
│           ├── construct.php
│           └── composer.json
└── tests/
    ├── unit/
    │   └── task/
    ├── integration/
    │   └── task/
    └── e2e/
        └── task/
```

### Architectural Boundaries

**API Boundaries:**
- Task module chỉ expose endpoint qua `/:siteID/rest/tasks/*`.
- Workflow actions tách riêng namespace `actions` để tránh lẫn CRUD với transition nghiệp vụ.
- Dashboard/report endpoint read-only, không được chứa side effect.

**Component Boundaries:**
- `Controller`: validate input + auth/scope + orchestration.
- `Lib/Service`: business rules, workflow guard, notification logic.
- `Model`: truy cập dữ liệu, không chứa quyết định workflow.
- `companyui/task`: chỉ rendering + interaction; business rule nằm backend.

**Service Boundaries:**
- `TaskWorkflowGuard`: nguồn chân lý duy nhất cho state transition.
- `TaskPermissionService`: nguồn chân lý duy nhất cho scope check theo phòng ban.
- `TaskNotificationService`: chịu trách nhiệm enqueue + retry tracking.
- `TaskReportService`: tổng hợp dữ liệu báo cáo theo kỳ.

**Data Boundaries:**
- `task` là aggregate root.
- Log tables (`task_progress_log`, `task_status_log`, `task_audit_log`) là append-only (trừ kỹ thuật vận hành được kiểm soát).
- Attachments tách bảng để tối ưu kích thước bản ghi task chính.

### Requirements to Structure Mapping

**Feature Mapping:**
- FR-05/06 -> `TaskCtrl`, `TaskAssigneeMapper`
- FR-07 -> `TaskCtrl` + `TaskNotificationService` + `deadlineReminderJob.php`
- FR-08 -> `TaskWorkflowCtrl` + `TaskWorkflowGuard`
- FR-09 -> `TaskCtrl` + `TaskProgressMapper`
- FR-10 -> `TaskDashboardCtrl` + `TaskDashboard.js`
- FR-11 -> `TaskReportCtrl` + `TaskReportService` + `TaskReport.js`
- FR-12 -> `TaskAuditLogMapper` + audit writes trong service layer
- FR-13 -> `TaskPermissionService` + guarded delete/lock flows

**Cross-Cutting Concerns:**
- RBAC/scope: `TaskPermissionService` + controller middleware chain.
- Audit: write-through tại service layer mọi action quan trọng.
- Performance: `TaskDashboardCtrl` và `TaskReportCtrl` dùng query tối ưu + cache.
- Reliability: retry job + monitoring counters cho notification pipeline.

### Integration Points

**Internal Communication:**
- Controller -> Service (`Lib`) -> Mapper.
- Workflow transition luôn qua `TaskWorkflowGuard`.
- Notification qua job enqueue (không gửi sync trong request chính).

**External Integrations:**
- Reuse auth/session/JWT hiện tại của `company/auth`.
- Reuse queue/scheduler/caching hiện tại (Redis + cơ chế job có sẵn).
- Reuse org structure từ module phòng ban/nhân sự để resolve assignment theo phòng.

**Data Flow:**
1. Manager tạo task -> lưu `task` + `task_assignee` + audit.
2. Staff cập nhật tiến độ -> lưu `task_progress_log` + có thể update summary trong `task`.
3. Gửi duyệt/duyệt/làm lại -> chạy workflow guard -> ghi `task_status_log` + audit -> invalidate cache.
4. Dashboard/report query đọc từ `task` + logs (có thể qua pre-aggregated cache).

### File Organization Patterns

**Configuration Files:**
- Config module task đặt trong `construct.php` + service config chuẩn hệ thống.

**Source Organization:**
- Naming giữ chuẩn hiện hữu (`*Ctrl`, `*Mapper`, `makeInstance()`).

**Test Organization:**
- Unit test cho workflow guard/permission service.
- Integration test cho API transitions + audit logging.
- E2E test cho luồng Manager/Staff chính.

**Asset Organization:**
- JS views riêng cho từng màn hình task.
- i18n keys tách trong `companyui/task/lang/*`.

### Development Workflow Integration

**Development Server Structure:**
- Không đổi bootstrap toàn hệ thống; module task được nạp qua lifecycle hiện tại.

**Build Process Structure:**
- Theo pipeline hiện hữu; thêm migration/install step cho module task.

**Deployment Structure:**
- Rollout theo module; có thể feature-flag nếu cần giảm rủi ro khi go-live.

## Architecture Validation Results

### Coherence Validation ✅

**Decision Compatibility:**
- Brownfield extension phù hợp stack hiện tại, không tạo xung đột runtime/framework.
- Data model, workflow guard, RBAC scope, notification retry hỗ trợ lẫn nhau.
- Patterns naming/structure/response đồng bộ với project context.

**Pattern Consistency:**
- Quy ước `Controller -> Service -> Mapper` rõ ràng, tránh lẫn business rule với data access.
- State transition được gom về guard chung, giảm rủi ro logic phân tán.
- Response/error/audit/caching có chuẩn thống nhất cho toàn module.

**Structure Alignment:**
- Cây thư mục module task map trực tiếp theo FR-05..FR-13.
- Boundary giữa backend service, UI, job, và reporting đã rõ.
- Tích hợp được với auth, org, cache, queue hiện hữu.

### Requirements Coverage Validation ✅

**Functional Requirements Coverage:**
- FR-05..FR-13 đều đã có component/endpoint/data-layer tương ứng.
- Luồng Manager/Staff/Admin được hỗ trợ đầy đủ theo PRD.

**Non-Functional Requirements Coverage:**
- Security: RBAC + scope check + audit bắt buộc.
- Performance: hướng tối ưu query dashboard/report + cache invalidation strategy.
- Availability/Reliability: thiết kế job retry cho notifications; phù hợp mục tiêu 24/7.
- Accessibility/Web: giữ nguyên UI foundation hiện hữu, bổ sung guideline cho luồng chính.

### Implementation Readiness Validation ✅

**Decision Completeness:**
- Critical decisions đã chốt: schema, workflow, authorization, API, notifications.
- Deferred scope đã tách rõ (sub-task, SLA, multi-channel notify).

**Structure Completeness:**
- Project tree cho module task đủ để bắt đầu implementation stories.
- Integration points nội bộ/ngoại vi đã được mô tả rõ.

**Pattern Completeness:**
- Conflict points chính đã có pattern và anti-pattern đi kèm.
- Enforcement guideline có thể đưa thẳng vào PR checklist/test checklist.

### Gap Analysis Results

**Critical Gaps:** None.

**Important Gaps:**
- Cần chốt dứt điểm 1 chuẩn FK naming trong module task (`entityID` vs `entityFK`) trước khi viết migration.
- Cần quyết định mức real-time cho dashboard (polling interval hay event push) để tránh over-engineering.
- Cần xác nhận nguồn dữ liệu phòng ban snapshot khi giao task theo department (đọc từ bảng nào và tại thời điểm nào).

**Nice-to-Have Gaps:**
- Chuẩn hóa template thông báo (nội dung/đa ngôn ngữ).
- Bổ sung chỉ số vận hành sâu hơn: transition failure rate, approval lead time.

### Validation Issues Addressed

- Đã giảm rủi ro lệch chuẩn bằng cách bắt buộc transition qua guard chung.
- Đã tránh rò quyền bằng permission chain + department scope check.
- Đã xử lý nhất quán dữ liệu dashboard/report qua pattern cache invalidation.

### Architecture Completeness Checklist

**✅ Requirements Analysis**
- [x] Project context analyzed
- [x] FR/NFR mapped
- [x] Cross-cutting concerns identified

**✅ Architectural Decisions**
- [x] Core decisions documented
- [x] Security/workflow/data/API covered
- [x] Deferred scope isolated

**✅ Implementation Patterns**
- [x] Naming/structure/format patterns defined
- [x] Process + enforcement rules defined
- [x] Good/anti-pattern examples provided

**✅ Project Structure**
- [x] Complete module tree proposed
- [x] Boundaries and integration points mapped
- [x] Requirement-to-structure mapping complete

### Architecture Readiness Assessment

**Overall Status:** READY FOR IMPLEMENTATION

**Confidence Level:** High

**Key Strengths:**
- Bám sát PRD và phù hợp hoàn toàn bối cảnh brownfield.
- Ràng buộc workflow + RBAC + audit rõ, giảm lỗi nghiệp vụ.
- Cấu trúc triển khai chi tiết, có thể chuyển ngay thành stories/dev tasks.

**Areas for Future Enhancement:**
- Sub-task model và SLA engine.
- Event-driven realtime dashboard nếu nhu cầu tăng.
- Multi-channel notification adapters.

### Implementation Handoff

**AI Agent Guidelines:**
- Tuân thủ tuyệt đối state machine guard, permission chain, audit logging.
- Không bypass conventions về response/error/naming.
- Mọi endpoint task mới phải có test cho quyền + workflow + audit.

**First Implementation Priority:**
1. Bootstrap `Module/company/task` + schema SQL.
2. Implement transition guard + permission service.
3. Build core task lifecycle APIs.
4. Add dashboard/report read APIs.
5. Add notification jobs + retry tracking.
