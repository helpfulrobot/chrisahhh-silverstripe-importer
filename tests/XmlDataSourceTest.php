<?php

/**
 * Class XmlDataSourceTest
 */
class XmlDataSourceTest extends SapphireTest
{
	/**
	 *
     */
	public function testXml_ParsesXml_BuildsCorrectProxy()
	{
		$fileName = realpath(dirname(__FILE__)) . '/sample.xml';
		$dataSource = XmlDataSource::loadFromFile($fileName);

		$this->assertEquals('Jobs', $dataSource->jobs[0]->getName());
		$this->assertEquals('10000', $dataSource->jobs[0]->jid());
		$this->assertTrue($dataSource->jobs[0]->job->isArray());
		$this->assertEquals(1, $dataSource->jobs[0]->job->count());

		$job = $dataSource->jobs[0]->job[0];
		$this->assertEquals('Sample Job', $job->title[0]());
		$this->assertEquals('sample001', $job->reference());
		$this->assertEquals('This is the search title', $job->searchTitle[0]());
		$this->assertEquals('BulletPoint', $job->bulletPoints[0]->bulletPoint[0]->getName());
		$this->assertEquals('Thrilling Partnership', $job->bulletPoints[0]->bulletPoint[0]());
		$this->assertEquals('65000', $job->salary[0]->minValue[0]());
		$this->assertEquals('Sub Category', $job->classifications[0]->classification[1]->name());
		$this->assertEquals('Architect', $job->classifications[0]->classification[1]());
	}
}
