# Project Overview - PACS2

## Project Information

| Property | Value |
|----------|-------|
| **Project Name** | PACS2 |
| **Type** | Medical Imaging System (PACS) |
| **Language** | PHP 8.0+ |
| **Framework** | Slim 2.0 |
| **Version** | 1.10 |

---

## Description

PACS2 is a comprehensive **Picture Archiving and Communication System (PACS)** designed for healthcare facilities. It implements international DICOM (Digital Imaging and Communications in Medicine) standards for storing, retrieving, and viewing medical images such as X-rays, CT scans, MRI, and ultrasound.

---

## Technology Stack

| Category | Technology |
|----------|------------|
| Language | PHP 8.0+ |
| Web Framework | Slim 2.0 |
| Database | MySQL 5.7+ |
| Search Engine | Elasticsearch 7.x |
| Cache | Redis 7.4+ |
| Message Queue | Kafka |
| Storage | AWS S3 / Ceph |
| Logging | Monolog, Fluentd |

---

## Architecture Type

**Monolith** - Single unified codebase with module-based design

---

## Repository Structure

```
Module/
├── company/     # Core framework (MVC, Auth, SQL, Cache)
├── pacs/       # Core PACS (DICOM services, storage)
├── pacsui/     # UI modules
├── ris/        # Radiology Information System
└── companyui/  # UI components
```

---

## Key Features

- **DICOM WADO-RS** - Web-based DICOM retrieval
- **DICOM QIDO-RS** - Query service
- **DICOM STOW-RS** - Store DICOM objects
- **DICOM Network** - C-STORE SCP support
- **HL7 Integration** - Healthcare messaging
- **Elasticsearch** - Fast metadata search
- **Redis Caching** - Performance optimization
- **Kafka Queuing** - Async processing
- **Cloud Storage** - S3/Ceph integration

---

## Generated Documentation

- [Architecture](./architecture.md) - System architecture
- [API Contracts](./api-contracts.md) - REST API endpoints
- [Data Models](./data-models.md) - Database schema
- [Source Tree](./source-tree-analysis.md) - Directory structure
- [Development Guide](./development-guide.md) - Setup and development

---

## Getting Started

See [Development Guide](./development-guide.md) for:
- Installation steps
- Configuration
- Running the application
- Testing

---

## External Links

- **Repository**: Internal
- **Documentation**: This documentation suite
- **Support**: Internal team
