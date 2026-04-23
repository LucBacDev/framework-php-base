# Feedback doi chieu PRD Task Management vs BRD

**Tai lieu doi chieu:**
- BRD: `docs/BRD Quản lý công việc doanh nghiệp.md`
- PRD: `_bmad-output/planning-artifacts/prd-task-management.md`

**Ket luan nhanh:** PRD **nhin chung da bám dung BRD** cho pham vi Task Management, nhung con mot so diem can chinh de khop hon va tang tinh san sang trien khai.

## Diem da phu hop tot

- Bao phu dung nhom chuc nang Task Management: khoi tao task, giao ca nhan/phong ban, deadline/canh bao, state flow, cap nhat tien do, dashboard, bao cao.
- Luong nghiep vu chinh phu hop BRD: giao viec -> thuc hien -> gui duyet -> phe duyet/lam lai.
- NFR cot loi co trong PRD: RBAC, co lap theo phong ban, 24/7, response <= 2 giay, browser va mobile support.
- Acceptance criteria quan trong tu BRD da duoc dua vao PRD.

## Diem chua khop hoac con thieu

1. **Muc do uu tien task chua day du**
   - BRD FR-05: `Thap/Vua/Cao/Khan`
   - PRD FR-05 hien tai: `Thap/Vua/Cao`
   - Tac dong: Lech nghiep vu voi BRD, co the anh huong dashboard/bao cao uu tien.

2. **Traceability chua day du cho FR-12, FR-13**
   - PRD co `Audit Trail` va `rang buoc xoa`, nhung 3 User Journey chua the hien ro hanh trinh nghiep vu cho 2 FR nay.
   - Tac dong: Khau implement/test de bi sot logic trong duyet va kiem soat du lieu.

3. **Section theo project type `web_app` con thieu**
   - Chua co section ro rang cho `browser_matrix`, `accessibility_level`.
   - `seo_strategy` chua duoc quyet dinh ro (co the N/A neu he thong noi bo, nhung nen ghi ro).

4. **Mot so NFR chua du metric kiem thu**
   - Cac muc reliability/bao toan du lieu/retry thong bao con mo ta dinh tinh.
   - Nen bo sung metric de test objective hon (vi du: retry toi da, ty le thanh cong, RTO/RPO neu co).

## De xuat cap nhat uu tien

- **P1:** Them muc uu tien `Khan` vao FR-05 va cac cho lien quan (validation, dashboard filter, bao cao).
- **P1:** Cap nhat User Journeys hoac them Journey 4 (governance/audit) de map ro FR-12, FR-13.
- **P2:** Them section `Browser Matrix` va `Accessibility Level` (vi du WCAG muc tieu cho web internal).
- **P2:** Chuan hoa metric cho NFR reliability/security de ho tro UAT va test performance.

## Danh gia tong the

- **Muc do dap ung BRD:** ~85-90%
- **Trang thai de di tiep sang phase tiep theo:** **Co the tiep tuc**, nhung nen sua cac muc P1 truoc khi chot baseline PRD.
