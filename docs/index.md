# Project Documentation Index - PACS2

## Project Overview

- **Type:** Monolith
- **Primary Language:** PHP 8.0+
- **Architecture:** Service-Oriented with Module-based design
- **Version:** 1.10
- **Description:** Medical Imaging System (PACS) - DICOM server and viewer

---

## Quick Reference

- **Tech Stack:** PHP 8.0, Slim 2.0, MySQL, Elasticsearch, Redis, Kafka
- **Entry Point:** Docroot/index.php
- **Framework:** Slim 2.0 MVC
- **Module System:** company/mvc

---

## Generated Documentation

### Core Documentation

- [Project Overview](./project-overview.md) - Project summary and technology stack
- [Architecture](./architecture.md) - System architecture and design patterns
- [Source Tree Analysis](./source-tree-analysis.md) - Directory structure and key files

### Technical Documentation

- [API Contracts](./api-contracts.md) - REST API endpoints (WADO-RS, QIDO-RS, STOW-RS)
- [Data Models](./data-models.md) - Database schema and Elasticsearch indices
- [Development Guide](./development-guide.md) - Installation, setup, and development

---

## Existing Documentation

- [CHANGELOG.txt](../CHANGELOG.txt) - Version history from v1.1.3 to v1.10

---

## Getting Started

### Prerequisites

- PHP 8.0+
- MySQL 5.7+
- Elasticsearch 7.x
- Redis 7.4+
- Kafka

### Quick Start

1. Clone repository
2. Run `composer install`
3. Copy `Config/Enviroments/config.example.json` to `config.json`
4. Configure database and services
5. Run migrations from `sql/` folder
6. Start web server pointing to `Docroot/`

### Running Services

```bash
# Web server
# Configure Apache/Nginx to Docroot/

# Swoole async server
php Module/pacs/swooleserver/Exec/swooleServer.php

# DICOM server
php Module/pacs/dicomnet/Exec/DicomServer/start.php

# Consumer
php Module/pacs/consumerProcess/Exec/consumerProcess.php
```

---

## Module Structure

| Module | Purpose |
|--------|---------|
| `company/mvc` | Core MVC framework |
| `company/auth` | Authentication |
| `company/sql` | Database layer |
| `company/cache` | Redis caching |
| `pacs/wado` | DICOM WADO-RS |
| `pacs/qidors` | DICOM QIDO-RS |
| `pacs/stow` | DICOM STOW-RS |
| `pacs/study` | Study management |
| `pacs/viewer` | Viewer integration |
| `pacsui/*` | UI modules |

---

## Key Endpoints

### DICOM Services

- WADO-RS: `/rest/:aet/rs/studies/*`
- QIDO-RS: `/:siteID/rest/:aet/rs/studies`
- STOW-RS: `/:siteID/rest/:aet/rs/studies` (POST)

### UI Endpoints

- Study List: `/:siteID/study/list`
- Viewer: `/:siteID/viewer`
- Storage: `/:siteID/storage`

---

## Additional Resources

- **Database Migrations:** `sql/v*.sql`
- **Configuration:** `Config/Enviroments/config.example.json`
- **Module Config:** `modules.json`
- **Dependencies:** `composer.json`

---

*Generated: 2026-03-08*
*Workflow: BMAD document-project v1.2.0*
