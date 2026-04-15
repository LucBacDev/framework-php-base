# Story 6.3: Data Retention

**Epic:** Epic 6: Audit & Compliance

**User Story:**

As a System,
I want enforce data retention policies,
So that comply với quy định.

**Acceptance Criteria:**

**Given** Retention period configured
**When** Background service runs
**Then** Clean up old data theo policy
**And** Support soft delete trước hard delete

**FRs Covered:** FR49, FR49a-FR49b, FR63

**Epic Goal:** Đảm bảo HIPAA compliance và audit trail
