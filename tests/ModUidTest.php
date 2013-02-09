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
}
