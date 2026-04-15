# Module Lifecycle Guide - PACS2 Architecture

_Document hướng dẫn vòng đời của 1 module trong hệ thống PACS2, sử dụng module `myae` làm ví dụ minh họa._

---

## 1. Module Structure Overview

Mỗi module trong PACS2 bao gồm 2 phần chính:

```
Module/
├── pacs/          # Backend API (PHP)
│   └── ae/        # Ví dụ: pacs/ae - REST API cho MyAE
│       ├── composer.json
│       ├── construct.php
│       ├── Controller/
│       │   └── AeCtrl.php
│       ├── Model/
│       │   └── MyAeMapper.php
│       ├── router.php
│       ├── install.php
│       └── sql/
│           └── pacs_my_ae.table.sql
│
└── pacsui/        # Frontend UI (JavaScript)
    └── ae/        # Ví dụ: pacsui/ae - Admin UI cho MyAE
        ├── composer.json
        ├── construct.php
        ├── UiLoader.php
        ├── router.php
        ├── lang/
        │   ├── en.json
        │   └── vi.json
        └── public/
            ├── autoload.json
            ├── construct.js
            ├── index.php
            ├── model/
            │   └── AeModel.js
            └── view/
                ├── MyAeList.js
                └── MyAeEdit.js
```

---

## 2. Backend Module (pacs/ae)

### 2.1 composer.json

Khai báo metadata của module.

```json
{
    "name": "pacs/ae",
    "description": "Manage myAE title and other ae title",
    "version": "1.0.0",
    "type": "library"
}
```

### 2.2 router.php

Định nghĩa các routes cho REST API.

```php
<?php
namespace Pacs\Ae;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;

$aeCtrl = "\\Pacs\\Ae\\Controller\\AeCtrl";
R::getInstance()->addRoute(new MVC('/:siteID/rest/myAe(/:ae)', 'POST,PUT', $aeCtrl, "updateMyAe", new RouterFilter("rest/myAe")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/myAe/:ae', 'DELETE', $aeCtrl, "deleteMyAe", new RouterFilter("rest/myAe")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/myAe', 'GET', $aeCtrl, "getAllMyAe", new RouterFilter("rest/myAe")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/myAe/:ae', 'GET', $aeCtrl, "getMyAe", new RouterFilter("rest/myAe")));
```

**Quy tắc đặt tên route:**
- Path format: `/:siteID/rest/myAe(/:ae)`
- Method: POST (create), PUT (update), GET (list/detail), DELETE
- RouterFilter: `"rest/myAe"` - quyền truy cập

### 2.3 Controller (Controller/AeCtrl.php)

Xử lý HTTP requests, auth checks, và business logic.

```php
<?php
namespace Pacs\Ae\Controller;

use Company\Auth\Auth;
use Pacs\Ae\Model as M;

class AeCtrl extends \Company\MVC\Controller {

    function updateMyAe($siteID, $ae = null) {
        $this->auth->requireAdmin();           // Check quyền admin
        $this->auth->requireSite($siteID);      // Check site context
        
        $data = $this->input();
        M\MyAeMapper::makeInstance()->updateMyAe($siteID, $ae, $data);
        $this->resp->setBody(json_encode(result(true)));  // Luôn dùng result()
    }

    function getAllMyAe($siteID) {
        if (!$this->auth->checkLocalIP()) {
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);
        }
        $entities = M\MyAeMapper::makeInstance()->getAllMyAe($siteID);
        $this->resp->setBody($entities->toJson());
    }
}
```

**Quy tắc Controller:**
- Dùng `Auth::getInstance()` để check authentication
- Dùng `requireLogin()`, `requireSite()`, `requireAdmin()` theo thứ tự
- Luôn dùng `result()` helper cho response
- Input: `$this->input()` - đọc request body/params
- Output: `$this->resp->setBody()` hoặc `$this->outputJSON()`

### 2.4 Model (Model/MyAeMapper.php)

Data access layer - giao tiếp database.

```php
<?php
namespace Pacs\Ae\Model;

use Company\Cache\CacheDriver;
use Company\SQL\Mapper;

class MyAeMapper extends \Company\SQL\Mapper {

    public function tableName() {
        return 'pacs_my_ae';    // Format: module_entity (snake_case prefix)
    }

    function getPkField() {
        return 'ae';
    }

    function updateMyAe($siteID, $ae, $data) {
        $this->startTrans();
        $this->makeInstance()->filterAe($ae)->update($data);
        $this->completeTransOrFail();
    }

    function getAllMyAe($siteID) {
        return $this->makeInstance()
            ->filterSite($siteID)
            ->getEntities();
    }

    // Filter methods
    function filterSite($siteID) {
        $this->where('siteID=?', __FUNCTION__)->setParamWhere($siteID, __FUNCTION__);
        return $this;
    }

    function filterAe($ae) {
        $this->where('ae=?', __FUNCTION__)->setParamWhere($ae, __FUNCTION__);
        return $this;
    }
}
```

