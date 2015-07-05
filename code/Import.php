<?php

/**
 * Class Import
 */
class Import
{
	/**
	 * @var
     */
	private $dataObjectClass;

	/**
	 * @var
     */
	private $proxy;

	/**
	 * @var
     */
	private $groups;

	/**
	 * @param $dataObjectClass
     */
	public function __construct($dataObjectClass)
	{
		$this->dataObjectClass = $dataObjectClass;
	}

	/**
	 * @param ProxyObject $proxy
	 * @return $this
     */
	public function from(ProxyObject $proxy)
	{
		$this->proxy = $proxy;
		return $this;
	}

	/**
	 * @return $this
     */
	public function group()
	{
		$this->groups = func_get_args();
		return $this;
	}

	/**
	 * @param $fields
	 * @return mixed
     */
	public function select($fields)
	{
		if (!$this->proxy->isArray()) {
			error_log('Data source is not an array');
			return false;
		}

		foreach ($this->proxy as $proxy) {
			$this->createObject($proxy, $fields);
		}

		return true;
	}

	/**
	 * @param ProxyObject $proxy
	 * @param $fields
     */
	private function createObject(ProxyObject $proxy, $fields)
	{
		$dataObject = $this->findDataObject($proxy, $fields);
		if (!$dataObject) {
			$class = $this->dataObjectClass;
			$dataObject = new $class;
		}

		foreach ($fields as $field => $proxyField) {
			$dataObject->$field = $proxy->$proxyField();
		}

		$dataObject->write();
	}

	/**
	 * @param $proxy
	 * @return mixed
     */
	private function findDataObject($proxy)
	{
		$filters = array();

		foreach ($this->groups as $group) {
			$value = $proxy->$group();
			$filters[$group] = $value;
		}

		$class = $this->dataObjectClass;
		return $class::get()->filter($filters)->first();
	}
}
