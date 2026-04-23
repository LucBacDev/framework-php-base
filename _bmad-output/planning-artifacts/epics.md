---
stepsCompleted:
  - "step-01-validate-prerequisites"
  - "step-02-design-epics"
  - "step-03-create-stories"
  - "step-04-final-validation"
inputDocuments:
  - "_bmad-output/planning-artifacts/prd-task-management.md"
  - "_bmad-output/planning-artifacts/architecture.md"
  - "_bmad-output/planning-artifacts/ux-design-specification.md"
  - "_bmad-output/planning-artifacts/prd.md"
---

# pacs2 - Epic Breakdown

## Overview

Tài liệu này sẽ cung cấp danh sách Epic và User Stories cho phân hệ **Quản lý Task (E-Office)** trong dự án `pacs2`, được phân rã từ PRD + Architecture + UX constraints.

## Requirements Inventory

### Functional Requirements

FR1: Khởi tạo task (tiêu đề, mô tả, ưu tiên, người xử lý, ngày bắt đầu, deadline, tệp đính kèm) với validation và lỗi theo từng trường; task mới mặc định trạng thái `Mới`.
FR2: Giao việc cho **cá nhân** hoặc **phòng ban**; đảm bảo Manager chỉ giao trong phạm vi quản lý; từ chối giao cho nhân sự `Đã nghỉ việc`; giao phòng ban cần snapshot danh sách thành viên hợp lệ tại thời điểm giao.
FR3: Quản lý deadline và cảnh báo sắp quá hạn/quá hạn; ràng buộc `start_time <= due_time`; Manager được cập nhật deadline trước khi task `Hoàn thành`.
FR4: Enforce workflow trạng thái bắt buộc: `Mới -> Đang làm -> Chờ duyệt -> Hoàn thành` và nhánh `Chờ duyệt -> Làm lại -> Đang làm`; không cho phép nhảy trạng thái; chỉ Manager (hoặc người được phân quyền) được duyệt/làm lại.
FR5: Staff cập nhật tiến độ (0-100) + nội dung thực hiện; khi gửi duyệt phải có kết quả/ghi chú tổng kết tối thiểu; lưu lịch sử cập nhật theo phiên bản; đồng bộ lên dashboard gần real-time.
FR6: Dashboard cá nhân theo vai trò (việc cần làm, ưu tiên cao, sắp quá hạn/quá hạn, chờ duyệt cho Manager) với bộ lọc trạng thái/ưu tiên hoạt động đúng và dữ liệu khớp danh sách task nguồn.
FR7: Báo cáo tiến độ phòng ban (tổng task, tỷ lệ hoàn thành, số quá hạn, số chờ duyệt) theo bộ lọc thời gian; Manager chỉ xem phòng ban phụ trách; hỗ trợ xuất thống kê theo kỳ (ngày/tuần/tháng).
FR8: Audit trail: ghi log đầy đủ tạo/sửa/đổi người nhận/đổi deadline/chuyển trạng thái/gửi duyệt/duyệt/làm lại, gồm before/after, actor, timestamp; Staff không được sửa/xóa.
FR9: Ràng buộc thao tác & phân quyền xóa: Staff không được xóa task đã giao; Manager không được xóa cứng khi task đã phát sinh xử lý (trừ Admin); xóa logic (nếu có) phải xác nhận và ghi audit log.

### NonFunctional Requirements

NFR1: RBAC theo vai trò Admin/Manager/Staff và phạm vi phòng ban; 100% API thao tác task phải kiểm tra quyền theo vai trò + scope trước khi xử lý.
NFR2: Hiệu năng truy vấn/tìm kiếm task đạt \(P95 \le 2s\).
NFR3: Vận hành 24/7.
NFR4: Hỗ trợ Chrome/Edge và giao diện responsive cho mobile.
NFR5: Accessibility tối thiểu WCAG 2.1 mức A cho màn hình chính (dashboard, list, detail); thao tác cốt lõi phải làm được bằng bàn phím (lọc task, cập nhật trạng thái, gửi duyệt).
NFR6: Reliability: RPO \(\le 5\) phút, RTO \(\le 30\) phút; retry thông báo tối đa 3 lần (1m, 5m, 15m) và tỷ lệ gửi thông báo thành công \(\ge 99\%\) theo thống kê ngày.

### Additional Requirements

