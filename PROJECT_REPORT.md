# BÁO CÁO CHI TIẾT DỰ ÁN: HỆ THỐNG QUẢN LÝ DOANH NGHIỆP (KANBAN, HR & DEPARTMENT)

## 1. Tổng quan dự án
Hệ thống cung cấp giải pháp quản trị doanh nghiệp tích hợp, tập trung vào 3 trụ cột: Quản lý công việc (Kanban), Quản lý nhân sự (HR), và Quản lý cơ cấu phòng ban. Hệ thống áp dụng cơ chế cách ly dữ liệu nghiêm ngặt giữa các phòng ban và phân quyền theo site.

## 2. Công nghệ sử dụng
- **Frontend**: React.js, SortableJS, Tailwind CSS.
- **Backend**: PHP MVC Framework, MySQL.
- **Phân quyền**: RBAC kết hợp Department Isolation.

---

## 3. Quản lý Công việc (Task Kanban) - Controller: `TaskCtrl`

### 3.1. Truy vấn danh sách Task (Read)
```mermaid
sequenceDiagram
    participant User as Người dùng
    participant UI as Kanban UI (React)
    participant Ctrl as TaskCtrl
    participant Mapper as TaskMapper
    participant DB as MySQL

    User->>UI: Truy cập Kanban
    UI->>Ctrl: GET /rest/task/tasks
    Ctrl->>Mapper: getTasks(siteID, actorID, privs, filters)
    Mapper->>Mapper: Lấy depFK của người dùng (Self-Discovery)
    Mapper->>DB: SELECT * FROM task WHERE siteFK=? AND depFK=?
    DB-->>Mapper: Danh sách Task
    Mapper-->>Ctrl: Array Data
    Ctrl-->>UI: JSON Response
    UI->>User: Hiển thị thẻ Task trên các cột
```

### 3.2. Tạo mới/Cập nhật Task (Create/Update)
```mermaid
sequenceDiagram
    participant User as Người dùng
    participant UI as TaskModal
    participant Ctrl as TaskCtrl
    participant Mapper as TaskMapper
    participant DB as MySQL

    User->>UI: Nhập Title, Progress, Assignees... & Lưu
    UI->>Ctrl: POST/PATCH /rest/task/tasks (data)
    Ctrl->>Mapper: createTask/updateTask
    Mapper->>Mapper: Gán depFK/Kiểm tra quyền sở hữu
    Mapper->>DB: INSERT/UPDATE task SET attrs=JSON
    Mapper->>DB: INSERT INTO task_audit_log (Ghi vết thay đổi)
    Mapper-->>Ctrl: Success
    Ctrl-->>UI: JSON OK
    UI->>User: Thông báo thành công & Refresh Board
```

---

## 4. Quản lý Nhân sự (Employee) - Controller: `EmployeeCtrl`

### 4.1. Truy vấn danh sách Nhân viên (Read)
```mermaid
sequenceDiagram
    participant User as Manager (manageUser)
    participant UI as EmployeeList (React)
    participant Ctrl as EmployeeCtrl
    participant Mapper as EmployeeMapper
    participant DB as MySQL

    User->>UI: Mở trang Nhân sự
    UI->>Ctrl: GET /rest/nhansu (filter: siteFK, depFK)
    Ctrl->>Mapper: getEmployees()
    Mapper->>DB: SELECT * FROM employee WHERE deleted=0
    DB-->>Mapper: Dataset
    Mapper-->>Ctrl: Array
    Ctrl-->>UI: JSON Response
    UI->>User: Hiển thị bảng danh sách nhân viên
```

### 4.2. Thêm mới/Cập nhật Nhân viên (Create/Update)
```mermaid
sequenceDiagram
    participant User as Admin
    participant UI as EmployeeEdit Modal
    participant Ctrl as EmployeeCtrl
    participant Mapper as EmployeeMapper
    participant DB as MySQL

    User->>UI: Nhập Fullname, Email, Role, Dept... & Save
    UI->>Ctrl: POST/PUT /rest/nhansu (data)
    Ctrl->>Ctrl: Auth::requirePrivilege('manageUser')
    Ctrl->>Mapper: updateEmployee(id, data)
    Mapper->>DB: INSERT/UPDATE employee
    Mapper->>DB: INSERT/UPDATE user_user (Đồng bộ tài khoản)
    Mapper->>DB: INSERT INTO user_role_user (Gán vai trò)
    DB-->>Mapper: Success
    Mapper-->>Ctrl: result(true)
    Ctrl-->>UI: JSON Result
    UI->>User: Thông báo "Lưu thành công"
```

