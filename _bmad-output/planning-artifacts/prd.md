---
stepsCompleted: ["step-01-init", "step-02-discovery", "step-02b-vision", "step-02c-executive-summary", "step-03-success", "step-04-journeys", "step-05-domain", "step-06-innovation", "step-07-project-type", "step-08-scoping", "step-09-functional", "step-10-nonfunctional", "step-11-polish", "step-12-complete", "step-01b-continue", "step-01b-continue"]
inputDocuments: ["docs/index.md", "docs/project-overview.md", "docs/architecture.md", "docs/source-tree-analysis.md", "docs/data-models.md", "docs/api-contracts.md", "docs/development-guide.md"]
workflowType: 'prd'
documentCounts:
  briefs: 0
  research: 0
  brainstorming: 0
  projectDocs: 7
projectType: 'brownfield'
classification:
  projectType: 'api_backend + web_app'
  domain: 'healthcare'
  complexity: 'high'
  projectContext: 'brownfield'
---

# Product Requirements Document - pacs2

**Author:** USER
**Date:** 2026-03-08

## Executive Summary

### Tầm nhìn sản phẩm

**PACS2** là hệ thống **Picture Archiving and Communication System (PACS)** - Hệ thống Lưu trữ và Truyền thông Hình ảnh Y tế, được xây dựng để phục vụ các cơ sở y tế trong việc lưu trữ, truy xuất và xem hình ảnh y tế (X-quang, CT scan, MRI, siêu âm...) theo tiêu chuẩn quốc tế **DICOM (Digital Imaging and Communications in Medicine)**.

Hệ thống cung cấp các DICOM services chuẩn hóa:
- **WADO-RS**: Truy xuất DICOM qua web
- **QIDO-RS**: Dịch vụ truy vấn
- **STOW-RS**: Lưu trữ đối tượng DICOM
- **C-STORE SCP**: Hỗ trợ nhận hình ảnh qua network
- **HL7 Integration**: Tích hợp tin nhắn y tế

### Điều gì làm cho sản phẩm này đặc biệt

PACS2 là hệ thống PACS nội bộ phiên bản **1.10** - đã có mặt trong môi trường sản xuất. Hệ thống tích hợp đầy đủ stack công nghệ y tế hiện đại:
- Cloud Storage (AWS S3/Ceph)
- Elasticsearch cho tìm kiếm metadata nhanh
- Redis Caching cho hiệu suất cao
- Kafka cho xử lý bất đồng bộ

## Project Classification

| Thuộc tính | Giá trị |
|------------|---------|
| **Project Type** | API Backend + Web App |
| **Domain** | Healthcare (Medical Imaging) |
| **Complexity** | High |
| **Project Context** | Brownfield (existing system v1.10) |

## Success Criteria

### User Success

- Hệ thống cung cấp API (WADO-RS) để DICOM Viewer bên thứ 3 truy xuất hình ảnh DICOM trong vòng **< 3 giây** cho 95% yêu cầu
- Kỹ thuật viên có thể upload hình ảnh DICOM thành công với tỷ lệ **> 99.5%**
- Hệ thống thông báo lỗi rõ ràng, giúp người dùng biết vấn đề và cách khắc phục

### Business Success

- Hệ thống hỗ trợ **≥ 10 sites** (bệnh viện/chi nhánh) đồng thời
- Xử lý **≥ 1000 studies/ngày** mà không có performance degradation
- Thời gian downtime **< 4 giờ/năm** (99.95% uptime)
- Tích hợp thành công với **≥ 3 hệ thống HIS/RIS** bên ngoài
- Tuân thủ đầy đủ tiêu chuẩn DICOM 3.0 và HIPAA compliance

### Technical Success

- API response time trung bình **< 500ms** cho 95% requests
- DICOM retrieval (WADO-RS) **< 3 giây** cho images < 50MB
- DICOM store (STOW-RS) **< 5 giây** per series
- Hệ thống auto-scaling khi load > 80% capacity
- Backup tự động với RPO **< 15 phút** và RTO **< 30 phút**
- Audit logging đầy đủ cho tất cả PHI (Protected Health Information) access

### Measurable Outcomes

| Metric | Target | Timeline |
|--------|--------|----------|
| System Uptime | ≥ 99.95% | 12 tháng |
| Image Retrieval via WADO-RS | < 3s (P95) | 6 tháng |
| Daily Study Throughput | ≥ 1000 studies | 6 tháng |
| Successful Upload Rate | > 99.5% | 3 tháng |
| Multi-site Support | ≥ 10 sites | 12 tháng |
| API Response Time | < 500ms (P95) | 6 tháng |

**Ghi chú:** Image retrieval time là thời gian PACS phục vụ hình ảnh qua WADO-RS API cho DICOM Viewer bên thứ 3.

## Product Scope

### MVP - Minimum Viable Product

- DICOM WADO-RS, QIDO-RS, STOW-RS services cơ bản
- DICOM C-STORE SCP cho nhận hình ảnh từ modalities
- RESTful API cho RIS/HIS integration
- Authentication và authorization cơ bản
- Elasticsearch-powered search cho patient/study metadata
- Integration với 1 HIS/RIS đầu tiên
- Single site deployment

### Growth Features (Post-MVP)

- Multi-site deployment và data distribution
- HL7 v2.x integration (ADT, ORM messages)
- DICOM routing và auto-routing rules
- Advanced analytics dashboard
- Integration với AI/ML diagnostic tools

### Vision (Future)

- Cloud-native architecture với Kubernetes
- Cross-enterprise image sharing
- International expansion support
- Full DICOMweb compliance
- International expansion support (multi-language, multi-region)

## User Journeys

**Lưu ý:** PACS là hệ thống trung gian (backend). Luồng dữ liệu:

```
HIS → RIS → PACS → Modality (chỉ định)
Modality → PACS → RIS (thông báo có ảnh qua RESTful)
```

**DICOM Viewer là phần mềm bên thứ 3** (ngoài phạm vi dự án) - tích hợp với PACS qua API.

Các hệ thống tương tác với PACS:
- **RIS** (Radiology Information System)
- **HIS** (Hospital Information System)  
- **Modality** (Thiết bị chụp - CT, MRI, X-Ray, Ultrasound)
- **Admin** (Admin Portal)

---

### 1. RIS (Radiology Information System) - Imaging Order

**Actor:** Hệ thống RIS bên ngoài

**Tình huống:** Bác sĩ chỉ định chụp hình ảnh cho bệnh nhân, RIS gửi order đến PACS.

**Journey:**
1. **Nhận order** từ HIS (qua HL7 ORM message)
2. **Gửi imaging order** đến PACS qua REST API hoặc HL7
3. **PACS tiếp nhận** và chuyển đến Modality tương ứng
4. **Nhận thông báo** từ PACS khi có hình ảnh mới (RESTful webhook)
5. **Truy xuất hình ảnh** qua WADO-RS để hiển thị (qua DICOM Viewer bên thứ 3)

**Technical Requirements:**
- HL7 v2.x ORM messages (order)
- REST API cho order management
- Webhook/RESTful notification khi có image
- OAuth 2.0 authentication
- WADO-RS integration với DICOM Viewer

---

### 2. Modality (Thiết bị chụp) - Image Acquisition

**Actor:** Thiết bị chụp (CT, MRI, X-Ray, Ultrasound)

**Tình huống:** Sau khi chụp, thiết bị gửi hình ảnh DICOM vào PACS.

**Journey:**
1. **Nhận chỉ định** từ PACS (forwarded từ RIS)
2. **Thực hiện chụp** hình ảnh cho bệnh nhân
3. **Gửi hình ảnh** DICOM vào PACS qua DICOM C-STORE
4. **Nhận xác nhận** storage thành công (C-STORE-RSP)
5. **PACS tự động** gửi notification đến RIS (RESTful)

**Technical Requirements:**
- DICOM C-STORE SCU support
- DICOM validation
- Metadata extraction (Patient ID, Study Date, Modality)
- Checksum validation

---

### 3. PACS → RIS Notification

**Actor:** PACS System

**Tình huống:** Sau khi nhận hình ảnh từ Modality, PACS thông báo cho RIS.

**Journey:**
1. **Nhận hình ảnh** DICOM từ Modality (C-STORE)
2. **Validate** DICOM metadata và integrity
3. **Index** metadata vào Elasticsearch
4. **Gửi RESTful notification** đến RIS endpoint
5. **RIS tiếp nhận** và truy xuất hình ảnh qua WADO-RS (sang DICOM Viewer)

**Technical Requirements:**
- RESTful webhook/notification
- Retry mechanism cho failed notifications
- Audit logging

---

### 4. HIS (Hospital Information System) - View Results

**Actor:** Hệ thống HIS bên ngoài

**Tình huống:** Bác sĩ tại các khoa khác (không phải radiology) cần xem hình ảnh của bệnh nhân.

**Journey:**
1. **Gọi API** QIDO-RS để tìm kiếm studies theo Patient ID
2. **Nhận kết quả** danh sách studies
3. **Gọi API** WADO-RS để lấy hình ảnh thumbnail/preview
4. **Hiển thị** trong EMR/EHR interface (qua DICOM Viewer bên thứ 3)
5. **Xử lý lỗi** (authentication, authorization)

**Technical Requirements:**
- OAuth 2.0 / API Key authentication
- HL7 v2.x ADT messages
- Read-only access
- Audit logging cho PHI access

---

### 5. Admin (Admin Portal)

**Actor:** System Administrator

**Tình huống:** Admin cần quản lý users, roles, permissions, và cấu hình hệ thống PACS.

**Journey:**
1. **Đăng nhập** vào Admin Portal
2. **Quản lý users** - tạo, sửa, xóa user accounts
3. **Quản lý roles** - gán roles (Radiologist, Technician, Admin)
4. **Cấu hình sites** - quản lý site access
5. **Theo dõi logs** - xem audit logs, system logs
6. **Quản lý cấu hình** - storage settings, integration settings

**Technical Requirements:**
- Role-based access control (RBAC)
- Audit logging
- User management API
- Configuration management

