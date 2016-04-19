<?php

class FileMakerEntity {

    const FM_NOT_FOUND_ERROR_CODE = 401;

    private $_fileMaker;
    private $_mapping;
    /** @var FileMaker_Layout */
    private $_layout;

    /** @var  FileMaker_Record */
    private $_currentRecord;

    function __construct($mappingObject, FileMaker $fileMaker)
    {
        $this->_fileMaker = $fileMaker;
        $this->_mapping = $mappingObject;

        if( !isset( $this->_mapping->layout ) || empty($this->_mapping->layout) ){
            throw new NotConfiguredException('Layout not set!');
        }

        $this->_layout = $this->_fileMaker->getLayout($this->_mapping->layout);

        if( $this->_layout instanceof FileMaker_Error ){
            throw new Exception( $this->_layout->getMessage(), $this->_layout->getCode() );
        }
    }

    public function isRecordActive()
    {
        return !empty($this->_currentRecord);
    }

    public function openNewRecord()
    {
        $this->_currentRecord = $this->_fileMaker->createRecord( $this->_mapping->layout );
    }

    public function openFirstRecord()
    {
        $findCommand = $this->_fileMaker->newFindAllCommand($this->_mapping->layout);

        $commandResult = $this->_executeCommand($findCommand);

        $this->_currentRecord = $this->_fetchOneFromResult( $commandResult );
    }

    function openRecordBy($fieldName, $value)
    {
        $findCommand = $this->_fileMaker->newFindCommand($this->_mapping->layout);

        $findCommand->addFindCriterion($this->_mapping->fields->$fieldName->field, $value);

        $commandResult = $this->_executeCommand($findCommand);

        $this->_currentRecord = $this->_fetchOneFromResult($commandResult );
    }

    private function _fetchOneFromResult( FileMaker_Result $findResult = null )
    {
        if( is_null($findResult) || !$findResult->getFetchCount() ){
            throw new NotFoundException("Record not found!");
        }

        return $findResult->getFirstRecord();
    }

    /**
     * @param FileMaker_Command $command
     * @return FileMaker_Result
     * @throws Exception
     */
    private function _executeCommand(FileMaker_Command $command)
    {
        $commandResult = $command->execute();
        $found = true;

        if( $commandResult instanceof FileMaker_Error ){
            if( $commandResult->getCode() != self::FM_NOT_FOUND_ERROR_CODE )
            {
                throw new Exception( $commandResult->getMessage() . '(' . get_class($commandResult) . ', code ' . $commandResult->getCode() . ')', $commandResult->getCode() );
            }

            $found = false;
        }

        return $found ? $commandResult : null;
    }

    function setFieldValue($name, $value)
    {
        $this->_checkCurrentRecordIsActive();

        if( $this->_fieldExists($name) ){
            $this->_currentRecord->setField($this->_mapping->fields->$name->field, $value);
        }
    }

    function getFieldValue($name)
    {
        $resultValue = null;
        $this->_checkCurrentRecordIsActive();

        if( $this->_fieldExists($name) ){
            $resultValue = $this->_currentRecord->getField($this->_mapping->fields->$name->field);
        }

        return $resultValue;
    }

    private function _checkCurrentRecordIsActive()
    {
        if( !$this->isRecordActive() ){
            throw new Exception('No active record!');
        }
    }

    private function _fieldExists($name)
    {
        return isset($this->_mapping->fields->$name->field) && !empty( $this->_mapping->fields->$name->field );
    }

    public function isRecordValid()
    {
        $this->_checkCurrentRecordIsActive();
        $validationResult = $this->_currentRecord->validate();

        if ( $validationResult instanceof FileMaker_Error_Validation)
        {
            $errors = $validationResult->getErrors();

            foreach ($errors as $error) {
                $rule = new FilemakerValidationRule($error[1]);

                $message = 'Field ' . $error[0]->getName()
                    . ' ' . $rule->getRule()
                    . '(' . $error[2] . ')';

                throw new Exception($message);
            }

        }
        return !( $validationResult instanceof FileMaker_Error_Validation );
    }

    function commit()
    {
        $this->_checkCurrentRecordIsActive();
        $commitResult = $this->_currentRecord->commit();

        if( $commitResult instanceof FileMaker_Error ){
            throw new Exception($commitResult->getMessage(), $commitResult->getCode());
        }
    }

    function rollback()
    {
        $this->_checkCurrentRecordIsActive();
        unset($this->_currentRecord);
    }
}