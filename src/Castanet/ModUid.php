<?php

/**
 * Emulates Apaches's mod_id or Nginx's ngx_http_userid_module module
 *
 * Usage:
 * <pre>
 * $modUid = new Castanet_ModUid;
 * $modUid->enable();
 *        ->start();
 * </pre>
 */
class Castanet_ModUid
{
    const NOTE_NAME_SET = 'uid_set';
    const NOTE_NAME_GOT = 'uid_got';
    const SEQUENCER_V1 = 1;
    const SEQUENCER_V2 = 0x03030302;


    protected static $configNames = array('name', 'domain', 'p3p', 'path', 'expires', 'service');

    private $enabled = false;
    private $name    = 'uid';
    private $domain  = null;
    private $p3p     = false;
    private $path    = '/';
    private $expires = 31449600;// 52 weeks
    private $service;

    public function __construct(array $options = null)
    {
        
    }

    public function start(array $cookie)
    {
        if ($cookieValue = $cookie[$this->name]) {
            $noteName = self::NOTE_NAME_GOT;
        } else {
            $noteName = self::NOTE_NAME_SET;
            $cookieValue = $this->calcCookieValue();
        }
        return $this->setNote($noteName, $cookieValue);
    }

    /**
     * @return Castanet_ModUid
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (boolean)$enabled;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return Castanet_ModUid
     */   
    public function enable()
    {
        return $this->setEnabled(true);
    }

    /**
     * @return Castanet_ModUid
     */
    public function disable()
    {
        return $this->setEnabled(false);
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->getEnabled();
    }

    /**
     * @return boolean
     */
    public function isDisabled()
    {
        return ! $this->getEnabled();
    }

    public function getConfig($name)
    {
        if (! in_array($name, self::$configNames)) {
            return;
        }
        return $this->$name;
    }

    /**
     * Usage:
     * <pre>
     * $uid->setConfig('name', 'castanet');
     * </pre>
     * 
     * @return Castanet_ModUid
     */
    public function setConfig($name, $value)
    {
        if (in_array($name, self::$configNames)) {
            $this->$name = $value;
        }
        return $this;
    }

    /**
     * @param array $configs
     * @return Castanet_ModUid
     */
    public function setConfigs(array $configs)
    {
        foreach ($configs as $name => $value) {
            $this->setConfig($name, $value);
        }
        return $this;
    }

    public function getTimestamp()
    {
        return isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME']
                                               : time();
    }

    public function uidToLog(array $seeds)
    {
        return sprintf('%08X%08X%08X%08X',
                       $seeds[0],
                       $seeds[1],
                       $seeds[2],
                       $seeds[3]);
    }

    public function uidToCookie(array $seeds)
    {
        $uid = '';
        foreach ($seeds as $seed) {
            $uid .= pack('V*', $seed);
        }
        return base64_encode($uid);
    }

    public function createSeeds()
    {
        list($usec, $sec) = explode(' ', microtime());
        return array($this->service,
                     $this->getTimestamp(),
                     (((int)$usec * 1000 * 1000 / 20) << 16 | getmypid()),
                     self::SEQUENCER_V2
                     );
    }

    /**
     * @return String|false previous note value.
     *                      false if failed to set note.
     */
    protected function setNote($name, $value)
    {
        return apache_note($name, $value);
    }
}
