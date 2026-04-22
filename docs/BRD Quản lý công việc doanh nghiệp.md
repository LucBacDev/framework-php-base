# **TÀI LIỆU YÊU CẦU KINH DOANH (BRD)**

**Dự án:** Hệ thống Quản trị Doanh nghiệp Nội bộ (E-Office)

**Phiên bản:** 1.0

**Trạng thái:** Draft

## **1\. THÔNG TIN CHUNG**

### **1.1 Mục đích**

Xây dựng hệ thống quản lý tập trung nhằm số hóa cấu trúc tổ chức, quản lý nhân sự và theo dõi luồng công việc. Hệ thống giúp tối ưu hóa việc phối hợp giữa các phòng ban và nâng cao tính minh bạch trong đánh giá hiệu suất.

### **1.2 Phạm vi dự án**

* **Bao gồm:** Quản lý sơ đồ tổ chức, thông tin nhân viên, giao việc, theo dõi tiến độ, phê duyệt kết quả và báo cáo thống kê.  
* **Không bao gồm:** Quản lý lương, Tuyển dụng, Quản lý tài chính kế toán.

## **2\. CÁC ĐỐI TƯỢNG LIÊN QUAN (STAKEHOLDERS)**

| Vai trò | Trách nhiệm chính |
| :---- | :---- |
| **Admin (Quản trị hệ thống)** | Thiết lập cấu trúc phòng ban, quản lý tài khoản, cấu hình hệ thống. |
| **Manager (Quản lý/Trưởng phòng)** | Giao việc cho nhân viên cấp dưới, phê duyệt báo cáo, theo dõi Dashboard phòng ban. |
| **Staff (Nhân viên)** | Tiếp nhận công việc, cập nhật tiến độ thực hiện, gửi báo cáo hoàn thành. |

## **3\. YÊU CẦU CHỨC NĂNG (FUNCTIONAL REQUIREMENTS)**

### **3.1 Phân hệ Quản lý Tổ chức & Nhân sự**

| ID | Yêu cầu chức năng | Mô tả chi tiết |
| :---- | :---- | :---- |
| **FR-01** | Quản lý Phòng ban | Cho phép Thêm/Sửa/Xóa phòng ban. Hỗ trợ mô hình phân cấp (Parent-Child). |
| **FR-02** | Định nghĩa Trưởng bộ phận | Mỗi phòng ban/tổ nhóm phải được gán ít nhất 01 người quản lý trực tiếp. |
| **FR-03** | Hồ sơ Nhân viên | Lưu trữ: Họ tên, Mã nhân viên, Email, Số điện thoại, Chức vụ, Phòng ban trực thuộc. |
| **FR-04** | Trạng thái nhân sự | Quản lý trạng thái: Thử việc, Chính thức, Nghỉ phép, Đã nghỉ việc. |

### **3.2 Phân hệ Quản lý Công việc (Task Management)**

| ID | Yêu cầu chức năng | Mô tả chi tiết |
| :---- | :---- | :---- |
| **FR-05** | Khởi tạo Công việc | Tạo Task với tiêu đề, mô tả, tệp đính kèm, mức độ ưu tiên (Thấp/Vừa/Cao/Khẩn). |
| **FR-06** | Giao việc đa đối tượng | Có thể giao cho 01 cá nhân xử lý hoặc giao cho toàn bộ 01 phòng ban. |
| **FR-07** | Quản lý Deadline | Thiết lập thời gian bắt đầu và kết thúc. Hệ thống tự động cảnh báo khi sắp quá hạn. |
| **FR-08** | Quy trình trạng thái | Luồng chuyển đổi: **Mới \-\> Đang làm \-\> Chờ duyệt \-\> Hoàn thành/Làm lại**. |

### **3.3 Phân hệ Báo cáo & Dashboard**

| ID | Yêu cầu chức năng | Mô tả chi tiết |
| :---- | :---- | :---- |
| **FR-10** | Dashboard cá nhân | Hiển thị danh sách việc cần làm ngay, việc quan trọng trong ngày. |
| **FR-11** | Báo cáo tiến độ phòng ban | Biểu đồ trực quan về tỷ lệ hoàn thành công việc của toàn bộ nhân viên trong phòng. |

---

## **4\. YÊU CẦU PHI CHỨC NĂNG (NON-FUNCTIONAL REQUIREMENTS)**

### **4.1 Bảo mật & Phân quyền**

* **Phân quyền (RBAC):** Chỉ những người có thẩm quyền mới được xem dữ liệu nhân sự nhạy cảm.  
* **Cô lập dữ liệu:** Manager phòng ban nào chỉ được xem công việc của phòng ban đó (trừ quyền lãnh đạo cấp cao).

### **4.2 Khả dụng & Hiệu năng**

* **Tính sẵn sàng:** Hệ thống hoạt động 24/7.  
* **Thời gian phản hồi:** Các tác vụ tìm kiếm, truy vấn dữ liệu không quá 2 giây.  
* **Tương thích:** Hoạt động tốt trên các trình duyệt hiện đại (Chrome, Edge) và hỗ trợ hiển thị trên thiết bị di động.

---

## **5\. QUY TRÌNH NGHIỆP VỤ TIÊU BIỂU (BUSINESS WORKFLOW)**

1. **Giao việc:** Manager truy cập phòng ban \-\> Chọn danh sách nhân viên \-\> Tạo Task \-\> Gán Deadline.  
2. **Thực hiện:** Staff nhận thông báo qua trạng thái Task “Mới”-\> Chuyển trạng thái "Đang làm" \-\> Cập nhật % tiến độ.  
3. **Hoàn thành:** Staff đính kèm kết quả \-\> Bấm "Gửi duyệt" \-\> Trạng thái Task chuyển sang "Chờ duyệt".  
4. **Kiểm soát:** Manager kiểm tra kết quả \-\> Bấm "Phê duyệt" (Task đóng) hoặc "Yêu cầu làm lại" (Task quay lại trạng thái Đang làm).

---

## **6\. TIÊU CHÍ NGHIỆM THU (ACCEPTANCE CRITERIA)**

* \[ \] Hiển thị đúng sơ đồ tổ chức theo dạng cây.  
* \[ \] Nhân viên không thể tự xóa Task đã được giao.  
* \[ \] Báo cáo Dashboard phản ánh đúng số liệu thực tế từ các Task đang mở.


