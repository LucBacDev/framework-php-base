---
stepsCompleted: [1, 2, 3, 4]
lastStep: 4
inputDocuments: ["_bmad-output/planning-artifacts/prd.md", "_bmad-output/planning-artifacts/architecture.md", "_bmad-output/planning-artifacts/ux-design-specification.md"]
---

# pacs2 - Epic Breakdown

## Overview

This document provides the complete epic and story breakdown for pacs2, decomposing the requirements from the PRD, UX Design if it exists, and Architecture requirements into implementable stories.

## Requirements Inventory

### Functional Requirements

**1. DICOM Services (FR1-FR6)**
- FR1: Nhận hình ảnh DICOM từ modalities qua C-STORE SCP
- FR2: Truy xuất hình ảnh DICOM qua WADO-RS API
- FR3: Truy vấn studies/series/instances qua QIDO-RS API
- FR4: Upload hình ảnh DICOM qua STOW-RS API
- FR5: Lưu trữ DICOM objects với đầy đủ metadata
- FR6: Quản lý AE Title - MyAe, OtherAe, AgentAe, AliasAe

**2. User Management (FR7-FR10)**
- FR7: Admin tạo, chỉnh sửa, xóa user accounts
- FR8: Admin gán roles cho users
- FR9: Admin cấu hình site access cho users
- FR10: User profile với thông tin cá nhân và credentials

**3. Authentication & Authorization (FR11-FR15)**
- FR11: Đăng nhập bằng username/password
- FR12: Hỗ trợ nhiều phương thức xác thực API (JWT, Basic Auth, Token, Session)
- FR14: RBAC - Role + Privilege based access control
- FR15: Restrict access theo site

**4. Study Management (FR16-FR19)**
- FR16: Lưu trữ và index DICOM studies với metadata
- FR17: Admin xem danh sách studies với filters
- FR18: Hỗ trợ delete/archival của studies
- FR19: Maintain data integrity của DICOM objects

**5. Search & Discovery (FR20-FR24)**
- FR20: Tìm kiếm theo Patient ID, Patient Name
- FR21: Tìm kiếm theo Study Date, Modality, Body Part
- FR22: Tìm kiếm theo Accession Number
- FR23: Search results phân trang và sortable
- FR24: Elasticsearch powered search với response time < 1s

**6. DICOM Support (FR25-FR30)**
- FR25: Hỗ trợ DICOM single-frame images
- FR26: Hỗ trợ DICOM multiframe images
- FR27: Truy xuất specific frames từ multiframe
- FR28: Streaming frames cho performance
- FR29: Validate DICOM conformance trước khi store
- FR30: Preserve all DICOM metadata

**7. Integration (FR40-FR45)**
- FR40: RIS gửi imaging order qua REST API hoặc HL7
- FR41: Modality kéo imaging order qua MWL (C-FIND)
- FR42: Modality gửi hình ảnh qua C-STORE hoặc STOW-RS
- FR43: PACS gửi notification đến RIS sau khi nhận hình ảnh
- FR44: HIS truy xuất hình ảnh qua WADO-RS
- FR45: Hỗ trợ HL7 v2.x messages

**8. Audit & Compliance (FR46-FR50)**
- FR46: Ghi log tất cả PHI access events
- FR47: Admin xem audit logs
- FR48: Đảm bảo HIPAA compliance
- FR49: Data retention policies
- FR50: Hỗ trợ DICOM Transfer Syntax tiêu chuẩn

**9. DICOM Network (FR51-FR57)**
- FR51: Cấu hình AE Title (MyAE)
- FR52: Cấu hình External AE (OtherAE)
- FR53: C-STORE SCP để nhận images từ modalities
- FR54: C-STORE SCU để gửi images
- FR55: C-FIND cho query
- FR56: C-MOVE cho retrieve
- FR57: C-ECHO để verify connection

**10. Storage Management (FR58-FR63)**
- FR58: Quản lý nhiều storage nodes
- FR59: Storage classes: ONLINE, NEARLINE, OFFLINE, CACHE
- FR60: Data replication với RF configurable
- FR61: Migrate dữ liệu giữa storage classes
- FR62: Storage speed monitoring
- FR63: Data cleanup theo retention policy

