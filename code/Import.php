<?php

class Import
{
	private $className;

	private $fields;

	private $uniqueFields;

	private $classMetaData = array();

	public function __construct($className)
	{
		$this->className = $className;
	}

	public function unique($uniqueFields)
	{
		$this->uniqueFields = $uniqueFields;
		return $this;
	}

	public function select($fields)
	{
		$this->fields = $fields;
		return $this;
	}

	public function run()
	{
		$this->buildMetaData();
		print_r($this->classMetaData);
	}

	private function buildMetaData()
	{
		$stack = array();
		$stack[] = $this->className;

		while ($stack) {
			$className = array_pop($stack);

			$obj = singleton($className);

			foreach ($obj->getClassAncestry() as $currClassName) {
				$currObj = singleton($currClassName);
				$hasOneFields = $currObj->has_one();
				foreach ($hasOneFields as $fieldName => $hasOneClassName) {
					if (!isset($this->classMetaData[$hasOneClassName])) {
						$stack[] = $hasOneClassName;
						$this->classMetaData[$hasOneClassName] = array();
					}

					$this->classMetaData[$className]['hasOne'][$fieldName] = array('type' => $hasOneClassName);
				}

				$hasManyFields = $currObj->has_many();
				foreach ($hasManyFields as $fieldName => $hasManyClassName) {
					if (!isset($this->classMetaData[$hasManyClassName])) {
						$stack[] = $hasManyClassName;
						$this->classMetaData[$hasManyClassName] = array();
					}

					$this->classMetaData[$className]['hasMany'][$fieldName] = array('type' => $hasManyClassName);
				}
			}
		}

		foreach ($this->uniqueFields as $className => $uniqueFields) {
			if (isset($this->classMetaData[$className])) {
				$this->classMetaData[$className]['uniqueFields'] = $uniqueFields;
			} else {
				// error
			}
		}
	}
}
