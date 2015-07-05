<?php

/**
 * Class ProxyObject
 */
class ProxyObject implements ArrayAccess, Iterator
{
	/**
	 * @var
     */
	private $name;

	/**
	 * @var
	 */
	private $data;

	/**
	 * @var
	 */
	private $keys;

	/**
	 * @var
	 */
	private $position = 0;

	/**
	 * @var array
	 */
	private $fields = array();

	/**
	 * @param null $data
	 * @param null $name
     */
	public function __construct($data = null, $name = null)
	{
		$this->setValue($data);
		$this->setName($name);
	}

	/**
	 *
     */
	public function setName($name)
	{
		$this->name = $name;
	}


	/**
	 *
     */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return ProxyObject
	 */
	public function getValue()
	{
		return $this->data;
	}

	/**
	 * @param $value
	 */
	public function setValue($value)
	{
		if ($value instanceof ProxyObject) {
			$value = $value->getValue();
		}

		$this->position = 0;

		if (is_array($value)) {
			$this->data = array();
			foreach ($value as $key => $v) {
				$this->data[$key] = $v instanceof ProxyObject ? $v : new ProxyObject($v);
			}
			$this->keys = array_keys($this->data);
		} else {
			$this->keys = null;
			$this->data = $value;
		}
	}

	/**
	 * @return null
	 */
	public function __invoke()
	{
		return $this->getValue();
	}

	/**
	 * @param $name
	 * @return ProxyObject
	 */
	public function __call($name, $arguments)
	{
		return $this->__get($name)->getValue();
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function __set($name, $value)
	{
		$name = strtolower($name);
		if (isset($this->fields[$name])) {
			$this->fields[$name]->setValue($value);
		} else {
			$this->fields[$name] = $value instanceof ProxyObject ? $value : new ProxyObject($value);
		}
	}

	/**
	 * @param $name
	 * @param $value
     */
	public function set($name, $value)
	{
		$this->__set($name, $value);
	}

	/**
	 * @param $name
	 * @return ProxyObject
	 */
	public function __get($name)
	{
		$name = strtolower($name);
		if (empty($this->fields[$name])) {
			$this->fields[$name] = new ProxyObject();
		}
		return $this->fields[$name];
	}

	/**
	 * @param $name
	 * @return ProxyObject
     */
	public function get($name)
	{
		return $this->__get($name);
	}

	/**
	 * @param $name
	 * @return bool
	 */
	public function __isset($name)
	{
		$name = strtolower($name);
		return isset($this->fields[$name]);
	}

	/**
	 * @param $name
	 */
	public function __unset($name)
	{
		$name = strtolower($name);
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
		if ($this->isArray()) {
			return isset($this->data[$offset]);
		}
		return false;
	}

	/**
	 * @param mixed $offset
	 * @return ProxyObject
	 */
	public function offsetGet($offset)
	{
		if ($this->isArray() && isset($this->data[$offset])) {
			return $this->data[$offset];
		}
		return new ProxyObject();
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		if ($this->isArray()) {
			$this->data[$offset] = $value instanceof ProxyObject ? $value : new ProxyObject($value);
			$this->keys = array_keys($offset);
		}
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetUnset($offset)
	{
		if ($this->isArray()) {
			unset($this->data[$offset]);
			$this->keys = array_keys($this->data);
		}
	}

	/**
	 * @return bool|int
     */
	public function count()
	{
		if ($this->isArray()) {
			return count($this->data);
		}
		return 0;
	}

	/**
	 * @return bool
	 */
	public function isArray()
	{
		return is_array($this->data);
	}

	/**
	 * @return ProxyObject
	 */
	public function current()
	{
		if (is_array($this->data)) {
			return isset($this->keys[$this->position]) ? $this->values[$this->keys[$this->position]] : new ProxyObject();
		} else {
			return $this->getValue();
		}
	}

	/**
	 *
	 */
	public function next()
	{
		$this->position++;
	}

	/**
	 * @return null
	 */
	public function key()
	{
		if (is_array($this->data)) {
			return $this->keys[$this->position];
		}
		return null;
	}

	/**
	 * @return bool
	 */
	public function valid()
	{
		if (is_array($this->data)) {
			return isset($this->values[$this->position]);
		}
		return false;
	}

	/**
	 *
	 */
	public function rewind()
	{
		$this->position = 0;
	}
}