**11. Access Management & Rate Limiting (FR64-FR68)**
- FR64: Giới hạn upload requests per minute
- FR65: Giới hạn download requests per minute
- FR66: Giới hạn file uploads per day
- FR67: Monitoring khi approaching limits
- FR68: Reset counters theo configurable intervals

**12. Study Management (FR69-FR74)**
- FR69: Update study metadata
- FR70: Delete study (xóa vĩnh viễn)
- FR71: Move study to trash
- FR72: Restore study từ trash
- FR73: Modify study
- FR74: Detach hoặc merge studies

**13. DICOM Processing (FR75-FR79)**
- FR75: DICOM tag morphing/anonymization
- FR76: DICOM encryption/decryption
- FR77: Thumbnail generation
- FR78: Rendered image generation (JPEG, PNG)
- FR79: Bulk data extraction

**14. Export & Distribution (FR80-FR83)**
- FR80: Export studies đến external storage
- FR81: Gửi studies đến remote PACS
- FR82: Tạo public links
- FR83: CD/DVD burning

**15. Background Services (FR84-FR99)**
- FR84: Async queue cho STOW operations
- FR85: Auto-sync đến RIS
- FR86: Auto-notify RIS khi có series mới
- FR87: Auto-notify RIS khi có instance mới
- FR88: Thumbnail cache generation (async)
- FR89: Viewer cache generation (async)
- FR90: Frame offset caching cho multiframe
- FR91: Auto-copy files đến multiple locations
- FR92: File integrity verification (async)
- FR93: Number of series/instances sync (async)
- FR94: Restore study từ trash (async)
- FR95: Offline to nearline migration async
- FR96: Move to trash operation (async)
- FR97: Merge studies operation (async)
- FR98: Copy instance operation (async)
- FR99: Advance forward operation (async)

**16. Storage Processing (FR100-FR105)**
- FR100: Auto-move files to nearline storage
- FR101: Auto-remove online files sau khi có NEARLINE copy
- FR102: Storage rebalancing async
- FR103: File compression khi migrate sang NEARLINE
- FR104: DICOM to media (CD/DVD) writing
- FR105: Media label printing

**17. System Configuration (FR106-FR109)**
- FR106: Global settings management
- FR107: Per-site settings management
- FR108: DICOM server installation/configuration
- FR109: Service health monitoring

**18. Storage Lifecycle (FR110-FR113)**
- FR110: Auto-move từ ONLINE sang NEARLINE
- FR111: Auto-move từ NEARLINE sang OFFLINE
- FR112: Move giữa các storage locations
- FR113: Scheduled migration với configurable time windows

### NonFunctional Requirements

**Performance:**
- API response time trung bình < 500ms cho 95% requests
- DICOM retrieval (WADO-RS) < 3 giây cho images < 50MB
- DICOM store (STOW-RS) < 5 giây per series
- Elasticsearch search < 1s (P95)

**Scalability:**
- Hỗ trợ ≥ 10 sites đồng thời
- Xử lý ≥ 1000 studies/ngày
- Auto-scaling khi load > 80% capacity

**Reliability:**
- Uptime ≥ 99.95% (downtime < 4 giờ/năm)
- Backup tự động với RPO < 15 phút và RTO < 30 phút

**Security:**
- HIPAA Compliance
- DICOM 3.0 compliance
- Encryption at rest (AES-256) và in transit (TLS 1.3)
- RBAC với granular permissions

**Data Retention:**
- Medical Imaging Data: tối thiểu 7 năm
- Pediatric Studies: lưu đến khi đủ 25 tuổi
- Audit Logs: tối thiểu 7 năm
- Soft Delete Policy: xóa mềm trước khi xóa vĩnh viễn
- Backup Retention: tối thiểu 30 ngày

### Additional Requirements

**Từ Architecture:**
- PHP 8.0 / Slim 2.0 framework (brownfield - không thay đổi)
- MySQL per-tenant database model
- Elasticsearch cho full-text search
- Kafka cho async processing
- AWS S3 / Ceph cho object storage
- Redis cho caching và distributed locks
- Multi-tenant routing
- JWT + API Key authentication

