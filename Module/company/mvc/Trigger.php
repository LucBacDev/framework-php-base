<?php

namespace Company\MVC;

class Trigger {

    static protected $hooks = [];
    static protected $executeEachMeta = [
        'event' => null,
        'index' => 0
    ];

    /**
     * Đăng ký callback
     * @param string $event
     * @param callable $callback
     * @param int $priority sắp xếp các hook theo thứ thự, default=0. Thực hiện theo thứ tự 1,2,4,5,... Có thể truyền số âm
     */
    static function register($event, $callback, $priority = 0) {
        if (!isset(static::$hooks[$event])) {
            static::$hooks[$event] = [];
        }
        static::$hooks[$event][] = [
            'priority' => $priority,
            'callback' => $callback
        ];
    }

    /**
     * Lấy danh sách callback
     * @param string $event
     * @return TriggerCollection
     */
    static function event($event) {
        return new TriggerCollection(arrData(static::$hooks, $event, []));
    }

    /**
     * Thực hiện các callback mà không cần biết giá trị trả về
     * @param string $event
     * @param array $args 
     * @return type
     */
    static function execute($event) {
        $args = func_get_args();
        array_shift($args); //remove event arg

        $callbacks = arrData(static::$hooks, $event, []);

        if (empty($callbacks)) {
            return [];
        }

        //sắp xếp theo priority
        usort($callbacks, function($a, $b) {
            return $a['priority'] > $b['priority'];
        });

        $ret = [];
        foreach ($callbacks as $callback) {
            $ret[] = call_user_func_array($callback['callback'], $args);
        }

        return $ret;
    }

}