**Từ Architecture (ràng buộc kỹ thuật ảnh hưởng triển khai):**
- Brownfield extension trên stack hiện hữu (PHP/Slim/module-based); tái sử dụng conventions (`makeInstance()`, auth/site/privilege checks, `result()` response wrapper).
- Cấu trúc module đề xuất: `Module/company/task/*` (Controller/Model/Lib/sql/Exec/router.php/construct.php/install.php) và UI ở `Module/companyui/task/*`.
- Data model mở rộng MySQL: `task`, `task_assignee`, `task_progress_log`, `task_status_log`, `task_attachment`, `task_audit_log` (append-only cho log tables).
- State machine & transition guard: mọi chuyển trạng thái phải đi qua `TaskWorkflowGuard` (nguồn chân lý duy nhất), tránh hardcode rải rác.
- Permission chain bắt buộc: `requireLogin()` -> site -> privilege -> scope theo phòng ban/task ownership; scope check tập trung tại `TaskPermissionService`.
- API conventions (REST): base `/:siteID/rest/task/*`, nhóm endpoint CRUD/assignment, workflow actions (`/actions/start|submit|approve|rework`), progress, dashboard/report, audit queries.
- Notification: async job (queue/scheduler hiện có) cho nhắc deadline & quá hạn; payload tối thiểu và retry policy 1m/5m/15m (max 3); theo dõi metrics (overdue, approval pending, success rate).
- Performance: dashboard/report dùng query tối ưu + Redis cache ngắn hạn; invalidate cache khi create/update/transition/approve/rework.
- Test expectations: unit cho workflow guard + permission service; integration cho transitions + audit logging; e2e cho luồng Manager/Staff chính.

**Từ UX Design (ràng buộc UX tổng quát có thể tái dùng):**
- Giữ pattern UI hiện hữu (Bootstrap/jQuery style), ưu tiên table-based list + pagination, toast notification cho success/error, loading states rõ ràng.
- Ngôn ngữ UI hỗ trợ Việt/Anh (i18n files).

### FR Coverage Map

FR1: Epic 1 - Khởi tạo task
FR2: Epic 1 - Giao việc cá nhân/phòng ban
FR3: Epic 2 - Deadline & cảnh báo sắp quá hạn/quá hạn
FR4: Epic 4 - Workflow trạng thái & phê duyệt/làm lại
FR5: Epic 3 - Cập nhật tiến độ & lịch sử cập nhật
FR6: Epic 5 - Dashboard cá nhân theo vai trò
FR7: Epic 6 - Báo cáo tiến độ phòng ban + xuất thống kê
FR8: Epic 1/3/4 - Audit trail cho toàn bộ thao tác trọng yếu theo từng luồng
FR9: Epic 1 - Ràng buộc thao tác & phân quyền xóa

## Epic List

### Epic 1: Khởi tạo & Giao việc (kèm quản trị dữ liệu cơ bản)
Người dùng có thể tạo task, giao cho cá nhân/phòng ban, quản lý tệp đính kèm; hệ thống đảm bảo phân quyền thao tác và ràng buộc xóa đúng vai trò.
**FRs covered:** FR1, FR2, FR8, FR9

### Epic 2: Deadline & Cảnh báo quá hạn
Người dùng quản lý mốc thời gian và nhận cảnh báo sắp quá hạn/quá hạn theo cấu hình.
**FRs covered:** FR3

### Epic 3: Thực hiện công việc & Cập nhật tiến độ
Staff thực hiện task, cập nhật % tiến độ và nội dung; lưu lịch sử cập nhật; sẵn sàng để submit vào luồng duyệt.
**FRs covered:** FR5, FR8

### Epic 4: Luồng trạng thái & Phê duyệt kết quả
Tuân thủ state machine, staff submit duyệt, manager duyệt hoặc yêu cầu làm lại; không cho phép chuyển trạng thái sai.
**FRs covered:** FR4, FR8

### Epic 5: Dashboard cá nhân theo vai trò
Người dùng xem “việc cần làm”, ưu tiên cao, sắp quá hạn/quá hạn, hàng chờ duyệt; lọc đúng theo trạng thái/ưu tiên.
**FRs covered:** FR6

### Epic 6: Báo cáo tiến độ phòng ban
Manager xem báo cáo tổng hợp theo phòng ban và thời gian; hỗ trợ export theo kỳ.
**FRs covered:** FR7

---

## Epic 1: Khởi tạo & Giao việc (kèm quản trị dữ liệu cơ bản)

Người dùng có thể tạo task, giao cho cá nhân/phòng ban, quản lý tệp đính kèm; hệ thống đảm bảo phân quyền thao tác và ràng buộc xóa đúng vai trò.

### Story 1.1: Tạo task (API + validation + lưu DB)

As a Manager,
I want tạo một task mới với tiêu đề/mô tả/ưu tiên/mốc thời gian cơ bản,
So that tôi có thể giao việc và bắt đầu theo dõi công việc trong hệ thống.

