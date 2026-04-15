# Story 7.2: RIS Sync

**Epic:** Epic 7: Background Services

**User Story:**

As a System,
I want sync với RIS asynchronously,
So that không impact performance.

**Acceptance Criteria:**

**Given** Study completed
**When** Trigger RIS sync
**Then** Queue notification
**And** Retry on failure

**FRs Covered:** FR85, FR86, FR87

**Epic Goal:** Xử lý asynchronous operations
