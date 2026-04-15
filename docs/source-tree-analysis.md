# Source Tree Analysis - PACS2

## Project Structure Overview

```
pacs2/                          # Project root (PACS2 Medical Imaging System)
├── Module/                     # Module-based architecture
│   ├── company/               # Core company utilities (framework)
│   ├── pacs/                 # Core PACS functionality
│   ├── pacsui/               # PACS User Interface
│   ├── ris/                  # Radiology Information System
│   ├── risui/                # RIS User Interface
│   └── companyui/            # Company UI components
├── Config/                    # Configuration files
├── Docroot/                  # Web document root
├── sql/                      # Database migrations
├── tests/                    # Test files
├── vendor/                   # Composer dependencies
└── _bmad/                   # BMAD system (not part of PACS)
```

---

## Critical Directories Explained

### Module/company/ - Core Framework

| Directory | Purpose |
|-----------|---------|
| `company/mvc/` | MVC Framework (Router, Controller, View, Bootstrap) |
| `company/auth/` | Authentication (Auth, AuthCtrl, LocalDBAuth) |
| `company/session/` | Session management |
| `company/sql/` | Database layer (Query, Mapper, DB, AnyMapper) |
| `company/cache/` | Caching (Redis, APCU, FileCache) |
| `company/elasticsearch/` | Elasticsearch integration |
| `company/kafka/` | Kafka producer/consumer |
| `company/file/` | File handling |
| `company/encrypt/` | Encryption utilities |
| `company/log/` | Logging (Monolog, Fluentd, Elasticsearch) |
| `company/queue/` | Message queue |
| `company/queueSql/` | SQL-based queue |
| `company/cassandra/` | Cassandra integration |

### Module/pacs/ - Core PACS Functionality

| Directory | Purpose |
|-----------|---------|
| `pacs/wado/` | DICOM WADO-RS/WADO-URI server |
| `pacs/qidors/` | DICOM QIDO-RS query service |
| `pacs/stow/` | DICOM STOW-RS store service |
| `pacs/study/` | Study management |
| `pacs/series/` | Series management |
| `pacs/instance/` | Instance management |
| `pacs/file/` | File storage and retrieval |
| `pacs/media/` | Media (CD/DVD) management |
| `pacs/storage/` | Storage management |
| `pacs/ae/` | Application Entity (AE) management |
| `pacs/mwl/` | Modality Worklist (MWL) |
| `pacs/viewer/` | Viewer integration |
| `pacs/dicomnet/` | DICOM network services |
| `pacs/consumer/` | Message consumer |
| `pacs/consumerprocess/` | Consumer process handler |
| `pacs/swooleserver/` | Swoole async server |
| `pacs/ai/` | AI integration |
| `pacs/ris/` | RIS integration |
| `pacs/publiclink/` | Public link generation |
| `pacs/compression/` | Image compression |
| `pacs/log/` | PACS logging |
| `pacs/integration/` | External integrations |
| `pacs/hl7server/` | HL7 server |
| `pacs/tool/` | Administrative tools |
| `pacs/accessmanagement/` | Access control |
| `pacs/dicomtagmorphing/` | DICOM tag morphing |
| `pacs/studylog/` | Study change logging |

### Module/pacsui/ - UI Modules

| Directory | Purpose |
|-----------|---------|
| `pacsui/viewer/` | DICOM viewer interface |
| `pacsui/study/` | Study list UI |
| `pacsui/storage/` | Storage management UI |
| `pacsui/zone/` | Zone management |
| `pacsui/ae/` | AE management UI |
| `pacsui/mwl/` | Worklist UI |
| `pacsui/setting/` | Settings UI |
| `pacsui/queue/` | Queue monitoring |
| `pacsui/tool/` | Tools UI |
| `pacsui/report/` | Reporting UI |
| `pacsui/ai/` | AI features UI |
| `pacsui/accessmanagement/` | Access management UI |

### Module/ris/ - RIS Modules

| Directory | Purpose |
|-----------|---------|
| `ris/setting/` | RIS settings |
| `ris/duty/` | Duty scheduling |

---

## Entry Points

### Web Entry Point
```
Docroot/index.php
```
- Initializes the Slim framework
- Loads module configuration
- Routes requests to appropriate controllers

### CLI Entry Points
```
Module/pacs/swooleserver/Exec/swooleServer.php    # Async server
Module/pacs/consumerProcess/Exec/consumerProcess.php  # Message consumer
Module/pacs/dicomnet/Exec/DicomServer/             # DICOM server
Module/pacs/hl7server/Exec/                       # HL7 server
```

---

## Key File Locations

| File | Purpose |
|------|---------|
| `composer.json` | Dependencies |
| `modules.json` | Module list |
| `Config/Enviroments/config.example.json` | Configuration template |
| `sql/v*.sql` | Database migrations |

---

## Integration Points

### DICOM Services
- WADO-RS (Web Access to DICOM Objects - Retrieve)
- QIDO-RS (Query based on ID of DICOM Objects)
- STOW-RS (Store DICOM Objects)
- DICOM C-STORE SCP (Storage SCP)
- DICOM C-MOVE SCP

### External Systems
- RIS (Radiology Information System)
- HL7 Server
- AI Integration
- Cloud Storage (AWS S3, Ceph)

### Internal Services
- Elasticsearch (metadata search)
- Redis (caching, sessions)
- Kafka (async messaging)
- MySQL (relational data)
