# Story 7.1: Async STOW Processing

**Epic:** Epic 7: Background Services

**User Story:**

As a System,
I want xử lý STOW operations asynchronously,
So that không block user requests.

**Acceptance Criteria:**

**Given** STOW request received
**When** Queue to Kafka
**Then** Process background
**And** Update status when complete

**FRs Covered:** FR84

**Epic Goal:** Xử lý asynchronous operations
