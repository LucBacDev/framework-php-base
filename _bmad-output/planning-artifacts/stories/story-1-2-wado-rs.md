# Story 1.2: WADO-RS Implementation

**Epic:** Epic 1: Core DICOM Services

**User Story:**

As a DICOM Viewer,
I want truy xuất hình ảnh DICOM qua WADO-RS API,
So that hiển thị hình ảnh cho bác sĩ chẩn đoán.

**Acceptance Criteria:**

**Given** User đã xác thực và có quyền truy cập study
**When** Gọi API WADO-RS để lấy study/series/instance
**Then** Trả về DICOM objects hoặc rendered images
**And** Response time < 3 giây cho images < 50MB

**FRs Covered:** FR2, FR44, FR44a-FR44i

**Epic Goal:** Cung cấp các DICOM services cơ bản cho hệ thống PACS
