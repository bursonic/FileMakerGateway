<?php

/**
 * Created by PhpStorm.
 * User: solbeguser653
 * Date: 4/19/2016
 * Time: 4:03 PM
 */
class FilemakerValidationRule
{
    private $ruleNumber = 0;

    private $ruleMapping = [
        FILEMAKER_RULE_NOTEMPTY => 'should not be empty',
        FILEMAKER_RULE_NUMERICONLY => 'should be numeric',
        FILEMAKER_RULE_MAXCHARACTERS => 'should is too long',
        FILEMAKER_RULE_TIME_FIELD => 'should be time format',
        FILEMAKER_RULE_TIMESTAMP_FIELD => 'should be timestamp format',
        FILEMAKER_RULE_DATE_FIELD => 'should be date format',
        FILEMAKER_RULE_FOURDIGITYEAR => 'year should be four digit',
        FILEMAKER_RULE_TIMEOFDAY => 'time of day is invalid',
    ];

    public function __construct($ruleNumber)
    {
        $this->ruleNumber = intval($ruleNumber);
    }

    public function getRule()
    {
        if( !array_key_exists($this->ruleNumber, $this->ruleMapping) )
        {
            return "rule not recognised";
        }

        return $this->ruleMapping[ $this->ruleNumber ];
    }
}