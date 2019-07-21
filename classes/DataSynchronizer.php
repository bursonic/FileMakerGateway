<?php

class DataSynchronizer {

    const WO_TYPE_NEW      = 'WONEW';
    const WO_TYPE_UPDATE   = 'WOUPDATE';
    const WO_TRACKING_FIELD = 'trNum';

    /** @var FileMaker */
    private $fileMaker;
    /** @var XmlCallWrapper */
    private $callWrapper;
    /** @var Logger */
    private $logger;
    /** @var FileMakerCallbackInvoker */
    private $callbackInvoker;

    function __construct(FileMaker $fileMaker, XmlCallWrapper $callWrapper, FileMakerCallbackInvoker $callbackInvoker)
    {
        $this->fileMaker = $fileMaker;
        $this->callWrapper = $callWrapper;
        $this->callbackInvoker = $callbackInvoker;
    }

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function syncWorkOrder(JsonMappingFile $mappingFile)
    {
        $context = $this->callWrapper->getWorkOrderContext();

        if( is_null($context) )
        {
            throw new NotFoundException("workorder context not found");
        }

        $this->log("workorder context found");

        $mapping = $mappingFile->getMappingObject();

        $fmEntity = new FileMakerEntity($mapping, $this->fileMaker);
        $apiEntity = new PinnedServiceChannelApiEntity($mapping, $context);

        $callback = 'onCreateWorkOrder';
        $callbackParams = [];

        $trackingNum = $apiEntity->getField(self::WO_TRACKING_FIELD);

        try {
            $fmEntity->openRecordBy(self::WO_TRACKING_FIELD, $trackingNum);
            $this->log("updating workorder " . $trackingNum);
            $callback = 'onUpdateWorkOrder';
            $callbackParams[] = $trackingNum;
        }
        catch (NotFoundException $exception) {
            $this->log('workorder not found for update');
        }

        if( !$fmEntity->isRecordActive() ){
            $this->log("creating workorder");
            $fmEntity->openNewRecord();
        }

        $this->log("workorder open");

        if( $this->_syncEntities($mapping, $apiEntity, $fmEntity)){
            $this->log('invoking ' . $callback . ' callback');
            $this->callbackInvoker->invokeCallback($callback, $callbackParams);
        }
    }

    private function log($message)
    {
        if( !empty($this->logger) ){
            $this->logger->log($message);
        }
    }

    function syncNote(JsonMappingFile $mappingFile)
    {
        $context = $this->callWrapper->getNoteContext();

        if( !is_null($context) ){
            $this->log("note context found");
            $mapping = $mappingFile->getMappingObject();
            $fmEntity = new FileMakerEntity($mapping, $this->fileMaker);
            $apiEntity = new UnpinnedServiceChannelApiEntity($mapping, $context);

            $this->log("creating note record");
            $fmEntity->openNewRecord();

            if ( $this->_syncEntities($mapping, $apiEntity, $fmEntity) ){
                if( $this->_syncEntities($mapping, $apiEntity, $fmEntity)){
                    $callback = 'onCreateNote';
                    $this->log('invoking ' . $callback . ' callback');
                    $this->callbackInvoker->invokeCallback('onCreateNote');
                }
            }
        } else {
            $this->log("note context not found");
        }
    }

    function syncCheck(JsonMappingFile $mappingFile)
    {
        $context = $this->callWrapper->getCheckContext();

        if( !is_null($context) ){
            $this->log("check context found");

            $mapping = $mappingFile->getMappingObject();
            $fmEntity = new FileMakerEntity($mapping, $this->fileMaker);
            $apiEntity = new PinnedServiceChannelApiEntity($mapping, $context);

            $this->log("creating check record");
            $fmEntity->openNewRecord();

            if( $this->_syncEntities($mapping, $apiEntity, $fmEntity) ){
                $callback = ( $apiEntity->getType() == 'IN' ) ? 'onCheckIn' : 'onCheckOut';
                $this->log('invoking ' . $callback . ' callback');
                $this->callbackInvoker->invokeCallback($callback);
            }
        } else {
            $this->log("check context not found");
        }
    }

    private function _syncEntities($mapping, ServiceChannelApiEntity $apiEntity, FileMakerEntity $fmEntity)
    {
        $success = false;

        foreach( $mapping->fields as $field => $fieldMapping ){
            $value = $apiEntity->getField($field);

            if( !empty($value) ){
                $value = $this->_validateByDatatype($value, $fieldMapping->datatype);
                $fmEntity->setFieldValue($field, $value);
            } else {
                $this->log($field . " is empty. skipped");
            }
        }

        if( $fmEntity->isRecordValid() ){
            $this->log("record is valid");
            try{
                $fmEntity->commit();
                $success = true;
                $this->log("commit successful");
            } catch (Exception $e){
                $this->log("commit failed: " . $e->getMessage() . "; code: " . $e->getCode());
            }

        } else {
            $this->log("record is invalid");
        }

        return $success;
    }

    private function _validateByDatatype( $value, $datatype )
    {
        $result = null;

        switch( $datatype ){
            case 'datetime' : {
                //     date/time:  1-y      2-m      3-d      4-h     5-m     6-s
                if (preg_match("/(\d{4})\/(\d{2})\/(\d{2})\s(\d{2})\:(\d{2})\:(\d{2})/i", $value, $match) )
                {
                    $time = mktime($match[4], $match[5], $match[6], $match[3], $match[2], $match[1]);
                    $result = date('d/m/Y h:i:s A', $time);
                }
                break;
            }
            case 'int': {
                $result = intval($value);
                break;
            }
            case 'text':
            default: {
                $result = $value;
            }
        }

        return $result;
    }

}