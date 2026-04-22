---
stepsCompleted: ["step-01-init"]
inputDocuments: ["docs/BRD Quản lý công việc doanh nghiệp.md"]
workflowType: "prd"
documentCounts:
  briefs: 0
  research: 0
  brainstorming: 0
  projectDocs: 1
projectType: "brownfield"
classification:
  projectType: "web_app"
  domain: "enterprise_operations"
  complexity: "medium"
  projectContext: "brownfield"
---

# Product Requirements Document - Chức năng Quản lý Task (E-Office)

**Author:** USER  
**Date:** 2026-04-22

## Executive Summary

### Tầm nhìn sản phẩm

Phát triển phân hệ Quản lý Công việc (Task Management) trong hệ thống E-Office nhằm số hóa quy trình giao việc, theo dõi tiến độ, phê duyệt kết quả và báo cáo hiệu suất theo phòng ban.

### Vấn đề cần giải quyết

- Công việc đang bị theo dõi rời rạc qua chat/email/file spreadsheet.
- Khó theo dõi trạng thái và deadline theo từng nhân sự, từng phòng ban.
- Thiếu minh bạch trong phê duyệt kết quả và báo cáo tiến độ.

### Mục tiêu kinh doanh

- Chuẩn hóa quy trình giao việc từ Manager đến Staff.
- Tăng tỷ lệ hoàn thành đúng hạn.
- Cung cấp dashboard để quản lý và nhân viên theo dõi công việc theo thời gian thực.

## Product Scope

### In-Scope (Task Management)

- Tạo task với tiêu đề, mô tả, tệp đính kèm, mức độ ưu tiên.
- Giao việc cho 1 cá nhân hoặc toàn bộ 1 phòng ban.
- Đặt thời gian bắt đầu/kết thúc và cảnh báo sắp quá hạn.
- Quản lý luồng trạng thái: Mới -> Đang làm -> Chờ duyệt -> Hoàn thành/Làm lại.
- Staff cập nhật tiến độ, gửi duyệt kết quả.
- Manager phê duyệt hoặc yêu cầu làm lại.
- Dashboard cá nhân và báo cáo tiến độ phòng ban liên quan đến task.

### Out-of-Scope

- Quản lý lương.
- Tuyển dụng.
- Quản lý tài chính kế toán.

## Stakeholders và User Roles

- **Admin:** Quản lý cấu trúc phòng ban, tài khoản, cấu hình.
- **Manager:** Giao việc, theo dõi, phê duyệt task của phòng ban.
- **Staff:** Nhận việc, cập nhật tiến độ, nộp kết quả cho duyệt.

## Success Criteria

### User Success

- Manager tạo và giao task trong <= 1 phút cho 90% trường hợp.
- Staff cập nhật tiến độ và gửi duyệt theo quy trình chuẩn, không bỏ qua trạng thái bắt buộc.
- Dashboard hiển thị đúng task cần xử lý theo vai trò và phòng ban.

### Business Success

- Tỷ lệ task hoàn thành đúng hạn đạt >= 85% sau 3 tháng triển khai.
- Giảm >= 50% trường hợp giao việc qua kênh ngoài hệ thống.
- 100% task có người phụ trách rõ ràng và lịch sử trạng thái đầy đủ.

### Technical Success

- 95% request tìm kiếm/truy vấn task có thời gian đáp ứng <= 2 giây.
- Hệ thống vận hành 24/7.
- Phân quyền RBAC đúng theo vai trò và phạm vi phòng ban.

## User Journeys

### Journey 1: Manager giao task

1. Manager vào danh sách phòng ban/nhân viên.
2. Tạo task, nhập thông tin chi tiết và mức ưu tiên.
3. Chọn người nhận (cá nhân hoặc phòng ban), đặt deadline.
4. Hệ thống tạo task ở trạng thái `Mới` và gửi thông báo.

### Journey 2: Staff thực hiện task

1. Staff nhận task mới trong dashboard cá nhân.
2. Chuyển trạng thái sang `Đang làm`.
3. Cập nhật tiến độ và đính kèm kết quả.
4. Gửi duyệt, task sang `Chờ duyệt`.

### Journey 3: Manager kiểm soát kết quả

1. Manager mở task `Chờ duyệt`.
2. Xem kết quả và lịch sử cập nhật.
3. Chọn `Phê duyệt` (sang `Hoàn thành`) hoặc `Yêu cầu làm lại` (sang `Làm lại`, sau đó quay về `Đang làm`).

### Journey 4: Kiểm soát dữ liệu và truy vết thao tác

1. Manager hoặc Admin mở lịch sử thay đổi của task để kiểm tra toàn bộ luồng xử lý.
2. Hệ thống hiển thị đầy đủ các sự kiện quan trọng: tạo/sửa task, đổi deadline, chuyển trạng thái, gửi duyệt, phê duyệt hoặc yêu cầu làm lại.
3. Staff thử thao tác xóa task đã được giao thì hệ thống từ chối theo phân quyền.
4. Khi Admin thực hiện thao tác xóa logic (nếu được phép), hệ thống bắt buộc xác nhận và ghi audit log trước/sau thay đổi.

