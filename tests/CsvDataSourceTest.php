<?php

/**
 * Class CsvDataSourceTest
 */
class CsvDataSourceTest extends SapphireTest
{
	/**
	 *
     */
	public function testCsv_ParsesCsv_BuildsCorrectProxyArray()
	{
		$fileName = realpath(dirname(__FILE__)) . '/sample.csv';
		$dataSource = CsvDataSource::loadFromFile($fileName);

		$this->assertTrue($dataSource->isArray());
		$this->assertEquals(985, $dataSource->count());
		$this->assertEquals('51 OMAHA CT', $dataSource[1]->street());
		$this->assertEquals('CA', $dataSource[7]->state());
		$this->assertTrue(isset($dataSource[0]->street));
		$this->assertTrue(isset($dataSource[10]->city));
		$this->assertTrue(isset($dataSource[27]->zip));
		$this->assertTrue(isset($dataSource[89]->state));
		$this->assertTrue(isset($dataSource[110]->beds));
		$this->assertTrue(isset($dataSource[887]->baths));
		$this->assertTrue(isset($dataSource[984]->sq__ft));
	}
}
