<?php

/**
 * Class JsonDataSource
 */
class JsonDataSource extends ProxyObject implements DataSource
{
	/**
	 * @param $fileName
	 * @return mixed
	 */
	public static function loadFromFile($fileName)
	{
		$data = file_get_contents($fileName);
		return self::loadFromString($data);
	}

	/**
	 * @param $dataString
	 * @return mixed
	 */
	public static function loadFromString($dataString)
	{
		$data = json_decode($dataString);
		$dataSource = new JsonDataSource();

		$dataSource->process($data, $dataSource);

		return $dataSource;
	}

	/**
	 * @param $data
	 * @param ProxyObject $proxy
     */
	private function process($data, ProxyObject $proxy)
	{
		if (is_object($data)) {
			foreach ($data as $key => $value) {
				$childProxy = new ProxyObject();
				$this->process($value, $childProxy);
				$proxy->$key = $childProxy;
			}
		} else if (is_array($data)) {
			$children = array();
			foreach ($data as $value) {
				$childProxy = new ProxyObject();
				$this->process($value, $childProxy);
				$children[] = $childProxy;
			}
			$proxy->setValue($children);
		} else {
			$proxy->setValue($data);
		}
	}
}
