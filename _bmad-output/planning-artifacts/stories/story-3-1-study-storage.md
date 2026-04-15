# Story 3.1: Study Storage & Indexing

**Epic:** Epic 3: Study Management & Search

**User Story:**

As a System,
I want lưu trữ và index DICOM studies,
So that có thể truy xuất nhanh.

**Acceptance Criteria:**

**Given** DICOM instances được upload hoặc nhận
**When** Lưu vào MySQL/Cassandra/Elasticsearch
**Then** Sync metadata async qua Kafka
**And** Maintain referential integrity

**FRs Covered:** FR16, FR16a-FR16b

**Epic Goal:** Quản lý studies và tìm kiếm hiệu quả
