# Story 1.5: Ràng buộc thao tác xóa task (soft delete/lock theo quyền)

Status: ready-for-dev

## Story

As an Admin, I want kiểm soát việc xóa task theo quyền/trạng thái, so that không mất dữ liệu nghiệp vụ sai quy định.

## Acceptance Criteria

1. Staff không được xóa task đã giao.
2. Manager không xóa cứng task đã phát sinh xử lý.
3. Admin chỉ xóa theo policy (ưu tiên soft delete).
4. Bắt buộc audit before/after + reason (nếu có).

## Tasks / Subtasks

- [ ] Endpoint delete/soft-delete.
- [ ] Rule matrix theo role + state.
- [ ] Persist soft delete/lock.
- [ ] Audit logging.
- [ ] Tests unauthorized/conflict.

## Dev Notes

- Ưu tiên deny-by-default cho delete.

## Diagrams

### Sequence

- User gọi delete.
- API check role/scope/rule.
- Nếu hợp lệ thì soft delete + audit.
- Nếu không hợp lệ trả lỗi quyền/conflict.

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  actor User as Staff/Manager/Admin
  participant API as TaskCtrl
  participant DB as MySQL
  User->>API: DELETE task
  API->>API: evaluate role + task state rules
  alt allowed
    API->>DB: update task deletedDate (soft delete)
    API->>DB: insert audit log
    API-->>User: result(true)
  else denied
    API-->>User: result(false, error, 403/409)
  end
```

## Dev Agent Record

### Agent Model Used
Codex 5.3

### Debug Log References
- N/A

### Completion Notes List
- Context copied from planning story and normalized to implementation artifact.

### File List
- `_bmad-output/implementation-artifacts/1-5-rang-buoc-thao-tac-xoa-task-soft-delete-lock-theo-quyen.md`
