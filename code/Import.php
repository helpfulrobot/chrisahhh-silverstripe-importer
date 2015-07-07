<?php

/**
 * Class Import
 * LIMITATIONS:
 * - cannot set multiple properties on the same has_many data object
 * - does not support unique nested fields e.g HasOne.FieldName
 *
 * TODO:
 * either change to pre process and group select fields
 * or change the fields syntax
 * to handle multiple properties for has_many relationships
 * (could change the group syntax to this afterwards)
 *
 * add many_many relationship support
 *
 * add namespacing
 *
 * need to add delete if not present (including has_one && has_many etc)
 *
 * add support for validating data objects ??
 *
 * add method for reporting e.g ->report(ReportWriter $writer) and pass a ImportRecord with errors/warnings/number objs imported/number objs deleted
 *
 * change deleteOldRecords to take an array of classNames that are to be deleted, or defaults to all touched
 */
class Import
{
    /**
     * @var
     */
    private $dataObjectClass;

    /**
     * @var
     */
    private $proxy;

    /**
     * @var
     */
    private $groups = array();

    /**
     * @var array
     */
    private $uniqueFields = array();

    /**
     * @var bool
     */
    private $deleteRecords = false;

    // won't touch other fields that could have values
    /**
     * @var array
     */
    private $savedRecords = array();

    /**
     * @var array
     */
    private $modifyCallbacks = array();

    /**
     * @param $dataObjectClass
     */
    public function __construct($dataObjectClass)
    {
        $this->dataObjectClass = $dataObjectClass;
    }

    /**
     * @param ProxyObject $proxy
     * @return $this
     */
    public function from(ProxyObject $proxy)
    {
        $this->proxy = $proxy;
        return $this;
    }

    /**
     * @return $this
     */
    public function group()
    {
        $this->groups = func_get_args();
        return $this;
    }

    /**
     * @return $this
     */
    public function unique()
    {
        $this->uniqueFields = array_merge($this->uniqueFields, func_get_args());
        return $this;
    }

    /**
     * @param callable $fn
     * @return $this
     */
    public function modify(callable $fn)
    {
        $this->modifyCallbacks[] = $fn;
        return $this;
    }

    /**
     * @param bool $remove
     * @return $this
     */
    public function deleteOldRecords($remove = true)
    {
        $this->deleteRecords = $remove;
        return $this;
    }

    /**
     * @param $fields
     * @return mixed
     */
    public function select($fields)
    {
        if (!$this->proxy->isArray()) {
            error_log('Data source is not an array');
            return false;
        }

        foreach ($this->proxy as $proxy) {
            $this->createObject($proxy, $fields);
        }

        if ($this->deleteRecords) {
            $this->deleteUntouchedRecords();
        }

        // TODO add record method that could take a RecordWriter interface or similar
        return $this;
    }

    /**
     * @param ProxyObject $proxy
     * @param $fields
     */
    private function createObject(ProxyObject $proxy, $fields)
    {
        $dataObject = $this->findDataObject($proxy, $fields);
        if (!$dataObject) {
            $class = $this->dataObjectClass;
            $dataObject = new $class;
        }

        foreach ($fields as $field => $proxyField) {
            // skip group fields as they are set later
            if (in_array($field, $this->groups)) {
                continue;
            }

            if (is_callable($proxyField)) {
                $result = $proxyField($proxy, $dataObject);
                $values = is_array($result) ? $result : array($result);
            } else {
                $values = $this->getProxyFieldValues($proxy, $proxyField);
            }
            if (in_array($field, $this->uniqueFields)) {
                $this->setField($dataObject, $field, $values, $field);
            } else {
                $this->setField($dataObject, $field, $values);
            }
        }

        $this->write($dataObject);
        $this->savedRecords[$this->dataObjectClass][] = $dataObject->ID;

        foreach ($this->modifyCallbacks as $callback) {
            $callback($dataObject, $proxy);
            $this->write($dataObject);
        }

        $this->setGroups($proxy, $dataObject, $fields);
    }

    /**
     * @param ProxyObject $proxy
     * @param DataObject $dataObject
     * @param $selectFields
     */
    private function setGroups(ProxyObject $proxy, DataObject $dataObject, $selectFields)
    {
        if (empty($this->groups)) {
            return;
        }

        $parentObject = null;
        foreach ($this->groups as $group) {
            $fields = explode('.', $group);
            if (count($fields) !== 2) {
                // TODO move to validation in ->group()
                error_log('Group must be of format [has_one].[has_one->FieldName]');
                break;
            }

            $class = $dataObject->has_one($fields[0]);
            if (!$class) {
                error_log('Group is not a valid has_one');
                break;
            }

            if (!isset($selectFields[$group])) {
                error_log('Group must have a corresponding column in select');
                break;
            }

            $values = $this->getProxyFieldValues($proxy, $selectFields[$group]);
            if (empty($values)) {
                // warning
                error_log('Group does not have a value');
                break;
            }

            $field = $fields[1];
            $value = $values[0];

            $groupObject = $class::get()->filter(array(
                $field => $value
            ))->first();

            if (!$groupObject) {
                $groupObject = new $class();
                $groupObject->$field = $value;
                $this->write($groupObject);
            }
            $this->savedRecords[$class][] = $groupObject->ID;

            // add data object to group
            $hasMany = $groupObject->has_many();
            $hasMany = array_flip($hasMany);

            if (isset($hasMany[$dataObject->ClassName])) {
                $collection = $hasMany[$dataObject->ClassName];
                $groupObject->$collection()->add($dataObject);
            }

            // set parent group, when multiple groups
            if ($parentObject) {
                $hasMany = $parentObject->has_many();
                $hasMany = array_flip($hasMany);
                if (isset($hasMany[$groupObject->ClassName])) {
                    $collection = $hasMany[$groupObject->ClassName];
                    $parentObject->$collection()->add($groupObject);
                }
            }
            $parentObject = $groupObject;
        }
    }


