# Architecture Documentation - PACS2

## Executive Summary

PACS2 is a comprehensive **Picture Archiving and Communication System (PACS)** built with PHP 8.0+ using the Slim 2.0 framework. It implements DICOM medical imaging standards for healthcare facilities, providing storage, retrieval, and viewing of medical images.

---

## Technology Stack

| Layer | Technology |
|-------|------------|
| **Language** | PHP 8.0+ |
| **Framework** | Slim 2.0 |
| **Database** | MySQL 5.7+ |
| **Search Engine** | Elasticsearch 7.x |
| **Cache** | Redis 7.4+ |
| **Message Queue** | Kafka |
| **Object Storage** | AWS S3 / Ceph |
| **Logging** | Monolog, Fluentd |

---

## Architecture Pattern

### Service-Oriented Architecture (SOA) with Module-Based Design

PACS2 follows a **modular SOA pattern**:

```
┌─────────────────────────────────────────────────────────────┐
│                    PACS2 Architecture                       │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌─────────────┐   ┌─────────────┐   ┌─────────────┐      │
│  │   pacsui/   │   │    pacs/    │  │    ris/     │      │
│  │   (UI)      │   │  (Core PACS)│  │    (RIS)    │      │
│  └──────┬──────┘   └──────┬──────┘   └──────┬──────┘      │
│         │                 │                  │              │
│         └─────────────────┼──────────────────┘              │
│                           │                                  │
│                    ┌──────▼──────┐                          │
│                    │  company/   │                          │
│                    │ (Framework)│                          │
│                    └──────┬──────┘                          │
│                           │                                  │
│         ┌─────────────────┼─────────────────┐               │
│         │                 │                 │               │
│   ┌─────▼─────┐    ┌─────▼─────┐    ┌─────▼─────┐        │
│   │   MySQL   │    │Elasticsearch│    │   Redis   │        │
│   └───────────┘    └───────────┘    └───────────┘        │
│                                                              │
│   ┌───────────┐    ┌───────────┐                           │
│   │   Kafka   │    │ AWS S3/Ceph│                          │
│   └───────────┘    └───────────┘                           │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## Core Components

### 1. Module System (company/mvc)

The framework provides a modular architecture:

| Component | File | Purpose |
|-----------|------|---------|
| Router | `Router.php` | URL routing and mapping |
| Controller | `Controller.php` | Request handling |
| View | `View.php` | Response rendering |
| Bootstrap | `Bootstrap.php` | Application initialization |
| Module | `Module.php` | Module loading |

### 2. DICOM Services Layer (Module/pacs/)

#### WADO-RS (Web Access to DICOM Objects - Retrieve)
- Implements DICOM WADO-RS standard
- Provides study, series, instance retrieval
- Supports thumbnails and rendered images
- Location: `Module/pacs/wado/`

#### QIDO-RS (Query based on ID of DICOM Objects)
- Implements DICOM QIDO-RS standard
- Provides search/query capabilities
- Elasticsearch-backed queries
- Location: `Module/pacs/qidors/`

#### STOW-RS (Store DICOM Objects)
- Implements DICOM STOW-RS standard
- Receives and stores DICOM studies
- Location: `Module/pacs/stow/`

### 3. Data Layer

#### MySQL Schema
- **pacs_study** - Study metadata
- **pacs_series** - Series metadata
- **pacs_instance** - Instance metadata
- **pacs_file** - File storage references

#### Elasticsearch Indices
- **pacs_study** - Study search
- **pacs_series** - Series search
- **pacs_instance** - Instance search
- **pacs_mwl** - Worklist search

### 4. Cache Layer (Redis)
- Session management
- Query result caching
- Thumbnail caching
- Metadata caching

### 5. Message Queue (Kafka)
- Async study processing
- File storage operations
- Elasticsearch indexing
- Cache invalidation

---

## Data Flow

### Study Storage Flow (STOW-RS)

```
1. Client → POST /rest/:aet/rs/studies (DICOM)
2. StowCtrl receives DICOM
3. Parse DICOM → Extract metadata
4. Save to MySQL (study, series, instance)
5. Send to Kafka → Async processing
6. Consumer → Index to Elasticsearch
7. Consumer → Store file to S3/Ceph
8. Update cache (Redis)
9. Return success to client
```

### Study Retrieval Flow (WADO-RS)

```
1. Client → GET /rest/:aet/rs/studies/:uid
2. WadoRSCtrl receives request
3. Check cache (Redis) → Return if cached
4. Query Elasticsearch for metadata
5. Retrieve file from S3/Ceph/local
6. Process image (if needed)
7. Return DICOM to client
```

### Query Flow (QIDO-RS)

```
1. Client → GET /rest/:aet/rs/studies?query
2. QidoRSCtrl receives query
3. Parse query parameters
4. Execute against Elasticsearch
5. Return results (JSON)
```

---

## Module Structure

### Module Naming Convention

```
Module/
├── company/           # Core framework modules
│   ├── mvc/          # MVC framework
│   ├── auth/         # Authentication
│   ├── session/      # Session management
│   ├── sql/          # Database layer
│   ├── cache/        # Caching
│   ├── elasticsearch/ # ES client
│   ├── kafka/        # Message queue
│   ├── file/         # File handling
│   └── log/          # Logging
│
├── pacs/             # Core PACS modules
│   ├── wado/         # WADO-RS server
│   ├── qidors/       # QIDO-RS server
│   ├── stow/         # STOW-RS server
│   ├── study/        # Study management
│   ├── series/       # Series management
│   ├── instance/     # Instance management
│   ├── storage/      # Storage management
│   ├── dicomnet/     # DICOM network
│   ├── swooleserver/ # Async server
│   └── ...
│
├── pacsui/           # UI modules
│   ├── viewer/       # DICOM viewer
│   ├── study/        # Study list UI
│   └── ...
│
└── ris/              # RIS modules
    ├── duty/        # Duty scheduling
    └── ...
