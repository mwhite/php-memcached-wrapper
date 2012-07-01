<?php

require_once 'MemcachedWrapper.php';

class MemcachedWrapperTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->foo = new MemcachedWrapper("foo");
        $this->foo->addServer('localhost', 11211);

        $this->bar = new Memcached();
        $this->bar->addServer('localhost', 11211);
    }

    public function tearDown() {
        $this->bar->flush();
    }

    public function testNativeMethod() {
        $this->foo->set('bar', 'x');
        $this->assertEquals($this->bar->get('foobar'), 'x');
    }

    public function testUnderlyingObjectMethod() {
        $this->foo->mc->set('bar', 'x');
        $this->assertEquals($this->bar->get('bar'), 'x');
    }

    public function testArrayAccess() {
        $this->assertFalse(isset($this->foo['bar']));
        $this->assertTrue(empty($this->foo['bar']));

        $this->foo['bar'] = "x";
        $this->assertTrue(isset($this->foo['bar']));
        $this->assertEquals($this->foo['bar'], "x");

        unset($this->foo['bar']);
        $this->assertFalse(isset($this->foo['bar']));
    }

    public function testFalsyArrayAccess() {
        $this->foo['bar'] = false;
        $this->assertTrue(isset($this->foo['bar']));
        $this->assertEquals($this->foo['bar'], false);
        unset($this->foo['bar']);
        $this->assertFalse(isset($this->foo['bar']));
    }

    /**
     * @expectedException         MemcachedWrapperError
     * @expectedExceptionMessage  Tried to set null offset
     */
    public function testSetNullOffset() {
        $this->foo[] = 3;
    }

    public function testPrefixSingle() {
        $this->foo['bar'] = 5;
        $this->assertEquals($this->bar->get('foobar'), 5);
    }

    public function testPrefixMulti() {
        $this->foo->setMulti(array('bar1' => 'x', 'bar2' => 'y'));
        $this->assertEquals($this->bar->get('foobar1'), 'x');
        $this->assertEquals($this->bar->get('foobar2'), 'y');
    }

    public function testMultidimensionalArrayAccess() {
        $this->foo['bar'] = array('asdf' => 'hjkl');
        $this->assertEquals($this->foo['bar']['asdf'], 'hjkl');
    }
}
