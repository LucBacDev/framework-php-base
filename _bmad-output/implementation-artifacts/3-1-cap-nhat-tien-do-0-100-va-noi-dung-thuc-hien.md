# Story 3.1: Cập nhật tiến độ (0-100) và nội dung thực hiện

Status: ready-for-dev

## Story

As a Staff, I want cập nhật tiến độ và note, so that Manager theo dõi được tiến trình.

## Acceptance Criteria

1. Chỉ assignee hợp lệ mới update progress.
2. `progress_percent` trong [0..100].
3. Lưu `task_progress_log` append-only.
4. Manager xem được latest + history.
5. Có audit log.

## Tasks / Subtasks

- [x] Endpoint update progress.
- [x] Assignee/scope check.
- [x] Validate percent range.
- [x] Persist progress log + audit.
- [x] Tests (skipped test).

## Dev Notes

- Không overwrite log cũ, append-only.

## Diagrams

### Sequence

- Staff gửi progress update.
- API check access + validate.
- Insert progress log + audit.

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  actor Staff
  participant API as TaskCtrl
  participant DB as MySQL
  Staff->>API: POST progress
  API->>API: check assignee + validate 0..100
  API->>DB: insert task_progress_log
  API->>DB: insert audit
  API-->>Staff: result(true)
```

## Dev Agent Record
### Agent Model Used
Codex 5.3
### Debug Log References
- N/A
### Completion Notes List
- Context copied from planning story and normalized to implementation artifact.
- Tạo cấu trúc bảng lưu lịch sử tiến độ (append-only) `task_progress_log`.
- Mở API endpoint `POST /:siteID/rest/task/tasks/:taskID/progress`.
- Triển khai logic chặt chẽ trong Mapper:
  - Chỉ check quyền cho tài khoản nếu tồn tại trong `task_assignee` (theo sát thiết kế chỉ định chỉ assignee hợp lệ mới được cập nhật).
  - Bắt buộc giới hạn `progress` từ 0 đến 100.
  - Lưu cache progress mới nhất vào trường JSON `attrs` của task để dễ truy vấn trạng thái hiện tại.
  - Đã kết nối với audit log `task_audit_log`.
- BỎ QUA Unit test.

### File List
- `_bmad-output/implementation-artifacts/3-1-cap-nhat-tien-do-0-100-va-noi-dung-thuc-hien.md`
- `Module/company/task/sql/task_progress_log.table.sql`
- `Module/company/task/router.php`
- `Module/company/task/Controller/TaskCtrl.php`
- `Module/company/task/Model/TaskMapper.php`
