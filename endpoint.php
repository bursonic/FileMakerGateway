<?php
error_reporting(E_ALL);

include 'FileMaker.php';
require_once 'classes/Controller.php';
require_once 'classes/mapping/JsonMappingFile.php';
require_once 'classes/filemaker/FileMakerEntity.php';
require_once 'classes/filemaker/FileMakerCallbackInvoker.php';
require_once 'classes/filemaker/FilemakerValidationRule.php';
require_once 'classes/xml/XmlCallWrapper.php';
require_once 'classes/xml/ContextedXpath.php';
require_once 'classes/xml/ServiceChannelApiEntity.php';
require_once 'classes/xml/PinnedServiceChannelApiEntity.php';
require_once 'classes/xml/UnpinnedServiceChannelApiEntity.php';
require_once 'classes/utils/Logger.php';
require_once 'classes/exception/NotFoundException.php';
require_once 'classes/exception/NotConfiguredException.php';
require_once 'classes/DataSynchronizer.php';

$isDebug =  isset($_GET['debug']);

$controller = new Controller(require(__DIR__ . DIRECTORY_SEPARATOR . 'config.php'));
try{
    if( $isDebug && isset($postData) ){
        $controller->setDebugXml($postData);
    } else {
        $controller->receivePostRequest();
    }

    if( $controller->isAuthorized() ){
        $controller->synchronize();
    } else {
        $controller->returnAccessDenied();
    }

} catch (Exception $e) {
    $controller->returnError("Internal error" . ($isDebug ? '; ' . $e->getMessage() : ''), $e);
}
