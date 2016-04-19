<?php

class FileMakerCallbackInvoker {
    private $mapping;
    private $fileMaker;

    function __construct(JsonMappingFile $callbacksMappingFile, FileMaker $fileMaker)
    {
        $this->mapping = $callbacksMappingFile->getMappingObject();
        $this->fileMaker = $fileMaker;
    }

    public function invokeCallback( $callbackName, $params = [] )
    {
        if( $this->callbackExists($callbackName) ){
            $layout = $this->mapping->$callbackName->layout;
            $script = $this->mapping->$callbackName->script;

            if( !empty( $layout ) && !empty( $script ) ){
                $command = $this->fileMaker->newPerformScriptCommand($layout, $script, $params);
                $command->execute();
            }
        }

    }

    private function callbackExists($callbackName)
    {
        return isset( $this->mapping->$callbackName );
    }
}