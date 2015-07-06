<?php

/**
 * Class ImporterTestCategoryDataObject
 */
class ImporterTestCategoryDataObject extends DataObject
{
    /**
     * @var array
     */
    private static $db = array(
        'Title' => 'Varchar(255)',
    );

    /**
     * @var array
     */
    private static $has_many = array(
        'Items' => 'ImporterTestDataObject',
        'SubCategories' => 'ImporterTestSubCategoryDataObject',
    );
}
