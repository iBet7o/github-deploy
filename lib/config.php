<?php

include 'repository.php';
include 'branch.php';

/**
 * Manage for the configuration
 *
 * @author Roberto Ramírez <robertoiran@gmail.com>
 */

class Config
{
    private static $_config = [];

    public static function init($file = null)
    {
        if (! file_exists($file)) {
            throw new InvalidArgumentException('Config file not found', 500);
        }

        self::$_config = json_decode(file_get_contents($file), true);

        return true;
    }

    public static function getRepository($id)
    {
        self::_validate();

        if (! isset(self::$_config['repositories'][$id])) {
            throw new InvalidArgumentException('Repository not found', 404);
        }

        return new Repository($id, self::$_config['repositories'][$id]);
    }

    private static function _validate()
    {
        if (0 == count(self::$_config)) {
            throw new InvalidArgumentException('Configuration is not loaded', 500);
        }
    }
}