---

### Journey Requirements Summary

| System | Actor Type | Vai trò | Priority |
|--------|-----------|---------|----------|
| RIS | External System | Send Order, Receive Notification, Retrieve Image | Must Have |
| Modality | Device | C-STORE SCU, Send Image | Must Have |
| HIS | External System | QIDO-RS, WADO-RS (read-only) | Must Have |
| DICOM Viewer | Third-party | Display Image (ngoài phạm vi) | External |
| Admin | Administrator | User Management, Configuration, Audit | Must Have |
| PACS→RIS | Internal | RESTful Notification, Webhook | Must Have |

### Cross-Cutting Requirements

- **Authentication**: SSO, OAuth 2.0, LDAP/Active Directory integration
- **Authorization**: RBAC (Role-Based Access Control), Site-based access
- **Audit Logging**: All PHI access must be logged
- **Performance**: < 3s image retrieval, < 500ms API response
- **Reliability**: 99.95% uptime, automated backup

## Domain-Specific Requirements

### Compliance & Regulatory

- **HIPAA Compliance**: Bảo vệ Protected Health Information (PHI) theo quy định HIPAA
- **DICOM 3.0**: Tuân thủ tiêu chuẩn quốc tế cho hình ảnh y tế
- **HL7 v2.x**: Tiêu chuẩn trao đổi tin nhắn y tế (ADT, ORM messages)
- **ISO 13482**: Medical device software quality standards (nếu applicable)

### Patient Safety Requirements

- **Patient-Study Association Accuracy**: Hệ thống phải đảm bảo độ chính xác 100% trong việc gán study vào đúng patient — wrong-patient assignment là lỗi lâm sàng nghiêm trọng
- **Image Integrity Verification**: Hệ thống phải xác minh tính toàn vẹn của DICOM files sau storage và retrieval (checksum validation)
- **Concurrent Modification Prevention**: Hệ thống phải ngăn chặn việc chỉnh sửa metadata study đồng thời bởi nhiều users (optimistic locking hoặc tương đương)
- **Audit Trail for Clinical Actions**: Mọi thao tác lâm sàng (xem, chỉnh sửa, xóa study) phải được ghi vào immutable audit log với timestamp và user identity

### Data Retention Policy

- **Medical Imaging Data**: Dữ liệu hình ảnh y tế phải được lưu trữ tối thiểu **7 năm** kể từ ngày chụp (theo quy định y tế Việt Nam)
- **Pediatric Studies**: Studies của bệnh nhân nhi phải được lưu trữ đến khi bệnh nhân đủ **25 tuổi** (18 tuổi + 7 năm)
- **Audit Logs**: PHI access logs phải được lưu trữ tối thiểu **7 năm**
- **Soft Delete Policy**: Dữ liệu bị xóa phải vào trash/bin trước khi xóa vĩnh viễn; hard delete chỉ được thực hiện sau khi qua retention period
- **Backup Retention**: Backup copies phải được giữ tối thiểu **30 ngày** với off-site storage

### DICOM Transfer Syntax Support

Hệ thống phải hỗ trợ các DICOM Transfer Syntaxes sau đây để đảm bảo khả năng tương thích với đa dạng các thiết bị hình ảnh (modalities) và tối ưu hóa truyền tải qua mạng Cloud:

| Loại | Transfer Syntax Name | UID | Ưu tiên | Ghi chú |
| :--- | :--- | :--- | :--- | :--- |
| **Mặc định** | Explicit VR Little Endian | 1.2.840.10008.1.2.1 | P0 | Cú pháp mặc định, yêu cầu bắt buộc |
| **Mặc định** | Implicit VR Little Endian | 1.2.840.10008.1.2 | P0 | Cú pháp mặc định truyền thống của DICOM |
| **Nén** | JPEG 2000 Lossless Only | 1.2.840.10008.1.2.4.90 | P0 | Tối ưu cho lưu trữ Cloud và xem ảnh chẩn đoán từ xa |
| **Nén** | JPEG 2000 (Lossy) | 1.2.840.10008.1.2.4.91 | P1 | Sử dụng cho việc hiển thị nhanh (preview) |
| **Nén** | RLE Lossless | 1.2.840.10008.1.2.5 | P1 | Phổ biến cho các loại ảnh siêu âm, nội soi |
| **Nén** | JPEG Baseline (Process 1) | 1.2.840.10008.1.2.4.50 | P1 | Đảm bảo tương thích với các thiết bị đời cũ |

### Technical Constraints

- **Bảo mật**: Encryption at rest (AES-256) và in transit (TLS 1.3)
- **Audit Logging**: Tất cả truy cập PHI phải được ghi log với timestamp, user, action
- **Access Control**: RBAC với granular permissions, site-based access control
- **Data Retention**: Tuân thủ quy định lưu trữ hồ sơ y tế (tối thiểu 5-10 năm)
- **Patient Safety**: Đảm bảo integrity của hình ảnh, không có data corruption

### Integration Requirements

- **Modality Integration**: Hỗ trợ kết nối với các thiết bị chụp (CT, MRI, X-Ray, Ultrasound)
- **HIS/RIS Integration**: Tích hợp với Hospital Information System và Radiology Information System
- **EMR/EHR Integration**: Electronic Medical Records integration
- **PACS Integration**: Kết nối với PACS systems khác qua DICOM

### Risk Mitigations

- **Data Loss Prevention**: Backup strategy với RPO < 15 phút, off-site backup
- **Disaster Recovery**: RTO < 30 phút, multi-region deployment
- **Uptime Requirements**: 99.95% uptime với SLA cho hệ thống y tế
- **Security Monitoring**: Real-time threat detection và incident response
- **Data Integrity**: Checksum validation cho tất cả DICOM transfers

## API Backend Specific Requirements

### API Endpoints Specification

| Service | Method | Endpoint | Description |
|---------|--------|----------|-------------|
| WADO-RS | GET | `/rest/:aet/rs/studies/:studyUID` | Retrieve DICOM study |
| WADO-RS | GET | `/rest/:aet/rs/studies/:studyUID/series/:seriesUID` | Retrieve series |
| WADO-RS | GET | `/rest/:aet/rs/studies/:studyUID/series/:seriesUID/instances/:instUID` | Retrieve instance |
| QIDO-RS | GET | `/:siteID/rest/:aet/rs/studies` | Query studies |
| QIDO-RS | GET | `/:siteID/rest/:aet/rs/studies/:studyUID/series` | Query series |
| STOW-RS | POST | `/:siteID/rest/:aet/rs/studies` | Store DICOM objects |
| C-STORE SCP | * | DICOM protocol | Receive from modalities |
| Admin API | GET/POST/PUT/DELETE | `/api/v1/*` | User, config, monitoring |

### Authentication Model

- **API Authentication**: OAuth 2.0 với JWT tokens
- **Token Expiration**: Access token 15 phút, Refresh token 7 ngày
- **SSO Integration**: LDAP/Active Directory cho internal users
- **API Key**: Cho system-to-system integration (HIS/RIS)

### Data Formats

- **Request/Response**: JSON (application/json)
- **DICOM**: Binary DICOM format qua HTTP
- **Compression**: Gzip cho JSON responses, DICOM lossless/lossy compression

### Rate Limiting

- **Default**: 1000 requests/phút per user
- **Tiered**: Có thể configure theo role
- **Headers**: X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset

### API Versioning

- **Strategy**: URL-based versioning (`/api/v1/`, `/api/v2/`)
- **Deprecation**: 6 tháng notice trước khi deprecate
- **Backward Compatibility**: Tối thiểu 2 versions được support

## Project Scoping & Phased Development

### MVP Strategy & Philosophy

**MVP Approach:** Problem-Solving MVP
- Tập trung vào giải quyết vấn đề cốt lõi: lưu trữ và truy xuất hình ảnh DICOM
- Cung cấp API cho DICOM Viewer bên thứ 3 tích hợp
- Chứng minh giá trị cơ bản của hệ thống trước khi mở rộng

**Resource Requirements:**
- Core Team: 4-6 developers
- Roles: 2 Backend (DICOM), 1 Integration, 1 DevOps, 1 PM/Lead
- Timeline: 6-9 tháng cho MVP

### MVP Feature Set (Phase 1)

**Core User Journeys Supported:**
1. Radiologist xem và phân tích hình ảnh DICOM
2. Technician upload hình ảnh từ modalities
3. Admin quản lý users và cấu hình hệ thống

**Must-Have Capabilities:**
- DICOM WADO-RS, QIDO-RS, STOW-RS services
- DICOM C-STORE SCP cho nhận hình ảnh từ thiết bị
- RESTful API cho RIS/HIS integration
- Authentication & Authorization (OAuth 2.0, RBAC)
- Elasticsearch-powered search
- Single site deployment
- Basic audit logging

### Post-MVP Features

**Phase 2 (Growth) - 6-12 tháng sau MVP:**
- Multi-site deployment và data distribution
- HL7 v2.x integration (ADT, ORM messages)
- DICOM routing và auto-routing rules
- Advanced analytics dashboard
- Integration với AI/ML diagnostic tools

**Phase 3 (Expansion) - 12+ tháng:**
- Cloud-native architecture với Kubernetes
- Cross-enterprise image sharing
- Full DICOMweb compliance
- International expansion support

### Risk Mitigation Strategy

**Technical Risks:**
- DICOM compliance complexity → Sử dụng proven DICOM libraries
- Performance requirements → Implement caching (Redis), async processing (Kafka)
- Multi-modality integration → Start với common modalities (CT, MRI, X-Ray)

**Market Risks:**
- Competition với existing PACS vendors → Focus vào integration requirements cụ thể của thị trường nội địa
- Changing regulations → Monitor HIPAA/DICOM updates, design for compliance flexibility

**Resource Risks:**
- Smaller team than planned → Prioritize core features, consider outsourcing non-critical components
- Timeline delays → Define MVP scope strictly, defer non-essentials to Phase 2

## Functional Requirements

### 1. DICOM Services

- FR1: Nhận hình ảnh DICOM từ modalities qua C-STORE SCP (module `pacs/dicomnet` — compiled binary ELF server)

