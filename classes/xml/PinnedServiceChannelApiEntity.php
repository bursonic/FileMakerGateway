<?php

class PinnedServiceChannelApiEntity extends ServiceChannelApiEntity {

    const PIN_XPATH = './@PIN';
    const ID_XPATH = './@ID';
    const TYPE_XPATH = './@TYPE';

    public function getPin()
    {
        return $this->getContext()->getValue(self::PIN_XPATH);
    }

    public function getId()
    {
        return $this->getContext()->getValue(self::ID_XPATH);
    }

    public function getType()
    {
        return $this->getContext()->getValue(self::TYPE_XPATH);
    }
}