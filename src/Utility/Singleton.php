<?php
namespace App\Utility;

class Singleton {
    /**
     * @var array
     */
    private static $_instance = [];

    /**
     * @return Singleton
     */
    public static function getInstance() {
        $className = get_called_class();
        if (!isset(self::$_instance[$className])) {
            self::$_instance[$className] = new $className();

            if (method_exists(self::$_instance[$className], 'initializeInstance')) {
                self::$_instance[$className]->initializeInstance();
            }
        }
        return self::$_instance[$className];
    }

    /**
     * Disable constructor
     */
    final private function __construct() {
    }

    /**
     * Disable clone
     *
     * @return void
     */
    final private function __clone() {
    }
}
