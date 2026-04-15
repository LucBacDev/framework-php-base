# Story 4.2: Modality Worklist (MWL)

**Epic:** Epic 4: Integration (RIS/HIS/Modality)

**User Story:**

As a Modality,
I want query danh sách lệnh chụp từ PACS,
So that biết được chỉ định cho bệnh nhân nào.

**Acceptance Criteria:**

**Given** Modality query MWL qua C-FIND
**When** PACS nhận C-FIND request
**Then** Dịch tham số DICOM C-FIND thành RESTful API params tương tứng
**And** Gửi các params lên RIS thông qua url cấu hình RIS của hệ thống
**And** Nhận kết quả và chuyển đổi thành Object để trả thẳng về Modality (không lưu DB pacs local)
**And** (Ngoài ra) nếu cấu hình RIS ở dạng local, hệ thống thực hiện query từ Elastic/Mysql trong backend PACS

**FRs Covered:** FR41, FR41a-FR41b, FR125

**Epic Goal:** Tích hợp với HIS, RIS và Modality devices
