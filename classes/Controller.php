<?php

class Controller
{
    const POST_XML_FIELD = 'msg';

    /** @var array  */
    private $settings;
    /** @var FileMaker  */
    private $fileMaker;
    /** @var Logger  */
    private $logger;
    /** @var  XmlCallWrapper */
    private $xmlWrapper;

    function __construct(array $settings)
    {
        $this->settings = $settings;

        $this->fileMaker = new FileMaker(
            $this->settings['db']['database'],
            $this->settings['db']['host'],
            $this->settings['db']['user'],
            $this->settings['db']['pass']
        );

        $this->logger = new Logger($this->settings['log']['path']);
    }

    public function receivePostRequest()
    {
        if( !isset( $_POST[self::POST_XML_FIELD] ) || empty($_POST[self::POST_XML_FIELD]) ){
            throw new Exception('Post data not found!');
        }

        $postdata = urldecode($_POST[self::POST_XML_FIELD]);
        $this->logger->log('xml: ' . $postdata);

        $this->xmlWrapper = new XmlCallWrapper($postdata);
    }

    public function setDebugXml( $xml )
    {
        $this->xmlWrapper = new XmlCallWrapper($xml);
    }

    public function isAuthorized()
    {
        $settingsMappingFile = new JsonMappingFile($this->settings['mapping']['preferences']);
        $settingsEntity = new FileMakerEntity($settingsMappingFile->getMappingObject(), $this->fileMaker);

        $coreMappingFile = new JsonMappingFile($this->settings['mapping']['call']);

        $settingsEntity->openFirstRecord();
        $pin = $settingsEntity->getFieldValue('pin');

        $callEntity = new PinnedServiceChannelApiEntity($coreMappingFile->getMappingObject(), $this->xmlWrapper->getMainContext());
        $receivedPin = $callEntity->getPin();

        $isPinAuthorized = ( $pin == $receivedPin );

        return $isPinAuthorized;
    }

    public function synchronize()
    {
        $synchronizer = new DataSynchronizer(
            $this->fileMaker,
            $this->xmlWrapper,
            new FileMakerCallbackInvoker(new JsonMappingFile($this->settings['mapping']['callbacks']), $this->fileMaker)
        );
        $synchronizer->setLogger($this->logger);

        try{
            $synchronizer->syncWorkOrder( new JsonMappingFile($this->settings['mapping']['workorder']) );
        } catch (NotFoundException $e){
            $this->returnNotFound('workorder');
        } catch (NotConfiguredException $e){
            $this->logger->log("WARNING workorder layout not configured");
        }

        try{
            $synchronizer->syncCheck( new JsonMappingFile($this->settings['mapping']['checkinout']) );
        } catch (NotConfiguredException $e){
            $this->logger->log("WARNING check in/out layout not configured");
        }

        try{
            $synchronizer->syncNote( new JsonMappingFile($this->settings['mapping']['note']) );
        } catch (NotConfiguredException $e){
            $this->logger->log("WARNING note layout not configured");
        }

    }

    public function returnAccessDenied()
    {
        http_response_code(401);
        $this->logger->log('PIN not authorized');
        exit("PIN not authorized");
    }

    public function returnError($error, Exception $e = null)
    {
        http_response_code(500);

        $errorLog = $error;
        if( !is_null($e) ){
            $errorLog .= '; Details:' . $e->getMessage();
        }

        $this->logger->log($errorLog);
        exit("Error occured: " . $error);
    }

    public function returnNotFound($entityName)
    {
        http_response_code(404);
        $this->logger->log($entityName . ' not found');
        exit("Not found: " . $entityName);
    }
}