- FR2: Truy xuất hình ảnh DICOM qua WADO-RS API — xem chi tiết tại FR44a–FR44i

- FR3: Truy vấn studies/series/instances qua QIDO-RS API (module `pacs/qidors`)
  - FR3a: Query studies: `GET (/:siteID)/rest/:aet(/rs)/studies` — filter đa điều kiện
  - FR3b: Query series theo study: `GET .../studies/:studyIUID/series`
  - FR3c: Query instances theo study+series: `GET .../studies/:studyIUID/series/:seriesIUID/instances`
  - FR3d: Query instances theo study: `GET .../studies/:studyIUID/instances`
  - FR3e: Query tất cả instances: `GET .../instances`
  - FR3f: Query tất cả series: `GET .../series`
  - FR3g: Query trash — studies/series/instances trong bin: `GET .../studyTrash`, `.../studyTrash/:studyUID/series`, `.../studyTrash/:studyUID/series/:seriesUID/instances`
  - FR3h: siteID optional trong URL — resolve từ AE Title nếu không có

- FR4: Upload hình ảnh DICOM qua STOW-RS API (module `pacs/stow`)
  - FR4a: Endpoint: `POST/PUT (/:siteID)/rest/:aet(/rs)/studies` — hỗ trợ CORS (OPTIONS preflight)
  - FR4b: Content-Type hỗ trợ: `application/dicom`, `multipart/related`, `application/octet-stream`
  - FR4c: AliasAe support — AE alias tự động resolve sang MyAe + siteID thực
  - FR4d: Chọn storage class khi upload: `?storageClass=ONLINE|NEARLINE`
  - FR4e: `stowMode` — điều khiển hành vi ghi (write all, v.v.)
  - FR4f: `newAttributes` — ghi đè DICOM tags khi store
  - FR4g: DicomTagMorphing tích hợp — áp dụng morphing rules theo sourceAet trước khi lưu
  - FR4h: AccessManagement check — từ chối upload nếu site bị rate-limit disable

- FR5: Lưu trữ DICOM objects với đầy đủ metadata trên đa hệ thống:
  - FR5a: Hệ thống lưu trữ study/series/instance records _(impl: MySQL — StudyMapper, SeriesMapper, InstanceMapper)_
  - FR5b: Hệ thống lưu trữ instance data _(impl: Cassandra — cql tables)_
  - FR5c: Hệ thống duy trì full-text search index _(impl: Elasticsearch — StudyElasticSearchMapper)_
  - FR5d: StudyLog + FileLog — audit trail ghi nhận từng file được lưu

- FR6: Quản lý AE Title — nhiều loại AE endpoint per site (module `pacs/ae`)
  - FR6a: **MyAe** — AE Title của chính site (`GET/POST/PUT/DELETE /:siteID/rest/myAe`)
  - FR6b: **OtherAe** — AE Title của DICOM node ngoài (`/:siteID/rest/otherAe`) — dùng cho C-MOVE, Q/R
  - FR6c: **AgentAe** — AE Title của agent node, hỗ trợ `autoCreateAgentAE` flag
  - FR6d: **AliasAe** — master-level alias mapping AE → MyAe + siteID (`/master/rest/aliasAe`) — cho phép nhiều alias trỏ vào cùng một site

### 2. User Management

- FR7: Admin tạo, chỉnh sửa, xóa user accounts (module `company/user`)
  - FR7a: CRUD user: `POST/PUT/GET/DELETE /:siteID/rest/users(/:id)` — require `manageUser` privilege
  - FR7b: `changePassword`: xác minh mật khẩu cũ + validate độ mạnh mật khẩu (`checkPasswordStrength`) + BCrypt hash
  - FR7c: Filter users: theo name, active status, department (parentID)
  - FR7d: Soft delete user (filterDeleted)

- FR8: Admin gán roles cho users
  - FR8a: CRUD roles: `GET/POST/PUT/DELETE /:siteID/rest/roles(/:id)` — require `manageRole` privilege
  - FR8b: Roles có 2 loại: **public** (siteFK=0, global) và **private** (per-site)
  - FR8c: Xem users trong một role: `GET /:siteID/rest/roles/:id/users`
  - FR8d: Gán role mặc định cho user: `POST /:siteID/rest/roles/setUserRoleDefault/:id`
  - FR8e: Tên roles (Radiologist, Technician, Admin, v.v.) do admin tự định nghĩa — không hardcode

- FR9: Admin cấu hình site access cho từng user
  - FR9a: Xem danh sách sites user được phép: `GET /:siteID/rest/users/:userID/sites`
  - FR9b: User merge — gộp tài khoản: `POST/PUT /:siteID/rest/user/merge`

- FR10: User profile với thông tin cá nhân và credentials
  - FR10a: `getUser`: load đầy đủ login methods, privileges, roles, department assignment
  - FR10b: `changePassword` — tự đổi mật khẩu (xem FR7b)
  - FR10c: Department management: `GET/POST/PUT/DELETE /:siteID/rest/departments` — require `manageDepartment`
  - FR10d: Danh sách tất cả privileges: `GET /rest/privileges/all`

### 3. Authentication & Authorization

- FR11: Đăng nhập bằng username/password qua pluggable auth method system (`Auth::registerAuthMethod`)
  - FR11a: `LocalDBAuth` — xác thực với DB nội bộ, password hash BCrypt (`PASSWORD_BCRYPT`)
  - FR11b: Hệ thống cache credentials để tránh query DB liên tục _(impl: Redis — share_cache)_
  - FR11c: CAPTCHA bắt buộc sau 3 lần đăng nhập sai
  - FR11d: Brute force protection: khóa tài khoản 1 phút sau 10 lần sai
  - FR11e: Audit log ghi lại mỗi lần đăng nhập thành công (action: LOGIN, username, siteID)
  - FR11f: Đăng nhập qua web form (`GET /auth/login`) và REST API (`POST /rest/auth`)

- FR12: Hệ thống hỗ trợ nhiều phương thức xác thực API — **không phải OAuth 2.0**
  - FR12a: JWT Bearer token: `Authorization: Bearer <jwt>` — verify bằng `Firebase\JWT` với `SECRET_KEY`
  - FR12b: HTTP Basic Auth: `Authorization: Basic <base64(user:password)>` — cached vào session
  - FR12c: Custom Token header: `Authorization: Token <token>` — session lookup
  - FR12d: URL params: `?user=<base64>&passwd=<base64>` — dùng cho embedded viewer
  - FR12e: Session cookie (`session` cookie) — browser-based login
  - FR12f: Local IP bypass (`checkLocalIP`) — request từ nội bộ không cần xác thực
  - ~~OAuth 2.0~~ — **không tồn tại** trong code

- FR14: RBAC — Role + Privilege based access control
  - FR14a: Privilege check: `requirePrivilege($priv)` — ví dụ: `manageUser`, `manageRole`, `manageDepartment`
  - FR14b: Role check: `requireRole($roleID)` — user phải thuộc role cụ thể
  - FR14c: Admin check: `requireAdmin()` — require privilege `accessAdmin`
  - FR14d: `PRIV_FULL_CONTROL` — superuser bypass tất cả privilege/site check
  - FR14e: Roles có 2 loại: **public roles** (siteFK=0, shared toàn hệ thống) và **private roles** (per-site)
  - FR14f: CRUD roles: `GET/POST/PUT/DELETE /:siteID/rest/roles`; gán user vào role per-site
  - FR14g: Danh sách tất cả privileges: `GET /rest/privileges/all`
  - FR14h: License-based feature control: `requireLicense()`, `requireLicenseModule()` — một số tính năng yêu cầu license hợp lệ

- FR15: Restrict access theo site
  - FR15a: `requireSite($siteID)` — check user có trong danh sách site được phép truy cập
  - FR15b: User-site mapping: mỗi user gắn với một hoặc nhiều sites (`getUserSites`: `GET /:siteID/rest/users/:userID/sites`)
  - FR15c: `PRIV_FULL_CONTROL` bypass site restriction — fullcontrol user truy cập được mọi site
  - FR15d: Hệ thống cache site info (TTL 3600s) để tránh query DB mỗi request _(impl: Redis)_
  - FR15e: URL pattern `/:siteID/rest/...` enforce siteID match tại router level

### 4. Study Management

- FR16: Hệ thống lưu trữ và index DICOM studies với đầy đủ metadata
  - *Chi tiết triển khai xem FR69-FR74 (Study Management chi tiết) và FR84-FR99 (DICOM Storage chi tiết)*
  - FR16a: Hệ thống lưu trữ study với persistence đa tầng: relational records, instance-level data, và search index _(impl: MySQL studys table + Cassandra + Elasticsearch)_
  - FR16b: Hệ thống đồng bộ search index async sau mỗi DICOM instance được nhận _(impl: Kafka topic `SYNC_DB_ELASTIC`)_
- FR17: Admin có thể xem danh sách studies với các filters
  - *Chi tiết xem FR20-FR24 (Search & Discovery)*
- FR18: Hệ thống hỗ trợ delete/archival của studies
  - FR18a: Xóa study qua REST API (`DELETE /rest/v1/dicom/delete`) — module `pacs/stow`
  - FR18b: Move storage giữa các nodes qua Tool exec `moveStorage` (module `pacs/tool`)
  - FR18c: Studylog CLEAN_DATA_STUDY_LOG service dọn dẹp dữ liệu cũ (xem FR49)
- FR19: Hệ thống maintain data integrity của DICOM objects
  - FR19a: Tool exec `ToolCheckFiles` kiểm tra file integrity trên storage
  - FR19b: Replication Factor (RF) đảm bảo redundancy cho ONLINE và NEARLINE (xem FR60)

### 5. Search & Discovery

- FR20: Users có thể tìm kiếm studies theo Patient ID, Patient Name
  - FR20a: QueryRetrieve module filter ở Patient level: `patient_id`, `patient_name`, `birth_date`
  - FR20b: Search qua Elasticsearch index với response < 1s; fallback MySQL cho filtered queries