## Functional Requirements

### FR-05: Khởi tạo Công việc

- **Mô tả:** Manager (hoặc Admin được ủy quyền) tạo task mới với các trường: tiêu đề, mô tả, ưu tiên, người xử lý, ngày bắt đầu, deadline, tệp đính kèm.
- **Actor chính:** Manager.
- **Tiền điều kiện:** Người tạo đã đăng nhập và có quyền tạo task trong phạm vi phòng ban.
- **Business rules:**
  - Tiêu đề là bắt buộc, tối đa 200 ký tự.
  - Ưu tiên thuộc tập giá trị: `Thấp`, `Vừa`, `Cao`.
  - Task mới được tạo mặc định ở trạng thái `Mới`.
  - Có thể đính kèm nhiều tệp; tổng dung lượng đính kèm theo cấu hình hệ thống.
- **Validation/lỗi nghiệp vụ:**
  - Từ chối lưu nếu thiếu tiêu đề, không có người nhận, hoặc sai định dạng dữ liệu.
  - Trả thông báo lỗi rõ ràng theo từng trường.
- **Acceptance criteria:**
  - Tạo thành công task hợp lệ trong <= 1 phút.
  - Task tạo mới xuất hiện ngay trong danh sách task của người được giao.

### FR-06: Giao việc đa đối tượng

- **Mô tả:** Hệ thống hỗ trợ giao việc cho một cá nhân hoặc một phòng ban.
- **Actor chính:** Manager.
- **Business rules:**
  - Chế độ `Cá nhân`: tạo 1 task gán cho 1 staff.
  - Chế độ `Phòng ban`: tạo task cho toàn bộ thành viên phòng ban tại thời điểm giao.
  - Manager chỉ được giao việc trong phạm vi phòng ban quản lý (trừ quyền cấp cao).
- **Validation/lỗi nghiệp vụ:**
  - Không cho phép giao cho nhân sự trạng thái `Đã nghỉ việc`.
  - Khi giao theo phòng ban mà không có thành viên hợp lệ, hệ thống cảnh báo và từ chối thao tác.
- **Acceptance criteria:**
  - Chế độ giao việc được lưu rõ ràng trong chi tiết task.
  - Danh sách người nhận phải truy vết được trong lịch sử task.

### FR-07: Quản lý Deadline và cảnh báo

- **Mô tả:** Hệ thống quản lý mốc thời gian thực hiện và cảnh báo sắp quá hạn.
- **Business rules:**
  - `start_time` <= `due_time`.
  - Cho phép cập nhật deadline bởi Manager trước khi task `Hoàn thành`.
  - Hệ thống cảnh báo các mốc: sắp quá hạn (VD: 24h) và đã quá hạn.
- **Acceptance criteria:**
  - Task quá hạn được đánh dấu rõ trạng thái quá hạn trên dashboard.
  - Người liên quan nhận được cảnh báo đúng mốc cấu hình.

### FR-08: Quy trình trạng thái task

- **Mô tả:** Áp dụng workflow bắt buộc cho vòng đời task.
- **State machine chuẩn:**
  - `Mới` -> `Đang làm` -> `Chờ duyệt` -> `Hoàn thành`
  - `Chờ duyệt` -> `Làm lại` -> `Đang làm`
- **Business rules:**
  - Không cho phép nhảy trạng thái trái workflow.
  - Chỉ Manager (hoặc người được phân quyền duyệt) mới được phê duyệt hoặc yêu cầu làm lại.
  - Task `Hoàn thành` không được sửa nội dung chính (chỉ cho phép ghi chú hệ thống nếu cần).
- **Acceptance criteria:**
  - Mỗi lần đổi trạng thái đều sinh lịch sử với người thao tác và timestamp.
  - Không có trường hợp chuyển trạng thái bất hợp lệ qua API/UI.

### FR-09: Cập nhật tiến độ thực hiện

- **Mô tả:** Staff cập nhật phần trăm tiến độ và nội dung thực hiện trong quá trình làm task.
- **Business rules:**
  - Tiến độ trong khoảng 0-100.
  - Khi gửi duyệt, hệ thống yêu cầu có kết quả/ghi chú tổng kết tối thiểu.
  - Tiến độ cập nhật được đồng bộ lên dashboard theo thời gian thực.
- **Acceptance criteria:**
  - Hệ thống lưu được lịch sử cập nhật tiến độ theo phiên bản.
  - Manager xem được tiến độ mới nhất và lịch sử thay đổi.

### FR-10: Dashboard cá nhân

- **Mô tả:** Cung cấp tổng quan công việc theo vai trò người dùng.
- **Nội dung tối thiểu:**
  - Việc cần làm ngay.
  - Việc ưu tiên cao.
  - Việc sắp quá hạn và đã quá hạn.
  - Việc đang chờ duyệt (đối với Manager).