**Từ UX Design:**
- Target Users: PACS Admin (web portal)
- Emotional Goal: Tự tin và yên tâm - "State is visible"
- Design: Incremental improvements trên hệ thống hiện tại
- Visual feedback cho async operations
- Status indicators (Online/Offline, Synced/Unsynced)
- Desktop-only Admin Portal

### FR Coverage Map

| Epic | FR Coverage |
|------|-------------|
| Epic 1: Core DICOM Services | FR1-FR6, FR25-FR30, FR51-FR57 |
| Epic 2: User & Access Management | FR7-FR15, FR64-FR68 |
| Epic 3: Study Management & Search | FR16-FR24, FR69-FR74 |
| Epic 4: Integration | FR40-FR45, FR124-FFR127 (MWL) |
| Epic 5: Storage Management | FR58-FR63, FR100-FR105, FR110-FR116 |
| Epic 6: Audit & Compliance | FR46-FFR50 |
| Epic 7: Background Services | FR84-FR99 |
| Epic 8: Admin Portal (UX Improvements) | UX improvements: Status indicators, Visual feedback |
| Epic 9: Media & Attachment | FR31-FR39b |
| Epic 10: DICOM Processing | FR75-FR79 |
| Epic 11: Export & Distribution | FR80-FR83 |
| Epic 12: System Config & Monitoring | FR106-FR109, FR117-FFR123 |
| Epic 13: Multi-Site & Multi-Zone | FR128-FR150 |
| Epic 14: Full Admin UI | FR151-FR175 |

## Epic List

Epic 1: Core DICOM Services
Epic 2: User & Access Management
Epic 3: Study Management & Search
Epic 4: Integration (RIS/HIS/Modality)
Epic 5: Storage Management
Epic 6: Audit & Compliance
Epic 7: Background Services
Epic 8: Admin Portal UX Improvements
Epic 9: Media & Attachment Management
Epic 10: DICOM Processing & Transformation
Epic 11: Export & Distribution
Epic 12: System Configuration & Monitoring
Epic 13: Multi-Site & Multi-Zone Architecture
Epic 14: Full Admin UI Implementation

---

<!-- Epics and stories will be populated in subsequent steps -->

## Epic 1: Core DICOM Services

**Goal:** Cung cấp các DICOM services cơ bản cho hệ thống PACS

### Story 1.1: C-STORE SCP Implementation

As a Modality,
I want gửi hình ảnh DICOM vào PACS qua C-STORE SCP,
So that hệ thống có thể lưu trữ và quản lý hình ảnh y tế.

**Acceptance Criteria:**

**Given** Modality đã kết nối mạng với PACS
**When** Modality gửi DICOM instances qua C-STORE SCP
**Then** PACS nhận và validate DICOM P10 format
**And** Lưu trữ file vào storage system
**And** Trả về C-STORE-RSP thành công

### Story 1.2: WADO-RS Implementation

As a DICOM Viewer,
I want truy xuất hình ảnh DICOM qua WADO-RS API,
So that hiển thị hình ảnh cho bác sĩ chẩn đoán.

**Acceptance Criteria:**

**Given** User đã xác thực và có quyền truy cập study
**When** Gọi API WADO-RS để lấy study/series/instance
**Then** Trả về DICOM objects hoặc rendered images
**And** Response time < 3 giây cho images < 50MB

### Story 1.3: QIDO-RS Implementation

As a User,
I want tìm kiếm studies qua QIDO-RS API,
So that tìm nhanh hình ảnh cần thiết.

**Acceptance Criteria:**

**Given** User đã xác thực
**When** Gọi QIDO-RS với các filters (PatientID, StudyDate, Modality...)
**Then** Trả về danh sách studies matching criteria
**And** Response time < 1 giây (P95)

### Story 1.4: STOW-RS Implementation

As a Technician,
I want upload hình ảnh DICOM qua STOW-RS API,
So that lưu trữ hình ảnh vào hệ thống.

**Acceptance Criteria:**

**Given** User upload DICOM files qua STOW-RS
**When** Validate Content-Type và DICOM conformance
**Then** Lưu vào storage system
**And** Index metadata vào Elasticsearch
**And** Trigger async notifications nếu có

### Story 1.5: AE Title Management

