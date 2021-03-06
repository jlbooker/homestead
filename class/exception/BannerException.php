<?php

PHPWS_Core::initModClass('hms', 'exception/HMSException.php');

class BannerException extends HMSException {

    public function __construct($message, $code = 0, $functionName, $params){
        parent::__construct($message, $code);
        $errorMsg = $functionName . '(' . implode(',', $params) . '): ' . $code;
        PHPWS_Core::log($errorMsg, 'soapError.log', ('BannerError'));
    }
}

