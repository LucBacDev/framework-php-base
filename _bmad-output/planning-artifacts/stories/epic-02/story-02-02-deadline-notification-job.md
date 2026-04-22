---
epic: 2
story: "2.2"
title: "Job thông báo sắp quá hạn và quá hạn (async + retry)"
source: "_bmad-output/planning-artifacts/epics.md"
updatedAt: "2026-04-22"
---

# Story 2.2: Job thông báo sắp quá hạn và quá hạn (async + retry)

As a System,  
I want tự động gửi thông báo sắp quá hạn và quá hạn theo lịch quét,  
So that người liên quan nhận cảnh báo đúng mốc và giảm task trễ hạn.

## Acceptance Criteria

**Given** hệ thống có cơ chế queue/scheduler hiện hữu  
**When** job quét deadline chạy theo lịch  
**Then** hệ thống enqueue notification cho các mốc “sắp quá hạn” (ví dụ 24h) và “đã quá hạn” theo cấu hình  
**And** áp dụng retry policy 1m -> 5m -> 15m, tối đa 3 lần và ghi nhận số lần retry  
**And** tỷ lệ gửi thành công được đo lường/ghi log phục vụ thống kê vận hành

## Diagrams

### Sequence

- **Actor**: Scheduler/Queue (System)
- **Mục tiêu**: scan tasks, enqueue notify “due soon” & “overdue”, retry 1m→5m→15m (max 3)
- **Reads**: task deadlines + recipients (assignees/managers)
- **Writes**: notification jobs + retry tracking/log/metrics

### Sequence diagram (Mermaid)

```mermaid
sequenceDiagram
  autonumber
  participant Cron as Scheduler/Cron
  participant Job as DeadlineScanJob
  participant TaskQ as TaskQuery (DB)
  participant DB as MySQL
  participant Noti as TaskNotificationService
  participant Queue as Queue/Jobs
  participant Metrics as Logs/Metrics

  Cron->>Job: Trigger scan (interval)
  Job->>TaskQ: findDueSoonAndOverdueTasks(config)
  TaskQ->>DB: SELECT tasks WHERE due_time near/overdue
  DB-->>TaskQ: tasks[]
  TaskQ-->>Job: tasks[]

  loop each task
    Job->>Noti: buildNotifications(task, recipients)
    Noti->>Queue: enqueue(task.deadline.reminder/overdue, payload, retry=0)
    alt enqueue failed
      Queue-->>Noti: error
      Noti->>Queue: retry (1m)
      Noti->>Metrics: log retryCount + reason
    else enqueue ok
      Queue-->>Noti: jobID
      Noti->>Metrics: record success (for >=99% tracking)
    end
  end
```

