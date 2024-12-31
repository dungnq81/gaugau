<?php

namespace Cores\Traits;

\defined('ABSPATH') || die;

/**
 * Singleton base class for having singleton implementation
 * This allows you to have only one instance of the necessary object
 * You can get the instance with $class = My_Class::get_instance();
 *
 * /!\ The get_instance method has to be implemented in the child class!
 */
trait Singleton
{
    /**
     * @var self
     */
    protected static $instance;

    /**
     * Get the instance of the class
     *
     * @return self
     */
    final public static function get_instance(): static
    {
        if (null === static::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Constructor is protected to prevent instantiation from outside
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * Optional init function for additional setup
     * Child classes may override this function to perform custom actions
     */
    protected function init(): void
    {
        // Custom initialization logic (can be overridden by child classes)
    }

    /**
     * Prevent the instance from being cloned
     *
     * @return void
     */
    final public function __clone()
    {
    }

    /**
     * Prevent from being unserialized
     *
     * @return void
     */
    final public function __wakeup()
    {
    }
}
