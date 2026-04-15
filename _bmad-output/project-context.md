---
project_name: 'pacs2'
user_name: 'USER'
date: '2026-03-08'
sections_completed: ['technology_stack', 'language_specific', 'framework_specific', 'testing', 'code_quality', 'workflow', 'critical_rules']
status: 'complete'
rule_count: 45
optimized_for_llm: true
---

# Project Context for AI Agents

_This file contains critical rules and patterns that AI agents must follow when implementing code in this project. Focus on unobvious details that agents might otherwise miss._

---

## Technology Stack & Versions

- **PHP** - Server-side language
- **Slim ~2.0** - Routing and HTTP handling
- **ADODB ~5.22** - Database abstraction
- **Elasticsearch 7.11** - Search engine
- **Guzzle ~6.5** - HTTP client
- **AWS SDK PHP ~3.190** - AWS services (S3)
- **Monolog ~1.26** - Logging
- **Doctrine Cache ~1.10** - Caching
- **Firebase JWT ~6.5** - JWT authentication
- **ZipStream PHP ~2.1** - ZIP streaming
- **Cron Expression ~3.0** - Scheduling

---

## Language-Specific Rules (PHP)

### Singleton Pattern
- Always use `ClassName::makeInstance()` not `new ClassName()`
- Example: `$mapper = StudyMapper::makeInstance();`

### Namespaces
- PACS modules: `namespace Pacs\*\Controller;`
- Company shared: `namespace Company\*;`

### Response Handling
```php
$this->resp->setBody(json_encode(result(true)));
$this->outputJSON(result(false, "E001"));
```

### Error Handling
```php
throw new \Company\Exception\BadRequestException($message);
```

### Authentication
```php
$this->auth = Auth::getInstance();
$this->auth->requireLogin();
$this->auth->requireSite($siteID);
```

---

## Framework-Specific Rules (Slim 2.0)

### Router
```php
$app->get('/path', 'Controller:method');
$app->post('/path', 'Controller:method');
```

### Controller Base
```php
class StudyCtrl extends \Company\MVC\Controller {
    function init() { parent::init(); }
}
```

### Request/Response
```php
$this->input();           // Get input data
$this->req->get($param);   // Get query param
$this->resp->setBody($content);
```

### DICOM Web Standards
- **WADO**: DICOM Retrieve
- **QIDO**: DICOM Query
- **STOW**: DICOM Store

---

## Code Organization

```
Module/
├── Controller/    # *Ctrl.php
├── Model/         # *Mapper.php
├── Lib/           # Helpers
├── router.php     # Routes
├── construct.php  # Init
└── composer.json  # Dependencies
```

---

## Testing Rules

- Config: `phpunit.xml`
- Directory: `tests/`
- Naming: `Test*.php`
- Test Mappers, APIs, DICOM parsing

---

## Code Quality & Style

### Naming
- Files: PascalCase (`StudyCtrl.php`)
- Controllers: `*Ctrl` suffix
- Mappers: `*Mapper` suffix
- Methods: camelCase

### Documentation
- PHPDoc for classes/methods
- Inline comments for complex logic

---

## Development Workflow

- **Branches**: `feature/*`, `fix/*`, `hotfix/*`
- **Commits**: Clear messages, reference issues
- **Config**: Use `Config/` directory
- **Migrations**: `sql/vX.Y.Z.sql`

---

## Critical Don't-Miss Rules

### Anti-Patterns
- ❌ NO `new ClassName()` - use `makeInstance()`
- ❌ NO skipping auth - always `requireLogin()` + `requireSite()`
- ❌ NO hardcoded config - use `Config/`

### Edge Cases
- DICOM UIDs must follow exact format
- Check offline status before operations
- Use streaming for large files

### Security
- Always validate login + site
- Validate all input
- Use parameterized queries (ADODB)

### Performance
- Optimize ES queries, avoid N+1
- Use Doctrine Cache
- Paginate large datasets

---

## Usage Guidelines

**For AI Agents:**
- Read this file before implementing any code
- Follow ALL rules exactly as documented
- When in doubt, prefer the more restrictive option
- Update this file if new patterns emerge

**For Humans:**
- Keep this file lean and focused on agent needs
- Update when technology stack changes
- Review quarterly for outdated rules

---

_Last Updated: 2026-03-08_