- FR21: Users có thể tìm kiếm studies theo Study Date, Modality, Body Part
  - FR21a: Study level query: `GET /:siteID/rest/queryretrieve/study` — filter: `study_date`, `modality`, `body_part`
  - FR21b: Series level query: `GET /:siteID/rest/queryretrieve/series` — filter: `series_date`, `modality`
  - FR21c: Instance level query: `GET /:siteID/rest/queryretrieve/instance` — filter: `sop_class_uid`, `instance_number`
- FR22: Users có thể tìm kiếm studies theo Accession Number
  - FR22a: `accession_no` là param filter trong `queryStudy` và `queryInstance`
  - FR22b: MWL query cũng hỗ trợ filter theo `AccessionNumber`: `GET /:siteID/rest/queryretrieve/mwl`
- FR23: Search results được phân trang và có thể sort
  - FR23a: Pagination: params `pageNo` (trang hiện tại), `pageSize` (số item/trang)
  - FR23b: Sorting: `filterStudySortParams` cho study query, `filterMwlSortParams` cho MWL query
  - FR23c: Sort direction (ASC/DESC) configurable per query
- FR24: Elasticsearch powered search với response time < 1s (P95)
  - FR24a: MySQL là primary store; Elasticsearch là secondary index cho full-text search với response < 1s
  - FR24b: Hệ thống sync search index async sau mỗi DICOM instance được nhận _(impl: Kafka topic `SYNC_DB_ELASTIC`)_
  - FR24c: QIDO-RS (module `pacs/qidors`) cũng hỗ trợ DICOMweb-standard query độc lập

### 6. DICOM Support

- FR25: Hệ thống hỗ trợ DICOM single-frame images (CR, DR, CT, MR, etc.)
  - Xem bảng SOP Classes section 6.1 để biết đầy đủ danh sách SOP Class hỗ trợ
- FR26: Hệ thống hỗ trợ DICOM multiframe images (Ultrasound, Mammo, XA, XRF)
  - FR26a: US Multiframe, XA Image, XRF Image — xem section 6.1 SOP Classes
  - FR26b: Hệ thống cache frame offsets để tăng tốc truy xuất frame trong multiframe instance _(impl: Redis)_
- FR27: Hệ thống hỗ trợ truy xuất specific frames từ multiframe images qua WADO-RS
  - *Đã chi tiết trong FR44d*: `GET /rest/:aet/studies/:studyIUID/series/:seriesIUID/instances/:instanceUID/frames/:frames`
- FR28: Hệ thống hỗ trợ streaming frames từ multiframe cho performance
  - *Đã chi tiết trong FR44d và FR44f*: rendered endpoint trả về JPEG/PNG frame; multipart/related response
- FR29: Hệ thống validate DICOM conformance trước khi store
  - FR29a: StowCtrl kiểm tra Content-Type header: chấp nhận `application/dicom`, `multipart/related; type="application/dicom"`, `multipart/related; type="application/octet-stream"` — từ chối các loại khác
  - FR29b: `dicomnet` binary (C++) validate DICOM P10 format và syntax khi nhận qua C-STORE SCP
- FR30: Hệ thống preserve all DICOM metadata khi lưu trữ
  - *Đã chi tiết trong FR5*: MySQL lưu study/series metadata; Cassandra lưu instance-level DICOM tags; filesystem lưu DICOM P10 file gốc nguyên vẹn

### 6.1. DICOM SOP Classes

Hệ thống phải hỗ trợ các SOP Classes sau:

**Storage SOP Classes (C-STORE SCP):**

| SOP Class | UID | Mô tả |
|-----------|-----|--------|
| MR Image Storage | 1.2.840.10008.5.1.4.1.1.4 | Magnetic Resonance |
| CT Image Storage | 1.2.840.10008.5.1.4.1.1.2 | Computed Tomography |
| CR Image Storage | 1.2.840.10008.5.1.4.1.1.1 | Computed Radiography |
| DX Image Storage | 1.2.840.10008.5.1.4.1.1.1.1 | Digital X-Ray |
| US Image Storage | 1.2.840.10008.5.1.4.1.1.6.1 | Ultrasound |
| US Multiframe Image Storage | 1.2.840.10008.5.1.4.1.1.2.1 | Ultrasound Multiframe |
| MG Image Storage | 1.2.840.10008.5.1.4.1.1.1.2 | Mammography |
| XA Image Storage | 1.2.840.10008.5.1.4.1.1.12.1 | X-Ray Angiography |
| XRF Image Storage | 1.2.840.10008.5.1.4.1.1.12.2 | X-Ray Radio Fluoroscopy |
| SC Image Storage | 1.2.840.10008.5.1.4.1.1.7 | Secondary Capture |
| PET Image Storage | 1.2.840.10008.5.1.4.1.1.128 | PET Image |
| RT Image Storage | 1.2.840.10008.5.1.4.1.1.481.1 | RT Image |
| VL Image Storage | 1.2.840.10008.5.1.4.1.1.77.1.1 | Visible Light |
| VL Endoscopic Image Storage | 1.2.840.10008.5.1.4.1.1.77.1.2 | Endoscopic |
| VL Microscopic Image Storage | 1.2.840.10008.5.1.4.1.1.77.1.3 | Microscopic |
| VL Slide Coordinates Microscopic Image Storage | 1.2.840.10008.5.1.4.1.1.77.1.3.1 | Whole Slide Imaging |
| Basic Text SR Storage | 1.2.840.10008.5.1.4.1.1.88.11 | Structured Report |
| Enhanced SR Storage | 1.2.840.10008.5.1.4.1.1.88.40 | Enhanced SR |
| Grayscale Softcopy Presentation State Storage | 1.2.840.10008.5.1.4.1.1.11.1 | Presentation State |

**Query/Retrieve SOP Classes (C-MOVE, C-FIND):**

| SOP Class | UID | Mô tả |
|-----------|-----|--------|
| Patient Root Q/R INF - Get | 1.2.840.10008.5.1.4.1.2.1.3 | Patient Root Query |
| Patient Root Q/R INF - Find | 1.2.840.10008.5.1.4.1.2.1.1 | Patient Root Find |
| Study Root Q/R INF - Get | 1.2.840.10008.5.1.4.1.2.2.3 | Study Root Query |
| Study Root Q/R INF - Find | 1.2.840.10008.5.1.4.1.2.2.1 | Study Root Find |
| Patient/Study Only Q/R INF - Get | 1.2.840.10008.5.1.4.1.2.3.3 | Patient/Study Only |
| Patient/Study Only Q/R INF - Find | 1.2.840.10008.5.1.4.1.2.3.1 | Patient/Study Only |

**Other SOP Classes:**

| SOP Class | UID | Mô tả |
|-----------|-----|--------|
| Verification SOP (C-ECHO) | 1.2.840.10008.1.1 | DICOM Verification |
| Modality Worklist C-FIND | 1.2.840.10008.5.1.4.31 | Modality Worklist |
| Instance Availability Notification | 1.2.840.10008.5.1.4.1.1.200.1 | IAN |

### 6.2. Document Attachment Storage

Hệ thống hỗ trợ lưu trữ tài liệu và media đính kèm (non-DICOM) qua module `pacs/media`:

- FR31: Upload tài liệu đính kèm qua `POST /rest/:aet/media` (multipart/form-data)
  - FR31a: JSON part đầu tiên chứa metadata: StudyInstanceUID, AccessionNumber, FileName, DiagnosisID
  - FR31b: Link file với study qua `StudyInstanceUID` trực tiếp, hoặc auto-resolve từ `AccessionNumber` (tìm study mới nhất theo accession_no)
  - FR31c: Tracking service request: `service_request_id`, `requester_id`, `service_request_upload_id`, `service_request_upload_time`
  - FR31d: Hệ thống xử lý file media async sau khi lưu _(impl: Kafka topic `PACS_MEDIA`)_
  - FR31e: External API: `POST /rest/v1/media/upload`

- FR32: MIME type detect tự động từ binary content (`finfo`) — không có whitelist MIME cố định
  - FR32a: File có `DiagnosisID` (phiếu chẩn đoán) bắt buộc phải là `application/pdf`
  - FR32b: Các loại file khác (DOCX, PDF, TXT, v.v.) không bị giới hạn MIME
  - ~~DOCX/PDF/TXT whitelist cứng~~ — không tồn tại trong code

- FR33: Hỗ trợ upload hình ảnh (PNG, JPG, BMP, v.v.) và video (MPEG/MP4)
  - FR33a: File hình ảnh: MIME detect động, mapping MIME → extension khi download
  - FR33b: File video: `POST /rest/:aet/media/video` — hỗ trợ append MPEG frames (`appendVideo`)
  - ~~Whitelist PNG/JPG/BMP cứng~~ — detect động

- FR34: File gắn với Study qua hai cơ chế:
  - FR34a: Trực tiếp qua `StudyInstanceUID` trong metadata
  - FR34b: Gián tiếp qua `AccessionNumber` → auto-resolve sang `StudyInstanceUID` (lấy study mới nhất)
  - FR34c: DB `pacs_media` có index trên `(site_id, study_iuid)` và `(site_id, accession_no)`

- FR35: Tìm kiếm và liệt kê media đính kèm:
  - FR35a: List theo `studyUID`: `GET /rest/:aet/media?studyUID=...`
  - FR35b: List theo `accessionNo`: `GET /rest/:aet/media?accessionNo=...`
  - FR35c: Mỗi item trả về URL download: `/rest/:aet/media/file/:file_id`

- FR36: Download media qua REST API:
  - FR36a: Download file đơn: `GET /rest/:aet/media/file/:fileID?type=download` — Content-Disposition header
  - FR36b: Stream video: `GET /rest/:aet/media/file/:fileID?type=stream` — HTTP Range (206 Partial Content)
  - FR36c: Render/preview inline: `GET /rest/:aet/media/file/:fileID?type=render`
  - FR36d: Download tất cả media của study dạng ZIP: `GET /rest/:aet/media/studies/:studyIUID`
  - FR36e: Token-based URL với expiry (md5 token) — chia sẻ link tạm thời không cần login
  - FR36f: External API: `DELETE /rest/v1/media/delete`, `POST /rest/v1/media/modify`

