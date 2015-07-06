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
     * @var array
     */
    private $uniqueFields = array();

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
     * @return $this
     */
    public function unique()
    {
        $this->uniqueFields = array_merge($this->uniqueFields, func_get_args());
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
            if (is_callable($proxyField)) {
                $result = $proxyField($proxy, $dataObject);
                $values = is_array($result) ? $result : array($result);
            } else {
                $values = $this->getProxyFieldValues($proxy, $proxyField);
            }
            $this->setField($dataObject, $field, $values);
		}

		$dataObject->write();
	}

	/**
	 * @param $proxy
     * @param $fields
	 * @return mixed
     */
	private function findDataObject(ProxyObject $proxy, $fields)
	{
		$filters = array();

		foreach ($this->uniqueFields as $field) {
            if (isset($fields[$field])) {
                $values = $this->getProxyFieldValues($proxy, $field);
                $filters[$field] = empty($values) ? null : $values[0];
            } else {
                // throw error, field does not exist
            }
		}

		$class = $this->dataObjectClass;
		return $class::get()->filter($filters)->first();
	}

    /**
     * @param $dataObject
     * @param $fieldString
     * @param $values
     */
    private function setField($dataObject, $fieldString, &$values)
    {
        $fields = explode('.', $fieldString);
        $field = array_shift($fields);
        $fieldString = implode('.', $fields);

        if ($fieldString === '') {
            $dataObject->$field = count($values) ? array_shift($values) : null;
            return;
        }

        if ($class = $dataObject->has_one($field)) {
            $child = new $class();
            $this->setField($child, $fieldString, $values);
            $child->write();
            $relationshipField = $field . 'ID';
            $dataObject->$relationshipField = $child->ID;
            $dataObject->write();
        } else if ($class = $dataObject->has_many($field)) {
            while ($values) {
                $value = array_shift($values);
                $child = new $class();
                $value = array($value);
                $this->setField($child, $fieldString, $value);
                $dataObject->$field()->add($child);
            }
        } else {
            // error
        }
    }

    /**
     * @param ProxyObject $proxy
     * @param $fieldString
     * @return array
     */
    private function getProxyFieldValues(ProxyObject $proxy, $fieldString)
    {
        $returnValues = array();

        // only do when $fieldString === '' since attributes etc have to be processed first
        if ($fieldString === '') {
            if ($proxy->isArray()) {
                foreach ($proxy as $field) {
                    $returnValues[] = $field();
                }
            } else {
                $returnValues[] = $proxy();
            }
            return $returnValues;
        }

        $fields = explode('.', $fieldString);
        $field = array_shift($fields);
        $fieldString = implode('.', $fields);

        // have to return an array of values to match for has_many relationships
        preg_match('/^(\w+)\[(\w+)=([a-zA-Z-_ ]([0-9a-zA-Z-_ ]+)?)\]$/', $field, $matches);
        if (!empty($matches)) {
            $field = $matches[1];
            $attribute = $matches[2];
            $value = $matches[3];

            if ($proxy->$field->isArray()) {
                foreach ($proxy->$field as $option) {
                    if ($option->$attribute() == $value) {
                        $values = $this->getProxyFieldValues($option, $fieldString);
                        $returnValues = array_merge($returnValues, $values);
                    }
                }
            } else if ($proxy->$attribute == $value) {
                $values = $this->getProxyFieldValues($proxy->$field, $fieldString);
                $returnValues = array_merge($returnValues, $values);
            }
        } else {
            if ($proxy->$field->isArray()) {
                foreach ($proxy->$field as $option) {
                    $values = $this->getProxyFieldValues($option, $fieldString);
                    $returnValues = array_merge($returnValues, $values);
                }

            } else {
                $values = $this->getProxyFieldValues($proxy->$field, $fieldString);
                $returnValues = array_merge($returnValues, $values);
            }
        }

        return $returnValues;
    }
}
