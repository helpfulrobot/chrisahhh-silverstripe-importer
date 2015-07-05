<?php

/**
 * Interface DataSource
 */
interface DataSource
{
	/**
	 * @param $fileName
	 * @return mixed
     */
	public static function loadFromFile($fileName);

	/**
	 * @param $dataString
	 * @return mixed
     */
	public static function loadFromString($dataString);
}