As an Admin,
I want quản lý AE Titles (MyAE, OtherAE, AgentAE, AliasAE),
So that cấu hình kết nối DICOM với external systems.

**Acceptance Criteria:**

**Given** Admin truy cập Admin Portal
**When** CRUD các AE Title configurations
**Then** Lưu vào database
**And** Áp dụng cho DICOM operations tương ứng

---

## Epic 2: User & Access Management

**Goal:** Quản lý users, roles, permissions và authentication

### Story 2.1: User Account Management

As an Admin,
I want tạo, sửa, xóa user accounts,
So that quản lý người dùng hệ thống.

**Acceptance Criteria:**

**Given** Admin có quyền manageUser
**When** Thực hiện CRUD operations trên user accounts
**Then** Cập nhật database
**And** Soft delete khi xóa

### Story 2.2: Role Management

As an Admin,
I want tạo và gán roles cho users,
So that phân quyền truy cập hệ thống.

**Acceptance Criteria:**

**Given** Admin có quyền manageRole
**When** Tạo roles và gán users
**Then** Lưu role-user mappings trong database
**And** Support public roles (global) và private roles (per-site)

### Story 2.3: Authentication System

As a User,
I want đăng nhập vào hệ thống,
So that truy cập các chức năng được phép.

**Acceptance Criteria:**

**Given** User nhập username/password
**When** Validate credentials với BCrypt hash
**Then** Tạo session hoặc JWT token
**And** Brute force protection sau 10 lần sai

### Story 2.4: RBAC Implementation

As a System,
I want kiểm tra quyền truy cập dựa trên roles và privileges,
So that đảm bảo security.

**Acceptance Criteria:**

**Given** User gọi API endpoint
**When** Check privilege/role required
**Then** Allow nếu user có quyền
**And** Deny nếu không có quyền
**And** Fullcontrol bypasses all checks

### Story 2.5: Site-based Access Control

As an Admin,
I want giới hạn user truy cập certain sites,
So that đảm bảo data isolation giữa các sites.

**Acceptance Criteria:**

**Given** User thuộc nhiều sites
**When** Gọi API với siteID
**Then** Check user có quyền truy cập site đó
**And** Fullcontrol bypasses site restriction

---

## Epic 3: Study Management & Search

**Goal:** Quản lý studies và tìm kiếm hiệu quả

### Story 3.1: Study Storage & Indexing

As a System,
I want lưu trữ và index DICOM studies,
So that có thể truy xuất nhanh.

**Acceptance Criteria:**

**Given** DICOM instances được upload hoặc nhận
**When** Lưu vào MySQL/Cassandra/Elasticsearch
**Then** Sync metadata async qua Kafka
**And** Maintain referential integrity

### Story 3.2: Study Search

As a User,
I want tìm kiếm studies với nhiều criteria,
So that tìm nhanh study cần thiết.

**Acceptance Criteria:**

**Given** User nhập search criteria
**When** Query Elasticsearch với filters
**Then** Trả về results trong < 1 giây
**And** Support pagination và sorting

### Story 3.3: Study Delete & Trash

As an Admin,
I want xóa studies và restore từ trash,
So that quản lý dữ liệu.

**Acceptance Criteria:**

**Given** Admin thực hiện delete/restore
**When** Move to trash hoặc hard delete
**Then** Update status trong database
**And** Audit log ghi lại action

### Story 3.4: Study Metadata Update

As an Admin,
I want cập nhật study metadata,
So that sửa thông tin bệnh nhân.

**Acceptance Criteria:**

**Given** Admin gọi API update metadata
**When** Validate required fields
**Then** Update database và Elasticsearch
**And** Audit log ghi lại thay đổi

---

## Epic 4: Integration (RIS/HIS/Modality)

**Goal:** Tích hợp với HIS, RIS và Modality devices

### Story 4.1: RIS Order Integration

As a RIS,
I want gửi imaging orders đến PACS,
So that bác sĩ có thể chỉ định chụp.

**Acceptance Criteria:**

**Given** RIS gọi REST API hoặc gửi HL7 message
**When** PACS nhận và validate order
**Then** Tạo worklist entry
**And** Return confirmation

### Story 4.2: Modality Worklist (MWL)

