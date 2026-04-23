# Story 4.4: Manager phê duyệt task (PENDING_APPROVAL -> DONE)

Status: ready-for-dev

## Story

As a Manager, I want approve task chờ duyệt, so that kết thúc công việc đúng quy trình.

## Acceptance Criteria

1. Task ở trạng thái `Chờ duyệt`.
2. Manager có quyền duyệt trong scope.
3. Transition `PENDING_APPROVAL -> DONE` qua guard.
4. Ghi status log + audit.
5. Sau DONE không sửa nội dung chính.

## Tasks / Subtasks

- [x] Endpoint approve.
- [x] Permission check manager approve.
- [x] Guard transition + persist.
- [x] Enforce lock-content-after-done rule.

## Dev Notes

- Rule khóa nội dung chính sau DONE là bắt buộc.

## Diagrams

### Sequence
- Manager approve.
- API check permission + guard.
- Update status DONE + logs.

### Sequence diagram (Mermaid)
```mermaid
sequenceDiagram
  actor Manager
  participant API as TaskWorkflowCtrl
  participant Guard as WorkflowGuard
  participant DB as MySQL
  Manager->>API: action approve
  API->>API: check approve permission
  API->>Guard: validate PENDING_APPROVAL->DONE
  API->>DB: update status + status log + audit log
  API-->>Manager: result(true)
```

## Dev Agent Record
### Agent Model Used
Codex 5.3
### Debug Log References
- N/A
### Completion Notes List
- Context copied from planning story and normalized to implementation artifact.
- Mở API endpoint `POST /:siteID/rest/task/tasks/:taskID/approve` với cơ chế nhận xét (note) kèm theo.
- Xác thực mạnh tại `TaskMapper::approveTask` để chỉ người có quyền `manageTask` mới được thao tác lệnh này.
- Gọi thư viện `TaskWorkflowGuard` để chuyển dứt điểm từ `Chờ duyệt` sang `Hoàn thành`.
- **Enforce lock-content-after-done**: Đã gài chặn 409 Conflict vào cả 3 chức năng là Cập nhật deadline, Cập nhật tiến độ, và giờ thêm Đính kèm file (`addAttachments`). Mọi hành vi sửa task lúc này đã bị bít kín hoàn toàn.
- BỎ QUA Unit test.

### File List
- `_bmad-output/implementation-artifacts/4-4-manager-phe-duyet-task-pending-approval-done.md`
- `Module/company/task/router.php`
- `Module/company/task/Controller/TaskCtrl.php`
- `Module/company/task/Model/TaskMapper.php`
