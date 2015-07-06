<?php

/**
 * Class ProxyObjectTest
 */
class ProxyObjectTest extends SapphireTest
{
	/**
	 *
     */
	public function testSetAndGet_SetValue_ReturnsValue()
	{
		$proxyObject = new ProxyObject();
		$proxyObject->Name = 'name';
		$proxyObject->Parent->Child = 'child';
		$proxyObject->Parent = 'Hello world';

		$this->assertEquals('name', $proxyObject->Name());
		$this->assertEquals('Hello world', $proxyObject->Parent());
		$this->assertEquals('child', $proxyObject->Parent->Child());
		$this->assertTrue(isset($proxyObject->Parent));
		$this->assertFalse(isset($proxyObject->DoesNotExist));
		$this->assertNull($proxyObject->SomeValue->SomeOtherValue());
	}

	/**
	 *
     */
	public function testIterator_SetArray_IteratesProxyValues()
	{
		$values = array('Test1', 'Test2', 'Test3');
		$nested = array('Test4', 'Test5');

		$proxyObject = new ProxyObject($values);
		$proxyObject->Nested = $nested;

        $count = 0;
		foreach ($proxyObject as $key => $value) {
            $count++;
			$this->assertEquals($values[$key], $value());
		}

		foreach ($proxyObject->Nested as $key => $value) {
            $count++;
			$this->assertEquals($nested[$key], $value());
		}
        $this->assertEquals(5, $count);
	}

    /**
     *
     */
    public function testIterator_SetEmptyArray_DoesNotIterate()
    {
        $values = array();
        $proxyObject = new ProxyObject($values);

        $this->assertTrue($proxyObject->isArray());
        $this->assertEquals(0, $proxyObject->count());

        foreach ($proxyObject as $key => $value) {
            $this->fail('Should not iterate an empty array');
        }
    }

    /**
     *
     */
    public function testIterator_SetSingleElement_IteratesProxyValues()
    {
        $values = array('This is a string');
        $proxyObject = new ProxyObject($values);

        $this->assertTrue($proxyObject->isArray());
        $this->assertEquals(1, $proxyObject->count());

        $count = 0;
        foreach ($proxyObject as $key => $value) {
            $count++;
            $this->assertEquals($values[0], $value());
        }
        $this->assertEquals(1, $count);
    }

	/**
	 *
     */
	public function testIterator_ValueIsKeyedArray_IteratesProxyValues()
	{
		$values = array(
			'Key1' => 'Value1',
			'Key2' => 'Value2',
			'Key3' => 'Value3',
		);
		$proxyObject = new ProxyObject(($values));

        $count = 0;
		foreach ($proxyObject as $key => $value) {
            $count++;
			$this->assertEquals($values[$key], $value());
		}
        $this->assertEquals(3, $count);
	}

	/**
	 *
	 */
	public function testOffsetGetAndSet_ValueIsArray_OffsetReturnsCorrectProxyValues()
	{
		$values = array('Hello', 'World', 'Test');

		$proxyObject = new ProxyObject($values);

		$this->assertEquals('Hello', $proxyObject[0]());
		$this->assertEquals('World', $proxyObject[1]());
		$this->assertEquals('Test', $proxyObject[2]());
		$this->assertEquals(3, $proxyObject->count());
		$this->assertNull($proxyObject[66]());

		unset($proxyObject[1]);
		$this->assertTrue(isset($proxyObject[0]));
		$this->assertFalse(isset($proxyObject[1]));
		$this->assertTrue(isset($proxyObject[2]));
		$this->assertEquals('Hello', $proxyObject[0]());
		$this->assertNull($proxyObject[1]());
		$this->assertEquals('Test', $proxyObject[2]());
		$this->assertEquals(2, $proxyObject->count());
	}

    /**
     *
     */
    public function testGetAndSet_CaseSensitivity_IgnoresCase()
	{
		$proxyObject = new ProxyObject();
		$proxyObject->Greeting = 'Hello World';
		$proxyObject->Greeting->speaker = 'chris';

		$this->assertEquals('Hello World', $proxyObject->Greeting());
		$this->assertEquals('Hello World', $proxyObject->greeting());
		$this->assertEquals('chris', $proxyObject->greeting->speaker());
		$this->assertEquals('chris', $proxyObject->greeting->Speaker());
		$this->assertTrue(isset($proxyObject->Greeting));
		$this->assertTrue(isset($proxyObject->greeting));
		$this->assertTrue(isset($proxyObject->greeting->speaker));
		$this->assertTrue(isset($proxyObject->greeting->Speaker));
		$this->assertFalse(isset($proxyObject->greeting->Hello));
		unset($proxyObject->Greeting->Speaker);
		$this->assertFalse(isset($proxyObject->greeting->speaker));
	}
}
