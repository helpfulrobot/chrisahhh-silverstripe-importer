<?php

/**
 * Class ImporterTestDataObject
 */
class ImporterTestHasManyDataObject extends DataObject
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
        'Parent' => 'ImporterTestDataObject',
    );
}