**Acceptance Criteria:**

**Given** Manager đã đăng nhập, đã chọn `siteID`, và có quyền tạo task trong phạm vi phòng ban
**When** gọi API tạo task với `title` (<= 200 ký tự), `description`, `priority` ∈ {Thấp, Vừa, Cao}, `start_time`, `due_time`
**Then** hệ thống validate dữ liệu (bao gồm `start_time <= due_time`) và tạo bản ghi `task` với trạng thái mặc định `Mới`
**And** response trả về theo wrapper chuẩn `result(true, data)`; task xuất hiện ngay trong danh sách của người tạo, và sẽ xuất hiện trong danh sách người nhận sau khi thực hiện thao tác gán người nhận
**And** ghi audit log cho sự kiện “tạo task” (actor, timestamp, before/after phù hợp)

### Story 1.2: Giao task cho cá nhân (assignee đơn)

As a Manager,
I want giao task cho một nhân viên cụ thể,
So that tôi có một người chịu trách nhiệm rõ ràng cho công việc đó.

**Acceptance Criteria:**

**Given** task tồn tại và Manager có quyền quản lý task trong phạm vi phòng ban
**When** gán `assigneeID` cho task theo chế độ “Cá nhân”
**Then** hệ thống từ chối nếu nhân sự có trạng thái `Đã nghỉ việc` hoặc không thuộc scope phòng ban cho phép
**And** hệ thống lưu quan hệ gán vào `task_assignee` (hoặc cấu trúc dữ liệu tương đương theo module) và ghi audit “đổi người nhận”
**And** danh sách người nhận có thể truy vết (kèm thời điểm gán) trong lịch sử task

### Story 1.3: Giao task cho phòng ban (snapshot thành viên)

As a Manager,
I want giao task cho cả phòng ban,
So that mọi thành viên hợp lệ trong phòng đều nhận được công việc tại thời điểm giao.

**Acceptance Criteria:**

**Given** task tồn tại và Manager có quyền giao việc trong phòng ban mục tiêu
**When** chọn “Phòng ban” và cung cấp `departmentID` để giao task
**Then** hệ thống resolve danh sách thành viên hợp lệ tại thời điểm giao (loại `Đã nghỉ việc`) và tạo snapshot người nhận
**And** nếu không có thành viên hợp lệ thì hệ thống cảnh báo và từ chối thao tác
**And** hệ thống lưu được chế độ giao việc + snapshot người nhận (phục vụ audit/truy vết)

### Story 1.4: Đính kèm tệp cho task

As a Staff,
I want đính kèm nhiều tệp vào task,
So that tôi có thể bổ sung tài liệu/ket quả làm việc phục vụ xử lý và phê duyệt.

**Acceptance Criteria:**

**Given** người dùng có quyền truy cập task và hệ thống có cấu hình giới hạn tổng dung lượng đính kèm
**When** upload/đính kèm một hoặc nhiều tệp vào task
**Then** hệ thống lưu metadata tệp (tên, kích thước, loại, người upload, thời gian) và liên kết đúng với `task_id`
**And** tổng dung lượng đính kèm vượt giới hạn cấu hình thì hệ thống từ chối với lỗi rõ ràng
**And** ghi audit log cho thao tác thêm/xóa tệp đính kèm

### Story 1.5: Ràng buộc thao tác xóa task (soft delete/lock theo quyền)

As an Admin,
I want kiểm soát việc xóa task theo phân quyền và trạng thái xử lý,
So that dữ liệu và lịch sử nghiệp vụ không bị mất sai quy định.

**Acceptance Criteria:**

**Given** có request xóa task
**When** actor là Staff cố gắng xóa task đã được giao
**Then** hệ thống từ chối với lỗi phân quyền phù hợp
**And** khi actor là Manager và task đã phát sinh xử lý thì hệ thống không cho phép xóa cứng (trừ quyền Admin)
**And** khi thực hiện xóa logic (nếu được phép) thì bắt buộc có xác nhận + ghi audit log before/after (kèm reason nếu có)

---

## Epic 2: Deadline & Cảnh báo quá hạn

Người dùng quản lý mốc thời gian và nhận cảnh báo sắp quá hạn/quá hạn theo cấu hình.

### Story 2.1: Cập nhật deadline task và hiển thị trạng thái sắp quá hạn/quá hạn

As a Manager,
I want cập nhật deadline của task trước khi hoàn thành,
So that tôi có thể điều chỉnh kế hoạch mà vẫn giữ đúng ràng buộc nghiệp vụ.

**Acceptance Criteria:**

