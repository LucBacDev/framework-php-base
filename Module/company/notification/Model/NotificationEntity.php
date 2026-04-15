<?php

/**
 * Module Notification - Entity
 * 
 * Entity đại diện cho 1 bản ghi trong database.
 * Kế thừa từ Company\Entity\Entity.
 * 
 * Entity base có sẵn:
 * - __construct($rawData): Tự động map tất cả field từ array vào properties
 * - __get($name): Trả về null thay vì notice error khi truy cập field chưa khai báo
 * - decodeAttrs(): Tự động decode trường JSON 'attrs' thành properties
 * - toJson(): Chuyển entity thành JSON string
 * 
 * Có thể khai báo thêm methods hoặc computed properties ở đây.
 * Nếu không cần tùy chỉnh, chỉ cần class rỗng kế thừa Entity là đủ.
 */

namespace Company\Notification\Model;

use Company\Entity\Entity;

class NotificationEntity extends Entity {

}
