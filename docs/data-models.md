# Data Models - PACS2 Backend

## Overview

This document describes the database schema for the PACS2 system. The system uses MySQL for relational data and Elasticsearch for metadata indexing.

---

## Core PACS Tables

### pacs_study

Main study table storing DICOM study information.

| Column | Type | Description |
|--------|------|-------------|
| study_iuid | varchar(255) | Study Instance UID (Primary Key) |
| site_id | varchar(255) | Site identifier |
| study_id | varchar(255) | Study ID |
| updated_time | timestamp | Last update time |
| study_datetime | timestamp | Study date/time |
| accession_no | varchar(255) | Accession number |
| ref_physician | varchar(255) | Referring physician |
| study_desc | text | Study description |
| src_aet | varchar(255) | Source AE Title |
| retrieve_aets | varchar(255) | Retrieve AE Titles |
| ext_retr_aet | varchar(255) | External retrieve AE |
| study_status_id | varchar(255) | Study status ID |
| study_attrs | longblob | DICOM study attributes |
| pat_id | varchar(255) | Patient ID |
| pat_id_issuer | varchar(255) | Patient ID issuer |
| pat_name | varchar(255) | Patient name |
| pat_age | varchar(255) | Patient age |
| pat_birthdate | date | Patient birthdate |
| pat_sex | varchar(255) | Patient sex |
| pat_attrs | mediumtext | Patient attributes |
| level | int | Permission level |
| permission | text | Permission details |
| attrs | text | Additional attributes |
| image_types | text | Image types |
| storage_instances | text | Storage instance info |
| storage_information | text | Storage information |
| num_series | int | Number of series |
| num_instances | int | Number of instances |
| created_time | timestamp | Creation time |
| deleted_time | timestamp | Deletion time |
| status | int | Status |
| status_study | int | Study status |
| study_original | varchar(255) | Original study UID |

**Indexes:**
- `study_site_id(site_id)`
- `study_accession_no(site_id, accession_no)`
- `study_create_time(created_time)`
- `study_original_site_id(site_id, study_original)`

---

### pacs_series

Series table storing DICOM series information.

| Column | Type | Description |
|--------|------|-------------|
| series_iuid | varchar(255) | Series Instance UID (PK) |
| study_iuid | varchar(255) | Parent Study UID |
| site_id | varchar(255) | Site identifier |
| series_no | int | Series number |
| modality | varchar(255) | Modality (CT, MR, CR, etc.) |
| series_desc | text | Series description |
| body_part | varchar(255) | Body part examined |
| station_name | varchar(255) | Station name |
| performed_by | varchar(255) | Performed by |
| date_time | timestamp | Series date/time |
| num_instances | int | Number of instances |
| instances_attrs | longtext | Instance attributes |
| attrs | text | Additional attributes |
| created_time | timestamp | Creation time |
| deleted_time | timestamp | Deletion time |
| storage_information | text | Storage information |

---

### pacs_instance

Instance table storing DICOM instance/file information.

| Column | Type | Description |
|--------|------|-------------|
| sop_iuid | varchar(255) | SOP Instance UID (PK) |
| sop_cuid | varchar(255) | SOP Class UID |
| series_iuid | varchar(255) | Parent Series UID |
| study_iuid | varchar(255) | Parent Study UID |
| site_id | varchar(255) | Site identifier |
| instance_no | int | Instance number |
| file_path | varchar(255) | File path |
| file_size | bigint | File size |
| file_md5 | varchar(255) | File MD5 hash |
| transfer_syntax | varchar(255) | Transfer syntax |
| image_type | varchar(255) | Image type |
| rows | int | Image rows |
| columns | int | Image columns |
| bits_allocated | int | Bits allocated |
| bits_stored | int | Bits stored |
| pixel_representation | int | Pixel representation |
| photon_energy | varchar(255) | Photon energy |
| window_center | varchar(255) | Window center |
| window_width | varchar(255) | Window width |
| created_time | timestamp | Creation time |
| deleted_time | timestamp | Deletion time |

---

### pacs_file

File storage table.

| Column | Type | Description |
|--------|------|-------------|
| file_uid | varchar(255) | File UID (PK) |
| study_iuid | varchar(255) | Study UID |
| series_iuid | varchar(255) | Series UID |
| sop_iuid | varchar(255) | SOP Instance UID |
| file_path | varchar(255) | File path |
| file_size | bigint | File size |
| storage_id | varchar(255) | Storage ID |
| storage_type | varchar(255) | Storage type (online/nearline/offline) |
| status | int | File status |
| created_time | timestamp | Creation time |

---

## Storage & Media Tables

### pacs_storage

| Column | Type | Description |
|--------|------|-------------|
| storage_id | varchar(255) | Storage ID (PK) |
| storage_name | varchar(255) | Storage name |
| storage_type | varchar(255) | Storage type |
| storage_path | varchar(255) | Storage path |
| storage_url | varchar(255) | Storage URL |
| capacity | bigint | Total capacity |
| used_space | bigint | Used space |
| site_id | varchar(255) | Site ID |

### pacs_media

| Column | Type | Description |
|--------|------|-------------|
| media_id | varchar(255) | Media ID (PK) |
| media_label | varchar(255) | Media label |
| creation_date | timestamp | Creation date |
| num_studies | int | Number of studies |
| num_series | int | Number of series |
| num_instances | int | Number of instances |
| status | int | Media status |

