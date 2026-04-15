# Story 1.4: STOW-RS Implementation

**Epic:** Epic 1: Core DICOM Services

**User Story:**

As a Technician,
I want upload hình ảnh DICOM qua STOW-RS API,
So that lưu trữ hình ảnh vào hệ thống.

**Acceptance Criteria:**

**Given** User upload DICOM files qua STOW-RS
**When** Validate Content-Type và DICOM conformance
**Then** Lưu vào storage system
**And** Index metadata vào Elasticsearch
**And** Trigger async notifications nếu có

**FRs Covered:** FR4, FR4a-FR4h

**Epic Goal:** Cung cấp các DICOM services cơ bản cho hệ thống PACS
