# Hệ thống Quản trị Doanh nghiệp Nội bộ (E-Office)
## Tài liệu Thiết kế: HLD · LLD · API · Database

> Dựa trên BRD v1.0 – Phạm vi: Quản lý Tổ chức & Nhân sự, Quản lý Công việc, Báo cáo & Dashboard.
> Không bao gồm: Lương, Tuyển dụng, Kế toán.

---

## Mục lục
1. [High-Level System Design (HLD)](#1-high-level-system-design-hld)
2. [Low-Level Design (LLD)](#2-low-level-design-lld)
3. [Thiết kế API & Database theo từng Task](#3-thiết-kế-api--database-theo-từng-task)
   - 3.1. Auth & RBAC
   - 3.2. FR-01/02 Quản lý Phòng ban (phân cấp, trưởng bộ phận)
   - 3.3. FR-03/04 Hồ sơ Nhân viên & Trạng thái
   - 3.4. FR-05 Khởi tạo Công việc
   - 3.5. FR-06 Giao việc đa đối tượng
   - 3.6. FR-07 Quản lý Deadline & cảnh báo
   - 3.7. FR-08 Quy trình trạng thái Task (state machine)
   - 3.8. FR-10 Dashboard cá nhân
   - 3.9. FR-11 Báo cáo tiến độ phòng ban
4. [Database Schema tổng hợp (ERD)](#4-database-schema-tổng-hợp-erd)
5. [Non-functional: Bảo mật, Hiệu năng, Khả dụng](#5-non-functional)

---

## 1. High-Level System Design (HLD)

### 1.1. Bối cảnh
- Người dùng: ~3 vai trò (Admin / Manager / Staff), truy cập qua Web (Chrome/Edge) và Mobile web.
- Tải dự kiến: doanh nghiệp vừa (500 – 5.000 nhân viên), peak ~ vài trăm request/giây cho tác vụ đọc dashboard.
- SLA: 24/7, p95 < 2s cho truy vấn/tìm kiếm.

### 1.2. Sơ đồ kiến trúc tổng thể

```
                 ┌────────────────────────────────────┐
                 │              Clients               │
                 │   I                                │
                 └───────────────┬────────────────────┘
                                 │ HTTP / JSON
                                 ▼
                      ┌──────────────────────┐
                      │        API           │
                      │ (Controller Layer)   │
                      └──────────┬───────────┘
                                 │
                                 ▼
                      ┌──────────────────────┐
                      │ Business Logic       │
                      │ (Service Layer)      │
                      └──────────┬───────────┘
                                 │
                ┌────────────────┴────────────────┐
                ▼                                 ▼
     ┌────────────────────┐           ┌────────────────────┐
     │        DB          │           │ Local Storage      │
     │ (MySQL )           │           │ (files, uploads,   │
     │                    │           │  images, docs)     │
     └────────────────────┘           └────────────────────┘

### 1.4. Module logic (bounded contexts)
1. **Identity & Access** – user, role, permission, session.
2. **Organization** – department (cây), position, assignment (manager ↔ department).
3. **HR Profile** – employee profile, employment status.
4. **Task Management** – task, assignee, state machine, attachment, comment, history.
5. **Reporting** – dashboard cá nhân, báo cáo phòng ban.

---

## 2. Low-Level Design (LLD)

### 2.1. Nguyên tắc chung
- **Layered**: Controller → Service → Repository → DB.
- **DTO validation** ở Controller (FormRequest).
- **Domain event** khi Task đổi trạng thái → emit sang queue (cho notify, audit, rebuild dashboard).
- **RBAC middleware** kiểm tra `permission` + **Row-level scope** theo `department_id` (Manager chỉ thấy phòng mình + phòng con).
- **Soft delete** cho Task, Employee, Department (`deleted_at`).
- **Audit log** bảng `audit_logs` ghi mọi thay đổi quan trọng (who, when, entity, before/after).

### 2.2. State machine Task (FR-08)

```
          create
   ┌────────────────┐
   ▼                │
 NEW ──start──► IN_PROGRESS ──submit──► PENDING_REVIEW ──approve──► DONE
                      ▲                         │
                      └──────── rework ─────────┘
```

- Điều kiện chuyển:
  - `NEW → IN_PROGRESS`: assignee là người hiện tại.
  - `IN_PROGRESS → PENDING_REVIEW`: yêu cầu ít nhất 1 attachment kết quả hoặc mô tả hoàn thành.
  - `PENDING_REVIEW → DONE`: phải là manager của assignee hoặc người giao việc.
  - `PENDING_REVIEW → IN_PROGRESS` (rework): kèm `rework_reason`.
- Lưu ở bảng `task_status_history`.

### 2.3. Phân quyền (RBAC) – ma trận rút gọn

| Hành động | Admin | Manager (phòng X) | Staff |
|---|:-:|:-:|:-:|
| CRUD phòng ban | ✓ | – | – |
| CRUD nhân viên (toàn hệ thống) | ✓ | – | – |
| Xem nhân viên phòng mình + phòng con | ✓ | ✓ | chỉ bản thân |
| Tạo Task, giao cho NV phòng mình/phòng con | ✓ | ✓ | – |
| Tự tạo Task cho bản thân | ✓ | ✓ | ✓ |
| Cập nhật tiến độ Task được giao | ✓ | ✓ | ✓ (task của mình) |
| Phê duyệt / yêu cầu làm lại | ✓ | ✓ (task do mình giao) | – |
| Xóa Task | ✓ | ✓ (task do mình tạo, chưa DONE) | **Không** (AC) |
| Dashboard phòng ban | ✓ | ✓ (phòng mình) | – |

### 2.4. Chiến lược cây phòng ban
- Dùng cả `parent_id` (adjacency list) + cột **`path` (ltree)** hoặc cột `materialized_path` (vd: `/1/4/12/`) để:
  - Truy vấn con/cháu nhanh (`path <@ '/1/4'`).
  - Render cây bằng 1 query.
- Khi move phòng: cập nhật `path` cho toàn bộ con cháu trong 1 transaction.

### 2.5. Dashboard & cache
- Dashboard cá nhân: query trực tiếp (index theo `assignee_id`, `status`, `due_date`).
- Dashboard phòng ban: precompute 1 phút/lần vào Redis key `dept:{id}:metrics` (worker cron). Giảm tải DB khi nhiều manager refresh.

---

## 3. Thiết kế API & Database theo từng Task

> **Quy ước chung**
> - Base URL: `/api/v1`
> - Auth: `Authorization: Bearer <JWT>`
> - Content-Type: `application/json`
> - Response bao bọc:
> ```json
> { "success": true, "data": { ... }, "meta": { "page": 1, "total": 100 } }
> ```
> - Lỗi:
> ```json
> { "success": false, "error": { "code": "TASK_INVALID_TRANSITION", "message": "..." } }
> ```

---

### 3.1. Auth & RBAC (nền tảng cho mọi FR)

**Vấn đề.** BRD yêu cầu RBAC và cô lập dữ liệu theo phòng ban (NFR 4.1). Không có bước này, mọi FR còn lại đều không an toàn.

**Giải pháp.**
- JWT access token (15 phút) + refresh token (7 ngày, lưu Redis, rotate khi dùng).
- Mỗi user có 1..n `roles`, mỗi role có n `permissions` (string key: `task.create`, `dept.manage`, …).
- Middleware `authorize(permission)` + scope `department_id` injected vào query.

**API.**

| Method | Path | Mô tả |
|---|---|---|
| POST | `/auth/login` | Đăng nhập |
| POST | `/auth/refresh` | Làm mới token |
| POST | `/auth/logout` | Thu hồi refresh token |
| GET  | `/auth/me` | Thông tin user hiện tại + permissions |

**Request `POST /auth/login`**
```json
{ "email": "manager@corp.vn", "password": "••••••" }
```
**Response**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJhbGciOi...",
    "refresh_token": "b2f1...",
    "expires_in": 900,
    "user": {
      "id": 12,
      "full_name": "Nguyễn Văn A",
      "email": "manager@corp.vn",
      "roles": ["MANAGER"],
      "permissions": ["task.create","task.approve","dept.view"],
      "department_ids_scope": [4, 12, 15]
    }
  }
}
```

**DB cần có.**
```sql
users (
  id BIGSERIAL PK,
  employee_id BIGINT FK employees(id),
  email CITEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  is_active BOOL DEFAULT TRUE,
  last_login_at TIMESTAMPTZ,
  created_at, updated_at
);

roles (
  id SERIAL PK,
  code VARCHAR(50) UNIQUE,  -- ADMIN, MANAGER, STAFF
  name VARCHAR(100)
);

permissions (
  id SERIAL PK,
  code VARCHAR(100) UNIQUE  -- task.create, task.approve...
);

role_permissions (role_id, permission_id, PK(role_id, permission_id));
user_roles       (user_id, role_id, PK(user_id, role_id));

refresh_tokens (
  id BIGSERIAL PK,
  user_id BIGINT FK,
  token_hash TEXT,
  expires_at TIMESTAMPTZ,
  revoked_at TIMESTAMPTZ
);
```

---

### 3.2. FR-01 & FR-02 – Quản lý Phòng ban & Trưởng bộ phận

**Vấn đề.**
- Doanh nghiệp có cơ cấu nhiều tầng (công ty → khối → phòng → tổ). Cần thêm/sửa/xóa và hiển thị dạng cây.
- Mỗi phòng ban phải có ≥ 1 manager trực tiếp (FR-02) để Task có người phê duyệt.

**Giải pháp.**
- Lưu cây bằng `parent_id` + `path` (ltree/materialized path).
- Bảng `department_managers` (n-n) cho phép nhiều manager, trong đó 1 người `is_primary = true`.
- Validate không cho xóa phòng ban còn nhân viên hoặc còn phòng con.

**API.**

| Method | Path | Mô tả | Role |
|---|---|---|---|
| GET  | `/departments/tree` | Lấy toàn cây | Admin, Manager |
| GET  | `/departments/{id}` | Chi tiết | Admin, Manager |
| POST | `/departments` | Tạo mới | Admin |
| PATCH| `/departments/{id}` | Sửa | Admin |
| DELETE | `/departments/{id}` | Xóa | Admin |
| POST | `/departments/{id}/managers` | Gán manager | Admin |
| DELETE | `/departments/{id}/managers/{userId}` | Gỡ manager | Admin |

**Request `POST /departments`**
```json
{
  "code": "ENG-BE",
  "name": "Phòng Backend",
  "parent_id": 4,
  "primary_manager_user_id": 22
}
```
**Response**
```json
{
  "success": true,
  "data": {
    "id": 27,
    "code": "ENG-BE",
    "name": "Phòng Backend",
    "parent_id": 4,
    "path": "/1/4/27",
    "managers": [{ "user_id": 22, "is_primary": true }],
    "created_at": "2026-04-21T06:00:00Z"
  }
}
```

**Response `GET /departments/tree`**
```json
{
  "success": true,
  "data": [
    {
      "id": 1, "name": "Công ty ABC", "parent_id": null,
      "children": [
        { "id": 4, "name": "Khối Công nghệ",
          "children": [
            { "id": 27, "name": "Phòng Backend", "children": [] }
          ]
        }
      ]
    }
  ]
}
```

**DB cần có.**
```sql
departments (
  id BIGSERIAL PK,
  code VARCHAR(50) UNIQUE,
  name VARCHAR(255) NOT NULL,
  parent_id BIGINT NULL FK departments(id),
  path LTREE NOT NULL,           -- hoặc VARCHAR materialized path '/1/4/27/'
  is_active BOOL DEFAULT TRUE,
  created_at, updated_at,
  deleted_at TIMESTAMPTZ NULL
);
CREATE INDEX idx_dept_path_gist ON departments USING GIST (path);
CREATE INDEX idx_dept_parent ON departments(parent_id);

department_managers (
  department_id BIGINT FK departments(id),
  user_id       BIGINT FK users(id),
  is_primary    BOOL DEFAULT FALSE,
  assigned_at   TIMESTAMPTZ DEFAULT now(),
  PRIMARY KEY (department_id, user_id)
);
-- Unique partial: chỉ 1 primary / dept
CREATE UNIQUE INDEX uq_dept_primary_manager
  ON department_managers(department_id) WHERE is_primary;
```

---

### 3.3. FR-03 & FR-04 – Hồ sơ & Trạng thái Nhân viên

**Vấn đề.** Cần lưu thông tin nhân viên (FR-03) và quản lý vòng đời trạng thái (Thử việc → Chính thức → Nghỉ phép → Nghỉ việc) để Task chỉ được giao cho người còn đang làm việc.

**Giải pháp.**
- Tách `employees` (HR profile) khỏi `users` (credential).
- Enum trạng thái `employment_status`. Khi set `RESIGNED`, tự động vô hiệu user + không cho assign Task mới.

**API.**

| Method | Path | Mô tả |
|---|---|---|
| GET   | `/employees?department_id=&status=&q=` | Danh sách (scope RBAC) |
| GET   | `/employees/{id}` | Chi tiết |
| POST  | `/employees` | Thêm (Admin) |
| PATCH | `/employees/{id}` | Sửa |
| PATCH | `/employees/{id}/status` | Đổi trạng thái |
| DELETE| `/employees/{id}` | Soft delete |

**Request `POST /employees`**
```json
{
  "employee_code": "NV0123",
  "full_name": "Trần Thị B",
  "email": "b.tran@corp.vn",
  "phone": "0901234567",
  "position": "Backend Engineer",
  "department_id": 27,
  "status": "PROBATION",
  "create_user_account": true
}
```
**Response**
```json
{
  "success": true,
  "data": {
    "id": 301,
    "employee_code": "NV0123",
    "full_name": "Trần Thị B",
    "email": "b.tran@corp.vn",
    "department": { "id": 27, "name": "Phòng Backend" },
    "position": "Backend Engineer",
    "status": "PROBATION",
    "user_id": 412
  }
}
```

**Request `PATCH /employees/{id}/status`**
```json
{ "status": "RESIGNED", "effective_date": "2026-05-01", "note": "Nghỉ tự nguyện" }
```

**DB cần có.**
```sql
CREATE TYPE employment_status AS ENUM
  ('PROBATION','OFFICIAL','ON_LEAVE','RESIGNED');

employees (
  id BIGSERIAL PK,
  employee_code VARCHAR(30) UNIQUE NOT NULL,
  full_name VARCHAR(255) NOT NULL,
  email CITEXT UNIQUE NOT NULL,
  phone VARCHAR(30),
  position VARCHAR(100),
  department_id BIGINT FK departments(id),
  status employment_status NOT NULL DEFAULT 'PROBATION',
  joined_at DATE,
  resigned_at DATE,
  created_at, updated_at,
  deleted_at TIMESTAMPTZ
);
CREATE INDEX idx_emp_dept ON employees(department_id);
CREATE INDEX idx_emp_status ON employees(status);

employee_status_history (
  id BIGSERIAL PK,
  employee_id BIGINT FK employees(id),
  from_status employment_status,
  to_status   employment_status NOT NULL,
  effective_date DATE NOT NULL,
  note TEXT,
  changed_by BIGINT FK users(id),
  changed_at TIMESTAMPTZ DEFAULT now()
);
```

---

### 3.4. FR-05 – Khởi tạo Công việc

**Vấn đề.** Manager cần tạo Task với tiêu đề, mô tả, **tệp đính kèm**, **mức độ ưu tiên** (Thấp/Vừa/Cao/Khẩn).

**Giải pháp.**
- 1 endpoint tạo Task, file upload riêng (presigned URL hoặc multipart) rồi attach bằng `attachment_ids`.
- Enum `priority`. Default `MEDIUM`.

**API.**

| Method | Path | Mô tả |
|---|---|---|
| POST  | `/attachments/presign` | Lấy URL upload S3 |
| POST  | `/tasks` | Tạo Task |
| GET   | `/tasks/{id}` | Chi tiết Task |
| PATCH | `/tasks/{id}` | Sửa thông tin cơ bản |

**Request `POST /tasks`**
```json
{
  "title": "Viết tài liệu API module Task",
  "description": "Cần chuẩn OpenAPI 3.1, deadline trước release v1.",
  "priority": "HIGH",
  "start_at": "2026-04-22T00:00:00Z",
  "due_at":   "2026-04-30T17:00:00Z",
  "assignee": { "type": "USER", "user_id": 301 },
  "attachment_ids": ["att_01H...", "att_01H..."]
}
```
**Response**
```json
{
  "success": true,
  "data": {
    "id": "tsk_01HXYZ...",
    "title": "Viết tài liệu API module Task",
    "status": "NEW",
    "priority": "HIGH",
    "progress_percent": 0,
    "created_by": { "id": 22, "name": "Mgr A" },
    "assignees": [{ "type": "USER", "user_id": 301, "name": "Trần Thị B" }],
    "department_id": 27,
    "start_at": "2026-04-22T00:00:00Z",
    "due_at":   "2026-04-30T17:00:00Z",
    "created_at": "2026-04-21T06:10:00Z"
  }
}
```

**DB cần có** (xem thêm bảng `task_assignees`, `attachments` ở mục 3.5 & ERD).
```sql
CREATE TYPE task_priority AS ENUM ('LOW','MEDIUM','HIGH','URGENT');
CREATE TYPE task_status   AS ENUM
  ('NEW','IN_PROGRESS','PENDING_REVIEW','DONE','REWORK');

tasks (
  id UUID PK DEFAULT gen_random_uuid(),
  title VARCHAR(255) NOT NULL,
  description TEXT,
  priority task_priority NOT NULL DEFAULT 'MEDIUM',
  status   task_status   NOT NULL DEFAULT 'NEW',
  progress_percent SMALLINT NOT NULL DEFAULT 0
    CHECK (progress_percent BETWEEN 0 AND 100),
  start_at TIMESTAMPTZ,
  due_at   TIMESTAMPTZ,
  department_id BIGINT FK departments(id),  -- phòng ban chủ quản (để scope RBAC)
  created_by BIGINT FK users(id),
  closed_at TIMESTAMPTZ,
  created_at, updated_at,
  deleted_at TIMESTAMPTZ
);
CREATE INDEX idx_tasks_dept_status ON tasks(department_id, status);
CREATE INDEX idx_tasks_due ON tasks(due_at) WHERE status NOT IN ('DONE');

attachments (
  id UUID PK,
  owner_type VARCHAR(30),   -- 'TASK','TASK_RESULT','COMMENT'
  owner_id   UUID,
  file_name  VARCHAR(255),
  content_type VARCHAR(100),
  size_bytes BIGINT,
  storage_key TEXT NOT NULL,  -- S3 key
  uploaded_by BIGINT FK users(id),
  created_at TIMESTAMPTZ
);
CREATE INDEX idx_att_owner ON attachments(owner_type, owner_id);
```

---

### 3.5. FR-06 – Giao việc đa đối tượng (cá nhân / phòng ban)

**Vấn đề.** 1 Task có thể giao cho **1 cá nhân** hoặc cho **toàn bộ 1 phòng ban**. Cần biết chính xác ai "sở hữu" để cập nhật tiến độ và phê duyệt, đồng thời tổng hợp được tiến độ theo phòng.

**Giải pháp.**
- Bảng `task_assignees` với `assignee_type ∈ {USER, DEPARTMENT}`.
- Nếu giao cho DEPARTMENT → khi có member nhận, sinh thêm bản ghi sub-task hoặc cập nhật `claimed_by_user_id` (tùy chính sách). Phương án khuyến nghị: giữ 1 Task gốc + mỗi member có 1 bản `task_member_progress` để theo dõi cá nhân, nhưng trạng thái tổng hợp của Task do manager quyết định.
- Validate assignee thuộc scope phòng ban của người giao.

**API.**

| Method | Path | Mô tả |
|---|---|---|
| POST | `/tasks/{id}/assignees` | Thêm/gán lại |
| DELETE | `/tasks/{id}/assignees/{assigneeId}` | Gỡ |
| POST | `/tasks/{id}/claim` | Staff trong phòng nhận Task dạng DEPARTMENT |

**Request `POST /tasks/{id}/assignees`**
```json
{ "assignees": [
  { "type": "DEPARTMENT", "department_id": 27 }
]}
```

**Response**
```json
{
  "success": true,
  "data": {
    "task_id": "tsk_01HXYZ...",
    "assignees": [
      { "type": "DEPARTMENT", "department_id": 27, "name": "Phòng Backend" }
    ]
  }
}
```

**DB cần có.**
```sql
CREATE TYPE assignee_type AS ENUM ('USER','DEPARTMENT');

task_assignees (
  id BIGSERIAL PK,
  task_id UUID FK tasks(id) ON DELETE CASCADE,
  assignee_type assignee_type NOT NULL,
  user_id BIGINT NULL FK users(id),
  department_id BIGINT NULL FK departments(id),
  assigned_at TIMESTAMPTZ DEFAULT now(),
  CHECK (
    (assignee_type='USER' AND user_id IS NOT NULL AND department_id IS NULL)
    OR
    (assignee_type='DEPARTMENT' AND department_id IS NOT NULL AND user_id IS NULL)
  ),
  UNIQUE (task_id, assignee_type, user_id, department_id)
);

-- Tiến độ theo member khi giao cho DEPARTMENT
task_member_progress (
  task_id UUID FK tasks(id),
  user_id BIGINT FK users(id),
  progress_percent SMALLINT DEFAULT 0,
  status task_status DEFAULT 'NEW',
  updated_at TIMESTAMPTZ,
  PRIMARY KEY (task_id, user_id)
);
```

---

### 3.6. FR-07 – Quản lý Deadline & Cảnh báo

**Vấn đề.** Mỗi Task có `start_at`, `due_at`. Hệ thống phải **tự động cảnh báo khi sắp quá hạn** (ví dụ còn 24h / đã quá hạn).

**Giải pháp.**
- Cron worker chạy mỗi 5 phút:
  - Quét Task `status NOT IN (DONE)` có `due_at` trong ngưỡng cảnh báo.
  - Đẩy sự kiện vào queue `notifications`.
- Ngưỡng cấu hình theo `priority` (URGENT: 2h, HIGH: 24h, MEDIUM: 48h, LOW: 72h).
- Ghi `deadline_alerts` để không gửi trùng.

**API.**

| Method | Path | Mô tả |
|---|---|---|
| GET | `/tasks/mine/upcoming?window=24h` | Task sắp hết hạn của tôi |
| GET | `/notifications?unread=true` | Notification in-app |
| POST | `/notifications/{id}/read` | Đánh dấu đã đọc |

**Response `GET /tasks/mine/upcoming`**
```json
{
  "success": true,
  "data": [
    {
      "id": "tsk_01H...",
      "title": "Làm slide họp",
      "due_at": "2026-04-22T09:00:00Z",
      "remaining_minutes": 1320,
      "priority": "HIGH",
      "status": "IN_PROGRESS"
    }
  ]
}
```

**DB cần có.**
```sql
notifications (
  id BIGSERIAL PK,
  user_id BIGINT FK users(id),
  type VARCHAR(50),                 -- 'TASK_DEADLINE','TASK_ASSIGNED',...
  title VARCHAR(255),
  body  TEXT,
  payload JSONB,                    -- { task_id, due_at, ... }
  read_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ DEFAULT now()
);
CREATE INDEX idx_notif_user_unread ON notifications(user_id) WHERE read_at IS NULL;

deadline_alerts (
  task_id UUID,
  user_id BIGINT,
  threshold VARCHAR(20),            -- '24H','2H','OVERDUE'
  sent_at TIMESTAMPTZ DEFAULT now(),
  PRIMARY KEY (task_id, user_id, threshold)
);
```

---

### 3.7. FR-08 – Quy trình trạng thái Task (State machine)

**Vấn đề.** Luồng nghiệp vụ: `Mới → Đang làm → Chờ duyệt → Hoàn thành | Làm lại`. Cần kiểm soát chuyển trạng thái theo quyền và điều kiện.

**Giải pháp.**
- 1 endpoint chung `POST /tasks/{id}/transition` nhận action. Backend validate:
  - Quyền của user.
  - Transition hợp lệ theo ma trận.
  - Điều kiện dữ liệu (ví dụ cần attachment kết quả khi submit).
- Ghi `task_status_history`.
- Publish event `TaskStatusChanged` → notification + rebuild dashboard cache.

**Ma trận transition**

| From → To | Action | Ai được làm |
|---|---|---|
| NEW → IN_PROGRESS | `start` | assignee |
| IN_PROGRESS → PENDING_REVIEW | `submit` | assignee (cần attachment hoặc note) |
| PENDING_REVIEW → DONE | `approve` | manager phòng / người giao việc |
| PENDING_REVIEW → IN_PROGRESS | `rework` | manager / người giao việc (kèm `reason`) |
| IN_PROGRESS → IN_PROGRESS | `update_progress` | assignee (cập nhật %) |

**API.**

| Method | Path | Mô tả |
|---|---|---|
| POST | `/tasks/{id}/transition` | Chuyển trạng thái |
| PATCH | `/tasks/{id}/progress` | Cập nhật % tiến độ |
| GET | `/tasks/{id}/history` | Lịch sử trạng thái |
| POST | `/tasks/{id}/comments` | Bình luận |

**Request `POST /tasks/{id}/transition`**
```json
{
  "action": "submit",
  "note": "Đã hoàn tất, file kết quả đính kèm.",
  "attachment_ids": ["att_01H..."]
}
```
**Response**
```json
{
  "success": true,
  "data": {
    "task_id": "tsk_01H...",
    "status": "PENDING_REVIEW",
    "progress_percent": 100,
    "transitioned_at": "2026-04-28T10:12:00Z"
  }
}
```

**Request `POST /tasks/{id}/transition` (rework)**
```json
{ "action": "rework", "reason": "Thiếu phần ví dụ response lỗi." }
```

**Request `PATCH /tasks/{id}/progress`**
```json
{ "progress_percent": 60, "note": "Đã xong 3/5 module." }
```

**Response lỗi khi transition sai**
```json
{
  "success": false,
  "error": {
    "code": "TASK_INVALID_TRANSITION",
    "message": "Không thể chuyển từ NEW sang DONE.",
    "details": { "from": "NEW", "to": "DONE" }
  }
}
```

**DB cần có.**
```sql
task_status_history (
  id BIGSERIAL PK,
  task_id UUID FK tasks(id) ON DELETE CASCADE,
  from_status task_status,
  to_status   task_status NOT NULL,
  action VARCHAR(30) NOT NULL,       -- start/submit/approve/rework/update_progress
  reason TEXT,
  progress_percent SMALLINT,
  changed_by BIGINT FK users(id),
  changed_at TIMESTAMPTZ DEFAULT now()
);
CREATE INDEX idx_tsh_task ON task_status_history(task_id, changed_at);

task_comments (
  id BIGSERIAL PK,
  task_id UUID FK tasks(id),
  user_id BIGINT FK users(id),
  body TEXT NOT NULL,
  created_at TIMESTAMPTZ DEFAULT now()
);
```

**Lưu ý AC:** *"Nhân viên không thể tự xóa Task đã được giao"* → `DELETE /tasks/{id}` kiểm tra `created_by = current_user` AND `status = NEW` AND không có assignee nào khác ngoài người tạo; nếu không, trả `403 TASK_DELETE_FORBIDDEN`.

---

### 3.8. FR-10 – Dashboard cá nhân

**Vấn đề.** Mỗi nhân viên cần nhanh chóng thấy **việc cần làm ngay** và **việc quan trọng trong ngày** khi mở app.

**Giải pháp.**
- 1 endpoint tổng hợp `GET /dashboard/me` trả về nhiều section, FE chỉ 1 request.
- Rule:
  - **Today-important**: `priority IN ('HIGH','URGENT')` và (`due_at::date = today` hoặc đã quá hạn) và `status != DONE`.
  - **To-do now**: `status IN ('NEW','IN_PROGRESS','REWORK')` sắp xếp theo `priority DESC, due_at ASC`.
  - **Pending review** (nếu là manager): Task do mình giao đang `PENDING_REVIEW`.
- Query dùng index `(assignee_id, status, due_at)`. Không cần cache ở mức cá nhân.

**API.**

**`GET /dashboard/me`**
```json
{
  "success": true,
  "data": {
    "counters": {
      "todo": 8,
      "in_progress": 3,
      "overdue": 1,
      "pending_review_for_me": 2
    },
    "important_today": [
      { "id":"tsk_01H...","title":"Deploy prod","priority":"URGENT",
        "due_at":"2026-04-21T17:00:00Z","status":"IN_PROGRESS" }
    ],
    "todo_now": [
      { "id":"tsk_02H...","title":"Review PR #123","priority":"HIGH",
        "due_at":"2026-04-22T09:00:00Z","status":"NEW" }
    ]
  }
}
```

**DB cần có.** Không tạo thêm bảng; bổ sung index:
```sql
CREATE INDEX idx_ta_user_open
  ON task_assignees(user_id)
  INCLUDE (task_id)
  WHERE assignee_type = 'USER';

CREATE INDEX idx_tasks_open_due
  ON tasks(due_at, priority)
  WHERE status <> 'DONE';
```

---

### 3.9. FR-11 – Báo cáo tiến độ phòng ban

**Vấn đề.** Manager muốn thấy tỷ lệ hoàn thành Task của **toàn bộ nhân viên trong phòng** dưới dạng biểu đồ.

**Giải pháp.**
- Endpoint trả về số liệu tổng hợp + breakdown theo nhân viên + theo trạng thái.
- Scope bắt buộc: chỉ phòng ban manager có quyền (sử dụng `path <@` để tính cả phòng con).
- Cache Redis key `dept:{id}:metrics:{period}` TTL 60s; worker cron refresh.

**API.**

**`GET /reports/departments/{id}?period=week|month|custom&from=&to=`**
```json
{
  "success": true,
  "data": {
    "department": { "id": 27, "name": "Phòng Backend" },
    "period": { "from":"2026-04-14","to":"2026-04-21" },
    "summary": {
      "total_tasks": 120,
      "done": 78,
      "in_progress": 30,
      "pending_review": 6,
      "overdue": 6,
      "completion_rate": 0.65
    },
    "by_employee": [
      {
        "employee_id": 301, "name": "Trần Thị B",
        "total": 14, "done": 10, "in_progress": 3, "overdue": 1,
        "completion_rate": 0.71
      }
    ],
    "by_priority": [
      { "priority":"URGENT","total":5,"done":4 },
      { "priority":"HIGH","total":40,"done":28 }
    ],
    "trend": [
      { "date":"2026-04-14","done":10,"created":15 },
      { "date":"2026-04-15","done":12,"created":14 }
    ]
  }
}
```

**DB cần có.** Dùng lại `tasks`, `task_assignees`, `employees`. Có thể tạo **materialized view** refresh theo giờ:
```sql
CREATE MATERIALIZED VIEW mv_department_task_metrics AS
SELECT
  d.id AS department_id,
  DATE_TRUNC('day', t.created_at) AS day,
  COUNT(*) FILTER (WHERE t.status='DONE')            AS done,
  COUNT(*) FILTER (WHERE t.status<>'DONE')           AS open,
  COUNT(*) FILTER (WHERE t.due_at < now() AND t.status<>'DONE') AS overdue,
  COUNT(*) AS total
FROM tasks t
JOIN departments d ON d.id = t.department_id
GROUP BY d.id, DATE_TRUNC('day', t.created_at);

CREATE INDEX ON mv_department_task_metrics(department_id, day);
-- Refresh: REFRESH MATERIALIZED VIEW CONCURRENTLY mv_department_task_metrics;
```

---

## 4. Database Schema tổng hợp (ERD)

```
 departments ──< department_managers >── users ──1:1── employees
     │ (parent_id, path)                           │
     │                                             │ (department_id)
     ▼                                             ▼
   tasks ──< task_assignees >── users / departments
     │                │
     │                └── task_member_progress (per user nếu assign DEPT)
     ├──< task_status_history (audit)
     ├──< task_comments
     ├──< attachments (owner_type='TASK')
     └──< deadline_alerts

 users ──< user_roles >── roles ──< role_permissions >── permissions
 users ──< refresh_tokens
 users ──< notifications
 employees ──< employee_status_history
 audit_logs (entity, entity_id, diff, actor)
```

Bảng **`audit_logs`** chung (phục vụ NFR minh bạch):
```sql
audit_logs (
  id BIGSERIAL PK,
  actor_user_id BIGINT,
  entity VARCHAR(50),      -- 'task','department','employee'
  entity_id TEXT,
  action VARCHAR(50),      -- 'create','update','delete','transition'
  before JSONB,
  after  JSONB,
  created_at TIMESTAMPTZ DEFAULT now()
);
CREATE INDEX idx_audit_entity ON audit_logs(entity, entity_id, created_at);
```

---

## 5. Non-functional

### 5.1. Bảo mật & Phân quyền (NFR 4.1)
- TLS 1.2+, HSTS, secure cookie cho refresh token.
- Mật khẩu: Argon2id.
- RBAC permission list được nạp vào JWT claims để FE ẩn/hiện nút; backend vẫn kiểm tra lại.
- **Row-level security**: Mỗi query Task/Employee được bọc bởi `WHERE department_id IN (scope)` dựa trên `ltree` của user. Có thể enforce bằng Postgres RLS cho ADMIN/MANAGER.
- Dữ liệu nhân sự nhạy cảm (lương, CCCD nếu có sau này): cột riêng, permission `hr.view_sensitive`.

### 5.2. Hiệu năng (NFR 4.2)
- p95 < 2s: tất cả query chính có index tương ứng (đã liệt kê).
- Connection pool (pgbouncer), HTTP keep-alive, gzip.
- Dashboard phòng ban dùng materialized view + Redis cache 60s.
- Pagination mặc định 20, giới hạn 100.

### 5.3. Khả dụng
- Deploy 2 instance API phía sau load balancer.
- Postgres primary + 1 replica (read-only cho reporting).
- Worker chạy 2 instance, dùng advisory lock / queue để không trùng job.
- Backup DB hằng ngày, PITR 7 ngày.

### 5.4. Tương thích & UX
- FE responsive, test trên Chrome/Edge + iOS/Android Safari/Chrome.
- API thống nhất JSON, thời gian ISO-8601 UTC; FE format theo locale `vi-VN`.

---

## Phụ lục A – Mapping yêu cầu BRD ↔ thiết kế

| BRD ID | Mô tả | Endpoint chính | Bảng chính |
|---|---|---|---|
| FR-01 | Quản lý Phòng ban (phân cấp) | `/departments*` | `departments` |
| FR-02 | Trưởng bộ phận | `/departments/{id}/managers` | `department_managers` |
| FR-03 | Hồ sơ Nhân viên | `/employees*` | `employees`, `users` |
| FR-04 | Trạng thái nhân sự | `PATCH /employees/{id}/status` | `employees`, `employee_status_history` |
| FR-05 | Khởi tạo Công việc | `POST /tasks` | `tasks`, `attachments` |
| FR-06 | Giao việc đa đối tượng | `/tasks/{id}/assignees` | `task_assignees`, `task_member_progress` |
| FR-07 | Deadline & cảnh báo | worker cron + `/notifications` | `deadline_alerts`, `notifications` |
| FR-08 | Quy trình trạng thái | `POST /tasks/{id}/transition` | `task_status_history` |
| FR-10 | Dashboard cá nhân | `GET /dashboard/me` | `tasks` + index |
| FR-11 | Báo cáo phòng ban | `GET /reports/departments/{id}` | `mv_department_task_metrics` |

## Phụ lục B – Tiêu chí nghiệm thu đã được đảm bảo

- **Sơ đồ tổ chức đúng dạng cây** → `GET /departments/tree` trả về cấu trúc lồng; `departments.path` (ltree) đảm bảo tính đúng đắn.
- **Nhân viên không thể tự xóa Task đã được giao** → `DELETE /tasks/{id}` kiểm tra `created_by`, `status='NEW'` và không có assignee khác.
- **Dashboard phản ánh số liệu thực tế** → `GET /reports/departments/{id}` đọc từ `tasks` đang mở, materialized view refresh ≤ 1 phút, xác thực bằng test `count(*)` so với raw.

# E-Office – Chi tiết từng trường trong Database

Tài liệu này liệt kê **toàn bộ trường (cột)** của từng bảng, kèm **kiểu dữ liệu (PostgreSQL)**, **ràng buộc** (PK/FK/UNIQUE/NOT NULL/DEFAULT/CHECK) và **mô tả nghiệp vụ**.

> Chú thích ký hiệu:
> - **PK** = khóa chính · **FK** = khóa ngoại · **U** = unique · **NN** = NOT NULL · **D** = default
> - Kiểu dữ liệu dùng cho PostgreSQL 15+. Có thể đổi tương đương cho MySQL (xem phụ lục cuối).

## Enum & Type định nghĩa sẵn

| Enum | Giá trị | Dùng ở |
|---|---|---|
| `employment_status` | `PROBATION`, `OFFICIAL`, `ON_LEAVE`, `RESIGNED` | `employees.status`, `employee_status_history.*_status` |
| `task_priority` | `LOW`, `MEDIUM`, `HIGH`, `URGENT` | `tasks.priority` |
| `task_status` | `NEW`, `IN_PROGRESS`, `PENDING_REVIEW`, `DONE`, `REWORK` | `tasks.status`, `task_member_progress.status`, `task_status_history.*_status` |
| `assignee_type` | `USER`, `DEPARTMENT` | `task_assignees.assignee_type` |

---

## 1. Nhóm Identity & Access

### 1.1. `users` – Tài khoản đăng nhập
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `id` | `BIGSERIAL` | PK | Khóa chính tự tăng |
| 2 | `employee_id` | `BIGINT` | FK → `employees.id`, U, nullable | Liên kết 1–1 tới hồ sơ nhân viên. NULL khi là tài khoản hệ thống (Admin) |
| 3 | `email` | `CITEXT` | NN, U | Email đăng nhập (case-insensitive) |
| 4 | `password_hash` | `TEXT` | NN | Mật khẩu đã băm Argon2id |
| 5 | `is_active` | `BOOLEAN` | NN, D `TRUE` | Cho phép đăng nhập hay không |
| 6 | `last_login_at` | `TIMESTAMPTZ` | nullable | Lần đăng nhập thành công gần nhất |
| 7 | `created_at` | `TIMESTAMPTZ` | NN, D `now()` | Thời điểm tạo |
| 8 | `updated_at` | `TIMESTAMPTZ` | NN, D `now()` | Thời điểm cập nhật gần nhất |

### 1.2. `roles` – Vai trò hệ thống
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `id` | `SERIAL` | PK | Khóa chính |
| 2 | `code` | `VARCHAR(50)` | NN, U | Mã định danh (`ADMIN`, `MANAGER`, `STAFF`) |
| 3 | `name` | `VARCHAR(100)` | NN | Tên hiển thị |
| 4 | `description` | `TEXT` | nullable | Mô tả vai trò |
| 5 | `created_at` | `TIMESTAMPTZ` | NN, D `now()` | |

### 1.3. `permissions` – Quyền thao tác
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `id` | `SERIAL` | PK | |
| 2 | `code` | `VARCHAR(100)` | NN, U | Dạng `resource.action`, ví dụ `task.create`, `dept.manage` |
| 3 | `description` | `TEXT` | nullable | Giải thích quyền |

### 1.4. `user_roles` – N–N user ↔ role
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `user_id` | `BIGINT` | PK, FK → `users.id` ON DELETE CASCADE | |
| 2 | `role_id` | `INT` | PK, FK → `roles.id` ON DELETE CASCADE | |
| 3 | `assigned_at` | `TIMESTAMPTZ` | NN, D `now()` | Thời điểm gán role |

### 1.5. `role_permissions` – N–N role ↔ permission
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `role_id` | `INT` | PK, FK → `roles.id` ON DELETE CASCADE | |
| 2 | `permission_id` | `INT` | PK, FK → `permissions.id` ON DELETE CASCADE | |

### 1.6. `refresh_tokens` – Token refresh đã phát hành
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `id` | `BIGSERIAL` | PK | |
| 2 | `user_id` | `BIGINT` | NN, FK → `users.id` ON DELETE CASCADE | Chủ của token |
| 3 | `token_hash` | `TEXT` | NN, U | Hash SHA-256 của refresh token (không lưu token gốc) |
| 4 | `user_agent` | `TEXT` | nullable | Thông tin thiết bị/trình duyệt |
| 5 | `ip_address` | `INET` | nullable | IP phát hành |
| 6 | `expires_at` | `TIMESTAMPTZ` | NN | Hạn sử dụng |
| 7 | `revoked_at` | `TIMESTAMPTZ` | nullable | Thời điểm thu hồi (logout / rotate) |
| 8 | `created_at` | `TIMESTAMPTZ` | NN, D `now()` | |

---

## 2. Nhóm Organization & HR

### 2.1. `departments` – Phòng ban (dạng cây)
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `id` | `BIGSERIAL` | PK | |
| 2 | `code` | `VARCHAR(50)` | NN, U | Mã phòng ban nội bộ (ví dụ `ENG-BE`) |
| 3 | `name` | `VARCHAR(255)` | NN | Tên phòng ban |
| 4 | `parent_id` | `BIGINT` | FK → `departments.id` ON DELETE RESTRICT, nullable | Phòng ban cha. NULL = nút gốc |
| 5 | `path` | `LTREE` | NN | Đường dẫn cây, ví dụ `1.4.27`. Dùng cho truy vấn con/cháu nhanh |
| 6 | `description` | `TEXT` | nullable | Mô tả phòng ban |
| 7 | `is_active` | `BOOLEAN` | NN, D `TRUE` | Phòng ban còn hoạt động |
| 8 | `created_at` | `TIMESTAMPTZ` | NN, D `now()` | |
| 9 | `updated_at` | `TIMESTAMPTZ` | NN, D `now()` | |
| 10 | `deleted_at` | `TIMESTAMPTZ` | nullable | Soft delete |

Index: `GIST(path)`, `btree(parent_id)`.

### 2.2. `department_managers` – Manager của phòng ban (N–N)
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `department_id` | `BIGINT` | PK, FK → `departments.id` ON DELETE CASCADE | |
| 2 | `user_id` | `BIGINT` | PK, FK → `users.id` ON DELETE CASCADE | Người quản lý |
| 3 | `is_primary` | `BOOLEAN` | NN, D `FALSE` | Có phải trưởng bộ phận chính. Mỗi phòng chỉ có 1 primary (unique partial index) |
| 4 | `assigned_at` | `TIMESTAMPTZ` | NN, D `now()` | |

### 2.3. `employees` – Hồ sơ nhân viên
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `id` | `BIGSERIAL` | PK | |
| 2 | `employee_code` | `VARCHAR(30)` | NN, U | Mã nhân viên (VD `NV0123`) |
| 3 | `full_name` | `VARCHAR(255)` | NN | Họ và tên |
| 4 | `email` | `CITEXT` | NN, U | Email công ty |
| 5 | `phone` | `VARCHAR(30)` | nullable | Số điện thoại |
| 6 | `position` | `VARCHAR(100)` | nullable | Chức vụ (VD `Backend Engineer`) |
| 7 | `department_id` | `BIGINT` | NN, FK → `departments.id` ON DELETE RESTRICT | Phòng ban trực thuộc |
| 8 | `status` | `employment_status` | NN, D `'PROBATION'` | Trạng thái: Thử việc / Chính thức / Nghỉ phép / Đã nghỉ việc |
| 9 | `joined_at` | `DATE` | nullable | Ngày vào làm |
| 10 | `resigned_at` | `DATE` | nullable | Ngày nghỉ việc (nếu có) |
| 11 | `avatar_url` | `TEXT` | nullable | Ảnh đại diện |
| 12 | `created_at` | `TIMESTAMPTZ` | NN, D `now()` | |
| 13 | `updated_at` | `TIMESTAMPTZ` | NN, D `now()` | |
| 14 | `deleted_at` | `TIMESTAMPTZ` | nullable | Soft delete |

### 2.4. `employee_status_history` – Lịch sử trạng thái nhân sự
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `id` | `BIGSERIAL` | PK | |
| 2 | `employee_id` | `BIGINT` | NN, FK → `employees.id` ON DELETE CASCADE | |
| 3 | `from_status` | `employment_status` | nullable | Trạng thái trước (NULL nếu là lần khởi tạo) |
| 4 | `to_status` | `employment_status` | NN | Trạng thái mới |
| 5 | `effective_date` | `DATE` | NN | Ngày hiệu lực |
| 6 | `note` | `TEXT` | nullable | Ghi chú (lý do, quyết định...) |
| 7 | `changed_by` | `BIGINT` | NN, FK → `users.id` | Người thực hiện |
| 8 | `changed_at` | `TIMESTAMPTZ` | NN, D `now()` | Thời điểm ghi nhận |

---

## 3. Nhóm Task Management

### 3.1. `tasks` – Công việc
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, D `gen_random_uuid()` | Khóa chính (UUID để tránh đoán) |
| 2 | `title` | `VARCHAR(255)` | NN | Tiêu đề |
| 3 | `description` | `TEXT` | nullable | Mô tả chi tiết (hỗ trợ markdown) |
| 4 | `priority` | `task_priority` | NN, D `'MEDIUM'` | Mức độ ưu tiên |
| 5 | `status` | `task_status` | NN, D `'NEW'` | Trạng thái tổng hợp của Task |
| 6 | `progress_percent` | `SMALLINT` | NN, D `0`, CHECK 0–100 | % tiến độ |
| 7 | `start_at` | `TIMESTAMPTZ` | nullable | Thời gian bắt đầu |
| 8 | `due_at` | `TIMESTAMPTZ` | nullable | Hạn chót (deadline) |
| 9 | `department_id` | `BIGINT` | NN, FK → `departments.id` | Phòng ban chủ quản – dùng để scope RBAC |
| 10 | `created_by` | `BIGINT` | NN, FK → `users.id` | Người tạo Task (thường là Manager) |
| 11 | `closed_at` | `TIMESTAMPTZ` | nullable | Thời điểm chuyển `DONE` |
| 12 | `approved_by` | `BIGINT` | FK → `users.id`, nullable | Người phê duyệt cuối |
| 13 | `rework_count` | `SMALLINT` | NN, D `0` | Số lần bị yêu cầu làm lại |
| 14 | `created_at` | `TIMESTAMPTZ` | NN, D `now()` | |
| 15 | `updated_at` | `TIMESTAMPTZ` | NN, D `now()` | |
| 16 | `deleted_at` | `TIMESTAMPTZ` | nullable | Soft delete |

Index: `(department_id, status)`, `(due_at) WHERE status <> 'DONE'`.

### 3.2. `task_assignees` – Người/Phòng được giao (N–N đa hình)
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `id` | `BIGSERIAL` | PK | |
| 2 | `task_id` | `UUID` | NN, FK → `tasks.id` ON DELETE CASCADE | |
| 3 | `assignee_type` | `assignee_type` | NN | `USER` hoặc `DEPARTMENT` |
| 4 | `user_id` | `BIGINT` | FK → `users.id`, nullable | Bắt buộc khi `assignee_type='USER'` |
| 5 | `department_id` | `BIGINT` | FK → `departments.id`, nullable | Bắt buộc khi `assignee_type='DEPARTMENT'` |
| 6 | `assigned_by` | `BIGINT` | NN, FK → `users.id` | Người gán |
| 7 | `assigned_at` | `TIMESTAMPTZ` | NN, D `now()` | |
| — | **CHECK** | — | `(type='USER' AND user_id NOT NULL AND department_id NULL) OR (type='DEPARTMENT' AND department_id NOT NULL AND user_id NULL)` | Đảm bảo tính nhất quán đa hình |
| — | **UNIQUE** | — | `(task_id, assignee_type, user_id, department_id)` | Không trùng |

### 3.3. `task_member_progress` – Tiến độ cá nhân (khi giao cho phòng)
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `task_id` | `UUID` | PK, FK → `tasks.id` ON DELETE CASCADE | |
| 2 | `user_id` | `BIGINT` | PK, FK → `users.id` | Thành viên trong phòng được giao |
| 3 | `progress_percent` | `SMALLINT` | NN, D `0`, CHECK 0–100 | |
| 4 | `status` | `task_status` | NN, D `'NEW'` | Trạng thái cá nhân |
| 5 | `note` | `TEXT` | nullable | Ghi chú khi cập nhật |
| 6 | `updated_at` | `TIMESTAMPTZ` | NN, D `now()` | |

### 3.4. `task_status_history` – Lịch sử chuyển trạng thái
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `id` | `BIGSERIAL` | PK | |
| 2 | `task_id` | `UUID` | NN, FK → `tasks.id` ON DELETE CASCADE | |
| 3 | `from_status` | `task_status` | nullable | Trạng thái trước (NULL khi tạo mới) |
| 4 | `to_status` | `task_status` | NN | Trạng thái mới |
| 5 | `action` | `VARCHAR(30)` | NN | `start`, `submit`, `approve`, `rework`, `update_progress`, `reopen`... |
| 6 | `reason` | `TEXT` | nullable | Lý do (bắt buộc khi `action='rework'`) |
| 7 | `progress_percent` | `SMALLINT` | nullable, CHECK 0–100 | % tại thời điểm chuyển |
| 8 | `changed_by` | `BIGINT` | NN, FK → `users.id` | Ai thực hiện |
| 9 | `changed_at` | `TIMESTAMPTZ` | NN, D `now()` | |

### 3.5. `task_comments` – Bình luận Task
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `id` | `BIGSERIAL` | PK | |
| 2 | `task_id` | `UUID` | NN, FK → `tasks.id` ON DELETE CASCADE | |
| 3 | `user_id` | `BIGINT` | NN, FK → `users.id` | Tác giả |
| 4 | `body` | `TEXT` | NN | Nội dung (markdown) |
| 5 | `parent_comment_id` | `BIGINT` | FK → `task_comments.id`, nullable | Reply lồng |
| 6 | `created_at` | `TIMESTAMPTZ` | NN, D `now()` | |
| 7 | `updated_at` | `TIMESTAMPTZ` | NN, D `now()` | |
| 8 | `deleted_at` | `TIMESTAMPTZ` | nullable | Soft delete |

### 3.6. `attachments` – Tệp đính kèm (đa hình)
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, D `gen_random_uuid()` | |
| 2 | `owner_type` | `VARCHAR(30)` | NN | `TASK`, `TASK_RESULT`, `COMMENT` |
| 3 | `owner_id` | `VARCHAR(64)` | NN | ID của entity chủ (UUID của task hoặc BIGINT của comment → đổi về text) |
| 4 | `file_name` | `VARCHAR(255)` | NN | Tên file gốc |
| 5 | `content_type` | `VARCHAR(100)` | NN | MIME type (VD `application/pdf`) |
| 6 | `size_bytes` | `BIGINT` | NN, CHECK `> 0` | Dung lượng |
| 7 | `storage_key` | `TEXT` | NN, U | Key trong S3/MinIO |
| 8 | `checksum_sha256` | `CHAR(64)` | nullable | Hash kiểm tra toàn vẹn |
| 9 | `uploaded_by` | `BIGINT` | NN, FK → `users.id` | Người upload |
| 10 | `created_at` | `TIMESTAMPTZ` | NN, D `now()` | |

Index: `(owner_type, owner_id)`.

---

## 4. Nhóm Notification & Deadline

### 4.1. `notifications` – Thông báo in-app
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `id` | `BIGSERIAL` | PK | |
| 2 | `user_id` | `BIGINT` | NN, FK → `users.id` ON DELETE CASCADE | Người nhận |
| 3 | `type` | `VARCHAR(50)` | NN | `TASK_ASSIGNED`, `TASK_DEADLINE`, `TASK_APPROVED`, `TASK_REWORK`, `COMMENT_ADDED`... |
| 4 | `title` | `VARCHAR(255)` | NN | Tiêu đề hiển thị |
| 5 | `body` | `TEXT` | nullable | Nội dung |
| 6 | `payload` | `JSONB` | nullable | Dữ liệu kèm theo (VD `{ "task_id": "...", "due_at": "..." }`) |
| 7 | `read_at` | `TIMESTAMPTZ` | nullable | Thời điểm đánh dấu đã đọc |
| 8 | `created_at` | `TIMESTAMPTZ` | NN, D `now()` | |

Index: `(user_id) WHERE read_at IS NULL` (đếm chưa đọc nhanh).

### 4.2. `deadline_alerts` – Chống gửi trùng cảnh báo deadline
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `task_id` | `UUID` | PK, FK → `tasks.id` ON DELETE CASCADE | |
| 2 | `user_id` | `BIGINT` | PK, FK → `users.id` ON DELETE CASCADE | |
| 3 | `threshold` | `VARCHAR(20)` | PK | `72H`, `48H`, `24H`, `2H`, `OVERDUE` |
| 4 | `sent_at` | `TIMESTAMPTZ` | NN, D `now()` | |

---

## 5. Nhóm Reporting & Audit

### 5.1. `audit_logs` – Nhật ký audit toàn hệ thống
| # | Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|---|
| 1 | `id` | `BIGSERIAL` | PK | |
| 2 | `actor_user_id` | `BIGINT` | FK → `users.id`, nullable | Ai thực hiện (NULL = hệ thống/cron) |
| 3 | `entity` | `VARCHAR(50)` | NN | Loại đối tượng: `task`, `department`, `employee`, `user`... |
| 4 | `entity_id` | `TEXT` | NN | ID của đối tượng (UUID hoặc BIGINT dưới dạng text) |
| 5 | `action` | `VARCHAR(50)` | NN | `create`, `update`, `delete`, `transition`, `login`... |
| 6 | `before` | `JSONB` | nullable | Snapshot trước |
| 7 | `after` | `JSONB` | nullable | Snapshot sau |
| 8 | `ip_address` | `INET` | nullable | |
| 9 | `user_agent` | `TEXT` | nullable | |
| 10 | `created_at` | `TIMESTAMPTZ` | NN, D `now()` | |

Index: `(entity, entity_id, created_at DESC)`.

### 5.2. `mv_department_task_metrics` – Materialized View tổng hợp theo phòng/ngày
| # | Trường | Kiểu dữ liệu | Mô tả |
|---|---|---|---|
| 1 | `department_id` | `BIGINT` | Phòng ban (tham chiếu `departments.id`) |
| 2 | `day` | `DATE` | Ngày thống kê |
| 3 | `total` | `INT` | Tổng số Task tạo trong ngày |
| 4 | `done` | `INT` | Số Task đã `DONE` |
| 5 | `open` | `INT` | Số Task khác `DONE` |
| 6 | `overdue` | `INT` | Số Task quá hạn chưa DONE |
| 7 | `completion_rate` | `NUMERIC(5,4)` | `done / NULLIF(total,0)` |

Index: `(department_id, day)`. Làm tươi: `REFRESH MATERIALIZED VIEW CONCURRENTLY mv_department_task_metrics;` (cron 60s).

---

## 6. Bảng tổng hợp nhanh (1 dòng/bảng)

| # | Bảng | Số trường | PK | Mục đích 1 câu |
|---|---|---:|---|---|
| 1 | `users` | 8 | `id` | Tài khoản đăng nhập, gắn 1–1 với `employees` |
| 2 | `roles` | 5 | `id` | Vai trò (ADMIN/MANAGER/STAFF) |
| 3 | `permissions` | 3 | `id` | Danh mục quyền dạng `resource.action` |
| 4 | `user_roles` | 3 | (user_id, role_id) | Gán role cho user |
| 5 | `role_permissions` | 2 | (role_id, permission_id) | Gán quyền cho role |
| 6 | `refresh_tokens` | 8 | `id` | Phiên refresh token |
| 7 | `departments` | 10 | `id` | Phòng ban phân cấp (ltree) |
| 8 | `department_managers` | 4 | (department_id, user_id) | Manager của phòng, có primary |
| 9 | `employees` | 14 | `id` | Hồ sơ nhân viên |
| 10 | `employee_status_history` | 8 | `id` | Lịch sử đổi trạng thái nhân sự |
| 11 | `tasks` | 16 | `id` (UUID) | Công việc |
| 12 | `task_assignees` | 7 | `id` | Gán Task cho user/phòng |
| 13 | `task_member_progress` | 6 | (task_id, user_id) | Tiến độ cá nhân khi giao phòng |
| 14 | `task_status_history` | 9 | `id` | Audit chuyển trạng thái Task |
| 15 | `task_comments` | 8 | `id` | Bình luận Task |
| 16 | `attachments` | 10 | `id` (UUID) | File đính kèm đa hình |
| 17 | `notifications` | 8 | `id` | Thông báo in-app |
| 18 | `deadline_alerts` | 4 | (task_id, user_id, threshold) | Chống gửi trùng cảnh báo |
| 19 | `audit_logs` | 10 | `id` | Nhật ký audit toàn hệ thống |
| MV | `mv_department_task_metrics` | 7 | (department_id, day) | Tổng hợp báo cáo phòng ban |

---

## Phụ lục – Mapping kiểu dữ liệu Postgres ↔ MySQL

| Postgres | MySQL tương đương | Ghi chú |
|---|---|---|
| `BIGSERIAL` | `BIGINT AUTO_INCREMENT` | |
| `SERIAL` | `INT AUTO_INCREMENT` | |
| `UUID` | `CHAR(36)` hoặc `BINARY(16)` | Cần `gen_random_uuid()` → `UUID()` |
| `CITEXT` | `VARCHAR(x) COLLATE utf8mb4_0900_ai_ci` | Case-insensitive |
| `TEXT` | `TEXT` / `LONGTEXT` | |
| `TIMESTAMPTZ` | `TIMESTAMP` (UTC) | MySQL không có timezone-aware, cần lưu UTC |
| `BOOLEAN` | `TINYINT(1)` | |
| `JSONB` | `JSON` | MySQL không có index GIN; cần functional index |
| `LTREE` | Không có – dùng `VARCHAR` + index | Hoặc chuyển sang closure table |
| `INET` | `VARBINARY(16)` + hàm | Hoặc `VARCHAR(45)` |
| `ENUM kiểu riêng` | `ENUM('...')` inline hoặc bảng lookup | |
| `SMALLINT` | `SMALLINT` | |
| `NUMERIC(5,4)` | `DECIMAL(5,4)` | |