    /**
     * used to find the imported DataObject, e.g not relations
     * @param $proxy
     * @param $fields
     * @return mixed
     */
    private function findDataObject(ProxyObject $proxy, $fields)
    {
        $filters = array();

        $uniqueFields = array();
        foreach ($this->uniqueFields as $field) {
            if (strpos($field, '.') === false) {
                $uniqueFields[] = $field;
            }
        }

        if (empty($uniqueFields)) {
            return null;
        }

        foreach ($this->uniqueFields as $field) {
            if (isset($fields[$field])) {
                $values = $this->getProxyFieldValues($proxy, $fields[$field]);
                $filters[$field] = empty($values) ? null : $values[0];
            } else {
                // throw error, field does not exist
            }
        }

        $class = $this->dataObjectClass;
        return $class::get()->filter($filters)->first();
    }

    /**
     * @param $dataObject
     * @param $fieldString
     * @param $values
     */
    private function setField($dataObject, $fieldString, &$values, $uniqueField = null)
    {
        $fields = explode('.', $fieldString);
        $field = array_shift($fields);
        $fieldString = implode('.', $fields);

        if ($fieldString === '') {
            $dataObject->$field = count($values) ? array_shift($values) : null;
            return;
        }

        if ($class = $dataObject->has_one($field)) {
            $child = $dataObject->$field();
            $this->setField($child, $fieldString, $values, $uniqueField ? $fieldString : null);

            $this->write($child);
            $this->savedRecords[$class][] = $child->ID;
            $relationshipField = $field . 'ID';
            $dataObject->$relationshipField = $child->ID;
            $this->write($dataObject);
        } else if ($class = $dataObject->has_many($field)) {
            while ($values) {
                $value = array_shift($values);
                // HACK
                $child = null;
                if (strpos($fieldString, '.') === false && $dataObject->exists()) {
                    $child = $dataObject->$field()->filter(array(
                        $fieldString => $value,
                    ))->first();
                }
                if (!$child) {
                    $child = new $class();
                }
                $value = array($value);
                $this->setField($child, $fieldString, $value, $uniqueField ? $fieldString : null);

                // TODO check if required
                if (!$child->exists()) {
                    $dataObject->$field()->add($child);
                } else {
                    $this->write($child);
                }
                $this->savedRecords[$class][] = $child->ID;
            }
        } else {
            // error
        }
    }

    /**
     * @param ProxyObject $proxy
     * @param $fieldString
     * @return array
     */
    private function getProxyFieldValues(ProxyObject $proxy, $fieldString)
    {
        $returnValues = array();

        // only do when $fieldString === '' since attributes etc have to be processed first
        if ($fieldString === '') {
            if ($proxy->isArray()) {
                foreach ($proxy as $field) {
                    $returnValues[] = $field();
                }
            } else {
                $returnValues[] = $proxy();
            }
            return $returnValues;
        }

        $fields = explode('.', $fieldString);
        $field = array_shift($fields);
        $fieldString = implode('.', $fields);

        // have to return an array of values to match for has_many relationships
        preg_match('/^(\w+)\[(\w+)=([a-zA-Z-_ ]([0-9a-zA-Z-_ ]+)?)\]$/', $field, $matches);
        if (!empty($matches)) {
            $field = $matches[1];
            $attribute = $matches[2];
            $value = $matches[3];

            if ($proxy->$field->isArray()) {
                foreach ($proxy->$field as $option) {
                    if ($option->$attribute() == $value) {
                        $values = $this->getProxyFieldValues($option, $fieldString);
                        $returnValues = array_merge($returnValues, $values);
                    }
                }
            } else if ($proxy->$attribute == $value) {
                $values = $this->getProxyFieldValues($proxy->$field, $fieldString);
                $returnValues = array_merge($returnValues, $values);
            }
        } else {
            if ($proxy->$field->isArray()) {
                foreach ($proxy->$field as $option) {
                    $values = $this->getProxyFieldValues($option, $fieldString);
                    $returnValues = array_merge($returnValues, $values);
                }

            } else {
                $values = $this->getProxyFieldValues($proxy->$field, $fieldString);
                $returnValues = array_merge($returnValues, $values);
            }
        }

        return $returnValues;
    }

    /**
     *
     */
    private function deleteUntouchedRecords()
    {
        // will not delete parent tables
        foreach ($this->savedRecords as $recordType => $ids) {
            $ids = array_unique($ids);

            $sql = 'DELETE FROM ' . $recordType . ' WHERE ID NOT IN (' . implode(', ', $ids) . ')';
            DB::query($sql);
        }
    }

    private function write(DataObject $dataObject)
    {
        Versioned::reading_stage('Stage');
        $dataObject->write();
        if ($dataObject instanceof SiteTree) {
            $dataObject->publish('Stage', 'Live');
        }
        Versioned::reading_stage('Live');
    }
}
