<?php

/**
 * Class ImporterTestSubCategoryDataObject
 */
class ImporterTestSubCategoryDataObject extends DataObject
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
    private static $has_one = array(
        'Category' => 'ImporterTestCategoryDataObject',
    );

    /**
     * @var array
     */
    private static $has_many = array(
        'Items' => 'ImporterTestDataObject',
    );
}
