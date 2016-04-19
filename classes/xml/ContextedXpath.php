<?php

class ContextedXpath {
    /** @var DOMNode|null  */
    private $_context = null;
    private $_xpath;

    function __construct(DOMXPath $xpath, DOMNode $context)
    {
        $this->_xpath = $xpath;
        $this->_context = $context;
    }

    /**
     * @param $xpath
     * @return DOMNode|null
     */
    private function _getNodeByXpath($xpath)
    {
        $resultValue = null;

        $nodeList = $this->_xpath->query($xpath, $this->_context);
        if( $nodeList->length > 0 ){
            $resultValue = $nodeList->item(0);
        }

        return $resultValue;
    }

    public function getValue($xpath)
    {
        $resultValue = null;

        $node = $this->_getNodeByXpath($xpath);
        if( !is_null($node) ){
            $resultValue = $node->nodeValue;
        }

        return $resultValue;
    }
}