- FR37: ~~Validate kích thước file tối đa ở app-level~~ — **xóa**: giới hạn do PHP/Apache config, không có validation trong application code

- FR38: Preserve original filename và content type:
  - FR38a: `FileName` gốc lưu trong file `.metadata` kèm theo file chính
  - FR38b: MIME type detect từ binary content (`finfo_buffer`) — không tin vào Content-Type header của client
  - FR38c: Filename sanitize khi download: loại ký tự đặc biệt `[^A-Za-z0-9. ]`
  - FR38d: Tự động thêm extension nếu filename không có (từ MIME mapping)

- FR39: ~~Audit log cho truy cập tài liệu~~ — **chưa xác nhận** trong media controller; cần xác minh

- FR39b: Quản lý vòng đời file media (trash/restore — có trong code, chưa có trong PRD gốc):
  - Xóa mềm: `DELETE /rest/:aet/media/file` → vào `pacs_media_trash`
  - Restore: `POST /rest/:aet/media/restore`
  - Xem trash: `GET /rest/:aet/mediaTrash?studyUID=...`
  - Sửa metadata: `POST /rest/:aet/media/file/:fileID` (`modifyFile`)

### 7. Integration

- FR40: RIS có thể gửi imaging order/request đến PACS qua REST API (`POST /rest/v1/:siteID/integration`) hoặc HL7 (Node.js HL7 server)
  - FR40a: Nhận study request từ RIS qua REST API endpoint `/rest/v1/:siteID/integration`
  - FR40b: Nhận HL7 messages từ RIS qua Node.js HL7 server (module `pacs/hl7server`)
  - FR40c: Tạo site tự động từ RIS request nếu chưa tồn tại (auto-provision siteID, AE Title, WADO URL, Contact Point)

- FR41: Modality tự kéo imaging order từ PACS qua Modality Worklist (MWL / C-FIND)
  - ~~PACS push order đến Modality~~ — **sai khái niệm**: PACS không push order; Modality chủ động query PACS bằng C-FIND MWL
  - FR41a: PACS cung cấp Modality Worklist (MWL) qua C-FIND SCP
  - FR41b: Modality query danh sách lệnh chụp theo AE Title, ngày, PatientID

- FR42: Modality có thể gửi hình ảnh DICOM vào PACS qua C-STORE hoặc STOW-RS
  - FR42a: Nhận DICOM instances qua C-STORE SCP (module `pacs/dicomnet`)
  - FR42b: Nhận DICOM instances qua STOW-RS (`POST /rest/v1/dicom/create` — module `pacs/stow`)
  - FR42c: Xóa study qua REST API (`DELETE /rest/v1/dicom/delete`)

- FR43: PACS tự động gửi notification đến RIS sau khi nhận đủ hình ảnh (RESTful callback)
  - FR43a: Push callback đến RIS URL với payload: StudyIUID, InstancesInStudy, SeriesInStudy, CompletedTime, StartedTime (qua `RisConnection`)
  - FR43b: Gửi notification thủ công cho study cụ thể (`POST /:siteID/rest/ris/update/:studyIUIDs`)
  - FR43c: Auto-sync định kỳ các study chưa notify RIS (service `autoSyncRis`)
  - FR43d: Xem danh sách studies chưa đồng bộ với RIS trong 24h qua (`GET /:siteID/rest/ris/study/unsynchronized`)
  - FR43e: Cấu hình RIS connection per site: URL callback, headers, params (CRUD `/:siteID/rest/ris`)
  - FR43f: RIS pull model — RIS có thể kéo dataset study theo batch (`GET /rest/:aet/ris/study` + `POST commit`) với pagination

- FR44: HIS/bên ngoài có thể truy xuất hình ảnh qua WADO-RS (read-only, module `pacs/wado`)
  - FR44a: Lấy toàn bộ study (`GET /rest/:aet/studies/:studyIUID`)
  - FR44b: Lấy series (`GET /rest/:aet/studies/:studyIUID/series/:seriesIUID`)
  - FR44c: Lấy instance cụ thể (`GET .../instances/:instanceUID`)
  - FR44d: Lấy frames cụ thể trong instance (`GET .../frames/:frames`)
  - FR44e: Lấy metadata (JSON) ở cấp study / series / instance
  - FR44f: Lấy ảnh đã render (JPEG/PNG) — rendered endpoint cho study, series, instance, frames
  - FR44g: Lấy thumbnail cho study, series, instance
  - FR44h: Lấy filepath vật lý của instance (internal use)
  - FR44i: Hỗ trợ siteID optional trong URL (multi-site access)

- FR45: Hệ thống hỗ trợ HL7 v2.x messages qua Node.js HL7 server (module `pacs/hl7server`)
  - FR45a: HL7 server chạy độc lập (Node.js process, `Exec/index.js`)
  - FR45b: Nhận và xử lý HL7 messages từ HIS/RIS *(loại message cụ thể — ADT/ORM — cần xác nhận thêm từ `index.js`)*

### 8. Audit & Compliance

- FR46: Hệ thống ghi log tất cả PHI access events với timestamp, user, action
  - FR46a: `AuditLogHandler` ghi audit event cho mỗi request có PHI access
  - FR46b: `Logger.makeAuditLogger` tạo dedicated logger với format chuẩn: timestamp, userID, action, resourceID
  - FR46c: Audit log được hook vào `AuthCtrl` — mỗi lần xác thực/access đều được ghi
  - FR46d: Log type `audit_log` tách biệt với `pacs_log`, `apache.access_log`, `dicom_net_advance_log`
- FR47: Admin có thể xem audit logs
  - FR47a: `getLogs` API filter theo type: `GET /:siteID/rest/log?type=audit_log`
  - FR47b: ~~Export audit logs~~ — **chưa xác nhận**: không tìm thấy export endpoint trong code; hiện chỉ có view/filter
- FR48: Hệ thống đảm bảo HIPAA compliance
  - *Design intent* — compliance đạt qua tổng hợp: audit log (FR46), RBAC (FR13-FR15), JWT auth (FR11), HTTPS, data retention (FR49)
- FR49: Hệ thống hỗ trợ data retention policies
  - FR49a: `CLEAN_DATA_STUDY_LOG` background service trong module `pacs/studylog` — dọn dẹp study log entries cũ theo cấu hình
  - FR49b: Retention hiện tại áp dụng cho **study log data**, không phải per-storage DICOM file retention (xem FR63)
- FR50: Hệ thống hỗ trợ các DICOM Transfer Syntax tiêu chuẩn
  - *Lưu ý: FR này bị đặt nhầm vào section Audit & Compliance — đúng ra phải ở section DICOM Support (section 6)*
  - FR50a: `dicomnet` binary (C++) negotiate Transfer Syntax với modality khi nhận C-STORE SCP
  - FR50b: STOW-RS tiếp nhận DICOM P10 với bất kỳ Transfer Syntax nào (không transcode tại điểm nhận)
  - FR50c: Transfer Syntax negotiate: Implicit VR Little Endian, Explicit VR Little Endian, JPEG Baseline, JPEG 2000, JPEG-LS (tùy cấu hình dicomnet binary)

### 9. DICOM Network (AE Title Management)

*Lưu ý: FR51-FR53 trùng lặp với FR6a-FR6b (AE Title mgmt) và FR1 (C-STORE SCP). Giữ lại để tham chiếu; chi tiết đã có ở section tương ứng.*

- FR51: Hệ thống hỗ trợ cấu hình AE Title (MyAE) cho PACS
  - *Trùng với FR6a* — CRUD MyAe: `POST/GET/PUT/DELETE /:siteID/rest/ae/my`
- FR52: Hệ thống hỗ trợ cấu hình External AE (OtherAE) để kết nối với systems khác
  - *Trùng với FR6b* — CRUD OtherAe: `POST/GET/PUT/DELETE /:siteID/rest/ae/other`; AgentAe và AliasAe cũng có ở FR6c-FR6d
- FR53: Hệ thống hỗ trợ DICOM C-STORE SCP để nhận images từ modalities
  - *Trùng với FR1* — `dicomnet` binary ELF (C++) chạy C-STORE SCP; nhận DICOM instances từ modality
- FR54: Hệ thống hỗ trợ DICOM C-STORE SCU để gửi images đến systems khác
  - FR54a: `dicomnet` binary thực hiện C-STORE SCU — push DICOM instance đến OtherAe destination
  - FR54b: Trigger bởi Tool exec `StowStudies` hoặc C-MOVE forward request
- FR55: Hệ thống hỗ trợ DICOM C-FIND cho query
  - FR55a: `queryStudy` — C-FIND ở Study level: filter PatientID, PatientName, StudyDate, Modality, AccessionNumber
  - FR55b: `querySeries` — C-FIND ở Series level
  - FR55c: `queryInstance` — C-FIND ở Instance level
  - FR55d: `queryMwl` — C-FIND ở Modality Worklist level (MWL)
  - FR55e: Implement trong module `pacs/queryretrieve` — gọi `dicomnet` binary
- FR56: Hệ thống hỗ trợ DICOM C-MOVE cho retrieve
  - FR56a: PACS nhận C-MOVE request → forward instances đến Destination AE qua C-STORE SCU
  - FR56b: Implement trong module `pacs/queryretrieve` (`moveStudy` / `moveSeries`)
- FR57: Hệ thống hỗ trợ DICOM C-ECHO để verify connection
  - FR57a: `dicomnet` binary thực hiện C-ECHO SCU — kiểm tra kết nối đến OtherAe
  - FR57b: API: `POST /:siteID/rest/ae/echo` — trả về kết quả ping kết nối DICOM

### 10. Storage Management

- FR58: Hệ thống hỗ trợ quản lý nhiều storage nodes
  - FR58a: CRUD storage nodes: `POST/GET/PUT/DELETE /:siteID/rest/storage`
  - FR58b: Mỗi node có: name, type (Dir/FileServer/S3/Ceph-Rados/SMB), host, port, path, credentials
  - FR58c: List storage per site: `GET /:siteID/rest/storage/list`; cấu hình zone/replication: `configStorage`
