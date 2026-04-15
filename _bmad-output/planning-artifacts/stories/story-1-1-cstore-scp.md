# Story 1.1: C-STORE SCP Implementation

**Epic:** Epic 1: Core DICOM Services

**User Story:**

As a Modality,
I want gửi hình ảnh DICOM vào PACS qua C-STORE SCP,
So that hệ thống có thể lưu trữ và quản lý hình ảnh y tế.

**Acceptance Criteria:**

**Given** Modality đã kết nối mạng với PACS
**When** Modality gửi DICOM instances qua C-STORE SCP
**Then** PACS nhận và validate DICOM P10 format
**And** Lưu trữ file vào storage system
**And** Trả về C-STORE-RSP thành công

**FRs Covered:** FR1, FR53

**Epic Goal:** Cung cấp các DICOM services cơ bản cho hệ thống PACS