### 4.3. Xóa Nhân viên (Delete)
```mermaid
sequenceDiagram
    participant User as Admin
    participant UI as EmployeeList
    participant Ctrl as EmployeeCtrl
    participant Mapper as EmployeeMapper

    User->>UI: Nhấn nút Xóa (Delete)
    UI->>Ctrl: DELETE /rest/nhansu/:id
    Ctrl->>Mapper: deleteEmployee(siteID, id)
    Mapper->>DB: UPDATE employee SET deleted=1
    Mapper->>DB: UPDATE user_user SET deleted=1
    Mapper-->>Ctrl: result(true)
    Ctrl-->>UI: JSON OK
    UI->>User: Xóa dòng trên giao diện
```

---

## 5. Quản lý Phòng ban (Department) - Controller: `DepartmentCtrl`

### 5.1. Xem danh sách Phòng ban (Read)
```mermaid
sequenceDiagram
    participant UI as DepartmentList (React)
    participant Ctrl as DepartmentCtrl
    participant Mapper as DepartmentMapper

    UI->>Ctrl: GET /rest/phongban
    Ctrl->>Mapper: getDepartments()
    Mapper->>DB: SELECT * FROM department WHERE siteFK=?
    DB-->>Mapper: Tree Data
    Mapper-->>Ctrl: Array
    Ctrl-->>UI: JSON Result
    UI->>UI: Render TreeView/Table
```

### 5.2. Thêm mới/Sửa Phòng ban (Create/Update)
Bao gồm logic quan trọng nhất: Kiểm tra trùng mã theo Site.

```mermaid
sequenceDiagram
    participant User as Admin (manageDepartment)
    participant UI as DeptModal
    participant Ctrl as DepartmentCtrl
    participant Mapper as DepartmentMapper
    participant DB as MySQL

    User->>UI: Nhập mã (Code) & Tên phòng ban
    UI->>Ctrl: POST /rest/phongban
    Ctrl->>Mapper: updateDepartment(id, data)
    Mapper->>DB: SELECT id FROM department WHERE code=? AND siteFK=?
    Note over DB,Mapper: Chỉ check trùng mã trong cùng 1 site
    DB-->>Mapper: (Result)
    alt Mã bị trùng trong Site
        Mapper-->>Ctrl: Throw BadRequestException
        Ctrl-->>UI: JSON Error "Mã phòng ban đã tồn tại"
        UI->>User: Hiển thị Alert đỏ
    else Mã hợp lệ
        Mapper->>DB: INSERT/UPDATE department
        Mapper-->>Ctrl: result(true)
        Ctrl-->>UI: JSON OK
        UI->>User: Thông báo thành công
    end
```

### 5.3. Xóa Phòng ban (Delete)
```mermaid
sequenceDiagram
    User->>UI: Nhấn Xóa phòng ban
    UI->>Ctrl: DELETE /rest/phongban/:id
    Ctrl->>Mapper: deleteDepartment(siteID, id)
    Mapper->>DB: UPDATE department SET deleted=1
    Mapper-->>Ctrl: result(true)
    Ctrl-->>UI: JSON OK
```

---

## 6. Tổng kết Giải pháp Kỹ thuật
1. **Giao diện Kanban chuyên nghiệp**: Sử dụng SortableJS kết hợp React, hỗ trợ kéo thả mượt mà với cơ chế hoàn tác DOM (DOM Revert) để tránh lỗi render của React.
2. **Hệ thống phân quyền 2 lớp**: Kiểm tra quyền tại Controller (Chặn truy cập trái phép) và lọc dữ liệu tại Mapper (Cách ly dữ liệu phòng ban).
3. **Mở rộng dữ liệu linh hoạt (JSON Attributes)**: Sử dụng cột `attrs` để lưu trữ các thông tin động như Tiến độ (%) mà không làm phình cấu trúc Database.
4. **Bộ chọn Nhân sự thông minh (Jira Style)**: Tìm kiếm thời gian thực, cho phép chọn nhiều người thực hiện (Multi-assignees) và tự động lọc chỉ hiện người cùng phòng ban.

---
*Báo cáo được lập bởi: Gemini CLI System*
*Ngày: 24/04/2026*
