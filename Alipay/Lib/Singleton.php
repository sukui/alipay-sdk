<?php
/**
 * Created by PhpStorm.
 * User: laogui
 * Date: 2018/3/22
 * Time: 12:08
 */

namespace Alipay\Lib;

trait Singleton
{

    /**
     * @var static
     */
    protected static $_instance = null;

    /**
     * @return static
     */
    final public static function instance($config)
    {
        return static::singleton($config);
    }

    final public static function singleton($config)
    {
        if (null === static::$_instance) {
            static::$_instance = new static($config);
        }
        return static::$_instance;
    }

    /**
     * @return static
     */
    final public static function getInstance($config)
    {
        return static::singleton($config);
    }

    final public static function swap($instance)
    {
        static::$_instance = $instance;
    }
}
