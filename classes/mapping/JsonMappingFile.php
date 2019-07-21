<?php

class JsonMappingFile {
    private $_rawData;
    private $_parsedData;

    function __construct( $filename )
    {
        if( !file_exists($filename) ){
            throw new Exception('File not found!');
        }
        $this->_rawData = file_get_contents($filename);
        $this->_parsedData = null;
    }

    private function parse()
    {
        $this->_parsedData = json_decode($this->_rawData);
    }

    public function getMappingObject()
    {
        if( empty($this->_parsedData) ){
            $this->parse();
        }
        return $this->_parsedData;
    }
}
