<?php

/**
 * Class CsvDataSource
 */
class CsvDataSource extends ProxyObject implements DataSource
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
		$dataString = trim(str_replace(array("\r\n", "\r", "\n"), PHP_EOL, $dataString));
		$rows = array_map('str_getcsv', explode(PHP_EOL, $dataString));

		if (empty($rows)) {
			return new CsvDataSource(array());
		}

		$columns = array_shift($rows);
		$proxyObjects = array();

		foreach ($rows as $row) {
			$proxy = new ProxyObject();
			foreach ($columns as $key => $column) {
				if (isset($row[$key])) {
					$proxy->$column = $row[$key];
				}
			}
			$proxyObjects[] = $proxy;
		}

		return new CsvDataSource($proxyObjects);
	}
}