- **Acceptance criteria:**
  - Dữ liệu dashboard khớp với danh sách task nguồn.
  - Bộ lọc theo trạng thái/ưu tiên hoạt động đúng.

### FR-11: Báo cáo tiến độ phòng ban

- **Mô tả:** Hiển thị báo cáo tổng hợp task theo phòng ban để quản lý hiệu suất.
- **Chỉ số tối thiểu:**
  - Tổng số task.
  - Tỷ lệ hoàn thành.
  - Số task quá hạn.
  - Số task đang chờ duyệt.
- **Business rules:**
  - Manager chỉ xem được phòng ban mình phụ trách (trừ quyền cấp cao).
  - Số liệu báo cáo tính theo bộ lọc thời gian.
- **Acceptance criteria:**
  - Số liệu biểu đồ trùng khớp với dữ liệu task thực tế.
  - Có thể xuất thống kê theo kỳ (ngày/tuần/tháng).

### FR-12: Lịch sử thay đổi task (Audit Trail)

- **Mô tả:** Ghi nhận đầy đủ các thao tác quan trọng liên quan đến task.
- **Sự kiện bắt buộc log:**
  - Tạo task, sửa task, đổi người nhận, đổi deadline.
  - Chuyển trạng thái, gửi duyệt, phê duyệt, yêu cầu làm lại.
- **Trường log tối thiểu:**
  - `task_id`, hành động, giá trị trước/sau (nếu có), người thao tác, thời gian.
- **Acceptance criteria:**
  - Lịch sử không được sửa/xóa bởi Staff.
  - Truy vết được đầy đủ luồng xử lý của mỗi task.

### FR-13: Ràng buộc thao tác và phân quyền xóa

- **Mô tả:** Áp dụng ràng buộc thao tác để đảm bảo toàn vẹn dữ liệu.
- **Business rules:**
  - Staff không được xóa task đã được giao.
  - Manager được đóng task theo workflow, không được xóa cứng khi task đã phát sinh xử lý (trừ quyền Admin).
  - Xóa task (nếu được phép) phải ghi audit log và có xác nhận.
- **Acceptance criteria:**
  - Mọi request xóa không hợp lệ bị từ chối với mã lỗi phân quyền phù hợp.
  - Không mất lịch sử nghiệp vụ khi task bị khóa/xóa logic.

## Non-Functional Requirements

### Security & Authorization

- RBAC theo vai trò Admin/Manager/Staff.
- Manager chỉ được xem task trong phạm vi phòng ban mình (trừ cấp lãnh đạo được cấp quyền).
- Lưu audit log với các thao tác quan trọng: tạo task, sửa task, đổi trạng thái, phê duyệt.
- 100% request API thao tác task phải được kiểm tra quyền theo vai trò và phạm vi phòng ban trước khi xử lý.

### Performance & Availability

- Tác vụ tìm kiếm/truy vấn <= 2 giây (P95).
- Hệ thống sẵn sàng 24/7.
- Hỗ trợ trình duyệt Chrome, Edge và giao diện responsive cho thiết bị di động.

### Web App Specific Requirements

- **Browser Matrix:**
  - Chrome (2 phiên bản stable gần nhất): hỗ trợ đầy đủ.
  - Edge (2 phiên bản stable gần nhất): hỗ trợ đầy đủ.
  - Safari/Firefox: không thuộc phạm vi hỗ trợ chính thức giai đoạn này.
- **Accessibility Level:**
  - Mục tiêu tối thiểu: WCAG 2.1 mức A cho các màn hình chính (dashboard, danh sách task, chi tiết task).
  - Điều hướng bằng bàn phím phải hoàn thành được các thao tác cốt lõi: lọc task, cập nhật trạng thái, gửi duyệt.
- **SEO Strategy:**
  - N/A cho giai đoạn hiện tại vì đây là hệ thống nội bộ, không phục vụ index công khai.

### Reliability

- Bảo toàn dữ liệu task và lịch sử thao tác khi có lỗi hệ thống với mục tiêu **RPO <= 5 phút** và **RTO <= 30 phút**.
- Cơ chế retry thông báo và cảnh báo quá hạn:
  - Retry tối đa 3 lần cho mỗi thông báo thất bại.
  - Khoảng cách retry: 1 phút, 5 phút, 15 phút.
  - Tỷ lệ gửi thông báo thành công >= 99% theo thống kê ngày.

## Acceptance Criteria

- [ ] Hiển thị đúng sơ đồ tổ chức dạng cây (để giao việc theo phòng ban chính xác).
- [ ] Nhân viên không thể tự xóa task đã được giao.
- [ ] Dashboard và báo cáo phản ánh đúng số liệu task thực tế.
- [ ] Luồng trạng thái task bắt buộc tuân thủ quy trình đã định nghĩa.

## Open Questions

- Có cần chia nhỏ task thành sub-task hay không?
- Có cần SLA riêng theo loại task/phòng ban không?
- Có cần thông báo qua email/Zalo/Teams ngoài hệ thống không?

