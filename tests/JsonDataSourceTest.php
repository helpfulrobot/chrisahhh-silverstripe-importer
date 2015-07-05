<?php

/**
 * Class JsonDataSourceTest
 */
class JsonDataSourceTest extends SapphireTest
{
	/**
	 *
     */
	public function testJson_ParsesJson_BuildsCorrectProxy()
	{
		$filePath = realpath(dirname(__FILE__)). '/sample.json';
		$dataSource = JsonDataSource::loadFromFile($filePath);

		$this->assertEquals('0001', $dataSource->id());
		$this->assertEquals('donut', $dataSource->type());
		$this->assertTrue($dataSource->batters->batter->isArray());
		$this->assertEquals('1001', $dataSource->batters->batter[0]->id());
		$this->assertEquals('Blueberry', $dataSource->batters->batter[2]->type());
		$this->assertTrue($dataSource->topping->isArray());
		$this->assertEquals('Powdered Sugar', $dataSource->topping[3]->type());
	}
}
