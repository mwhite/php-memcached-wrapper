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
        $this->assertEquals('x', $this->bar->get('foobar'));
    }

    public function testUnderlyingObjectMethod() {
        $this->foo->mc->set('bar', 'x');
        $this->assertEquals('x', $this->bar->get('bar'));
    }
    
    /**
     * @expectedException MemcachedWrapperError
     */
    public function testNonexistentMethod() {
        $this->foo->asdf();
    }

    public function testArrayAccess() {
        $this->assertFalse(isset($this->foo['bar']));
        $this->assertTrue(empty($this->foo['bar']));

        $this->foo['bar'] = "x";
        $this->assertTrue(isset($this->foo['bar']));
        $this->assertEquals('x', $this->foo['bar']);

        unset($this->foo['bar']);
        $this->assertFalse(isset($this->foo['bar']));
    }

    public function testFalsyArrayAccess() {
        $this->foo['bar'] = false;
        $this->assertTrue(isset($this->foo['bar']));
        $this->assertEquals(false, $this->foo['bar']);
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
        $this->assertEquals(5, $this->bar->get('foobar'));
    }

    public function testPrefixMulti() {
        $this->foo->setMulti(array('bar1' => 'x', 'bar2' => 'y'));
        $this->assertEquals('x', $this->bar->get('foobar1'));
        $this->assertEquals('y', $this->bar->get('foobar2'));
    }

    public function testMultidimensionalArrayAccess() {
        $this->foo['bar'] = array('asdf' => 'hjkl');
        $this->assertEquals('hjkl', $this->foo['bar']['asdf']);
    }

    public function testKeysInReturnValues() {
        $keys = array('x', 'y', 'z');
        foreach ($keys as $k) {
            $this->foo[$k] = $k;
        }

        $this->assertEquals($keys, array_keys($this->foo->getMulti($keys)));

        $this->foo->getDelayed($keys);
        $first = $this->foo->fetch();
        $this->assertTrue(strlen($first['key']) == 1);
        
        $rest = $this->foo->fetchAll();
        $this->assertCount(count($keys) - 1, $rest);
        foreach ($rest as $res) {
            $this->assertTrue(strlen($res['key']) == 1);
        }
    }
}
