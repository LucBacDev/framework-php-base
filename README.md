# PACS2 - Picture Archiving and Communication System

<p align="center">
  <img src="docs/assets/pacs-logo.png" alt="PACS2 Logo" width="200"/>
</p>

> Hệ thống lưu trữ và truyền hình ảnh y tế (PACS) tuân thủ tiêu chuẩn DICOM quốc tế

[![Version](https://img.shields.io/badge/version-1.10-blue.svg)](VERSION.txt)
[![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)](https://www.php.net/)
[![Framework](https://img.shields.io/badge/Slim-2.0-green.svg)](https://www.slimframework.com/)

---

## 📋 Mục lục

- [Tổng quan](#-tổng-quan)
- [Tính năng chính](#-tính-năng-chính)
- [Công nghệ sử dụng](#-công-nghệ-sử-dụng)
- [Cấu trúc project](#-cấu-trúc-project)
- [Yêu cầu hệ thống](#-yêu-cầu-hệ-thống)
- [Cài đặt nhanh với Docker](#-cài-đặt-nhanh-với-docker)
- [Cài đặt thủ công](#-cài-đặt-thủ-công)
- [Cấu hình](#-cấu-hình)
- [API Endpoints](#-api-endpoints)
- [Khắc phục sự cố](#-khắc-phục-sự-cố)
- [Bảo mật](#-bảo-mật)

---

## 🎯 Tổng quan

**PACS2** là hệ thống Picture Archiving and Communication System (PACS) toàn diện, được thiết kế cho các cơ sở y tế. Hệ thống triển khai các tiêu chuẩn DICOM (Digital Imaging and Communications in Medicine) quốc tế để lưu trữ, truy xuất và xem hình ảnh y khoa như X-quang, CT scan, MRI và siêu âm.

### Thông tin project

| Thuộc tính | Giá trị |
|------------|---------|
| **Tên project** | PACS2 |
| **Loại** | Medical Imaging System (PACS) |
| **Ngôn ngữ** | PHP 8.0+ |
| **Framework** | Slim 2.0 |
| **Phiên bản** | 1.10 |

---

## ✨ Tính năng chính

### DICOM Services

| Tính năng | Mô tả |
|-----------|-------|
| **WADO-RS** | Web Access to DICOM Objects - Truy xuất hình ảnh qua web |
| **QIDO-RS** | Query based on ID of DICOM Objects - Dịch vụ tìm kiếm |
| **STOW-RS** | Store DICOM Objects - Lưu trữ đối tượng DICOM |
| **DICOM Network** | Hỗ trợ C-STORE SCP cho kết nối thiết bị |

### Các tính năng khác

- 🔍 **Elasticsearch Integration** - Tìm kiếm metadata nhanh chóng
- ⚡ **Redis Caching** - Tối ưu hóa hiệu suất với cache
- 📬 **Kafka Queuing** - Xử lý bất đồng bộ
- ☁️ **Cloud Storage** - Tích hợp AWS S3 / Ceph
- 🏥 **HL7 Integration** - Tích hợp HL7


---

## 🛠 Công nghệ sử dụng

```
┌─────────────────────────────────────────────────────────────┐
│                      Technology Stack                        │
├─────────────────────────────────────────────────────────────┤
│  Language      │  PHP 8.0+                                  │
│  Framework     │  Slim 2.0                                  │
│  Database      │  MySQL 5.7+ / MariaDB                      │
│  Search        │  Elasticsearch 7.x                         │
│  Cache         │  Redis 7.4+                                │
│  Queue         │  Apache Kafka                              │
│  Storage       │  AWS S3 / Ceph                             │
│  Logging       │  Fluentd                          │
└─────────────────────────────────────────────────────────────┘
```

---

## 📁 Cấu trúc project

```
pacs2/
├── Config/                 # Cấu hình ứng dụng
│   ├── Ceph/              # Cấu hình Ceph
│   ├── Enviroments/       # Cấu hình môi trường
│   ├── Includes/          # Include files
│   └── Viewer/            # Cấu hình viewer
├── Deploy-Dev/            # Cấu hình deploy Docker
│   ├── docker-compose.yml # Docker Compose file
│   ├── install.ps1        # Script cài đặt Windows
│   └── kafka/             # Cấu hình Kafka
├── Docroot/               # Thư mục web root
├── Module/                # Các module chính
│   ├── company/          # Framework core (MVC, Auth, SQL, Cache)
│   ├── pacs/             # Core PACS (DICOM services, storage)
│   ├── pacsui/           # UI modules
│   └── companyui/        # UI components
├── sql/                   # Database migrations
├── install/               # Script cài đặt
├── Encrypt/               # Mã hóa
├── FileUpload/            # Upload file
└── docs/                  # Tài liệu
```

### Chi tiết Module

| Module | Mô tả |
|--------|-------|
| `company/mvc` | Framework MVC chính |
| `company/auth` | Xác thực người dùng |
| `company/sql` | Layer database |
| `company/cache` | Quản lý cache |
| `company/elasticsearch` | Client Elasticsearch |
| `company/kafka` | Message queue |
| `pacs/wado` | WADO-RS server |
| `pacs/qidors` | QIDO-RS server |
| `pacs/stow` | STOW-RS server |
| `pacs/dicomnet` | DICOM network |

---

## 📦 Yêu cầu hệ thống

### Development

| Requirement | Version | Ghi chú |
|------------|---------|---------|
| PHP | 8.0+ | Yêu cầu bởi Composer |
| MySQL | 5.7+ | Database chính |
| Elasticsearch | 7.x | Indexing metadata |
| Redis | 7.4+ | Cache và sessions |
| Docker | 20.10+ | Containerization |


### Production Infrastructure

| Service | Mục đích | Khuyến nghị |
|---------|----------|-------------|
| MySQL | Primary database | Master-Slave replication |
| Elasticsearch | Metadata search | 3-node cluster |
| Redis | Cache/Session | Sentinel or Cluster |
| Kafka | Message queue | Multi-broker cluster |

---

## 🚀 Cài đặt nhanh với Docker

### Windows

1. **Cài đặt Docker Desktop**
   - Tải Docker Desktop từ [docker.com](https://www.docker.com/products/docker-desktop)
   - Đảm bảo WSL2 được bật

2. **Clone project**
   ```bash
   git clone <repository-url> pacs2
   cd pacs2
   ```

3. **Chạy script cài đặt**
   ```powershell
   cd Deploy-Dev
   powershell -ExecutionPolicy Bypass -File .\install.ps1
   ```

4. **Nhập thông tin khi được yêu cầu**
   ```
   Enter MariaDB password: ********
   Confirm password: ********
   PACS version (blank = latest): 
   Enter zone ID: default
   ```

5. **Kiểm tra các service đã chạy**
   ```powershell
   docker compose ps
   ```

6. **Chạy install Pacs**
   ```
   docker exec -it pacs bash
   php /var/www/html/install/install.php
   ```

### Linux/macOS

1. **Cài đặt Docker và Docker Compose**
   ```bash
   # Ubuntu/Debian
   sudo apt update
   sudo apt install docker.io docker-compose

   # macOS - Cài đặt Docker Desktop
   ```

2. **Clone và cài đặt**
   ```bash
   git clone <repository-url> pacs2
   cd pacs2/Deploy-Dev
   
   # Tạo thư mục data
   export DOCKER_ROOT=./.docker-data
   export PROJECT_PATH=$(pwd)/..
   mkdir -p $DOCKER_ROOT/{local/{zookeeper,kafka,elastic,mysql,mysql-log,pacs/server},share/kafka/secrets}
   
   # Tạo file .env
   cat > .env << 'EOF'
   PACS_MYSQL_HOST=mysql:3306
   PACS_MYSQL_USER=pacs
   PACS_MYSQL_PASS=your_password
   PACS_DATABASE_NAME=pacsdb
   PACS_ELASTIC_HOSTS=http://elastic:MyElasticPassword_678@elasticsearch:9200
   PACS_REDIS_HOST=redis
   PACS_REDIS_PORT=6379
   PACS_REDIS_PASS=Redis_92932382832
   PACS_KAFKA_BROKER_LIST=kafka:9092
   PACS_VERSION=
   PACS_URL_CODE=http://support.cdhaviet.vn/pacs-cloud
   PACS_ZONE_ID=default
   DOCKER_ROOT=/path/to/.docker-data
   PROJECT_PATH=/path/to/pacs2
   EOF
   
   # Khởi động containers
   docker compose up -d zookeeper kafka elasticsearch kibana redis mysql pacs
   ```

3. **Đợi MySQL khởi động và tạo database**
   ```bash
   docker exec mysql mysqladmin ping
   docker exec mysql mysql -uroot -e "CREATE DATABASE pacsdb CHARACTER SET utf8 COLLATE utf8_unicode_ci;"
   docker exec mysql mysql -uroot -e "CREATE DATABASE cloud_viewer CHARACTER SET utf8 COLLATE utf8_unicode_ci;"
   docker exec mysql mysql -uroot -e "GRANT ALL ON pacsdb.* TO 'pacs'@'%' IDENTIFIED BY 'your_password';"
   docker exec mysql mysql -uroot -e "GRANT ALL ON cloud_viewer.* TO 'pacs'@'%';"
   ```

4. **Chạy install script**
   ```bash
   docker exec pacs php /var/www/html/install/install.php
   ```

### Truy cập các service

Sau khi cài đặt thành công:

| Service | URL | Mô tả |
|---------|-----|-------|
| **PACS Web** | http://localhost:8080 | Web application |
| **PACS DICOM** | http://localhost:4242 | DICOM server |
| **Elasticsearch** | http://localhost:9200 | Search API |
| **Kibana** | http://localhost:5601 | Elasticsearch UI |
| **Kafka UI** | http://localhost:8432 | Kafka management |

---

## 🔧 Cài đặt thủ công

### 1. Cài đặt PHP và dependencies

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install php8.1 php8.1-cli php8.1-common php8.1-mbstring php8.1-xml php8.1-curl php8.1-pdo php8.1-mysql composer

# macOS với Homebrew
brew install php@8.1 composer
```

### 2. Clone project

```bash
git clone <repository-url> pacs2
cd pacs2
```

### 3. Cài đặt Composer dependencies

```bash
composer install
```

### 4. Cấu hình

```bash
cp Config/Enviroments/config.example.json Config/Enviroments/config.json
```

Chỉnh sửa `config.json`:

```json
{
  "PACS_MYSQL_HOST": "localhost:3306",
  "PACS_MYSQL_USER": "root",
  "PACS_MYSQL_PASS": "your_password",
  "PACS_DATABASE_NAME": "pacs",
  "PACS_ELASTIC_HOSTS": "http://localhost:9200",
  "PACS_REDIS_HOST": "localhost",
  "PACS_REDIS_PORT": 6379,
  "PACS_KAFKA_BROKER_LIST": "localhost:9092",
  "PACS_VERSION": "1.10",
  "PACS_ZONE_ID": "default",
  "PACS_TIME_ZONE": "Asia/Bangkok"
}
```

### 5. Database Setup

```bash
mysql -u root -p -e "CREATE DATABASE pacs CHARACTER SET utf8 COLLATE utf8_unicode_ci;"

# Import migrations theo thứ tự
mysql -u root -p pacs < sql/v1.2.sql
mysql -u root -p pacs < sql/v1.4.sql
# ... tiếp tục với các phiên bản khác
```

### 6. Cấu hình Web Server

**Apache:**
```apache
<VirtualHost *:80>
    DocumentRoot /path/to/pacs2/Docroot
    ServerName pacs.local
    
    <Directory /path/to/pacs2/Docroot>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name pacs.local;
    root /path/to/pacs2/Docroot;
    
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 7. Chạy các service

```bash
# Web server (Apache/Nginx)
sudo systemctl start apache2  # hoặc nginx

# Swoole Async Server (optional)
php Module/pacs/swooleserver/Exec/swooleServer.php &

# DICOM Server
php Module/pacs/dicomnet/Exec/DicomServer/start.php &

# Kafka Consumer
php Module/pacs/consumerProcess/Exec/consumerProcess.php &
```

---

## 📡 API Endpoints

### DICOM WADO-RS

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/rest/:aet/rs/studies` | Lấy danh sách studies |
| GET | `/rest/:aet/rs/studies/:uid` | Lấy study cụ thể |
| GET | `/rest/:aet/rs/studies/:uid/metadata` | Lấy metadata study |
| GET | `/rest/:aet/rs/series/:uid` | Lấy series |
| GET | `/rest/:aet/rs/instances/:uid` | Lấy instances |

### DICOM QIDO-RS

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/rest/:aet/rs/studies?PatientName=...` | Tìm kiếm studies |
| GET | `/rest/:aet/rs/series?StudyInstanceUID=...` | Tìm kiếm series |
| GET | `/rest/:aet/rs/instances?SeriesInstanceUID=...` | Tìm kiếm instances |

### DICOM STOW-RS

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| POST | `/rest/:aet/rs/studies` | Lưu DICOM study |

### Authentication

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| POST | `/api/auth/login` | Đăng nhập |
| POST | `/api/auth/logout` | Đăng xuất |
| GET | `/api/auth/me` | Thông tin user hiện tại |

---

## 🔍 Khắc phục sự cố

### Vấn đề thường gặp

| Vấn đề | Giải pháp |
|--------|-----------|
| "PHP version mismatch" | Đảm bảo PHP 8.0+ được cài đặt |
| "Elasticsearch connection failed" | Kiểm tra cấu hình ES host |
| "Redis session errors" | Xác minh kết nối Redis |
| "Module not loading" | Kiểm tra modules.json và paths |
| "MySQL container không khởi động" | Kiểm tra logs: `docker logs mysql` |
| "Kafka consumer lag cao" | Tăng số lượng partitions |

### Lệnh kiểm tra

```bash
# Kiểm tra containers
docker compose ps

# Xem logs
docker compose logs -f pacs
docker compose logs -f elasticsearch
docker compose logs -f kafka

# Kiểm tra Elasticsearch
curl -u elastic:MyElasticPassword_678 http://localhost:9200/_cluster/health

# Kiểm tra MySQL
docker exec mysql mysqladmin ping

# Kiểm tra Redis
docker exec redis redis-cli ping
```

### Logs

| Service | Vị trí log |
|---------|-----------|
| PACS | `docker compose logs pacs` |
| Elasticsearch | `docker compose logs elasticsearch` |
| Kafka | `docker compose logs kafka` |

---

## 🔒 Bảo mật

### Khuyến nghị

1. **HTTPS**: Bật HTTPS trong môi trường production
2. **Firewall**: Cấu hình firewall cho các DICOM ports (104, 11112)
3. **Passwords**: Sử dụng mật khẩu mạnh cho MySQL, Redis, Elasticsearch
4. **Config**: Không commit file config.json vào git
5. **Secrets**: Lưu trữ secrets trong environment variables hoặc secret manager

### Cổng mạng cần mở

| Port | Service | Mô tả |
|------|---------|-------|
| 80/443 | HTTP/HTTPS | Web traffic |
| 3306 | MySQL | Database (internal only) |
| 6379 | Redis | Cache (internal only) |
| 9092 | Kafka | Message queue (internal only) |
| 9200 | Elasticsearch | Search API |
| 4242 | DICOM | DICOM server |

---

## 📚 Tài liệu thêm

- [Architecture](./docs/architecture.md) - Kiến trúc hệ thống
- [API Contracts](./docs/api-contracts.md) - REST API endpoints
- [Data Models](./docs/data-models.md) - Database schema
- [Source Tree](./docs/source-tree-analysis.md) - Cấu trúc thư mục
- [Development Guide](./docs/development-guide.md) - Hướng dẫn phát triển

---

## 📄 License

Internal use only - Copyright © 2024

---

<p align="center">
  <strong>PACS2 - Medical Imaging System</strong><br>
  Powered by DICOM Standards
</p>