As a Modality,
I want query danh sách lệnh chụp từ PACS,
So that biết được chỉ định cho bệnh nhân nào.

**Acceptance Criteria:**

**Given** Modality query MWL qua C-FIND
**When** PACS nhận C-FIND request
**Then** Trả về danh sách worklist entries
**And** Filter theo AE Title, ngày, PatientID

### Story 4.3: RIS Notification

As a RIS,
I want nhận notification khi có hình ảnh mới,
So that biết khi nào cần truy xuất hình ảnh.

**Acceptance Criteria:**

**Given** PACS nhận đủ hình ảnh cho study
**When** Trigger notification đến RIS callback URL
**Then** Send payload với study metadata
**And** Retry mechanism cho failed notifications

### Story 4.4: HIS Image Access

As a HIS,
I want truy xuất hình ảnh qua WADO-RS,
So that hiển thị trong EMR interface.

**Acceptance Criteria:**

**Given** HIS xác thực và có quyền
**When** Gọi WADO-RS API
**Then** Return DICOM data hoặc rendered images
**And** Audit log ghi lại PHI access

---

## Epic 5: Storage Management

**Goal:** Quản lý storage nodes, classes và lifecycle

### Story 5.1: Storage Node Management

As an Admin,
I want thêm, sửa, xóa storage nodes,
So that cấu hình hệ thống lưu trữ.

**Acceptance Criteria:**

**Given** Admin quản lý storage
**When** CRUD operations trên storage nodes
**Then** Update database
**And** Rebalance nếu cần

### Story 5.2: Storage Classes

As a System,
I want hỗ trợ nhiều storage classes (ONLINE, NEARLINE, OFFLINE),
So that tối ưu chi phí và performance.

**Acceptance Criteria:**

**Given** Storage nodes được cấu hình
**When** Instance stored
**Then** Save to appropriate storage class
**And** Maintain replication factor

### Story 5.3: Data Replication

As a System,
I want replicate data across storage nodes,
So that đảm bảo redundancy.

**Acceptance Criteria:**

**Given** Replication Factor configured per class
**When** Storing instance
**Then** Copy đến RF nodes
**And** Track replication status

### Story 5.4: Storage Migration

As an Admin,
I want migrate data giữa storage tiers,
So that quản lý storage hiệu quả.

**Acceptition Criteria:**

**Given** Admin trigger migration
**When** Move storage operation
**Then** Copy data to destination
**And** Update database
**And** Optional: remove source

### Story 5.5: Storage Monitoring

As an Admin,
I want monitor storage performance,
So that biết tình trạng hệ thống.

**Acceptance Criteria:**

**Given** Admin truy cập monitoring
**When** Run speed test
**Then** Return read/write speeds
**And** Display capacity usage

---

## Epic 6: Audit & Compliance

**Goal:** Đảm bảo HIPAA compliance và audit trail

### Story 6.1: Audit Logging

As a System,
I want ghi log tất cả PHI access,
So that comply với HIPAA.

**Acceptance Criteria:**

**Given** User access PHI
**When** Action performed
**Then** Log timestamp, userID, action, resource
**And** Store in audit log table

### Story 6.2: Audit Log Viewing

As an Admin,
I want xem và filter audit logs,
So that theo dõi ai đã truy cập dữ liệu.

**Acceptance Criteria:**

**Given** Admin truy cập audit logs
**When** Query với filters
**Then** Return matching entries
**And** Support pagination

### Story 6.3: Data Retention

As a System,
I want enforce data retention policies,
So that comply với quy định.

**Acceptance Criteria:**

**Given** Retention period configured
**When** Background service runs
**Then** Clean up old data theo policy
**And** Support soft delete trước hard delete

---

## Epic 7: Background Services

**Goal:** Xử lý asynchronous operations một cách minh bạch cho Admin

### Story 7.1: Async STOW Processing

As an Admin,
I want STOW uploads complete in background,
So that không phải chờ đợi và có thể tiếp tục công việc khác.

**Acceptance Criteria:**

**Given** User upload DICOM via STOW-RS
**When** Request submitted
**Then** Process in background
**And** User có thể check status
**And** Notification khi complete

### Story 7.2: RIS Sync

