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

    private $_repo;

    public function __construct($name, $config = [], $repo)
    {
        $this->_name    = $name;
        $this->_config  = $config;
        $this->_repo    = $repo;
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

    public function getOnDeleted()
    {
        return isset($this->_config['onDelete']) ?
            explode('|', $this->_config['onDelete']) : false
        ;
    }


    /* Methods
     --------------------------------------*/

    public function isLocalDirectory()
    {
        return is_dir($this->getLocalDirectory());
    }

    public function triggerOnDeleted()
    {
        $commands = [];
        $actions = $this->getOnDeleted();

        if ($this->isLocalDirectory() && is_array($actions)) {
            foreach ($actions as $action) {
                switch ($action) {
                    case 'backup':
                        $commands[] = sprintf('tar czf %s/%s/%s-%s.tar.gz %s',
                            $this->_repo->getBackupDirectory(),
                            $this->_repo->getId(),
                            $this->getName(),
                            date('YmdHis'),
                            $this->getLocalDirectory()
                        );
                        break;
                    case 'remove':
                        $commands[] = sprintf('rm -rf %s',
                            $this->getLocalDirectory()
                        );
                        break;
                }
            }
        }

        return $commands;
    }

    public function gitClone()
    {
        $command = 'git clone --depth=1 --branch=' . $this->getName();

        if ($this->getSyncSubmodule()) {
            $command .= ' --recursive';
        }

        $commands[] = sprintf('%s %s %s',
            $command,
            $this->_repo->getURLRemote(),
            $this->getLocalDirectory()
        );

        return $commands;
    }

    public function gitPull()
    {
        $commands[] = 'git reset --hard';
        $commands[] = 'git pull origin ' . $this->getName();

        if ($this->getSyncSubmodule()) {
            $commands[] = 'git submodule update --init --recursive';
        }

        return $commands;
    }
}
