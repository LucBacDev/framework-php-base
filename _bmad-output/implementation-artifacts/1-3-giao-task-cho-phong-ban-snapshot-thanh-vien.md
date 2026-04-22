# Story 1.3: Giao task cho phòng ban (snapshot thành viên)

Status: ready-for-dev

## Story

As a Manager, I want giao task cho cả phòng ban, so that mọi thành viên hợp lệ nhận việc tại thời điểm giao.

## Acceptance Criteria

1. Manager phải có quyền trong scope phòng ban.
2. Resolve danh sách thành viên hợp lệ (loại `Đã nghỉ việc`).
3. Nếu không có thành viên hợp lệ thì từ chối.
4. Lưu snapshot assignees + audit log.

## Tasks / Subtasks

- [ ] Endpoint assign by department.
- [ ] Scope check department.
- [ ] Query active members.
- [ ] Snapshot assignees.
- [ ] Audit + tests.

## Dev Notes

- Snapshot phải phản ánh đúng tại thời điểm giao.
- Dữ liệu phục vụ truy vết lịch sử.

## Diagrams

### Sequence

- Manager chọn department.
- API check scope.
- Lấy active members.
- Snapshot assignees + audit.

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  actor Manager
  participant API as TaskCtrl
  participant Org as DepartmentService
  participant DB as MySQL
  Manager->>API: Assign task by departmentID
  API->>Org: list active members
  Org-->>API: memberIDs
  API->>DB: insert snapshot assignees
  API->>DB: insert audit log
  API-->>Manager: result(true, assigneeCount)
```

## Dev Agent Record

### Agent Model Used
Codex 5.3

### Debug Log References
- N/A

### Completion Notes List
- Context copied from planning story and normalized to implementation artifact.

### File List
- `_bmad-output/implementation-artifacts/1-3-giao-task-cho-phong-ban-snapshot-thanh-vien.md`