As an Admin,
I want RIS được sync tự động sau khi có study mới,
So that không cần thao tác thủ công.

**Acceptance Criteria:**

**Given** Study completed
**When** Trigger RIS sync
**Then** Queue notification
**And** Auto retry on failure
**And** View sync status trong UI

### Story 7.3: Thumbnail Generation

As a Radiologist,
I want thumbnails sẵn sàng khi tôi xem study,
So that không phải chờ loading.

**Acceptance Criteria:**

**Given** Instance stored
**When** Thumbnail not exists
**Then** Auto-generate in background
**And** Cache for fast retrieval

### Story 7.4: Storage Migration Async

As an Admin,
I want migration chạy ngầm,
So that có thể monitor progress và tiếp tục công việc khác.

**Acceptance Criteria:**

**Given** Migration triggered
**When** Queue to Kafka
**Then** Process in background
**And** View progress in UI
**And** Notification khi complete

### Story 7.5: Offline to Nearline Restore

As a Radiologist,
I want dữ liệu offline có thể được restore nhanh,
So that không mất thời gian chờ đợi không cần thiết.

**Acceptance Criteria:**

**Given** Request for offline data
**When** Trigger restore
**Then** Extract from TAR
**And** Move to nearline

---

## Epic 8: Admin Portal UX Improvements

**Goal:** Cải thiện UX cho Admin Portal (brownfield improvements)

### Story 8.1: Status Indicators

As an Admin,
I want thấy rõ trạng thái của systems,
So that biết hệ thống đang hoạt động bình thường hay có vấn đề.

**Acceptance Criteria:**

**Given** Admin xem dashboard
**When** Display status badges
**Then** Show Online/Offline/Synced/Unsynced
**And** Use semantic colors (green/red/yellow)

### Story 8.2: Visual Feedback for Async Operations

As an Admin,
I want biết tiến độ của async operations,
So that không phải đoán hệ thống đang làm gì.

**Acceptance Criteria:**

**Given** Admin trigger move storage/import
**When** Operation running
**Then** Show spinner/progress indicator
**And** Show success/failure notification

### Story 8.3: Error Messages Improvement

As an Admin,
I want nhận thông báo lỗi rõ ràng,
So that biết vấn đề và cách xử lý.

**Acceptance Criteria:**

**Given** Operation fails
**When** Display error
**Then** Show error message + cause + resolution
**And** Use toast notifications

### Story 8.4: Quick Filters

As an Admin,
I want lọc nhanh studies,
So that tìm thấy thông tin nhanh hơn.

**Acceptance Criteria:**

**Given** Admin xem study list
**When** Click quick filter (Modality, Date)
**Then** Filter results immediately
**And** Show active filters

---

## Epic 9: Media & Attachment Management

**Goal:** Quản lý tài liệu và media đính kèm cho studies

### Story 9.1: Upload Documents & Media

As a Technician,
I want upload tài liệu và media files đính kèm vào studies,
So that lưu trữ các file bổ sung như phiếu chẩn đoán, video.

**Acceptance Criteria:**

**Given** User có quyền upload
**When** Upload file (PDF, DOCX, PNG, JPG, video)
**Then** Detect MIME type từ content
**And** Link file với study qua StudyInstanceUID hoặc AccessionNumber
**And** Lưu metadata vào database

### Story 9.2: Download & Stream Media

As a User,
I want download hoặc stream media files,
So that xem tài liệu đính kèm.

**Acceptance Criteria:**

**Given** User có quyền truy cập study
**When** Request download/stream
**Then** Return file với correct Content-Type
**And** Support video streaming với HTTP Range

### Story 9.3: Media Trash & Restore

As an Admin,
I want xóa và restore media files,
So that quản lý tài liệu an toàn.

**Acceptance Criteria:**

**Given** Admin xóa media file
**When** Soft delete
**Then** Move to trash
**And** Có thể restore từ trash

---

## Epic 10: DICOM Processing & Transformation

**Goal:** Xử lý và chuyển đổi DICOM objects

### Story 10.1: DICOM Tag Morphing/Anonymization

As an Admin,
I want áp dụng DICOM tag morphing rules,
So that ẩn thông tin bệnh nhân khi cần thiết.

