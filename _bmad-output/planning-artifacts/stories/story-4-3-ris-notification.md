# Story 4.3: RIS Notification

**Epic:** Epic 4: Integration (RIS/HIS/Modality)

**User Story:**

As a RIS,
I want nhận notification khi có hình ảnh mới,
So that biết khi nào cần truy xuất hình ảnh.

**Acceptance Criteria:**

**Given** PACS nhận đủ hình ảnh cho study
**When** Trigger notification đến RIS callback URL
**Then** Send payload với study metadata
**And** Retry mechanism cho failed notifications

**FRs Covered:** FR43, FR43a-FR43f

**Epic Goal:** Tích hợp với HIS, RIS và Modality devices
