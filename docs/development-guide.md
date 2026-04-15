# Development Guide - PACS2

## Prerequisites

### System Requirements

| Requirement | Version | Notes |
|-------------|---------|-------|
| PHP | 8.0+ | Required by Composer platform check |
| MySQL | 5.7+ | Primary database |
| Elasticsearch | 7.x | Metadata indexing |
| Redis | 7.4+ | Caching and sessions |
| Kafka | - | Async message processing |

### Required PHP Extensions

- `ext-mbstring` - Multibyte string support
- `ext-json` - JSON support
- `ext-pdo` - PDO database support
- `ext-curl` - HTTP client support

---

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url> pacs2
cd pacs2
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configuration

Copy and configure the environment file:

```bash
cp Config/Enviroments/config.example.json Config/Enviroments/config.json
```

Edit `config.json` with your settings:

```json
{
  "PACS_MYSQL_HOST": "mysql:3306",
  "PACS_MYSQL_USER": "root",
  "PACS_MYSQL_PASS": "your_password",
  "PACS_DATABASE_NAME": "pacs",
  "PACS_ELASTIC_HOSTS": "http://elastic:9200",
  "PACS_REDIS_HOST": "redis",
  "PACS_REDIS_PORT": 6379,
  "PACS_KAFKA_BROKER_LIST": "kafka:9092",
  "PACS_VERSION": "1.10",
  "PACS_ZONE_ID": "default",
  "PACS_TIME_ZONE": "Asia/Bangkok"
}
```

### 4. Database Setup

Run the SQL migrations in order:

```bash
# Execute migration files
mysql -u root -p pacs < sql/v1.2.sql
mysql -u root -p pacs < sql/v1.4.sql
# ... continue with all versions
```

### 5. Module Setup

Ensure all modules are registered in `modules.json`.

---

## Running the Application

### Web Server

Configure your web server (Apache/Nginx) to point to `Docroot/`:

```apache
# Apache example
<VirtualHost *:80>
    DocumentRoot /path/to/pacs2/Docroot
    ServerName pacs.local
    
    <Directory /path/to/pacs2/Docroot>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Swoole Async Server

For high-performance async processing:

```bash
php Module/pacs/swooleserver/Exec/swooleServer.php
```

### DICOM Server

```bash
# Start DICOM network listener
php Module/pacs/dicomnet/Exec/DicomServer/start.php
```

### HL7 Server

```bash
# Start HL7 server (Node.js based)
cd Module/pacs/hl7server/Exec
node hl7server.js
```

### Message Consumer

```bash
php Module/pacs/consumerProcess/Exec/consumerProcess.php
```

---

## Testing

### Run Unit Tests

```bash
# Using PHPUnit
./vendor/bin/phpunit

# Or with custom config
./vendor/bin/phpunit --configuration phpunit.xml
```

### Available Test Files

- `tests/TestWado.php` - WADO service tests
- `tests/TestStow.php` - STOW service tests
- `tests/TestQido.php` - QIDO service tests

---

## Common Development Tasks

### Adding a New Module

1. Create module directory: `Module/vendor/module-name/`
2. Add to `modules.json`:
   ```json
   "vendor/module-name": "*"
   ```
3. Create required files:
   - `Module.php` - Module bootstrap
   - `router.php` - Route definitions
   - `install.php` - Installation script

### Database Migrations

Create a new migration file in `sql/`:

```bash
# v1.10.1.sql
ALTER TABLE pacs_study ADD COLUMN new_column varchar(255);
```

### Adding API Endpoints

Define routes in your module's `router.php`:

```php
use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;

$ctrl = "\\Vendor\\Module\\Controller\\MyCtrl";
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/module/endpoint', 'GET', $ctrl, "myAction")
);
```

---

## Deployment

### Recommended Infrastructure

| Service | Purpose | Recommended |
|---------|---------|-------------|
| MySQL | Primary database | Master-Slave replication |
| Elasticsearch | Metadata search | 3-node cluster |
| Redis | Cache/Session | Sentinel or Cluster |
| Kafka | Message queue | Multi-broker cluster |
| S3/Ceph | File storage | - |

### Environment Variables

Set production environment variables:

```bash
export PACS_MYSQL_HOST="production-mysql"
export PACS_ELASTIC_HOSTS="http://elastic-prod:9200"
export PACS_REDIS_HOST="redis-prod"
```

---

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| "PHP version mismatch" | Ensure PHP 8.0+ is installed |
| "Elasticsearch connection failed" | Check ES host configuration |
| "Redis session errors" | Verify Redis connection |
| "Module not loading" | Check modules.json and paths |

### Logs

- Application logs: Check configured log handlers
- Elasticsearch: Check ES cluster health
- Kafka: Monitor consumer lag

---

## Security Considerations

- Keep `config.json` out of version control
- Use strong Redis/MySQL passwords
- Enable HTTPS in production
- Configure firewall rules for DICOM ports (104, 11112)