**Acceptance Criteria:**

**Given** Morphing rules configured
**When** DICOM stored hoặc retrieved
**Then** Apply tag transformation theo rules
**And** Support both incoming và outgoing directions

### Story 10.2: Thumbnail Generation

As a User,
I want xem thumbnails của studies,
So that nhanh chóng preview hình ảnh.

**Acceptance Criteria:**

**Given** DICOM instance stored
**When** Thumbnail requested
**Then** Generate JPEG thumbnail
**And** Cache for fast retrieval

### Story 10.3: Rendered Image Generation

As a User,
I want xem rendered images (JPEG/PNG),
So that hiển thị trong viewer không hỗ trợ DICOM.

**Acceptance Criteria:**

**Given** DICOM instance exists
**When** Request rendered image
**Then** Generate JPEG/PNG
**And** Support multiple resolution options

---

## Epic 11: Export & Distribution

**Goal:** Xuất và phân phối studies

### Story 11.1: Export to External Storage

As an Admin,
I want export studies sang external storage,
So that backup hoặc transfer dữ liệu.

**Acceptance Criteria:**

**Given** Admin select studies
**When** Trigger export
**Then** Copy files to destination
**And** Verify integrity

### Story 11.2: Send to Remote PACS

As an Admin,
I want gửi studies đến remote PACS,
So that chia sẻ với hệ thống khác.

**Acceptance Criteria:**

**Given** Remote PACS configured
**When** Select studies to send
**Then** Transfer via C-MOVE hoặc STOW
**And** Track transfer status

### Story 11.3: Public Link Sharing

As a User,
I want tạo public link để share studies,
So that cung cấp access tạm thời không cần login.

**Acceptance Criteria:**

**Given** User create public link
**When** Configure expiration và security options
**Then** Generate unique URL
**And** Support optional password/CAPTCHA

---

## Epic 12: System Configuration & Monitoring

**Goal:** Cấu hình và giám sát hệ thống

### Story 12.1: Global Settings Management

As an Admin,
I want quản lý global settings,
So that cấu hình system-wide behavior.

**Acceptance Criteria:**

**Given** Admin access settings
**When** View/update global config
**Then** Changes apply to all sites
**And** Audit log ghi lại changes

### Story 12.2: Per-Site Settings Management

As an Admin,
I want quản lý per-site settings,
So that customize behavior per site.

**Acceptance Criteria:**

**Given** Admin access site settings
**When** View/update site-specific config
**Then** Changes apply to selected site only
**And** Override global settings where applicable

### Story 12.3: Health Monitoring

As an Admin,
I want xem system health status,
So that biết tình trạng hệ thống.

**Acceptance Criteria:**

**Given** Admin view dashboard
**When** Check health indicators
**Then** Show service status (Online/Offline)
**And** Display key metrics

---

## Epic 13: Multi-Site & Multi-Zone Architecture

**Goal:** Hỗ trợ nhiều sites và zones

### Story 13.1: Multi-Site Support

As an Admin,
I want quản lý nhiều sites (≥10),
So that deploy PACS cho nhiều bệnh viện.

**Acceptance Criteria:**

**Given** Admin create new site
**When** Configure site settings, AE Title, storage
**Then** Site isolated from other sites
**And** Support ≥10 concurrent sites

### Story 13.2: Per-Site Configuration

As an Admin,
I want cấu hình riêng cho từng site,
So that customize theo nhu cầu từng bệnh viện.

**Acceptance Criteria:**

**Given** Admin configure site
**When** Set AE Title, storage, integration
**Then** Configuration per-site
**And** Independent from other sites

### Story 13.3: Multi-Zone Architecture

As an Admin,
I want quản lý multiple zones,
So that distribute data across locations.

**Acceptance Criteria:**

**Given** Admin configure zones
**When** Set up master/child zones
**Then** Support zone-based storage
**And** Cross-zone replication

### Story 13.4: Database Per Site (Tenant Isolation)

As a System,
I want maintain database per site,
So that đảm bảo data isolation.

**Acceptance Criteria:**

**Given** Multi-site deployment
**When** Each site operates
**Then** Separate database/schema per tenant
**And** Support horizontal sharding

---

