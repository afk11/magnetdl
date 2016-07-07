<?php

namespace Torrentz;


class Params
{
    const RPC_USER = 'transmission_rpc_user';
    const RPC_PASS = 'transmission_rpc_pass';
    const RPC_HOST = 'transmission_rpc_host';
    const RPC_PORT = 'transmission_rpc_port';
    const SFTP_USER = 'sftp_user';
    const SFTP_HOST = 'sftp_host';
    const SFTP_IDENTITYFILE = 'sftp_identityfile';
    const DIR_LOCAL = 'dir_local';
    const DIR_REMOTE = 'dir_remote';
    const DEFAULT_RPC_PORT = 9091;
    /**
     * @var bool
     */
    private $authenticate = false;
    /**
     * @var string
     */
    private $dir_local;
    /**
     * @var string
     */
    private $dir_remote;
    /**
     * @var string
     */
    private $sftp_host;
    /**
     * @var string
     */
    private $sftp_user;
    /**
     * @var string
     */
    private $sftp_identityfile;
    /**
     * @var string
     */
    private $rpc_host;
    /**
     * @var int
     */
    private $rpc_port;
    /**
     * @var string
     */
    private $rpc_user;
    /**
     * @var string
     */
    private $rpc_pass;

    /**
     * Params constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        foreach ([
                     self::RPC_HOST, self::SFTP_HOST, self::SFTP_USER,
                     self::SFTP_IDENTITYFILE, self::DIR_LOCAL, self::DIR_REMOTE
                 ] as $index) {
            if (!isset($config[$index])) {
                throw new \RuntimeException('missing config param: ' . $index);
            }
        }

        if (isset($config[self::RPC_USER]) && isset($config[self::RPC_PASS])) {
            $this->authenticate = true;
        } else if (
            isset($config[self::RPC_USER]) && !isset($config[self::RPC_PASS])
            || !isset($config[self::RPC_USER]) && isset($config[self::RPC_PASS])
        ) {
            throw new \RuntimeException('Both username and password required');
        }

        $this->dir_local = $config[self::DIR_LOCAL];
        $this->dir_remote = $config[self::DIR_REMOTE];
        $this->sftp_user = $config[self::SFTP_USER];
        $this->sftp_host = $config[self::SFTP_HOST];
        $this->sftp_identityfile = $config[self::SFTP_IDENTITYFILE];
        $this->rpc_host = $config[self::RPC_HOST];
        $this->rpc_port = isset($config[self::RPC_PORT]) ? $config[self::RPC_PORT] : self::DEFAULT_RPC_PORT;
        if ($this->authenticate) {
            $this->rpc_user = $config[self::RPC_USER];
            $this->rpc_pass = $config[self::RPC_PASS];
        }
    }

    /**
     * @return boolean
     */
    public function doAuthentication()
    {
        return $this->authenticate;
    }

    /**
     * @return string
     */
    public function getDirLocal()
    {
        return $this->dir_local;
    }

    /**
     * @return string
     */
    public function getDirRemote()
    {
        return $this->dir_remote;
    }

    /**
     * @return string
     */
    public function getSftpHost()
    {
        return $this->sftp_host;
    }

    /**
     * @return string
     */
    public function getSftpUser()
    {
        return $this->sftp_user;
    }

    /**
     * @return string
     */
    public function getSftpIdentityfile()
    {
        return $this->sftp_identityfile;
    }

    /**
     * @return string
     */
    public function getRpcHost()
    {
        return $this->rpc_host;
    }

    /**
     * @return int
     */
    public function getRpcPort()
    {
        return $this->rpc_port;
    }

    /**
     * @return string
     */
    public function getRpcUser()
    {
        return $this->rpc_user;
    }

    /**
     * @return string
     */
    public function getRpcPass()
    {
        return $this->rpc_pass;
    }

}