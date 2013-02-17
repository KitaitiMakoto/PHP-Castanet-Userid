<?php
require 'src/Castanet/ModUid.php';

class Castanet_ModUid_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->modUid = new Castanet_ModUid;
    }

    public function testDisabledWhenConstructed()
    {
        $modUid = new Castanet_ModUid;
        $this->assertFalse($modUid->isEnabled());
        $this->assertTrue($modUid->isDisabled());
    }

    public function testSetEnabled()
    {
        $this->modUid->setEnabled(true);
        $this->assertTrue($this->modUid->getEnabled());

        $this->modUid->setEnabled(false);
        $this->assertFalse($this->modUid->getEnabled());
    }

    public function testEnable()
    {
        $this->modUid->enable();
        $this->assertTrue($this->modUid->getEnabled());
    }

    public function testDisable()
    {
        $this->modUid->disable();
        $this->assertFalse($this->modUid->getEnabled());
    }

    public function testIsEnabled()
    {
        $this->assertFalse($this->modUid->isEnabled());

        $this->modUid->enable();
        $this->assertTrue($this->modUid->isEnabled());

        $this->modUid->disable();
        $this->assertFalse($this->modUid->isEnabled());
    }

    public function testIsDisabled()
    {
        $this->assertTrue($this->modUid->isDisabled());

        $this->modUid->enable();
        $this->assertFalse($this->modUid->isDisabled());

        $this->modUid->disable();
        $this->assertTrue($this->modUid->isDisabled());
    }

    public function testGetConfig()
    {
        $this->assertEquals('uid', $this->modUid->getConfig('name'));
        $this->assertNull($this->modUid->getConfig('none'));
    }

    public function testSetConfig()
    {
        $path = '/tracking';
        $this->modUid->setConfig('path', $path);
        $this->assertEquals($path, $this->modUid->getConfig('path'));
    }

    public function testSetConfigs()
    {
        $name = 'tracking-user-id';
        $service = 1;
        $this->modUid->setConfigs(array(
                                        'name' => $name,
                                        'service' => $service
                                        ));
        $this->assertEquals($name, $this->modUid->getConfig('name'));
        $this->assertEquals($service, $this->modUid->getConfig('service'));
    }

    public function testCreateSeeds()
    {
        $this->assertTrue(false);
    }

    public function testUidToLog()
    {
        $seeds = array('0100007F', '5DB41B51', 'C4074954', '02040303');

        $this->assertEquals(implode($seeds),
                            $this->modUid->uidToLog(array(
                                                    hexdec($seeds[0]),
                                                    hexdec($seeds[1]),
                                                    hexdec($seeds[2]),
                                                    hexdec($seeds[3])
                                                          )));
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

    public function testUidToCookie()
    {
        $seeds = array('0100007F', '5DB41B51', 'C4074954', '02040303');
        $cookieValue = 'fwAAAVEbtF1USQfEAwMEAg==';
        $this->assertEquals($cookieValue,
                            $this->modUid->uidToCookie(array(
                                                             hexdec($seeds[0]),
                                                             hexdec($seeds[1]),
                                                             hexdec($seeds[2]),
                                                             hexdec($seeds[3])
                                                             )));
    }

    public function testHtonl()
    {
        $long = ip2long('127.0.0.1');
        $this->assertEquals(hexdec('0100007F'), $this->modUid->htonl($long));
    }
}
