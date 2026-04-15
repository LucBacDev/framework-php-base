# Phân Tích Luồng Chạy Framework PACS2 & Hướng Dẫn Tạo Module

> Tài liệu phân tích chi tiết kiến trúc framework PHP (Slim 2.0) của hệ thống PACS2,
> kèm hướng dẫn từng bước để tạo một module mới.

---

## Mục lục

1. [Tổng quan kiến trúc](#1-tổng-quan-kiến-trúc)
2. [Luồng chạy chi tiết (Request Lifecycle)](#2-luồng-chạy-chi-tiết-request-lifecycle)
3. [Hệ thống Module](#3-hệ-thống-module)
4. [Các thành phần MVC](#4-các-thành-phần-mvc)
5. [Hệ thống Trigger/Hook](#5-hệ-thống-triggerhook)
6. [Cấu trúc thư mục một module](#6-cấu-trúc-thư-mục-một-module)
7. [Hướng dẫn tạo module mới (từng bước)](#7-hướng-dẫn-tạo-module-mới-từng-bước)
8. [Module mẫu: Notification](#8-module-mẫu-notification)
9. [Checklist tạo module](#9-checklist-tạo-module)

---

## 1. Tổng quan kiến trúc

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT (Browser)                         │
│              HTTP Request → GET /pacs/1/rest/users              │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                     Apache / Nginx                              │
│          .htaccess: RewriteRule → index.php                     │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                   Docroot/index.php                              │
│  1. define('BASE_DIR')                                          │
│  2. require vendor/autoload.php (Composer)                      │
│  3. require Module/company/mvc/autoload.php (Custom autoloader) │
│  4. new \Company\MVC\Bootstrap()                                │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                   Bootstrap::__construct()                       │
│                                                                  │
│  ┌─ 1. Load Config (Enviroments/*.config.php)                   │
│  ├─ 2. registerComponentHook() ← Load tất cả construct.php     │
│  ├─ 3. Trigger::execute('Bootstrap/begin')                      │
│  ├─ 4. Khởi tạo Slim Framework                                 │
│  ├─ 5. Load Routes (Router::getRoutes → tất cả router.php)     │
│  ├─ 6. Kết nối Database (DB::Connect)                           │
│  ├─ 7. Trigger::execute('Bootstrap/completed')                  │
│  └─ 8. $this->slim->run() ← Slim xử lý request                │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                   Slim Router Matching                           │
│  URL match → gọi Bootstrap::executeAction(MvcContext)           │
│                                                                  │
│  1. Trigger::execute('Bootstrap/action')                        │
│  2. createController(context) → new Controller(context)         │
│  3. Controller::init() ← override để khởi tạo dependencies     │
│  4. call_user_func([$controller, $action], ...$args)            │
│  5. Trigger::execute('Bootstrap/shutdown')                      │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                   Controller Action                             │
│                                                                  │
│  ┌─ Auth::requireLogin() / requirePrivilege()                   │
│  ├─ $this->input() ← đọc JSON body                             │
│  ├─ Mapper::makeInstance()->filterXXX()->getEntities()          │
│  └─ $this->outputJSON($data) ← trả response                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. Luồng chạy chi tiết (Request Lifecycle)

### Bước 1: Entry Point (`Docroot/index.php`)

```php
<?php
define('BASE_DIR', dirname(__DIR__));          // Đường dẫn gốc project
require_once BASE_DIR . '/vendor/autoload.php'; // Composer autoloader
require_once BASE_DIR . '/Module/company/mvc/autoload.php'; // Custom autoloader
$application = new \Company\MVC\Bootstrap();    // Khởi động ứng dụng
```

**Custom Autoloader** (`Module/company/mvc/autoload.php`):
- Chuyển namespace thành đường dẫn file: `Company\User\Controller\UserCtrl` → `Module/company/user/Controller/UserCtrl.php`
- 2 phần đầu namespace (vendor/module) được chuyển thành chữ thường
- Load các file global: `Macroable`, `Collection`, `DateTimeEx`, `Fn.php` (chứa các helper functions)

### Bước 2: Bootstrap khởi tạo

Bootstrap thực hiện các bước theo thứ tự:

#### 2a. Load Config
```php
$config = getConfig('Enviroments/enviroment.config.php');
$config += getConfig("Enviroments/" . $config['enviroment'] . ".config.php");
```
- Đọc file `enviroment.config.php` để xác định môi trường (development/production)
- Load config tương ứng (database, redis, elasticsearch, ...)

#### 2b. Đăng ký Component Hooks (`registerComponentHook`)
```
Quét: Module/{vendor}/{component}/construct.php
```
- Duyệt qua **TẤT CẢ** thư mục trong `Module/`
- Load file `construct.php` của mỗi module (nếu tồn tại và không rỗng)
- Nếu request là REST API → bỏ qua các module UI (có chứa `ui/` trong path)
- Kết quả cache bằng APCu (60 giây) trong môi trường production

#### 2c. Trigger 'Bootstrap/begin'
- Thực thi tất cả callback đã đăng ký cho event này
- Các module có thể hook vào đây để khởi tạo sớm

#### 2d. Khởi tạo Slim Framework
```php
$this->slim = new Slim([
    'cookies.encrypt' => true,
    'cookies.secret_key' => $config['cryptSecret'],
    'debug' => $debug
]);
```

#### 2e. Load Routes
```
Quét: Module/{vendor}/{component}/router.php
```
- `Router::getRoutes()` duyệt tất cả module, load file `router.php`
- Mỗi file router đăng ký routes bằng `Router::getInstance()->addRoute(new MvcContext(...))`
- Routes được lọc theo:
  - `filterUri`: URL có chứa chuỗi này không?
  - `hasMethod`: HTTP method có match không?
  - `filterIsRest`: Request có phải REST không?
- Routes được sắp xếp: **đường dẫn dài hơn được ưu tiên** (tránh bị route ngắn override)

#### 2f. Kết nối Database
```php
foreach ($config['db'] as $connName => $connInfo) {
    DB::setConfig($connName, ...);
}
DB::Connect();
```

#### 2g. Slim chạy và match request
```php
$this->slim->run();
```

### Bước 3: Xử lý Request

Khi Slim match được route → gọi `Bootstrap::executeAction(MvcContext, $args)`:

```php
function executeAction(MvcContext $context, $args) {
    Trigger::execute('Bootstrap/action');
    $controller = $this->createController($context);  // new Controller($context)
    call_user_func_array([$controller, $context->action], $args);
    Trigger::execute('Bootstrap/shutdown');
}
```

1. **Tạo Controller**: `new $class($context)` → gọi `__construct(MvcContext)` → gọi `init()`
2. **Gọi Action**: `$controller->actionName($param1, $param2, ...)`
3. **Trong Action**:
   - Kiểm tra auth: `Auth::getInstance()->requireLogin()`
   - Đọc input: `$this->input()` (JSON body) hoặc `$this->req->get('key')` (query param)
   - Gọi Mapper để truy cập DB
   - Trả response: `$this->outputJSON($data)` hoặc `$this->resp->setBody(...)`

---

## 3. Hệ thống Module

### Cách framework phát hiện module

Framework tự động quét `Module/` directory với cấu trúc 2 cấp:
```
Module/
├── {vendor}/          ← Cấp 1: tên vendor (company, companyui, pacs, pacsui)
│   └── {component}/   ← Cấp 2: tên component (user, setting, notification, ...)
```

### Các file framework tự động load

| File | Khi nào load | Mục đích |
|------|-------------|----------|
| `construct.php` | Bootstrap khởi tạo | Đăng ký hooks/triggers |
| `router.php` | Router tải routes | Đăng ký URL routes |
| `install.php` | Chạy install script | Tạo bảng DB, seed data |
| `composer.json` | Module metadata | Tên, phiên bản, mô tả |

### Đăng ký module trong modules.json

```json
{
    "require": {
        "company/notification": "*"
    }
}
```

File `modules.json` ở gốc project chứa danh sách các module được kích hoạt. `Module::getModules()` đọc file này để biết module nào cần quản lý.

### 2 loại module chính

| Loại | Vendor | Chức năng | Ví dụ |
|------|--------|-----------|-------|
| **Backend** | `company` | Logic, API, Database | `company/user`, `company/auth`, `company/notification` |
| **Frontend/UI** | `companyui` | Giao diện, JS, CSS | `companyui/user`, `companyui/home` |

- Module **Backend**: Chứa Controller (REST API), Model (Mapper), SQL, Lib
- Module **UI**: Chứa Controller (render layout), JS/CSS (React components), lang files

---

## 4. Các thành phần MVC

### 4.1 Router & MvcContext

**MvcContext** là đối tượng mô tả 1 route:
```php
new MvcContext(
    $path,        // '/:siteID/rest/users/:id' - URL pattern
    $method,      // 'GET' hoặc 'POST,PUT' hoặc '*'
    $controller,  // '\\Company\\User\\Controller\\UserCtrl'
    $action,      // 'getUser' - tên method
    $filter       // new RouterFilter("/rest/users", true)
);
```

**RouterFilter** giúp tối ưu performance:
```php
new RouterFilter(
    $filterUri,     // Chỉ load route nếu URL chứa chuỗi này
    $filterIsRest   // true = chỉ REST, false = chỉ web, '' = cả hai
)
```

**Path parameters**:
- `:siteID` → tham số bắt buộc
- `(/:id)` → tham số optional
- Tham số truyền vào action method theo thứ tự

### 4.2 Controller

```php
class MyCtrl extends \Company\MVC\Controller {
    function init() {
        // Khởi tạo dependencies (KHÔNG dùng __construct)
    }
    
    function myAction($siteID, $id) {
        // $this->req     → Slim Request (query params, headers)
        // $this->resp    → Slim Response
        // $this->input() → JSON body
        // $this->context → MvcContext
    }
}
```

**Các method hữu ích**:
- `$this->input($key, $default)` → Đọc JSON body
- `$this->req->get('key', 'default')` → Đọc query parameter
- `$this->outputJSON($data)` → Trả JSON response
- `$this->isRest()` → Kiểm tra có phải REST request
- `$this->getCookie($name)` / `$this->setCookie($name, $value)`

### 4.3 Model (Mapper Pattern)

Mapper = Data Access Object, mỗi Mapper tương ứng 1 bảng DB:

```php
class NotificationMapper extends \Company\SQL\Mapper {
    function tableName()  { return 'notification'; }  // Tên bảng
    function tableAlias() { return 'noti'; }          // Alias trong SQL

    // Filter methods: luôn return $this để chain
    function filterSiteFK($siteFK) {
        $this->where('noti.siteFK = ?', __FUNCTION__)
             ->setParamWhere($siteFK, __FUNCTION__);
        return $this;
    }
}
```

**Sử dụng Mapper** (fluent interface / method chaining):
```php
// Lấy 1 entity
$user = UserMapper::makeInstance()
    ->filterID($id)
    ->filterSiteFK($siteID)
    ->setLoadRoles()           // eager load quan hệ
    ->getEntity();

// Lấy nhiều entities
$users = UserMapper::makeInstance()
    ->filterActive(1)
    ->filterSiteFK($siteID)
    ->setPage($pageNo, $pageSize)
    ->getEntities();            // → Collection

// Insert
$mapper->insert(['id' => uid(), 'name' => 'test']);

// Update (BẮT BUỘC phải có filter)
$mapper->filterID($id)->update(['name' => 'new name']);

// Delete (BẮT BUỘC phải có filter)
$mapper->filterID($id)->delete();

// Transaction
$mapper->startTrans();
// ... các thao tác DB ...
$mapper->completeTransOrFail();
```

**Quan trọng**: `update()` và `delete()` sẽ **throw Exception** nếu không có điều kiện WHERE, để tránh xóa/cập nhật toàn bộ bảng.

### 4.4 Entity

Entity đại diện cho 1 bản ghi, tự động map từ array:

```php
class NotificationEntity extends \Company\Entity\Entity {
    // Tùy chỉnh thêm nếu cần
}
```

Entity base tự động:
- Map tất cả column thành property: `$entity->id`, `$entity->title`, ...
- Decode trường JSON `attrs` thành properties
- Trả `null` thay vì error khi truy cập property chưa tồn tại

### 4.5 View & Layout (cho module UI)

```php
// Controller UI
function userList($siteID) {
    $layout = Layout::getLayout('admin');
    $layout->setSiteID($siteID)
           ->renderReact('UserList');    // Render React component
}

// Hoặc render PHP template
$layout->render('companyui/user/template.php', ['data' => $data]);
```

**UiLoader**: Load CSS/JS vào Layout
```php
class UiLoader implements UiLoadable {
    public function load(Layout $layout) {
        $module = Module::getInstance('companyui/mymodule');
        $layout->addCSS($module->getPublicURL() . '/css/style.css')
               ->addJS($module->getBabelURL('autoload.json'));
    }
}
```

---

## 5. Hệ thống Trigger/Hook

Framework sử dụng Trigger pattern để cho phép modules giao tiếp lỏng lẻo (loose coupling):

```php
// Đăng ký hook (trong construct.php)
Trigger::register('Bootstrap/completed', function($config) {
    // Code chạy sau khi bootstrap hoàn tất
}, $priority = 0);

// Thực thi hook
Trigger::execute('Bootstrap/completed', $config);
```

**Các event có sẵn**:

| Event | Thời điểm | Tham số |
|-------|-----------|---------|
| `Bootstrap/begin` | Sau load config, trước Slim | `$config` |
| `Bootstrap/completed` | Sau kết nối DB, trước `slim->run()` | `$config` |
| `Bootstrap/action` | Trước khi gọi controller action | (không) |
| `Bootstrap/shutdown` | Sau khi controller action hoàn tất | (không) |

Bạn có thể tạo event tùy chỉnh cho module của mình.

---

## 6. Cấu trúc thư mục một module

### Module Backend (company/notification)
```
Module/company/notification/
├── composer.json              ← Metadata: tên, version, mô tả
├── construct.php              ← Hooks/Triggers (bắt buộc nếu muốn framework nhận diện)
├── router.php                 ← Đăng ký routes
├── install.php                ← Script cài đặt DB
├── Controller/
│   └── NotificationCtrl.php   ← Xử lý request/response
├── Model/
│   ├── NotificationMapper.php ← Truy cập database
│   └── NotificationEntity.php ← Đối tượng dữ liệu
├── Lib/                       ← Business logic, utility
│   └── NotificationHelper.php
└── sql/
    └── notification.table.sql ← Script tạo bảng
```

### Module UI (companyui/notification)
```
Module/companyui/notification/
├── composer.json
├── construct.php
├── router.php                 ← Routes cho trang web (không phải REST)
├── UserCtrl.php               ← Controller render layout
├── UiLoader.php               ← Load CSS/JS vào layout
├── public/
│   ├── autoload.json          ← Khai báo JS modules
│   ├── css/
│   │   └── notification.css
│   ├── model/
│   │   └── NotificationModel.js  ← JS model (gọi REST API)
│   └── view/
│       └── NotificationList.js   ← React component
└── lang/
    ├── vi.json                ← Tiếng Việt
    └── en.json                ← Tiếng Anh
```

---

## 7. Hướng dẫn tạo module mới (từng bước)

### Bước 1: Tạo cấu trúc thư mục

```bash
# Tên module: company/notification
mkdir -p Module/company/notification/{Controller,Model,Lib,sql}
```

### Bước 2: Tạo composer.json

```json
{
    "name": "company/notification",
    "description": "Quản lý thông báo hệ thống",
    "version": "1.0.0",
    "type": "library",
    "authors": [
        { "name": "Tên bạn", "email": "email@example.com" }
    ]
}
```

### Bước 3: Tạo construct.php

```php
<?php
namespace Company\Notification;

use Company\MVC\Trigger;

// Đăng ký hooks nếu cần
// Trigger::register('Bootstrap/completed', function($config) { ... });
```

> ⚠️ File PHẢI có nội dung (ít nhất là khai báo namespace). File rỗng sẽ bị bỏ qua.

### Bước 4: Tạo SQL table

File: `sql/notification.table.sql`
```sql
CREATE TABLE IF NOT EXISTS `notification` (
    `id` VARCHAR(50) NOT NULL,
    `title` VARCHAR(500) NOT NULL DEFAULT '',
    `content` TEXT,
    `type` VARCHAR(50) DEFAULT 'system',
    `siteFK` VARCHAR(50) NOT NULL DEFAULT '',
    `userID` VARCHAR(50) DEFAULT NULL,
    `isRead` TINYINT(1) NOT NULL DEFAULT 0,
    `createdDate` VARCHAR(30) DEFAULT '',
    `attrs` TEXT,
    PRIMARY KEY (`id`),
    KEY `idx_notification_site` (`siteFK`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

> **Quy ước đặt tên file SQL**: `{tên_bảng}.table.sql`
> Framework sẽ tự động extract tên bảng từ tên file để kiểm tra bảng đã tồn tại chưa.

### Bước 5: Tạo Model (Mapper + Entity)

**Mapper** - xem `Module/company/notification/Model/NotificationMapper.php`

Quy tắc:
- Kế thừa `\Company\SQL\Mapper`
- Implement `tableName()` và `tableAlias()`
- Tạo `filterXXX()` methods cho các điều kiện thường dùng
- Dùng `__FUNCTION__` làm key cho where/param để tránh trùng lặp
- Luôn return `$this` từ filter methods

**Entity** - xem `Module/company/notification/Model/NotificationEntity.php`

### Bước 6: Tạo Controller

Xem `Module/company/notification/Controller/NotificationCtrl.php`

Quy tắc:
- Kế thừa `\Company\MVC\Controller`
- Override `init()` thay vì `__construct()`
- Tham số action phải match với route params
- Luôn kiểm tra auth trước khi xử lý

### Bước 7: Đăng ký Routes

Xem `Module/company/notification/router.php`

Quy tắc:
- Sử dụng `Router::getInstance()->addRoute(new MvcContext(...))`
- Thêm `RouterFilter` để tối ưu performance
- Route dài hơn sẽ được ưu tiên match trước

### Bước 8: Tạo install.php

```php
<?php
use Company\MVC\Module;

$module = new Module("company/notification");
$module->initDatabase();
$module->checkExistsOrCreateModuleRecord();
```

### Bước 9: Đăng ký module

Thêm vào `modules.json`:
```json
{
    "require": {
        "company/notification": "*"
    }
}
```

### Bước 10: Chạy install

```bash
docker exec pacs php /var/www/html/install/install.php
```

---

## 8. Module mẫu: Notification

Module mẫu đã được tạo tại `Module/company/notification/` với đầy đủ các file:

| File | Mô tả |
|------|-------|
| `composer.json` | Metadata module |
| `construct.php` | Đăng ký hooks (hiện tại trống) |
| `router.php` | 6 REST API endpoints |
| `Controller/NotificationCtrl.php` | Controller với 6 actions |
| `Model/NotificationMapper.php` | Mapper với filter methods |
| `Model/NotificationEntity.php` | Entity class |
| `sql/notification.table.sql` | Script tạo bảng |
| `install.php` | Script cài đặt |

### API Endpoints

| Method | Path | Action | Mô tả |
|--------|------|--------|-------|
| GET | `/:siteID/rest/notifications` | getNotifications | Danh sách thông báo (phân trang) |
| GET | `/:siteID/rest/notifications/:id` | getNotification | Chi tiết 1 thông báo |
| POST/PUT | `/:siteID/rest/notifications(/:id)` | updateNotification | Tạo/cập nhật |
| DELETE | `/:siteID/rest/notifications/:id` | deleteNotification | Xóa thông báo |
| POST/PUT | `/:siteID/rest/notifications/:id/read` | markAsRead | Đánh dấu đã đọc |
| POST/PUT | `/:siteID/rest/notifications/read-all` | markAllAsRead | Đọc tất cả |

---

## 9. Checklist tạo module

- [ ] Tạo thư mục `Module/{vendor}/{module}/`
- [ ] Tạo `composer.json` (tên, version, mô tả)
- [ ] Tạo `construct.php` (ít nhất khai báo namespace)
- [ ] Tạo `sql/*.table.sql` (scripts tạo bảng)
- [ ] Tạo `Model/*Mapper.php` (kế thừa `\Company\SQL\Mapper`)
- [ ] Tạo `Model/*Entity.php` (kế thừa `\Company\Entity\Entity`)
- [ ] Tạo `Controller/*Ctrl.php` (kế thừa `\Company\MVC\Controller`)
- [ ] Tạo `router.php` (đăng ký routes)
- [ ] Tạo `install.php` (cài đặt DB)
- [ ] Thêm vào `modules.json`
- [ ] Chạy install script
- [ ] (Tùy chọn) Tạo module UI tương ứng trong `companyui/`

---

## Phụ lục: Các helper functions thường dùng

| Function | Mô tả | Ví dụ |
|----------|-------|-------|
| `uid()` | Sinh ID duy nhất | `$id = uid()` |
| `result($status, $data)` | Tạo response chuẩn | `result(true, ['id' => $id])` |
| `arrData($arr, $key, $default)` | Lấy giá trị an toàn từ array | `arrData($input, 'name', '')` |
| `app()` | Lấy Bootstrap instance | `app()->config` |
| `url($path)` | Tạo URL với rewriteBase | `url('/users')` |
| `env($key, $default)` | Đọc biến môi trường | `env('REDIS_HOST', 'localhost')` |
| `dd($data)` | Debug và die | `dd($variable)` |
| `stop()` | Dừng Slim gracefully | `stop()` |
| `escapeStr($str)` | Escape XSS | `escapeStr($input)` |
