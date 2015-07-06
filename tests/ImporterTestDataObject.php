<?php

/**
 * Class ImporterTestDataObject
 */
class ImporterTestDataObject extends DataObject
{
    /**
     * @var array
     */
    private static $db = array(
        'Value' => 'Varchar(255)',
    );

    /**
     * @var array
     */
    private static $has_one = array(
        'Child' => 'ImporterTestHasOneDataObject',
    );

    /**
     * @var array
     */
    private static $has_many = array(
        'Children' => 'ImporterTestHasManyDataObject',
    );
}