**Quy tắc Mapper:**
- Dùng `makeInstance()` thay vì `new`
- Đặt tên table theo format `module_entity` (snake_case prefix)
- Dùng `filter*()` methods cho query conditions
- Dùng `startTrans()`, `completeTransOrFail()` cho transactions

### 2.5 install.php

Module installation logic.

```php
<?php
use Company\MVC\Module;

$module = new Module("pacs/ae");
$module->initDatabase();  // Chạy các SQL files trong sql/
```

**Quy tắc install:**
- Tự động chạy tất cả files trong `sql/*.table.sql`
- Có thể custom installation logic nếu cần

### 2.6 SQL Schema (sql/pacs_my_ae.table.sql)

```sql
CREATE TABLE pacs_my_ae(
    ae VARCHAR(50) PRIMARY KEY,
    siteID VARCHAR(50) NOT NULL,
    autoCreateAgentAE tinyint DEFAULT 0,
    aeMaster tinyint DEFAULT 0
);

CREATE INDEX idx_site ON pacs_my_ae(siteID);

INSERT INTO pacs_my_ae(ae, siteID, autoCreateAgentAE, aeMaster) VALUES('MINERVA', 'master', 1, 0);
```

**Quy tắc SQL:**
- Table name: `module_entity` (snake_case prefix)
- Primary key: `id` (VARCHAR 50 UUID) hoặc business key như `ae`
- Columns: camelCase
- Foreign keys: `entityFK` format
- Indexes: `idx_columnName`

---

## 3. Frontend Module (pacsui/ae)

### 3.1 composer.json

```json
{
    "name": "pacsui/ae",
    "description": "Ae components",
    "version": "1.0.0",
    "type": "library"
}
```

### 3.2 construct.php

Bootstrap hook cho UI module.

```php
// Empty - module bootstrap hook
```

### 3.2 UiLoader.php

Loader để đăng ký module vào layout.

```php
<?php
namespace PacsUi\Ae;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('pacsui/ae');
        $layout->addJS($module->getBabelURL('autoload.json'));
    }
}
```

**Quy tắc UiLoader:**
- Implements `Company\MVC\UiLoadable`
- Đăng ký JS/CSS qua `addJS()`, `addCSS()` sử dụng Babel transpiler

### 3.3 router.php

Định nghĩa routes cho admin UI pages.

```php
<?php
namespace PacsUi\Ae;

use Company\MVC\MvcContext;
use Company\MVC\Router;

$ctrl = "PacsUi\\Ae\\AeCtrl";

Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/myAe', 'GET', $ctrl, 'MyAeList', new RouterFilter("", false)));
```

### 3.4 autoload.json

Khai báo cấu trúc output của module assets.

```json
{
    "model": "dir",
    "view": "dir",
    "construct.js": "file"
}
```

**Ý nghĩa:** Module sẽ output tất cả files trong `model/` và `view/` directories, cộng với `construct.js`.

### 3.5 public/index.php

Build script - combine tất cả JS files thành 1 output.

```php
<?php
outputDir(__DIR__ . '/model');
outputDir(__DIR__ . '/view');
outputFile(__DIR__, 'construct.js');
```

**Output format:**
```
// AeModel.js
...content...

// MyAeList.js
...content...
```

### 3.6 Model (public/model/AeModel.js)

Gọi REST API từ frontend.

```javascript
class AeModel {
    getMyAe(filter) {
        var url = App.url('/:siteID/rest/myAe',{siteID: App.siteID});
        return $.rest({
            'url': url,
            'data': filter
        });
    }

    updateMyAe(aet, updateData) {
        var url = App.url('/:siteID/rest/myAe',{siteID: App.siteID});
        if (aet) {
            url += '/' + aet;
        }
        return $.rest({
            'url': url,
            'method': 'put',
            'data': updateData
        });
    }

    deleteMyAe(aet) {
        var url = App.url('/:siteID/rest/myAe',{siteID: App.siteID});
        if (aet) {
            url += '/' + aet;
        }
        return $.rest({
            'url': url,
            'method': 'delete'
        });
    }
}
```

### 3.7 View (public/view/MyAeList.js)

React component cho UI.

```javascript
class MyAeList extends PureComponent {
    constructor(props) {
        super(props);
        this.aeModel = new AeModel;
        this.state = {
            'myAe': [],
            'filter': { 'nameSearch': '' }
        };
    }

    componentDidMount() {
        App.requireLogin();
        this.getMyAe();
    }

    editMyAe(ae) {
        MyAeEdit.open(ae).then((resp) => {
            if (resp.status) {
                this.getMyAe();
            }
        });
    }

    getMyAe() {
        this.aeModel.getMyAe().then((resp) => {
            this.setState({ 'myAe': resp});
        });
    }

    pageContent() {
        return (
            <div>
                <PageHeader>{Lang.t('myAe.header')}</PageHeader>
                <table className="table table-striped">
                    ...
                </table>
            </div>
        );
    }

    render() {
        return (
            <AdminLayout>
                {this.pageContent()}
            </AdminLayout>
        );
    }
}
```

