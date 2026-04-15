# Story 1.3: QIDO-RS Implementation

**Epic:** Epic 1: Core DICOM Services

**User Story:**

As a User,
I want tìm kiếm studies qua QIDO-RS API,
So that tìm nhanh hình ảnh cần thiết.

**Acceptance Criteria:**

**Given** User đã xác thực
**When** Gọi QIDO-RS với các filters (PatientID, StudyDate, Modality...)
**Then** Trả về danh sách studies matching criteria
**And** Response time < 1 giây (P95)

**FRs Covered:** FR3, FR3a-FR3h

**Epic Goal:** Cung cấp các DICOM services cơ bản cho hệ thống PACS