**Given** task chưa ở trạng thái `Hoàn thành` và Manager có quyền trong scope
**When** cập nhật `start_time`/`due_time`
**Then** hệ thống validate `start_time <= due_time` và lưu thay đổi
**And** ghi audit log (before/after) cho “đổi deadline”
**And** task sắp quá hạn/quá hạn được đánh dấu rõ ràng (theo mốc cấu hình) trên dữ liệu trả về cho dashboard/list

### Story 2.2: Job thông báo sắp quá hạn và quá hạn (async + retry)

As a System,
I want tự động gửi thông báo sắp quá hạn và quá hạn theo lịch quét,
So that người liên quan nhận cảnh báo đúng mốc và giảm task trễ hạn.

**Acceptance Criteria:**

**Given** hệ thống có cơ chế queue/scheduler hiện hữu
**When** job quét deadline chạy theo lịch
**Then** hệ thống enqueue notification cho các mốc “sắp quá hạn” (ví dụ 24h) và “đã quá hạn” theo cấu hình
**And** áp dụng retry policy 1m -> 5m -> 15m, tối đa 3 lần và ghi nhận số lần retry
**And** tỷ lệ gửi thành công được đo lường/ghi log phục vụ thống kê vận hành

---

## Epic 3: Thực hiện công việc & Cập nhật tiến độ

Staff thực hiện task, cập nhật % tiến độ và nội dung; lưu lịch sử cập nhật; sẵn sàng để submit vào luồng duyệt.

### Story 3.1: Cập nhật tiến độ (0-100) và nội dung thực hiện

As a Staff,
I want cập nhật % tiến độ và nội dung thực hiện của task,
So that Manager có thể theo dõi tình trạng công việc theo thời gian.

**Acceptance Criteria:**

**Given** Staff là người được gán task (cá nhân hoặc thuộc snapshot phòng ban) và có quyền truy cập task
**When** gửi cập nhật tiến độ với `progress_percent` trong [0..100] và `note`
**Then** hệ thống validate dữ liệu và lưu bản ghi log tiến độ (append-only) kèm actor + timestamp
**And** Manager xem được tiến độ mới nhất và lịch sử thay đổi
**And** ghi audit log cho sự kiện cập nhật tiến độ

### Story 3.2: Truy vấn lịch sử cập nhật tiến độ theo phiên bản

As a Manager,
I want xem lịch sử các lần cập nhật tiến độ của task,
So that tôi có thể truy vết ai cập nhật gì và khi nào.

**Acceptance Criteria:**

**Given** Manager có quyền xem task trong scope phòng ban
**When** mở lịch sử tiến độ của task
**Then** hệ thống trả về danh sách phiên bản cập nhật (thời gian, người cập nhật, % tiến độ, nội dung)
**And** dữ liệu trả về có thể phân trang nếu số lượng log lớn

---

## Epic 4: Luồng trạng thái & Phê duyệt kết quả

Tuân thủ state machine, staff submit duyệt, manager duyệt hoặc yêu cầu làm lại; không cho phép chuyển trạng thái sai.

### Story 4.1: Workflow guard cho state machine task

As a System,
I want enforce state machine cho mọi chuyển trạng thái task qua một guard chung,
So that không có trường hợp nhảy trạng thái bất hợp lệ qua API/UI.

**Acceptance Criteria:**

**Given** có request chuyển trạng thái task
**When** thực hiện transition
**Then** hệ thống chỉ cho phép các cạnh hợp lệ: `Mới -> Đang làm -> Chờ duyệt -> Hoàn thành` và `Chờ duyệt -> Làm lại -> Đang làm`
**And** mọi transition đều đi qua một service/guard duy nhất (không hardcode rải rác)
**And** mọi transition đều ghi status log + audit log (actor, timestamp)

### Story 4.2: Staff bắt đầu làm task (NEW -> IN_PROGRESS)

As a Staff,
I want chuyển task sang trạng thái `Đang làm`,
So that hệ thống ghi nhận tôi đã bắt đầu xử lý công việc.

**Acceptance Criteria:**

**Given** task đang ở trạng thái `Mới` và Staff là người được gán
**When** gọi action “start”
**Then** task chuyển sang `Đang làm` nếu hợp lệ theo workflow guard
**And** hệ thống ghi status log + audit log

### Story 4.3: Staff gửi duyệt kết quả (IN_PROGRESS -> PENDING_APPROVAL)

As a Staff,
I want gửi duyệt kết quả của task,
So that Manager có thể kiểm tra và phê duyệt theo quy trình.

**Acceptance Criteria:**

