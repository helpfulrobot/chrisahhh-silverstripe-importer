<?php

/**
 * Class ImporterSubClassTestDataObject
 */
class ImporterSubClassTestDataObject extends ImporterTestDataObject
{
    /**
     * @var array
     */
    private static $db = array(
        'SubClassValue' => 'Varchar(255)',
    );

    /**
     * @var array
     */
    private static $has_one = array(
        'SubClassChild' => 'ImporterTestHasOneDataObject',
    );

    /**
     * @var array
     */
    private static $has_many = array(
        'SubClassChildren' => 'ImporterTestHasManyDataObject',
    );
}