## Epic 14: Full Admin UI Implementation

**Goal:** Triển khai đầy đủ Admin Portal UI

### Story 14.1: Study Management UI

As an Admin,
I want quản lý studies qua UI,
So that view, search, edit, delete studies.

**Acceptance Criteria:**

**Given** Admin access study list
**When** View/search/edit/delete studies
**Then** Full CRUD operations
**And** Support filters, pagination

### Story 14.2: Storage Management UI

As an Admin,
I want quản lý storage qua UI,
So that configure nodes, classes, monitoring.

**Acceptance Criteria:**

**Given** Admin access storage config
**When** CRUD storage nodes
**Then** View capacity và performance
**And** Configure replication

### Story 14.3: AE Title Management UI

As an Admin,
I want quản lý AE Titles qua UI,
So that configure DICOM connections.

**Acceptance Criteria:**

**Given** Admin access AE config
**When** Manage MyAE, OtherAE, AgentAE, AliasAE
**Then** Full CRUD operations
**And** Test connection capability

### Story 14.4: RIS Integration UI

As an Admin,
I want quản lý RIS integration qua UI,
So that configure connections và notifications.

**Acceptance Criteria:**

**Given** Admin access RIS config
**When** Configure RIS connection
**Then** Set callback URL, headers
**And** View sync status

### Story 14.5: Worklist (MWL) Management UI

As an Admin,
I want quản lý Worklist items qua UI,
So that view và manage MWL entries.

**Acceptance Criteria:**

**Given** Admin access MWL
**When** View/insert/modify worklist
**Then** Full CRUD (for local mode)
**And** Configure proxy/local mode

### Story 14.6: DICOM Tag Morphing UI

As an Admin,
I want quản lý morphing rules qua UI,
So that configure anonymization rules.

**Acceptance Criteria:**

**Given** Admin access morphing config
**When** Create/edit/delete rules
**Then** Visual rule editor
**And** Preview before apply

### Story 14.7: Access Management UI

As an Admin,
I want quản lý rate limiting qua UI,
So that configure và monitor access.

**Acceptance Criteria:**

**Given** Admin access access management
**When** Configure limits, view counters
**Then** Real-time monitoring dashboard
**And** Enable/disable per site

### Story 14.8: Public Link Management UI

As an Admin,
I want quản lý public links qua UI,
So that create và manage share links.

**Acceptance Criteria:**

**Given** Admin access public links
**When** Create/edit/delete links
**Then** View active/expired status
**And** Configure security options

### Story 14.9: Queue Monitoring UI

As an Admin,
I want monitor Kafka queues qua UI,
So that track async operations.

**Acceptance Criteria:**

**Given** Admin access queue monitor
**When** View topics, messages, lag
**Then** Real-time status
**And** Retry failed messages

### Story 14.10: System Logs UI

As an Admin,
I want xem system logs qua UI,
So that troubleshoot issues.

**Acceptance Criteria:**

**Given** Admin access logs
**When** View Apache, PACS, DICOM logs
**Then** Filter by date, search
**And** Real-time tail capability

### Story 14.11: Study Logs UI

As an Admin,
I want xem audit logs qua UI,
So that compliance với HIPAA.

**Acceptance Criteria:**

**Given** Admin access audit logs
**When** View study audit trail
**Then** Filter by study, date
**And** Download log files

### Story 14.12: AI Integration UI

As an Admin,
I want quản lý AI tools qua UI,
So that configure AI integration.

**Acceptance Criteria:**

**Given** Admin access AI config
**When** Add/edit AI tools
**Then** Track sync status
**And** Configure anonymization

### Story 14.13: Reports/Analytics UI

As an Admin,
I want xem reports và analytics,
So that understand system usage.

**Acceptance Criteria:**

**Given** Admin access reports
**When** View dashboard, storage analytics
**Then** Show metrics by modality, site
**And** Filter by date range

### Story 14.14: Tool Execution UI

As an Admin,
I want chạy tools qua UI,
So that perform maintenance operations.

**Acceptance Criteria:**

**Given** Admin access tools
**When** Execute storage tools, sync tools
**Then** View progress
**And** See results/errors

---

**Confirm the Requirements are complete and correct to [C] continue:**
