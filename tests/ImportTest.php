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
     *
     */
    public function testGroup_OneGroup_CreatesGroup()
    {
        $this->assertEquals(0, ImporterTestDataObject::get()->count());
        $this->assertEquals(0, ImporterTestCategoryDataObject::get()->count());

        $dataSource = XmlDataSource::loadFromFile($this->getFilePath('import-sample.xml'));

        $import = new Import('ImporterTestDataObject');
        $import->from($dataSource->Jobs[0]->Job)
            ->group('Category.Title')
            ->select(array(
                'Children.Value' => 'Title',
                'Category.Title' => 'Classifications.Classification[name=Category]',
            ));

        $this->assertEquals(1, ImporterTestDataObject::get()->count());
        $this->assertEquals(1, ImporterTestCategoryDataObject::get()->count());
        $record = ImporterTestDataObject::get()->first();
        $this->assertNotNull($record);
        $this->assertEquals('IT', $record->Category()->Title);
    }

    /**
     *
     */
    public function testGroup_SubGroup_CreatesGroupAndSubGroup()
    {
        $this->assertEquals(0, ImporterTestDataObject::get()->count());
        $this->assertEquals(0, ImporterTestCategoryDataObject::get()->count());
        $this->assertEquals(0, ImporterTestSubCategoryDataObject::get()->count());

        $dataSource = XmlDataSource::loadFromFile($this->getFilePath('import-sample.xml'));

        $import = new Import('ImporterTestDataObject');
        $import->from($dataSource->Jobs[0]->Job)
            ->group('Category.Title', 'SubCategory.Title')
            ->select(array(
                'Value' => 'Title',
                'Category.Title' => 'Classifications.Classification[name=Category]',
                'SubCategory.Title' => 'Classifications.Classification[name=Sub Category]',
            ));

        $this->assertEquals(1, ImporterTestDataObject::get()->count());
        $this->assertEquals(1, ImporterTestCategoryDataObject::get()->count());
        $this->assertEquals(1, ImporterTestSubCategoryDataObject::get()->count());

        $record = ImporterTestDataObject::get()->first();
        $this->assertNotNull($record);
        $this->assertEquals('IT', $record->Category()->Title);
        $this->assertEquals('Architect', $record->SubCategory()->Title);

        $category = ImporterTestCategoryDataObject::get()->first();
        $this->assertNotNull($category);
        $this->assertEquals(1, $category->SubCategories()->count());
        $this->assertEquals('Architect', $category->SubCategories()->first()->Title);
        $this->assertEquals(1, $category->Items()->count());
        $this->assertEquals('Sample Job', $category->Items()->first()->Value);

        $subCategory = ImporterTestSubCategoryDataObject::get()->first();
        $this->assertNotNull($subCategory);
        $this->assertEquals('IT', $subCategory->Category()->Title);
        $this->assertEquals(1, $subCategory->Items()->count());
        $this->assertEquals('Sample Job', $subCategory->Items()->first()->Value);
    }

    /**
     *
     */
    public function testUnique_ImportTwiceWithUniqueID_CreatesOneDataObject()
    {
        $this->assertEquals(0, ImporterTestDataObject::get()->count());

        $dataSource = XmlDataSource::loadFromFile($this->getFilePath('import-sample.xml'));

        $import = new Import('ImporterTestDataObject');
        $import->from($dataSource->Jobs[0]->Job)
            ->unique('UniqueID')
            ->select(array(
                'UniqueID' => 'jid',
                'Value' => 'Title',
            ));

        $this->assertEquals(1, ImporterTestDataObject::get()->count());
        $this->assertEquals('100000', ImporterTestDataObject::get()->first()->UniqueID);

        $import = new Import('ImporterTestDataObject');
        $import->from($dataSource->Jobs[0]->Job)
            ->unique('UniqueID')
            ->select(array(
                'UniqueID' => 'jid',
                'Value' => 'Title',
            ));

        $this->assertEquals(1, ImporterTestDataObject::get()->count());
        $this->assertEquals('100000', ImporterTestDataObject::get()->first()->UniqueID);
    }

    /**
     *
     */
    public function testUnique_ImportTwiceWithUniqueHasMany_CreatesUniqueChildren()
    {
        $this->assertEquals(0, ImporterTestDataObject::get()->count());

        $dataSource = XmlDataSource::loadFromFile($this->getFilePath('import-sample.xml'));

        $import = new Import('ImporterTestDataObject');
        $import->from($dataSource->Jobs[0]->Job)
            ->unique('UniqueID', 'Children.Value')
            ->select(array(
                'UniqueID' => 'jid',
                'Value' => 'Title',
                'Children.Value' => 'BulletPoints.BulletPoint',
            ));

        $object = ImporterTestDataObject::get()->first();
        $this->assertEquals(1, ImporterTestDataObject::get()->count());
        $this->assertEquals('100000', $object->UniqueID);
        $this->assertEquals(3, $object->Children()->count());

        $import = new Import('ImporterTestDataObject');
        $import->from($dataSource->Jobs[0]->Job)
            ->unique('UniqueID', 'Children.Value')
            ->select(array(
                'UniqueID' => 'jid',
                'Value' => 'Title',
                'Children.Value' => 'BulletPoints.BulletPoint',
            ));

        $object = ImporterTestDataObject::get()->first();
        $this->assertEquals(1, ImporterTestDataObject::get()->count());
        $this->assertEquals('100000', $object->UniqueID);
        $this->assertEquals(3, $object->Children()->count());
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