```

---

## Security

### Authentication

- JWT tokens (Firebase PHP JWT)
- Session-based authentication
- API key support for services

### Authorization

- Role-based access control (RBAC)
- Site-based isolation
- AE Title restrictions

### Network Security

- DICOM port access control
- TLS/SSL for web traffic
- Firewall rules for services

---

## Scalability

### Horizontal Scaling

1. **Load Balancer** → Multiple PACS2 instances
2. **Database Sharding** → Partition by site_id
3. **Elasticsearch** → 3-node cluster minimum
4. **Kafka** → Multi-broker cluster

### Performance Optimizations

- Redis caching for frequent queries
- Elasticsearch indexing for fast search
- CDN integration for thumbnail delivery
- S3/Ceph for distributed storage

---

## Version History

| Version | Date | Key Features |
|---------|------|--------------|
| 1.10 | Jan 2026 | Sharding, Multi-service manager |
| 1.9 | Dec 2024 | Cache stow metadata |
| 1.8 | Oct 2024 | Forward cache, Study log |
| 1.4 | May 2023 | PHP 8.1 support, Queue module |
| 1.3 | Sep 2022 | Swoole WADO support |
| 1.2 | May 2022 | Media module, Swoole server |

---

## Deployment Architecture

```
                    ┌──────────────┐
                    │ Load Balancer│
                    └──────┬───────┘
                           │
         ┌─────────────────┼─────────────────┐
         │                 │                 │
   ┌─────▼─────┐     ┌─────▼─────┐     ┌─────▼─────┐
   │  PACS2    │     │  PACS2    │     │  PACS2    │
   │ Instance1 │     │ Instance2 │     │ Instance3 │
   └─────┬─────┘     └─────┬─────┘     └─────┬─────┘
         │                 │                 │
         └─────────────────┼─────────────────┘
                           │
      ┌────────┬──────────┼──────────┬────────┐
      │         │          │          │         │
┌─────▼───┐ ┌──▼──┐  ┌───▼───┐ ┌───▼──┐ ┌───▼───┐
│ MySQL   │ │Redis│  │Elastic │ │Kafka │ │  S3   │
│Primary  │ │     │  │Search │ │     │ │Ceph   │
└─────────┘ └─────┘  └───────┘ └──────┘ └───────┘
```

---

## Integration Points

### External Systems

| System | Protocol | Purpose |
|--------|----------|---------|
| RIS | DICOM, HL7 | Study scheduling |
| AI | REST API | Image analysis |
| Modality | DICOM C-STORE | Image acquisition |
| Viewer | WADO-RS | Image display |

### Cloud Services

| Service | Integration |
|---------|-------------|
| AWS S3 | File storage |
| Ceph | S3-compatible storage |