**Given** task đang ở trạng thái `Đang làm`
**When** Staff gọi action “submit” kèm kết quả/ghi chú tổng kết tối thiểu
**Then** hệ thống từ chối nếu thiếu nội dung tổng kết tối thiểu
**And** nếu hợp lệ, task chuyển sang `Chờ duyệt` và ghi status log + audit log

### Story 4.4: Manager phê duyệt task (PENDING_APPROVAL -> DONE)

As a Manager,
I want phê duyệt kết quả task đang `Chờ duyệt`,
So that task được kết thúc đúng quy trình và phản ánh vào báo cáo.

**Acceptance Criteria:**

**Given** task đang ở trạng thái `Chờ duyệt` và Manager có quyền duyệt
**When** gọi action “approve”
**Then** task chuyển sang `Hoàn thành`
**And** hệ thống ghi status log + audit log
**And** task `Hoàn thành` không cho phép sửa nội dung chính (theo rule) ngoài các ghi chú hệ thống nếu có

### Story 4.5: Manager yêu cầu làm lại (PENDING_APPROVAL -> REWORK -> IN_PROGRESS)

As a Manager,
I want yêu cầu Staff làm lại kết quả task,
So that chất lượng đầu ra được đảm bảo trước khi hoàn thành.

**Acceptance Criteria:**

**Given** task đang ở trạng thái `Chờ duyệt`
**When** Manager gọi action “rework” kèm lý do
**Then** task chuyển sang `Làm lại` và ghi status log + audit log
**And** Staff có thể đưa task quay lại `Đang làm` theo workflow guard

---

## Epic 5: Dashboard cá nhân theo vai trò

Người dùng xem “việc cần làm”, ưu tiên cao, sắp quá hạn/quá hạn, hàng chờ duyệt; lọc đúng theo trạng thái/ưu tiên.

### Story 5.1: API dashboard cá nhân (filters theo role/status/priority)

As a User (Manager/Staff),
I want xem dashboard cá nhân tổng hợp task theo vai trò,
So that tôi biết ngay việc nào cần xử lý trước.

**Acceptance Criteria:**

**Given** user đã đăng nhập và có role hợp lệ
**When** gọi API dashboard cá nhân
**Then** hệ thống trả về tối thiểu: việc cần làm ngay, ưu tiên cao, sắp quá hạn/quá hạn
**And** nếu user là Manager thì có thêm khối “đang chờ duyệt”
**And** bộ lọc theo trạng thái/ưu tiên hoạt động đúng và dữ liệu khớp nguồn task

### Story 5.2: Danh sách “My Tasks” và hàng chờ duyệt của Manager

As a Manager,
I want xem danh sách task theo trạng thái và hàng chờ duyệt,
So that tôi có thể duyệt nhanh và theo dõi tiến độ phòng ban.

**Acceptance Criteria:**

**Given** Manager có scope phòng ban
**When** lọc danh sách theo `status`/`priority`/`overdue`/`dueSoon`
**Then** hệ thống trả về danh sách phân trang + sort (nếu có) theo chuẩn API hiện hữu
**And** chỉ hiển thị các task trong phạm vi phòng ban được phép

---

## Epic 6: Báo cáo tiến độ phòng ban

Manager xem báo cáo tổng hợp theo phòng ban và thời gian; hỗ trợ export theo kỳ.

### Story 6.1: API báo cáo tổng hợp theo phòng ban và thời gian

As a Manager,
I want xem báo cáo tiến độ phòng ban theo khoảng thời gian,
So that tôi đánh giá hiệu suất và tình trạng công việc của phòng ban.

**Acceptance Criteria:**

**Given** Manager có quyền xem báo cáo phòng ban trong scope
**When** gọi API report với bộ lọc thời gian (`fromDate`, `toDate`)
**Then** hệ thống trả về các chỉ số tối thiểu: tổng task, tỷ lệ hoàn thành, số quá hạn, số chờ duyệt
**And** số liệu trùng khớp dữ liệu task thực tế

### Story 6.2: Xuất báo cáo theo kỳ (ngày/tuần/tháng)

As a Manager,
I want xuất báo cáo theo kỳ,
So that tôi có thể chia sẻ số liệu và lưu trữ báo cáo định kỳ.

**Acceptance Criteria:**

**Given** Manager đang xem báo cáo theo bộ lọc thời gian
**When** chọn xuất báo cáo theo kỳ (ngày/tuần/tháng)
**Then** hệ thống tạo file export (ví dụ CSV) với dữ liệu đúng phạm vi phòng ban và thời gian
**And** hành động export được ghi log/audit theo chuẩn hệ thống nếu yêu cầu
