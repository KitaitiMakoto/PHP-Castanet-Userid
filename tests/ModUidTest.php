<?php
require 'src/Castanet/ModUid.php';

class Castanet_ModUid_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->uid = new Castanet_ModUid;
    }

    public function testDisabledWhenConstructed()
    {
        $uid = new Castanet_ModUid;
        $this->assertFalse($uid->isEnabled());
        $this->assertTrue($uid->isDisabled());
    }

    public function testSetEnabled()
    {
        $this->uid->setEnabled(true);
        $this->assertTrue($this->uid->getEnabled());

        $this->uid->setEnabled(false);
        $this->assertFalse($this->uid->getEnabled());
    }

    public function testEnable()
    {
        $this->uid->enable();
        $this->assertTrue($this->uid->getEnabled());
    }

    public function testDisable()
    {
        $this->uid->disable();
        $this->assertFalse($this->uid->getEnabled());
    }

    public function testIsEnabled()
    {
        $this->assertFalse($this->uid->isEnabled());

        $this->uid->enable();
        $this->assertTrue($this->uid->isEnabled());

        $this->uid->disable();
        $this->assertFalse($this->uid->isEnabled());
    }

    public function testIsDisabled()
    {
        $this->assertTrue($this->uid->isDisabled());

        $this->uid->enable();
        $this->assertFalse($this->uid->isDisabled());

        $this->uid->disable();
        $this->assertTrue($this->uid->isDisabled());
    }

    public function testGetConfig()
    {
        $this->assertEquals('uid', $this->uid->getConfig('name'));
        $this->assertNull($this->uid->getConfig('none'));
    }

    public function testSetConfig()
    {
        $path = '/tracking';
        $this->uid->setConfig('path', $path);
        $this->assertEquals($path, $this->uid->getConfig('path'));
    }

    public function testSetConfigs()
    {
        $name = 'tracking-user-id';
        $service = 1;
        $this->uid->setConfigs(array(
                                        'name' => $name,
                                        'service' => $service
                                        ));
        $this->assertEquals($name, $this->uid->getConfig('name'));
        $this->assertEquals($service, $this->uid->getConfig('service'));
    }

    public function testTolog()
    {
        $timestamp = time();
        $pid = getmypid();
        Castanet_ModUid::refreshSequencer();
        $uid = new Castanet_ModUid;
        $uid->setConfigs(array(
                                        'service' => ip2long('127.0.0.1'),
                                        'timestamp' => $timestamp,
                                        'startValue' => $pid,
                                        ));
        $log = $uid->toLog();

        $this->assertEquals('0100007F', substr($log, 0, 8));
        $this->assertEquals('02030303', substr($log, 24, 8));
    }

    public function testToCookie()
    {
        $cookieValue = 'fwAAAVEbtF1USQfEAwMEAg==';
        $uid = Castanet_ModUid::createFromCookie($cookieValue);

        $this->assertEquals($cookieValue, $uid->toCookie());
    }

    public function testSequencer()
    {
        Castanet_ModUid::refreshSequencer();
        $uid = new Castanet_ModUid;

        $this->assertEquals('02030303', substr($uid->toLog(), 24, 8));

        $uid2 = new Castanet_ModUid;
        $this->assertEquals('02040303', substr($uid2->toLog(), 24, 8));
    }

    public function testCreateFromCookie()
    {
        Castanet_ModUid::refreshSequencer();
        $uid = Castanet_ModUid::createFromCookie('fwAAAVEbtF1USQfEAwMEAg==');

        $this->assertEquals(ip2long('127.0.0.1'), $uid->getConfig('service'));
        $this->assertEquals(1360770141, $uid->getTimestamp());
        $this->assertEquals('0100007F5DB41B51C407495402040303', $uid->toLog());
    }

    public function testHtonl()
    {
        $long = ip2long('127.0.0.1');
        $this->assertEquals(hexdec('0100007F'), $this->uid->htonl($long));
    }
}
