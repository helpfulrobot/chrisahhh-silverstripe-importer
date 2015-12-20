<?php

/**
 * Class XmlDataSource
 */
class XmlDataSource extends ProxyObject implements DataSource
{
    /**
     * @param $fileName
     * @return XmlDataSource
     */
    public static function loadFromFile($fileName)
    {
        $data = file_get_contents($fileName);
        return self::loadFromString($data);
    }

    /**
     * @param $dataString
     * @return XmlDataSource
     */
    public static function loadFromString($dataString)
    {
        $dataSource = new XmlDataSource();
        $dom = new DOMDocument();
        $dom->loadXML($dataString);
        $dataSource->process($dom, $dataSource);
        return $dataSource;
    }

    /**
     * @param DOMNode $node
     * @param ProxyObject $proxy
     */
    private function process(DOMNode $node, ProxyObject $proxy)
    {
        $proxy->setName($node->nodeName);

        if ($node->hasAttributes()) {
            for ($i = 0; $i < $node->attributes->length; $i++) {
                $attribute = $node->attributes->item($i);
                $proxy->set($attribute->name, $attribute->value);
            }
        }

        if ($node->hasChildNodes()) {
            $nodeTypes = array();

            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeName === '#text') {
                    $proxy->setValue($childNode->nodeValue);
                } else {
                    $childProxy = new ProxyObject();
                    $this->process($childNode, $childProxy);
                    $nodeTypes[$childProxy->getName()][] = $childProxy;
                }
            }

            foreach ($nodeTypes as $tagName => $nodes) {
                $proxy->set($tagName, $nodes);
            }
        }
    }
}
