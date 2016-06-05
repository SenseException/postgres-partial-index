<?php

namespace SenseException\PartialIndex;

use XMLReader;

class XmlConfigReader
{
    /**
     * @param XMLReader $xmlReader
     *
     * @return array
     */
    public function readConfig(XMLReader $xmlReader)
    {
        $config = [];
        while ($xmlReader->read()) {
            switch (true) {
                case XMLReader::ELEMENT === $xmlReader->nodeType:
                    $key = $xmlReader->localName;
                    break;
                case XMLReader::TEXT === $xmlReader->nodeType:
                    $config[$key] = $xmlReader->value;
                    break;
            }
        }

        return $config;
    }
}