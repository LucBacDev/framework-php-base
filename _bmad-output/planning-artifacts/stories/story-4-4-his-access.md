# Story 4.4: HIS Image Access

**Epic:** Epic 4: Integration (RIS/HIS/Modality)

**User Story:**

As a HIS,
I want truy xuất hình ảnh qua WADO-RS,
So that hiển thị trong EMR interface.

**Acceptance Criteria:**

**Given** HIS xác thực và có quyền
**When** Gọi WADO-RS API
**Then** Return DICOM data hoặc rendered images
**And** Audit log ghi lại PHI access

**FRs Covered:** FR44, FR44a-FR44i

**Epic Goal:** Tích hợp với HIS, RIS và Modality devices
