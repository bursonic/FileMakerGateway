<?php

class XmlCallWrapper {
    const MAIN_CONTEXT  = '/DATA2SC';
    const WO_CONTEXT    = '/DATA2SC/CALL';
    const NOTE_CONTEXT  = '/DATA2SC/CALL/ATTR';
    const CHECK_CONTEXT = '/DATA2SC/CALL/CHECK';

    private $_xmlDocument;
    private $_xpath;

    function __construct($xml)
    {
        $this->_xmlDocument = new DOMDocument();
        $xml = iconv("UTF-8", "UTF-16", $xml); //hack for SC serializer
        $this->_xmlDocument->loadXML($xml);
        $this->_xpath = new DOMXPath($this->_xmlDocument);
    }

    private function getContextObject($contextPath)
    {
        $resultValue = null;

        $nodeList = $this->_xpath->query($contextPath);
        if( $nodeList->length > 0 ){
            $contextNode = $nodeList->item(0);
            $resultValue = new ContextedXpath($this->_xpath, $contextNode);
        }

        return $resultValue;
    }

    public function getMainContext()
    {
        return $this->getContextObject(self::MAIN_CONTEXT);
    }

    public function getWorkOrderContext()
    {
        return $this->getContextObject(self::WO_CONTEXT);
    }

    public function getNoteContext()
    {
        return $this->getContextObject(self::NOTE_CONTEXT);
    }

    public function getCheckContext()
    {
        return $this->getContextObject(self::CHECK_CONTEXT);
    }
}