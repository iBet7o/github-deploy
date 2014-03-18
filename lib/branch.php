<?php

/**
 * Class for branches
 *
 * @author Roberto Ramírez <robertoiran@gmail.com>
 */

class Branch
{
    private $_name;

    private $_config;

    public function __construct($name, $config = [])
    {
        $this->_name    = $name;
        $this->_config  = $config;
    }


    /* Getters
     --------------------------------------*/

    public function getName()
    {
        return $this->_name;
    }

    public function getLocalDirectory()
    {
        if (! isset($this->_config['local_dir'])) {
            throw new Exception('You need to define the variable "local_dir"', 401);
        }

        return $this->_config['local_dir'];
    }

    public function getSyncSubmodule()
    {
        return isset($this->_config['sync_submodule'])?: false;
    }
}
