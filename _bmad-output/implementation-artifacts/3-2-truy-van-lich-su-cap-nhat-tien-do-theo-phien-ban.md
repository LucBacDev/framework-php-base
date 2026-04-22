# Story 3.2: Truy vấn lịch sử cập nhật tiến độ theo phiên bản

Status: ready-for-dev

## Story

As a Manager, I want xem history progress theo phiên bản, so that truy vết được thay đổi.

## Acceptance Criteria

1. Manager có quyền/scope phù hợp.
2. Trả danh sách log gồm thời gian, người cập nhật, %, nội dung.
3. Hỗ trợ phân trang.

## Tasks / Subtasks

- [ ] Endpoint list progress logs.
- [ ] Permission/scope check.
- [ ] Paging support.
- [ ] Tests.

## Dev Notes

- Ưu tiên query index tốt theo `task_id`, `createdDate`.

## Diagrams

### Sequence

- Manager gọi API lịch sử progress.
- API check scope và query log phân trang.

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  actor Manager
  participant API as TaskCtrl
  participant DB as MySQL
  Manager->>API: GET progress history
  API->>API: check role/scope
  API->>DB: select progress logs with paging
  API-->>Manager: result(true, items)
```

## Dev Agent Record
### Agent Model Used
Codex 5.3
### Debug Log References
- N/A
### Completion Notes List
- Context copied from planning story and normalized to implementation artifact.
### File List
- `_bmad-output/implementation-artifacts/3-2-truy-van-lich-su-cap-nhat-tien-do-theo-phien-ban.md`