- FR59: Hệ thống hỗ trợ storage classes: ONLINE, NEARLINE, OFFLINE, CACHE; trạng thái chuyển tiếp ONLINE_NEARLINE
  - FR59a: ONLINE_NEARLINE: trạng thái khi file tồn tại đồng thời ở ONLINE và NEARLINE trong quá trình migration
  - FR59b: Mỗi storage node được gán một class; class xác định tier và hành vi truy cập dữ liệu
- FR60: Hệ thống hỗ trợ data replication với Replication Factor (RF) configurable riêng cho ONLINE và NEARLINE
  - FR60a: Hệ thống duy trì RF cache per site (online và nearline) _(impl: Redis keys `rf_online`, `rf_nearline`)_
  - FR60b: Khi lưu instance, hệ thống replicate đến RF nodes của storage class tương ứng
  - FR60c: `rebalanceZone` / `stopRebalance` / `isRebalancing` API — quản lý quá trình rebalance storage nodes
- FR61: Hệ thống hỗ trợ migrate dữ liệu giữa các storage classes
  - FR61a: Tool exec `moveStorage` (module `pacs/tool`) — di chuyển study/instance giữa storage nodes
  - FR61b: Tool exec `importFromCloud` — import DICOM từ cloud storage về ONLINE
  - FR61c: Tool exec `copyInstance` — copy instance sang storage node khác
- FR62: Hệ thống hỗ trợ storage speed monitoring
  - FR62a: Speed test API: `POST /:siteID/rest/storage/speedtest` — đo `write_small_speed`, `write_large_speed`, `read_speed` trên từng storage node
  - FR62b: ~~Storage capacity alerts~~ — **chưa xác nhận**: không tìm thấy alerting/threshold mechanism; chỉ có manual speed test endpoint
- FR63: Hệ thống hỗ trợ data cleanup theo study log retention policy
  - FR63a: `CLEAN_DATA_STUDY_LOG` service trong module `pacs/studylog` — dọn dẹp study log entries cũ theo cấu hình retention
  - FR63b: ~~Per-storage retention period~~ — **chưa xác nhận**: không tìm thấy cấu hình retention riêng cho từng storage node; DICOM file retention không có cơ chế tự động trong code hiện tại

#### 10.1 Storage Data Model

Mỗi file (DICOM hoặc MEDIA) lưu trữ thông tin vị trí theo cấu trúc `nodes_contain`:

```json
[
  {
    "zone_id": "zone1",
    "storage_information": [
      {
        "storage_class": "ONLINE|NEARLINE|OFFLINE|CACHE",
        "file_size": 1234567,
        "encryption": {
          "type": "none|tar",
          "transferSyntaxUID": "1.2.840.10008.1.2.1",
          "tarCompress": 0
        },
        "nodes": [
          { "node_id": 1, "status": 1 }
        ]
      }
    ]
  }
]
```

- FR63a: Hệ thống hỗ trợ zone-based storage: mỗi zone có thể có bản sao độc lập của file
- FR63b: Hệ thống ưu tiên master zone khi tìm kiếm và đọc file
- FR63c: Hệ thống lưu file size riêng cho từng storage class (raw_size, compress_size)

### 11. Access Management & Rate Limiting

- FR64: Hệ thống hỗ trợ giới hạn upload requests per minute
- FR65: Hệ thống hỗ trợ giới hạn download requests per minute
- FR66: Hệ thống hỗ trợ giới hạn file uploads per day
- FR67: Hệ thống hỗ trợ monitoring và warning khi approaching limits
- FR68: Hệ thống hỗ trợ reset counters theo configurable intervals

### 12. Study Management

- FR69: Hệ thống hỗ trợ update study metadata
- FR70: Hệ thống hỗ trợ delete study (xóa vĩnh viễn)
- FR71: Hệ thống hỗ trợ move study to trash
- FR72: Hệ thống hỗ trợ restore study từ trash
- FR73: Hệ thống hỗ trợ modify study (thay đổi metadata)
- FR74: Hệ thống hỗ trợ detach hoặc merge studies

### 13. DICOM Processing & Transformation

- FR75: Hệ thống hỗ trợ DICOM tag morphing/anonymization
- FR76: Hệ thống hỗ trợ DICOM encryption/decryption
- FR77: Hệ thống hỗ trợ thumbnail generation
- FR78: Hệ thống hỗ trợ rendered image generation (JPEG, PNG)
- FR79: Hệ thống hỗ trợ bulk data extraction

### 14. Export & Distribution

- FR80: Hệ thống hỗ trợ export studies đến external storage
- FR81: Hệ thống hỗ trợ gửi studies đến remote PACS (C-MOVE/STOW)
- FR82: Hệ thống hỗ trợ tạo public links cho share studies
- FR83: Hệ thống hỗ trợ CD/DVD burning (export to media)

### 15. Background Services & Queue Processing

- FR84: Hệ thống hỗ trợ async queue processing cho STOW operations
- FR85: Hệ thống hỗ trợ auto-sync DICOM info đến RIS
- FR86: Hệ thống hỗ trợ auto-notify RIS khi có series mới
- FR87: Hệ thống hỗ trợ auto-notify RIS khi có instance mới
- FR88: Hệ thống hỗ trợ thumbnail cache generation (async)
- FR89: Hệ thống hỗ trợ viewer cache generation (async)
- FR90: Hệ thống hỗ trợ frame offset caching cho multiframe
- FR91: Hệ thống hỗ trợ auto-copy files đến multiple storage locations
- FR92: Hệ thống hỗ trợ file integrity verification (async)
- FR93: Hệ thống hỗ trợ number of series/instances sync (async)
- FR94: Hệ thống hỗ trợ restore study từ trash (async)
- FR95: Hệ thống hỗ trợ offline to nearline migration async (bao gồm TAR extraction và cache invalidation) _(impl: Kafka queue `OFFLINE_TO_NEARLINE`)_
- FR96: Hệ thống hỗ trợ move to trash operation (async)
- FR97: Hệ thống hỗ trợ merge studies operation (async)
- FR98: Hệ thống hỗ trợ copy instance operation (async)
- FR99: Hệ thống hỗ trợ advance forward operation (async)

### 16. Storage Processing

- FR100: Hệ thống hỗ trợ auto-move files to nearline storage async _(impl: Redis queue `move_nearline_data` + Kafka consumer `ConsumerMoveNearlineStorage`)_
- FR101: Hệ thống hỗ trợ auto-remove online files sau khi đã có NEARLINE copy async _(impl: Kafka queue `UPDATE_DB_REMOVE_ONLINE_FILE`)_
- FR102: Hệ thống hỗ trợ storage rebalancing async _(impl: Redis counters `rebalance_counter`, `move_nearline_counter`)_
- FR103: Hệ thống hỗ trợ file compression khi di chuyển sang NEARLINE: JPEG-LS Lossless (`jls`), JPEG Lossless (`jpegls`), JPEG 2000 Lossless (`j2kls`); không nén nếu file đã ở compressed transfer syntax
- FR104: Hệ thống hỗ trợ DICOM to media (CD/DVD) writing
- FR105: Hệ thống hỗ trợ media label printing

#### 16.1 Kafka Queue Topics

| Topic | Mô tả |
|-------|--------|
| `MOVE_NEARLINE_STORAGE` | Consumer xử lý copy files ONLINE → NEARLINE |
| `OFFLINE_TO_NEARLINE` | Consumer restore files từ OFFLINE TAR → NEARLINE |
| `UPDATE_DB_REMOVE_ONLINE_FILE` | Consumer cập nhật DB sau khi xóa ONLINE files |

#### 16.2 Redis Keys cho Storage

| Key | Mô tả |
|-----|--------|
| `move_nearline_data` | Redis list queue cho DB updates (nearline migration) |
| `move_nearline_counter` | Hash counter theo processID |
| `rebalance_counter` | Counter cho storage rebalancing |
| `cache_online_replication_factor` | Cache RF cho ONLINE storage |
| `cache_nearline_replication_factor` | Cache RF cho NEARLINE storage |

### 17. System Configuration & Administration

- FR106: Hệ thống hỗ trợ global settings management
- FR107: Hệ thống hỗ trợ per-site settings management
- FR108: Hệ thống hỗ trợ DICOM server installation/configuration
- FR109: Hệ thống hỗ trợ service health monitoring

### 18. Storage Lifecycle Management

- FR110: Hệ thống hỗ trợ auto-move từ ONLINE sang NEARLINE storage (scheduled job `MOVE_NEARLINE`)
- FR111: Hệ thống hỗ trợ auto-move từ NEARLINE sang OFFLINE storage (scheduled job `MOVE_OFFLINE`)
- FR112: Hệ thống hỗ trợ move giữa các storage locations (cross-storage migration - MoveStorage mode)
- FR113: Hệ thống hỗ trợ scheduled migration với configurable time windows (startTime/endTime theo giờ trong ngày)
- FR114: Hệ thống hỗ trợ DICOM compression during migration: JpegLSLossless, JpegLossless, Jpeg2000Lossless
- FR115: Hệ thống hỗ trợ retention-based auto-archive (configurable limitTime theo số ngày)
- FR116: Hệ thống hỗ trợ configurable storage tiering policies

#### 18.1 ONLINE → NEARLINE Migration Chi Tiết

