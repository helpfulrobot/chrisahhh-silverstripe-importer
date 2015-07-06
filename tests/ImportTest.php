<?php

/**
 * Class ImportTest
 */
class ImportTest extends SapphireTest
{
    /**
     * @var bool
     */
    protected $usesDatabase = true;

    /**
     *
     */
    public function testSelect_SimpleField_WritesField()
    {
        $this->assertEquals(0, ImporterTestDataObject::get()->count());

        $dataSource = XmlDataSource::loadFromFile($this->getFilePath('import-sample.xml'));

        $import = new Import('ImporterTestDataObject');
        $import->from($dataSource->Jobs[0]->Job)->select(array(
            'Value' => 'Title'
        ));

        $this->assertEquals(1, ImporterTestDataObject::get()->count());
        $record = ImporterTestDataObject::get()->first();
        $this->assertNotNull($record);
        $this->assertEquals('Sample Job', $record->Value);
    }

    /**
     *
     */
    public function testSelect_SimpleAttribute_WritesField()
    {
        $this->assertEquals(0, ImporterTestDataObject::get()->count());

        $dataSource = XmlDataSource::loadFromFile($this->getFilePath('import-sample.xml'));

        $import = new Import('ImporterTestDataObject');
        $import->from($dataSource->Jobs[0]->Job)->select(array(
            'Value' => 'reference'
        ));

        $this->assertEquals(1, ImporterTestDataObject::get()->count());
        $record = ImporterTestDataObject::get()->first();
        $this->assertNotNull($record);
        $this->assertEquals('sample001', $record->Value);
    }

    /**
     *
     */
    public function testSelect_NestedField_WritesField()
    {
        $this->assertEquals(0, ImporterTestDataObject::get()->count());

        $dataSource = XmlDataSource::loadFromFile($this->getFilePath('import-sample.xml'));

        $import = new Import('ImporterTestDataObject');
        $import->from($dataSource->Jobs[0]->Job)->select(array(
            'Value' => 'Salary.MinValue'
        ));

        $this->assertEquals(1, ImporterTestDataObject::get()->count());
        $record = ImporterTestDataObject::get()->first();
        $this->assertNotNull($record);
        $this->assertEquals('65000', $record->Value);
    }

    /**
     *
     */
    public function testSelect_NestedFieldFilteredByAttribute_WritesField()
    {
        $this->assertEquals(0, ImporterTestDataObject::get()->count());

        $dataSource = XmlDataSource::loadFromFile($this->getFilePath('import-sample.xml'));

        $import = new Import('ImporterTestDataObject');
        $import->from($dataSource->Jobs[0]->Job)->select(array(
            'Value' => 'Classifications.Classification[name=Location]',
        ));

        $this->assertEquals(1, ImporterTestDataObject::get()->count());
        $record = ImporterTestDataObject::get()->first();
        $this->assertNotNull($record);
        $this->assertEquals('Sydney', $record->Value);
    }

    /**
     *
     */
    public function testSelect_CallbackField_WritesField()
    {
        $this->assertEquals(0, ImporterTestDataObject::get()->count());

        $dataSource = XmlDataSource::loadFromFile($this->getFilePath('import-sample.xml'));

        $import = new Import('ImporterTestDataObject');
        $import->from($dataSource->Jobs[0]->Job)->select(array(
            'Value' => function ($proxy) {
                return $proxy->Title[0]();
            },
        ));

        $this->assertEquals(1, ImporterTestDataObject::get()->count());
        $record = ImporterTestDataObject::get()->first();
        $this->assertNotNull($record);
        $this->assertEquals('Sample Job', $record->Value);
    }

    /**
     *
     */
    public function testSelect_HasOneChildField_WritesField()
    {
        $this->assertEquals(0, ImporterTestDataObject::get()->count());
        $this->assertEquals(0, ImporterTestHasOneDataObject::get()->count());

        $dataSource = XmlDataSource::loadFromFile($this->getFilePath('import-sample.xml'));

        $import = new Import('ImporterTestDataObject');
        $import->from($dataSource->Jobs[0]->Job)->select(array(
            'Child.Value' => 'Title',
        ));

        $this->assertEquals(1, ImporterTestDataObject::get()->count());
        $this->assertEquals(1, ImporterTestHasOneDataObject::get()->count());
        $record = ImporterTestDataObject::get()->first();
        $this->assertNotNull($record);
        $this->assertEquals('Sample Job', $record->Child()->Value);
    }

    /**
     *
     */
    public function testSelect_NestedHasOneChildField_WritesField()
    {
        $this->assertEquals(0, ImporterTestDataObject::get()->count());
        $this->assertEquals(0, ImporterTestHasOneDataObject::get()->count());

        $dataSource = XmlDataSource::loadFromFile($this->getFilePath('import-sample.xml'));

        $import = new Import('ImporterTestDataObject');
        $import->from($dataSource->Jobs[0]->Job)->select(array(
            'Child.Child.Child.Value' => 'Title',
        ));

        $this->assertEquals(1, ImporterTestDataObject::get()->count());
        $this->assertEquals(3, ImporterTestHasOneDataObject::get()->count());
        $record = ImporterTestDataObject::get()->first();
        $this->assertNotNull($record);
        $this->assertEquals('Sample Job', $record->Child()->Child()->Child()->Value);
    }

    /**
     *
     */
    public function testSelect_HasManyChildrenField_WritesFields()
    {
        $this->assertEquals(0, ImporterTestDataObject::get()->count());
        $this->assertEquals(0, ImporterTestHasManyDataObject::get()->count());

        $dataSource = XmlDataSource::loadFromFile($this->getFilePath('import-sample.xml'));

        $import = new Import('ImporterTestDataObject');
        $import->from($dataSource->Jobs[0]->Job)->select(array(
            'Children.Value' => 'BulletPoints.BulletPoint',
        ));

        $this->assertEquals(1, ImporterTestDataObject::get()->count());
        $this->assertEquals(3, ImporterTestHasManyDataObject::get()->count());
        $record = ImporterTestDataObject::get()->first();
        $this->assertNotNull($record);

        $values = array(
            'Thrilling Partnership',
            'Convenient Sydney CBD location',
            'Remarkable Career Opportunity'
        );
        foreach ($record->Children() as $i => $child) {
            $value = array_shift($values);
            $this->assertEquals($value, $child->Value);
        }
        $this->assertEmpty($values);
    }

    /**
     * @param $fileName
     * @return string
     */
    private function getFilePath($fileName)
    {
        return realpath(dirname(__FILE__)) . '/' . $fileName;
    }
}
