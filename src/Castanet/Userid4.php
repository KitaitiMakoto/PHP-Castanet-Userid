<?php

/**
 * Emulates Apaches's mod_uid or Nginx's ngx_http_userid_module module
 *
 * Usage:
 * <pre>
 * $uid = new Castanet_Userid;
 * $uid->enable();
 * $uid->start();
 * </pre>
 */
class Castanet_Userid4
{
    var $NOTE_NAME_SET = 'uid_set';
    var $NOTE_NAME_GOT = 'uid_got';
    var $NOTE_NAME_MERGED = 'uid';
    var $SEQUENCER_V1 = 1;
    var $SEQUENCER_V2 = 0x03030302;

    var $configNames = array('name', 'domain', 'p3p', 'path', 'expires', 'service', 'timestamp', 'startValue');
    var $INITIAL_SEQUENCER = 0x03030302;

    var $enabled = false;
    var $name    = 'uid';
    var $domain  = null;
    var $p3p     = false;
    var $path    = '/';
    var $expires = null;
    var $service;
    var $timestamp;
    var $startValue;
    var $sequencer;

    function refreshSequencer()
    {
        $this->INITIAL_SEQUENCER = $this->SEQUENCER_V2;
    }

    function createFromCookie($cookieValue)
    {
        $uid = new self;

        $props = $this->parseCookie($cookieValue);
        $uid->service    = $props['service'];
        $uid->timestamp  = $props['timestamp'];
        $uid->startValue = $props['startValue'];
        $uid->sequencer  = $props['sequencer'];

        return $uid;
    }

    function Castanet_Userid()
    {
        $this->sequencer = $this->INITIAL_SEQUENCER;
        $this->expires = time()+60*60*24*365;
        $this->INITIAL_SEQUENCER += 0x100;
        if ($this->INITIAL_SEQUENCER < 0x03030302) {
            $this->INITIAL_SEQUENCER = 0x03030302;
        }
    }

    function start($cookie=array())
    {
        if (! $this->enabled) {
            return;
        }
        if (empty($cookie)) {
            $cookie = $_COOKIE;
        }
        if (isset($cookie[$this->name])) {
            $props = $this->parseCookie($cookie[$this->name]);
            $this->service    = $props['service'];
            $this->timestamp  = $props['timestamp'];
            $this->startValue = $props['startValue'];
            $this->sequencer  = $props['sequencer'];
            $noteName = $this->NOTE_NAME_GOT;
        } else {
            $noteName = $this->NOTE_NAME_SET;
            $cookieHeader  = 'Set-Cookie: ' . $this->name . '=' . $this->toCookie() . ';';
            $cookieHeader .= ' expires=' . $this->expires . ';';
            $cookieHeader .= ' path=' . $this->path . ';';
            if (! is_null($this->domain)) {
                $cookieHeader .= ' domain=' . $this->domain . ';';
            }
            header($cookieHeader);
        }
        $logValue = $this->toLog();
        $this->setNote($this->NOTE_NAME_MERGED, $logValue, false);
        return $this->setNote($noteName, $logValue);
    }

    /**
     * @return Castanet_Userid
     */
    function setEnabled($enabled)
    {
        $this->enabled = (boolean)$enabled;
        return $this;
    }

    /**
     * @return boolean
     */
    function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return Castanet_Userid
     */   
    function enable()
    {
        return $this->setEnabled(true);
    }

    /**
     * @return Castanet_Userid
     */
    function disable()
    {
        return $this->setEnabled(false);
    }

    /**
     * @return boolean
     */
    function isEnabled()
    {
        return $this->getEnabled();
    }

    /**
     * @return boolean
     */
    function isDisabled()
    {
        return ! $this->getEnabled();
    }

    function getConfig($name)
    {
        if (! in_array($name, $this->configNames)) {
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
     * @return Castanet_Userid
     */
    function setConfig($name, $value)
    {
        if (in_array($name, $this->configNames)) {
            $this->$name = $value;
        }
        return $this;
    }

    /**
     * @param array $configs
     * @return Castanet_Userid
     */
    function setConfigs($configs)
    {
        foreach ($configs as $name => $value) {
            $this->setConfig($name, $value);
        }
        return $this;
    }

    function getService()
    {
        if (! $this->service) {
            $this->service = isset($_SERVER['SERVER_ADDR'])
                           ? ip2long($_SERVER['SERVER_ADDR'])
                           : ip2long('1');
        }
        return $this->service;
    }

    function getTimestamp()
    {
        if (! $this->timestamp) {
            $this->timestamp = $_SERVER['REQUEST_TIME']
                             ? $_SERVER['REQUEST_TIME']
                             : time();
        }
        return $this->timestamp;
    }

    function getStartValue()
    {
        if (! $this->startValue) {
            list($usec, $sec) = explode(' ', microtime());
            $this->startValue = (((int)$usec * 1000 * 1000 / 20) << 16 | getmypid());
        }
        return $this->startValue;
    }

    function toLog()
    {
        return sprintf('%08X%08X%08X%08X',
                       $this->htonl($this->getService()),
                       $this->htonl($this->getTimestamp()),
                       $this->htonl($this->getStartValue()),
                       $this->htonl($this->sequencer));
    }

    function toCookie()
    {
        $buf = '';
        foreach (array($this->getService(), $this->getTimestamp(), $this->getStartValue(), $this->sequencer) as $seed) {
            $buf .= pack('N*', $seed);
        }
        return base64_encode($buf);
    }

    function uidToLog($seeds)
    {
        return sprintf('%08X%08X%08X%08X',
                       $seeds[0],
                       $seeds[1],
                       $seeds[2],
                       $seeds[3]);
    }

    function htonl($integer)
    {
        $hex = sprintf('%08X', $integer);
        if (strlen($hex) <= 2) {
            return $integer;
        }
        $unpacked = unpack('H*', strrev(pack('H*', $hex)));
        return hexdec($unpacked[1]);
    }

    /**
     * @return String|false previous note value.
     *                      false if failed to set note.
     */
    function setNote($name, $value, $noteName=true)
    {
        $note = $noteName ? "{$name}={$value}" : $value;
        if (function_exists('apache_note')) {
            return apache_note($name, $note);
        }
    }

    function parseCookie($cookieValue)
    {
        $decoded = base64_decode($cookieValue);
        $unpacked = unpack('N*', $decoded);
        return array(
            'service'    => $unpacked[1],
            'timestamp'  => $unpacked[2],
            'startValue' => $unpacked[3],
            'sequencer'  => $unpacked[4]
        );
    }
}