- FR116a: Service `MOVE_NEARLINE` chạy vòng lặp liên tục, chỉ hoạt động trong time window `startTime`-`endTime`
- FR116b: Hệ thống query studies cũ hơn `limitTime` ngày có `storage_instances = ONLINE` từ search index _(impl: Elasticsearch)_
- FR116c: Hệ thống xử lý cả DICOM và MEDIA (non-DICOM) files trong một pipeline
- FR116d: Files được copy sang NEARLINE theo batch với max batch size configurable (mặc định 25MB)
- FR116e: Files lớn (> 1MB) được xử lý riêng lẻ; files nhỏ được batch copy bất đồng bộ với concurrency = 10
- FR116f: Hệ thống cập nhật dữ liệu và enqueue async job để `addNearlineDatabase` xử lý tách biệt _(impl: MySQL + Elasticsearch updates, Redis queue `move_nearline_data`)_
- FR116g: Sau khi copy nearline thành công, study metadata cập nhật `storage_instances = ONLINE_NEARLINE`
- FR116h: Hệ thống áp dụng backpressure: nếu migration queue có ≥ 10.000 entries thì tạm dừng 5 phút _(impl: Redis queue `move_nearline_data`)_
- FR116i: MoveStorage mode cho phép chuyển files giữa 2 storage nodes cụ thể (src/dest configurable)

#### 18.2 Xóa File ONLINE Sau Khi Đã Có NEARLINE

- FR116j: Service `REMOVE_ONLINE_FILES` xóa file ONLINE của studies đã ở trạng thái `ONLINE_NEARLINE` và cũ hơn `limitTime` ngày (mặc định 30 ngày)
- FR116k: **Bắt buộc** kiểm tra file integrity trước khi xóa: so sánh actual file size trên NEARLINE storage với file_size trong database - chỉ xóa ONLINE khi kích thước khớp
- FR116l: Nếu file NEARLINE bị lỗi (size không khớp), hệ thống xóa file nearline bị lỗi và giữ nguyên ONLINE, cập nhật `storage_instances = ONLINE`
- FR116m: Hệ thống cập nhật metadata sau khi xóa ONLINE thành công (`nodes_contain` và `storage_instances = NEARLINE`) async _(impl: Kafka queue `UPDATE_DB_REMOVE_ONLINE_FILE`)_
- FR116n: Retry logic khi xóa file: tối đa 10 lần retry, sleep 1 giây giữa các lần

#### 18.3 NEARLINE → OFFLINE Migration Chi Tiết

- FR116o: Service `MOVE_OFFLINE` xử lý studies ở NEARLINE (không có ONLINE, không có ONLINE_NEARLINE), cũ hơn `limitTime` ngày (mặc định 100 ngày)
- FR116p: Files DICOM được đóng gói vào TAR archives; mỗi series trong một study có thể có nhiều TAR files (nếu vượt `MaxTarSize`, mặc định 50MB)
- FR116q: Cấu trúc path TAR: `/{siteID}/{year}/{month}/{day}/{studyIUID}/{seriesIUID}/{uuid}.tar`; mỗi instance trong TAR có path `{uuid}.tar.{sopIUID}.dcm`
- FR116r: TAR creation configurable: `MaxUploadSize` (chunk upload, mặc định 10MB), `MaxTarSize` (max TAR size, mặc định 50MB), `MaxFileSize` (chunk size, mặc định ~10MB)
- FR116s: Hệ thống serialize và lưu instance metadata vào Series record để track TAR entries _(impl: MySQL field `instances_attrs`)_
- FR116t: Sau khi đóng gói TAR thành công: xóa file instance riêng lẻ trên NEARLINE, xóa instance record khỏi persistence và search index _(impl: MySQL + Elasticsearch)_
- FR116u: Encryption field của OFFLINE files: `{"type": "tar", "tarCompress": 1}`
- FR116v: Chỉ xử lý DICOM files (không xử lý MEDIA files) cho OFFLINE archiving
- FR116w: Hệ thống giữ nguyên OFFLINE storage info khi re-processing (không overwrite existing OFFLINE entries)

#### 18.4 OFFLINE → NEARLINE Restore Chi Tiết

- FR116x: Queue consumer `ConsumerOfflineToNearline` (topic: `OFFLINE_TO_NEARLINE`, group: `ConsumerOfflineToNearline`)
- FR116y: Message payload bao gồm: `siteID`, `studyIUID`, và cache invalidation key _(impl: Redis key `cacheKey`)_
- FR116z: Hệ thống tìm nearline storage **cùng zoneID** với offline storage để extract files vào
- FR116aa: TAR extraction: đọc TAR từ OFFLINE storage, extract files vào NEARLINE storage với path mới
- FR116ab: Path mới sau extract: bỏ 3 path segments cuối của TAR path, giữ `{sopIUID}.dcm` làm filename
- FR116ac: Sau extract thành công: tạo instance records mới trong MySQL + Elasticsearch với NEARLINE storage_information
- FR116ad: Xóa field `instances_attrs` trong Series record (sau khi đã extract ra từng instances)
- FR116ae: Xóa TAR file khỏi OFFLINE storage sau khi extract xong
- FR116af: Cập nhật study `storage_instances` loại bỏ OFFLINE, thêm NEARLINE
- FR116ag: Xóa Redis cache key sau khi hoàn thành toàn bộ quá trình restore

### 19. Data Cleanup & Maintenance

- FR117: Hệ thống hỗ trợ auto-cleanup expired upload logs
- FR118: Hệ thống hỗ trợ auto-cleanup old MWL records
- FR119: Hệ thống hỗ trợ auto-cleanup old system logs (Apache, PACS, DICOM logs)
- FR120: Hệ thống hỗ trợ auto-cleanup expired study logs
- FR121: Hệ thống hỗ trợ auto-cleanup cache instance metadata
- FR122: Hệ thống hỗ trợ auto-cleanup RIS study logs
- FR123: Hệ thống hỗ trợ permanent delete từ trash sau configurable retention period

### 20. Modality Worklist (MWL)

- FR124: Hệ thống hỗ trợ Modality Worklist (MWL) service qua DICOM C-FIND
- FR125: Hệ thống hỗ trợ query worklist items với 2 cơ chế (tuỳ theo cấu hình RIS version):
  - FR125a: **Cơ chế Proxy (RIS version "new")**: Dịch tham số DICOM C-FIND thành RESTful API params, gọi trực tiếp đến RIS (`urlWorklist`), sau đó map kết quả trả thẳng về Modality (không lưu bản ghi vào DB PACS).
  - FR125b: **Cơ chế Local (RIS version "old")**: Lưu trữ bản ghi MWL và query trực tiếp từ Elasticsearch/MySQL nội bộ của PACS.
- FR126: Hệ thống hỗ trợ quản lý worklist items (insert, modify, erase) (chỉ áp dụng cho cơ chế Local).
- FR127: Hệ thống hỗ trợ scheduled cleanup old MWL records (chỉ áp dụng cho cơ chế Local).

### 21. Data Synchronization

- FR128: Hệ thống hỗ trợ sync data giữa relational store và search index _(impl: MySQL → Elasticsearch)_
- FR129: Hệ thống hỗ trợ sync study data to search index _(impl: Elasticsearch)_
- FR130: Hệ thống hỗ trợ sync instance data to search index _(impl: Elasticsearch)_
- FR131: Hệ thống hỗ trợ database migration tools

### 22. Multi-Site & Zone Management

- FR132: Hệ thống hỗ trợ multi-site (≥ 10 sites)
- FR133: Hệ thống hỗ trợ per-site AE Title (MyAE) configuration
- FR134: Hệ thống hỗ trợ per-site External AE (OtherAE) configuration
- FR135: Hệ thống hỗ trợ per-site storage allocation
- FR136: Hệ thống hỗ trợ per-site access control và rate limiting
- FR137: Hệ thống hỗ trợ per-site RIS integration
- FR138: Hệ thống hỗ trợ per-site settings và configuration
- FR139: Hệ thống hỗ trợ per-site data retention policies

### 23. Multi-Zone Architecture

- FR140: Hệ thống hỗ trợ multi-zone (master/child zones)
- FR141: Hệ thống hỗ trợ zone-based storage management
- FR142: Hệ thống hỗ trợ cross-zone data replication
- FR143: Hệ thống hỗ trợ zone-specific storage configuration
- FR144: Hệ thống hỗ trợ inter-zone data synchronization

### 24. Database Architecture (Per Tenant/Site)

- FR145: Hệ thống hỗ trợ database per site (tenant isolation) _(Infrastructure sẵn sàng qua `SHARDING_ENABLE`, cần bật theo deployment)_
- FR146: Hệ thống hỗ trợ Elasticsearch filter per site trong shared index; per-index là roadmap _(Hiện tại dùng 1 index chung với `site_id` field)_
- FR147: Hệ thống hỗ trợ Redis cache per site _(Chưa implement — cần phát triển)_
- FR148: ✅ Hệ thống hỗ trợ storage token management per zone _(Đã hoàn thành: `StorageTokenMapper.php`)_
- FR149: ✅ Hệ thống hỗ trợ database migration tools _(Đã hoàn thành: nhiều script trong `/Module/pacs/tool/Exec/`)_
- FR150: Hệ thống hỗ trợ data export per site _(Export AE mapping đã có; export study data đang phát triển)_

## Non-Functional Requirements

### Performance

| Requirement | Target |
|-------------|--------|
| API Response Time (P95) | < 500ms |
| DICOM Image Retrieval (WADO-RS) | < 3s cho images < 50MB |
| DICOM Store (STOW-RS) | < 5s per series |
| Search Response | < 1s |
| Concurrent Users | ≥ 50 simultaneous radiologists |
| Daily Throughput | ≥ 1000 studies/ngày |

### Security

| Requirement | Target |
|-------------|--------|
| Data Encryption at Rest | AES-256 |
| Data Encryption in Transit | TLS 1.3 |
| Authentication | OAuth 2.0, LDAP/AD |
| Access Control | RBAC với granular permissions |
| Audit Logging | All PHI access logged; retention ≥ 7 năm; immutable audit trail |
| Compliance | HIPAA, DICOM security profiles |

### Scalability

| Requirement | Target |
|-------------|--------|
| Multi-site Support | ≥ 10 sites |
| Auto-scaling | When load > 80% |
| Storage | Cloud-native (S3/Ceph) |
| Database Scalability | Horizontal sharding per site; ≥ 10 concurrent sites |

### Reliability

| Requirement | Target |
|-------------|--------|
| Uptime | ≥ 99.95% (< 4.4 giờ downtime/năm) |
| RPO (Recovery Point Objective) | < 15 phút |
| RTO (Recovery Time Objective) | < 30 phút |
| Backup | Automated daily; off-site; RPO < 15 phút; retention ≥ 30 ngày |

