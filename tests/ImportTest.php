<?php

class ImportTest extends SapphireTest
{
	public function testRun()
	{
		$import = new Import('ImporterTestDataObject');
		$import->unique(array(
			'ImporterTestDataObject' => array('UniqueID', 'Value'),
		))->run();
	}
}