---

## AE (Application Entity) Tables

### pacs_my_ae

| Column | Type | Description |
|--------|------|-------------|
| ae_id | varchar(255) | AE ID (PK) |
| aet | varchar(255) | AE Title |
| hostname | varchar(255) | Hostname |
| port | int | Port |
| encryption | int | Encryption enabled |
| description | text | Description |

### pacs_other_ae

| Column | Type | Description |
|--------|------|-------------|
| ae_id | varchar(255) | AE ID (PK) |
| aet | varchar(255) | AE Title |
| hostname | varchar(255) | Hostname |
| port | int | Port |
| description | text | Description |
| active | tinyint | Active status |

### pacs_agent_ae

| Column | Type | Description |
|--------|------|-------------|
| agent_ae_id | varchar(255) | Agent AE ID (PK) |
| aet | varchar(255) | AE Title |
| remote_aet | varchar(255) | Remote AE Title |
| remote_host | varchar(255) | Remote hostname |
| remote_port | int | Remote port |

---

## RIS Integration Tables

### pacs_ris

| Column | Type | Description |
|--------|------|-------------|
| id | varchar(255) | ID (PK) |
| study_iuid | varchar(255) | Study UID |
| accession_no | varchar(255) | Accession number |
| patient_id | varchar(255) | Patient ID |
| patient_name | varchar(255) | Patient name |
| modality | varchar(255) | Modality |
| scheduled_date | date | Scheduled date |
| status | int | Status |

### pacs_ris_study_log

| Column | Type | Description |
|--------|------|-------------|
| id | varchar(255) | ID (PK) |
| study_iuid | varchar(255) | Study UID |
| accession_no | varchar(255) | Accession number |
| status | varchar(255) | Status |
| action | varchar(255) | Action |
| created_time | timestamp | Creation time |

---

## MWL (Modality Worklist) Tables

### mwl

| Column | Type | Description |
|--------|------|-------------|
| mwl_id | varchar(255) | MWL ID (PK) |
| patient_id | varchar(255) | Patient ID |
| patient_name | varchar(255) | Patient name |
| patient_birthdate | date | Patient birthdate |
| patient_sex | varchar(255) | Patient sex |
| accession_no | varchar(255) | Accession number |
| study_iuid | varchar(255) | Study UID |
| modality | varchar(255) | Modality |
| scheduled_date | date | Scheduled date |
| scheduled_time | time | Scheduled time |
| requesting_physician | varchar(255) | Requesting physician |
| procedure_desc | varchar(255) | Procedure description |
| station_name | varchar(255) | Station name |
| status | varchar(255) | Status |

---

## User & Auth Tables

### user_user

| Column | Type | Description |
|--------|------|-------------|
| user_id | varchar(255) | User ID (PK) |
| username | varchar(255) | Username |
| password | varchar(255) | Password hash |
| full_name | varchar(255) | Full name |
| email | varchar(255) | Email |
| department | varchar(255) | Department |
| role_id | varchar(255) | Role ID |
| active | int | Active status |
| created_time | timestamp | Creation time |

### user_role_list

| Column | Type | Description |
|--------|------|-------------|
| role_id | varchar(255) | Role ID (PK) |
| role_name | varchar(255) | Role name |
| description | text | Description |
| permissions | text | Permissions |

---

## Elasticsearch Indices

The system uses Elasticsearch for metadata indexing with the following indices:

- `pacs_study` - Study metadata
- `pacs_series` - Series metadata  
- `pacs_instance` - Instance metadata
- `pacs_mwl` - Worklist metadata
- `pacs_media` - Media metadata

---

## Database Migrations

Database schema versions (from sql/ folder):

| Version | File | Description |
|---------|------|-------------|
| 1.2 | v1.2.sql | Initial schema |
| 1.2.1 | v1.2.1.sql | Swoole support |
| 1.2.2 | v1.2.2.sql | Additional fields |
| 1.4 | v1.4.sql | Series attributes |
| 1.4.1 | v1.4.1.sql | AI integration |
| 1.4.2 | v1.4.2.sql | Import tools |
| 1.5 | v1.5.sql | Modality AgentAE |
| 1.5.2 | v1.5.2.sql | Additional features |
| 1.5.3 | v1.5.3.sql | Minor updates |
| 1.6 | v1.6.sql | DicomNet v1.4 |
| 1.6.1 | v1.6.1.sql | Integration table |
| 1.8 | v1.8.sql | Forward cache |
| 1.9 | v1.9.sql | Cache stow metadata |
| 1.9.1 | v1.9.1.sql | Filters |
| 1.9.2 | v1.9.2.sql | Modify study |
| 1.9.3 | v1.9.3.sql | Wado cache |
| 1.9.4 | v1.9.4.sql | QIDO cache |
| 1.9.4.1 | v1.9.4.1.sql | Sharding |

---

## Sharding Support

As of v1.10, the system supports database sharding for the following tables:

- pacs_study
- pacs_series
- pacs_instance
- pacs_files
- pacs_media
- pacs_study_trash
- pacs_series_trash
- pacs_instance_trash
- pacs_media_trash
