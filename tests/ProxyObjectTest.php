<?php

class ProxyObjectTest extends SapphireTest
{
	public function testSetAndGet_SetValue_ReturnsValue()
	{
		$proxyObject = new ProxyObject();
		$proxyObject->Name = 'name';
		$proxyObject->Parent->Child = 'child';
		$proxyObject->Parent = 'Hello world';

		$this->assertEquals('name', $proxyObject->Name());
		$this->assertEquals('child', $proxyObject->Parent->Child());
		$this->assertEquals('Hello world', $proxyObject->Parent());
		$this->assertNull($proxyObject->SomeValue->SomeOtherValue());
	}
}