**Quy tắc View:**
- Kế thừa `PureComponent`
- Dùng `App.requireLogin()` để check auth
- Dùng `AdminLayout` cho page structure
- Dùng `PageHeader`, `Lang.t()` cho localization
- Navigation: `App.Component.trigger('leftNav.active', 'MyAe')`

### 3.8 Language Files (lang/en.json, lang/vi.json)

```json
{
    "myAe.header": "MyAE Management",
    "ae.btnNew": "New AE",
    "ae.btnDelete": "Delete",
    "ae.name": "AE Title",
    "ae.siteID": "Site ID",
    "myAe.autoCreateAgentAE": "Auto Create Agent",
    "myAe.aeMaster": "AE Master"
}
```

---

## 4. Module Lifecycle Flow

### 4.1 Runtime Phase

```
HTTP Request Flow:
┌─────────────────────────────────────────────────────────────┐
│  Request: GET /:siteID/rest/myAe                            │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  1. Slim Router matches route                                │
│     Route: 'POST,PUT,GET' + /:siteID/rest/myAe(/:ae)        │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  2. Controller init() - Auth check                          │
│     - Auth::getInstance()->requireLogin()                   │
│     - Auth::getInstance()->setSiteID($siteID)               │
│     - Auth::getInstance()->requirePrivilege()                │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  3. Action method executes                                   │
│     - getMyAe($siteID)                                       │
│     - Mapper.getAllMyAe($siteID)                            │
│     - Return entities                                        │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  4. Response                                                 │
│     - result(true, $entities) → JSON                        │
│     - $this->resp->setBody(json_encode(...))               │
└─────────────────────────────────────────────────────────────┘

Admin UI Flow:
┌─────────────────────────────────────────────────────────────┐
│  Request: GET /:siteID/myAe                                  │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  UiLoader loads autoload.json                                │
│  Babel compiles: model/*.js + view/*.js + construct.js      │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  React Component renders                                     │
│  MyAeList → fetches API → displays data                     │
└─────────────────────────────────────────────────────────────┘
```

---

## 5. Naming Conventions Summary

| Element | Convention | Example |
|---------|------------|---------|
| Module name | `vendor/module` | `pacs/ae`, `pacsui/ae` |
| Table name | `module_entity` | `pacs_my_ae` |
| Table columns | camelCase | `siteID`, `aeMaster` |
| Controller | PascalCase + Ctrl | `AeCtrl` |
| Mapper | PascalCase + Mapper | `MyAeMapper` |
| Methods | camelCase | `getMyAe()`, `updateMyAe()` |
| Routes | RESTful | `/:siteID/rest/myAe(/:ae)` |
| View components | PascalCase | `MyAeList`, `MyAeEdit` |
| Model (JS) | PascalCase | `AeModel` |
| Language keys | dot notation | `myAe.header`, `ae.btnNew` |

---

## 6. Enforcement Rules for AI Agents

Tất cả AI agents khi implement module mới PHẢI:

- [ ] Dùng `result()` helper cho mọi JSON response
- [ ] Dùng `makeInstance()` thay vì `new` cho mapper
- [ ] Đặt tên table theo `module_entity` (snake_case prefix)
- [ ] Đặt tên column theo camelCase (không phải snake_case)
- [ ] Check auth theo thứ tự: `requireLogin()` → `setSiteID()` → `requirePrivilege()`
- [ ] Throw exceptions từ `Company\Exception\*` (không throw raw `\Exception`)
- [ ] Đặt SQL files theo `module_entity.table.sql`
- [ ] Không tạo route với version prefix (`/v1/`, `/api/`)
- [ ] Luôn dùng `startTrans()` / `completeTransOrFail()` cho database writes

---

## 7. Checklist khi tạo module mới

### Backend (pacs/module)

- [ ] `composer.json` - khai báo metadata
- [ ] `construct.php` - bootstrap hook (trống hoặc custom logic)
- [ ] `router.php` - định nghĩa routes
- [ ] `Controller/EntityCtrl.php` - HTTP handlers
- [ ] `Model/EntityMapper.php` - data access
- [ ] `sql/module_entity.table.sql` - database schema
- [ ] `install.php` - installation logic

### Frontend (pacsui/module)

- [ ] `composer.json` - khai báo metadata
- [ ] `construct.php` - bootstrap hook
- [ ] `UiLoader.php` - đăng ký vào layout
- [ ] `router.php` - UI routes
- [ ] `lang/en.json`, `lang/vi.json` - i18n strings
- [ ] `public/autoload.json` - asset structure
- [ ] `public/construct.js` - initialization
- [ ] `public/index.php` - asset builder
- [ ] `public/model/EntityModel.js` - API calls
- [ ] `public/view/EntityList.js`, `EntityEdit.js` - UI components