### Accessibility

| Requirement | Target |
|-------------|--------|
| Standard | WCAG 2.1 Level AA |
| Keyboard Navigation | Full support per WCAG 2.1 Level AA — all functions operable via keyboard |
| Screen Reader | ARIA labels |

### 25. Admin UI (PacsUI)

- FR151: Hệ thống cung cấp Web Admin UI (PacsUI) cho quản lý
- FR152: Hỗ trợ Study Management UI (list, search, view, edit)
- FR153: Hỗ trợ Study Bin/Trash UI (xem, restore, delete vĩnh viễn)
- FR154: Hỗ trợ Study Upload UI (upload DICOM files)
- FR155: Hỗ trợ Study Edit UI (modify metadata)
- FR156: Hỗ trợ Study Delete/Move to Trash UI
- FR157: Hỗ trợ Study Merge/Detach UI
- FR158: Hỗ trợ Media/Attachment Management UI
- FR159: Hỗ trợ Storage Management UI (list, config, monitoring)
- FR160: Hỗ trợ Zone Management UI (list, config)
- FR161: Hỗ trợ Contact Point Management UI
- FR162: Hỗ trợ AE Title Management UI (MyAE, OtherAE, AliasAE)
- FR163: Hỗ trợ DICOM Server Configuration UI
- FR164: Hỗ trợ RIS Integration UI (list, config)
- FR165: Hỗ trợ Worklist (MWL) Management UI
- FR166: Hỗ trợ DICOM Tag Morphing UI (anonymization rules)
  - FR166a: Danh sách tất cả morphing rules của một site (filter theo source_aet, direction)
  - FR166b: Tạo morphing rule mới — required fields: source_aet (AE Title nguồn), direction (chiều áp dụng: incoming/outgoing), rule (JSON)
  - FR166c: Chỉnh sửa morphing rule (update source_aet, direction, rule JSON)
  - FR166d: Xóa morphing rule theo ID
  - FR166e: Lookup rules theo sourceAet — xem tất cả rules áp dụng cho một AE Title cụ thể (dùng cho debug/preview)
  - FR166f: Rule editor trực quan — mỗi rule định nghĩa một cặp SourceTag → TargetTag:
    - **SourceTag**: tagName (DICOM tag nguồn), parsingValue (regex/delimiter để trích xuất giá trị con), removeCopied (xóa tag gốc sau khi copy)
    - **TargetTag**: tagName (DICOM tag đích), optionWrite (OVERWRITE | FIRST_INDEX | END_INDEX), prefix (thêm trước giá trị), suffix (thêm sau giá trị)
  - FR166g: Preview kết quả morphing — nhập DICOM dataset mẫu, xem output sau khi áp dụng rules (dùng DicomMorphingHelper::morphingDataset)
  - FR166h: Cache invalidation tự động sau khi tạo/sửa/xóa rule (private memory cache key theo siteID)
- FR167: Hỗ trợ Settings UI (global settings, per-site settings)
- FR168: Hỗ trợ Access Management UI (rate limiting, monitoring)
  - FR168a: Xem cấu hình rate limit per site (giới hạn request upload, request download, file upload, file upload per day)
  - FR168b: Cập nhật cấu hình rate limit per site (limit_request_upload, limit_request_download, limit_file_upload, limit_file_upload_per_day)
  - FR168c: Dashboard monitoring real-time: hiển thị counters (request_upload, request_download, file_upload), warnings, và trạng thái (state_upload, state_download) cho từng site
  - FR168d: Thủ công enable/disable upload hoặc download cho từng site
  - FR168e: Reset counters và warnings cho site (tức thì hoặc theo lịch daily reset)
  - FR168f: Master view: xem monitoring tổng hợp tất cả sites cùng lúc (master site aggregation)

- FR169: Hỗ trợ Public Link Management UI (share studies)
  - FR169a: Danh sách tất cả public links của một site (list với trạng thái active/expired)
  - FR169b: Tạo public link mới cho study (cấu hình expire time, optional password/CAPTCHA)
  - FR169c: Chỉnh sửa public link (cập nhật expire time, cài đặt bảo mật)
  - FR169d: Xóa public link
  - FR169e: Xem trạng thái active/expired của từng link (so sánh expireTime với thời điểm hiện tại)
  - FR169f: CAPTCHA protection khi khách truy cập public link (generateCaptcha + checkCaptcha)

- FR170: Hỗ trợ Queue Monitoring UI (Kafka topics, messages)
  - FR170a: Danh sách Kafka topics và trạng thái (active, consumer group, partition info)
  - FR170b: Xem messages trong queue theo topic (pending, processing, failed)
  - FR170c: Xem SQL Queue messages (queueSql) cho các job không dùng Kafka
  - FR170d: Retry failed messages thủ công
  - FR170e: Monitor consumer lag per topic (số message chưa được xử lý)
  - FR170f: Thống kê throughput queue (messages/sec, tổng đã xử lý)

- FR171: Hỗ trợ System Logs UI (Apache, PACS, DICOM logs)
  - FR171a: Xem Apache Access Logs (filter theo datetime range, full-text search)
  - FR171b: Xem Apache Error Logs (filter theo datetime range)
  - FR171c: Xem PACS Application Logs / pacs_log (filter theo datetime, patientID, search; hỗ trợ pagination với searchAfter)
  - FR171d: Xem DICOM Network Advance Logs / dicom_net_advance_log (filter theo datetime, search)
  - FR171e: Cấu hình PACS Log Filters: thêm/sửa/xoá filter rules (lưu cache Redis, có TTL)
  - FR171f: Real-time tail DICOM Server log file (GET /master/rest/log/dicomnet/:n — n dòng cuối)

- FR172: Hỗ trợ Study Logs UI (audit logs)
  - FR172a: Danh sách study audit logs theo site (filter theo StudyIUID, date range)
  - FR172b: Download log file của một study cụ thể
  - FR172c: Xem metadata của study từ log (DICOM metadata snapshot tại thời điểm ghi log)
  - FR172d: Cấu hình thời gian lưu giữ log (logStudyTime, tính bằng ngày; dùng bởi service CLEAN_DATA_STUDY_LOG)

- FR173: Hỗ trợ AI Integration UI (AI tools management)
  - FR173a: Danh sách AI tools đã cấu hình per site (name, URL, trạng thái)
  - FR173b: Thêm / sửa AI tool configuration (endpoint URL, custom headers JSON, query params/attrs)
  - FR173c: Sync study lên AI tool thủ công (chọn một hoặc nhiều studyIUIDs, gửi qua syncAI API)
  - FR173d: Upload study vào AI queue (async, qua UPLOAD_AI job queue, chọn model từ ModelName constants)
  - FR173e: Cấu hình anonymization khi gửi lên AI (tùy chọn remove patient information — PatientName → "Patient Name")
  - FR173f: Xem trạng thái sync/upload AI (track_id từ AI response, lỗi đồng bộ per study)

- FR174: Hỗ trợ Reports/Analytics UI
  - FR174a: Dashboard tổng quan per site: số study (num_studies), breakdown modality, tổng dung lượng raw (total_raw_size) và compressed (total_compress_size), last_update
  - FR174b: Phân tích dung lượng storage theo từng node (storage_class: ONLINE/NEARLINE/OFFLINE, tổng số study per node)
  - FR174c: Filter báo cáo theo StudyDate range
  - FR174d: Filter báo cáo theo tags và tên site
  - FR174e: So sánh tỉ lệ nén (raw vs compressed) để đánh giá hiệu quả compression

- FR175: Hỗ trợ Tool UI — tách thành các công cụ riêng biệt:
  - FR175a: **Sync DB → Elasticsearch UI**: start/stop/reset quá trình đồng bộ (SYNC_DB_ELASTIC service); hiển thị progress (SyncStudyCount, lastStudyIUID, isRunning)
  - FR175b: **Sync DB → MySQL UI**: đồng bộ dữ liệu từ Cassandra/Elastic về MySQL
  - FR175c: **Move Storage UI**: di chuyển files DICOM giữa các storage nodes; verify sau khi move (checkMoveStorage / checkMoveStorageV2)
  - FR175d: **Move Folder UI**: di chuyển thư mục storage vật lý (moveFolder)
  - FR175e: **Copy DICOM Instances UI**: copy instances giữa sites hoặc storage nodes (ToolCopyInstance); hỗ trợ consumerCopyInstance queue
  - FR175f: **Import từ Storage UI**: import studies từ external storage vào hệ thống (ToolImportFromStorage)
  - FR175g: **Import từ Cloud UI**: import studies từ cloud storage (importFromCloud)
  - FR175h: **Export Sites UI**: export toàn bộ data của một hoặc nhiều sites (ExportSites)
  - FR175i: **Upload DICOM lên Cloud UI**: upload study files lên cloud storage (uploadDicom2Cloud)
  - FR175j: **STOW Studies UI**: gửi studies lên DICOM server qua STOW-RS (StowStudies); hỗ trợ batch
  - FR175k: **Compress DICOM UI**: nén/tái nén DICOM files (compressDicom); theo dõi tiến trình
  - FR175l: **Extract DICOM từ MIME UI**: tách file DICOM từ MIME multipart (extractDicomFromMime)
  - FR175m: **Check Files Integrity UI**: kiểm tra tính toàn vẹn file (ToolCheckFiles — checksum verification)
  - FR175n: **Check Study Integrity UI**: kiểm tra study có đủ series/instances không (ToolCheckStudy)
  - FR175o: **Check Import Status UI**: xem trạng thái và kết quả của các import job (ToolCheckImport)
  - FR175p: **Download Files by Site UI**: tải toàn bộ files của một site về (ToolDownloadFilesBySite)
  - FR175q: **Clean Data UI**: xóa data orphan, rác không cần thiết (cleanData, clearSite, clearCephPool)
  - FR175r: **Forward Message UI**: forward Kafka messages thủ công giữa topics (consumerForwardMessage)
