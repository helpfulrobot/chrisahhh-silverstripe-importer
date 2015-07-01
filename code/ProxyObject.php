<?php

/**
 * Class ProxyObject
 */
class ProxyObject implements ArrayAccess
{
	/**
	 * @var
     */
	private $value;

	/**
	 * @var array
     */
	private $fields = array();

	/**
	 * @param null $value
     */
	public function __construct($value = null)
	{
		$this->setValue($value);
	}

	/**
	 * @return ProxyObject
     */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param $value
     */
	public function setValue($value)
	{
		if ($value instanceof ProxyObject) {
			$value = $value->getValue();
		}
		$this->value = $value;
	}

	/**
	 * @return null
     */
	public function __invoke()
	{
		return $this->value;
	}

	/**
	 * @param $name
	 * @return ProxyObject
     */
	public function __call($name)
	{
		return $this->__get($name)->getValue();
	}

	/**
	 * @param $name
	 * @param $value
     */
	public function __set($name, $value)
	{
		if (isset($this->fields[$name])) {
			$this->fields[$name]->setValue($value);
		} else {
			$this->fields[$name] = $value instanceof ProxyObject ? $value : new ProxyObject($value);
		}
	}

	/**
	 * @param $name
	 * @return ProxyObject
     */
	public function __get($name)
	{
		if (empty($this->fields[$name])) {
			return new ProxyObject();
		}
		return $this->fields[$name];
	}

	/**
	 * @param $name
	 * @return bool
     */
	public function __isset($name)
	{
		return isset($this->fields[$name]);
	}

	/**
	 * @param $name
     */
	public function __unset($name)
	{
		if (isset($this->fields[$name])) {
			unset($this->fields[$name]);
		}
	}

	/**
	 * @param mixed $offset
	 * @return bool
     */
	public function offsetExists($offset)
	{
		return $this->__isset($offset);
	}

	/**
	 * @param mixed $offset
	 * @return ProxyObject
     */
	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
     */
	public function offsetSet($offset, $value)
	{
		$this->__set($offset, $value);
	}

	/**
	 * @param mixed $offset
     */
	public function offsetUnset($offset)
	{
		$this->__unset($offset);
	}
}
