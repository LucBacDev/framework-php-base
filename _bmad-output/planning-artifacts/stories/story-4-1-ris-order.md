# Story 4.1: RIS Order Integration

**Epic:** Epic 4: Integration (RIS/HIS/Modality)

**User Story:**

As a RIS,
I want gửi imaging orders đến PACS,
So that bác sĩ có thể chỉ định chụp.

**Acceptance Criteria:**

**Given** RIS gọi REST API hoặc gửi HL7 message
**When** PACS nhận và validate order
**Then** Tạo worklist entry
**And** Return confirmation

**FRs Covered:** FR40, FR40a-FR40c

**Epic Goal:** Tích hợp với HIS, RIS và Modality devices
