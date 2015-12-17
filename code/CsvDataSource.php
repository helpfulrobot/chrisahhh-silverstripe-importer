<?php

/**
 * Class CsvDataSource
 */
class CsvDataSource extends ProxyObject implements DataSource
{
    /**
     * @param $fileName
     * @return mixed
     */
    public static function loadFromFile($fileName)
    {
        $data = file_get_contents($fileName);
        return self::loadFromString($data);
    }

    /**
     * @param $dataString
     * @return mixed
     */
    public static function loadFromString($dataString)
    {
        $dataString = trim(str_replace(array("\r\n", "\r", "\n"), PHP_EOL, $dataString));

        $rows = array();
        $row = '';
        $inCell = false;
        $len = strlen($dataString);
        for ($i = 0; $i < $len; $i++) {
            $ch = $dataString[$i];
            if ($ch === "\n" && !$inCell) {
                $rows[] = $row;
                $row = '';
            } else {
                $row .= $ch;
                if ($ch === '"') {
                    $inCell = !$inCell;
                }
            }
        }

        $rows = array_map('str_getcsv', $rows);

        if (empty($rows)) {
            return new CsvDataSource(array());
        }

        $columns = array_shift($rows);
        $columns = array_map(function ($column) {
            $column = str_replace('-', ' ', $column);
            return preg_replace('/\s+/', '', ucwords($column));
        }, $columns);

        $proxyObjects = array();

        foreach ($rows as $row) {
            // skip empty rows
            $content = trim(implode('', $row));
            if (!$content) {
                continue;
            }

            $proxy = new ProxyObject();
            foreach ($columns as $key => $column) {
                if (isset($row[$key])) {
                    $proxy->$column = $row[$key];
                }
            }
            $proxyObjects[] = $proxy;
        }

        return new CsvDataSource($proxyObjects);
    }
}
