# Story 4.1: Workflow guard cho state machine task

Status: ready-for-dev

## Story

As a System, I want enforce state-machine tập trung, so that ngăn chuyển trạng thái sai.

## Acceptance Criteria

1. Chỉ cho phép cạnh hợp lệ theo workflow.
2. Mọi transition đi qua guard chung.
3. Mọi transition ghi status log + audit log.

## Tasks / Subtasks

- [ ] Implement `TaskWorkflowGuard`.
- [ ] Wire guard vào all workflow actions.
- [ ] Persist status log + audit.
- [ ] Tests valid/invalid transitions.

## Dev Notes

- Không hardcode transition ở nhiều controller.

## Diagrams

### Sequence

- API nhận action transition.
- Guard validate from->to.
- Nếu hợp lệ thì update status + logs.

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  participant API as TaskWorkflowCtrl
  participant Guard as TaskWorkflowGuard
  participant DB as MySQL
  API->>Guard: validate transition(from,to)
  alt valid
    API->>DB: update task status
    API->>DB: insert status_log + audit_log
  else invalid
    API-->>API: return 400 invalid transition
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
- `_bmad-output/implementation-artifacts/4-1-workflow-guard-cho-state-machine-task.md`
