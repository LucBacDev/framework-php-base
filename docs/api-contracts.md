# API Contracts - PACS2 Backend

## Overview

This document describes the RESTful API endpoints provided by the PACS2 backend system.

## DICOM WADO-RS Endpoints

### Study Retrieval

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID` | WadoRSCtrl | Get study by UID |
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/metadata` | WadoRSCtrl | Get study metadata |
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/rendered` | WadoRSCtrl | Get rendered study |
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/thumbnail` | WadoRSCtrl | Get study thumbnail |

### Series Retrieval

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/series/:seriesIUID/` | WadoRSCtrl | Get series by UID |
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/series/:seriesIUID/rendered` | WadoRSCtrl | Get rendered series |
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/series/:seriesIUID/thumbnail` | WadoRSCtrl | Get series thumbnail |
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/series/:seriesIUID/metadata` | WadoRSCtrl | Get series metadata |

### Instance Retrieval

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/series/:seriesIUID/instances/:instanceUID` | WadoRSCtrl | Get instance by UID |
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/series/:seriesIUID/instances/:instanceUID/rendered` | WadoRSCtrl | Get rendered instance |
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/series/:seriesIUID/instances/:instanceUID/thumbnail` | WadoRSCtrl | Get instance thumbnail |
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/series/:seriesIUID/instances/:instanceUID/metadata` | WadoRSCtrl | Get instance metadata |
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/series/:seriesIUID/instances/:instanceUID/filepath` | WadoRSCtrl | Get instance file path |

### Frame Retrieval

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/series/:seriesIUID/instances/:instanceUID/frames/:frames` | WadoRSCtrl | Get frames in instance |
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/series/:seriesIUID/instances/:instanceUID/frames/:frames/rendered` | WadoRSCtrl | Get rendered frames |
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/series/:seriesIUID/instances/:instanceUID/frames/:frames/thumbnail` | WadoRSCtrl | Get frame thumbnail |

### Bulk Data

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/series/:seriesIUID/instances/:instanceUID/bulk/:tag` | WadoRSCtrl | Get bulk data by tag |

### WADO URI

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/rest/:aet/wadouri` | WadoURICtrl | WADO URI retrieval |
| GET | `/:siteID/rest/:aet/wado` | WadoURICtrl | WADO retrieval |
| GET | `/:aet/wado(/wado)` | WadoURICtrl | WADO URI for RIS |

### Image Server

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:aet/imageserver/dicomData/GetImage` | WadoURICtrl | Get DICOM image |
| GET | `/:aet/imageserver/DicomImage/GetImageJpeg` | WadoURICtrl | Get JPEG image |
| GET | `/:aet/imageserver/StudyData/GetStudies` | WadoURICtrl | Get studies for viewer |

---

## DICOM QIDO-RS Endpoints (Query)

### Studies

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/rest/:aet(/rs)/studies` | QidoRSCtrl | Query studies |
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/series` | QidoRSCtrl | Query series by study |
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/series/:seriesIUID/instances` | QidoRSCtrl | Query instances by study/series |
| GET | `/:siteID/rest/:aet(/rs)/studies/:studyIUID/instances` | QidoRSCtrl | Query instances by study |

### Series

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/rest/:aet(/rs)/series` | QidoRSCtrl | Query series |

### Instances

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/rest/:aet(/rs)/instances` | QidoRSCtrl | Query instances |

### Trash Query

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/rest/:aet/studyTrash` | QidoRSCtrl | Query studies in trash |
| GET | `/:siteID/rest/:aet/studyTrash/:studyUID/series` | QidoRSCtrl | Query series in trash |

---

## DICOM STOW-RS Endpoints (Store)

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| POST, PUT | `/:siteID/rest/:aet(/rs)/studies` | StowCtrl | Store studies/instances |

---

## UI REST Endpoints

### Study Management

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/study/list` | StudyCtrl | Get study list |
| GET | `/:siteID/study/bin` | StudyCtrl | Get study bin (trash) |
| GET | `/:siteID/study/list/:studyIUIDs` | StudyCtrl | Get study information |
| POST, PUT | `/:siteID/rest/study(/:studyIUID)` | StudyCtrl | Create/update study |
| POST | `/rest/:aet/study/copy` | StudyCtrl | Copy study |
| POST | `/rest/:aet/study/modify` | StudyCtrl | Modify study |
| POST | `/:siteID/rest/:aet/study/detachOrMerge` | StudyCtrl | Detach or merge study |

### Viewer

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/viewer` | ViewerCtrl | Get viewer list |
| POST, PUT | `/:siteID/rest/viewer(/:viewerID)` | ViewerCtrl | Create/update viewer |
| GET | `/:siteID/rest/viewer/studies/:studyIUID/wadolist` | ViewerCtrl | Get WADO list |
| GET | `/dcm/vietradviewer/:studyIUID` | ViewerCtrl | Get study struct |
| GET | `/dcm/vietradviewer/accession/:accessionNo` | ViewerCtrl | Get study by accession |

### Storage

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/storage` | StorageCtrl | Get storage list |

### AE (Application Entity)

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/aliasAe` | AeCtrl | Get alias AE list |
| GET | `/:siteID/myAe` | AeCtrl | Get my AE list |
| GET | `/:siteID/otherAe` | AeCtrl | Get other AE list |
| GET | `/:siteID/agentAe` | AeCtrl | Get agent AE list |

### MWL (Modality Worklist)

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/mwl` | MwlCtrl | Get worklist |

### Tool

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/tool/StudySync` | ToolCtrl | Tool sync study |
| GET | `/:siteID/tool/import` | ToolCtrl | Tool import |
| GET | `/:siteID/tool/copy` | ToolCtrl | Tool copy |
| GET | `/:siteID/tool/MoveStorage` | ToolCtrl | Tool move storage |
| POST | `/master/rest/tool/syncStudy` | ToolCtrl | Sync study action |

### Settings

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/pacs-setting` | SettingCtrl | Get PACS settings |
| GET | `/:siteID/pacs-site-setting` | SettingCtrl | Get site settings |

### Zone

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/zone` | ZoneCtrl | Get zone list |

### Queue/Message

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/topics` | QueueCtrl | Get topics |
| GET | `/:siteID/messages` | QueueCtrl | Get messages |

### Logs

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| GET | `/:siteID/log` | LogCtrl | Get server logs |
| GET | `/:siteID/rest/studylog` | StudyLogCtrl | Get study logs |

---

## Authentication

- Uses JWT tokens via Firebase PHP JWT
- Session-based authentication via company/session module
- Token-based API authentication

---

## Error Handling

- Standard HTTP status codes (200, 400, 404, 500)
- DICOM compliant error responses
- JSON error format for REST endpoints
