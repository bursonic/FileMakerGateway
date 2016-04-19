<?php

abstract class ServiceChannelApiEntity {
    private $_mapping;
    private $_context;

    function __construct($mappingObject, ContextedXpath $context)
    {
        $this->_mapping = $mappingObject;
        $this->_context = $context;
    }

    public function getField($name){
        if( !isset($this->_mapping->fields->$name->xpath) ){
            throw new Exception( "Field '{$name}' not mapped" );
        }

        return $this->_context->getValue($this->_mapping->fields->$name->xpath);
    }

    final protected function getContext(){
        return $this->_context;
    }
}
