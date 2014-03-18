<?php

/**
 * Class for repositories
 *
 * @author Roberto Ramírez <robertoiran@gmail.com>
 */

class Repository
{
    private $_id;

    private $_config;

    public function __construct($id, $config = [])
    {
        $this->_id      = $id;
        $this->_config  = $config;
    }


    /* Getters
     --------------------------------------*/

    public function getId()
    {
        return $this->_id;
    }

    public function getURLRemote()
    {
        if (! isset($this->_config['remote'])) {
            throw new Exception(
                'You need to define the variable "remote" for repository',
                401
            );
        }

        return $this->_config['remote'];
    }


    /* Methods
     --------------------------------------*/

    public function validateToken($token = null)
    {
        if (! isset($this->_config['security']['token'])) {
            throw new Exception('For safety it is necessary to define a token for the repository', 401);
        }

        if ($token != $this->_config['security']['token']) {
            throw new InvalidArgumentException('Unauthorized', 401);
        }

        return $this;
    }

    public function getBranch($branch = null)
    {
        if (! isset($this->_config['branches']) ||
            0 == count($this->_config['branches'])
        ) {
            throw new Exception('You need to define the settings for the branches', 401);
        }

        if (! isset($this->_config['branches'][$branch])) {
            throw new InvalidArgumentException('Unknown branch', 404);
        }

        return new Branch($branch, $this->_config['branches'][$branch]);
    }
}
