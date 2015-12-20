<?php

/**
 * Class ImporterTestDataObject
 */
class ImporterTestHasOneDataObject extends DataObject
{
    /**
     * @var array
     */
    private static $db = array(
        'Value' => 'Varchar(255)',
        'OtherValue' => 'Varchar(255)',
    );

    /**
     * @var array
     */
    private static $has_one = array(
        'Child' => 'ImporterTestHasOneDataObject',
    );
}
