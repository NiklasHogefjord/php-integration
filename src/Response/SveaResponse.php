<?php
require_once SVEA_REQUEST_DIR . '/Includes.php';

/**
 * SveaResponse creates a uniform response object from a call to Svea services.
 * 
 * For asynchronous services, create an instance of SveaResponse, pass it the 
 * resulting xml response as part of the $_REQUEST response along with 
 * countryCode and config, then receive your HostedResponse instance by calling 
 * the getResponse() method. 
 * 
 * For synchronous services, the appropriate WebServiceResponse instance is 
 * returned by calling ->doRequest() on the order object.
 * 
 * @author Anneli Halld'n, Daniel Brolund, Kristian Grossman-Madsen for Svea WebPay
 */
class SveaResponse {

    /**
     * @deprecated, use SveaResponse->getResponse() to access the $response object directly
     * @var public $response, instance of HostedResponse or WebServiceResponse
     */
    public $response;


    /**
     * Returns an instance of the response class corresponding to the request 
     * sent to Svea, i.e. instances of subclasses to HostedResponse and 
     * WebServiceResponse, respectively.
     *
     * SveaResponse checks the parameter $message to see if the service response
     * has come in as a SimpleXMLElement object or as a raw xml string. Then it
     * creates the appropriate Response object which parses the response and does
     * error handling et al.
     * 
     * The resulting parsed response attributes are available for inspection 
     * through the getResponse() method. Inspect the individual response using
     * i.e. $myInstanceOfSveaResponse->getResponse()->serviceResponseAttribute
     * 
     * @param SimpleXMLElement|string  $message contains the Svea service response, either as an object or as raw xml (for hosted payments)
     * @param string $countryCode
     * @param SveaConfigurationProvider $config
     * @return mixed instance of a subclass to HostedResponse or WebServiceResponse, respectively
     */
    public function __construct($message, $countryCode, $config = NULL) {
         
        if (is_object($message)) {
            
            if (property_exists($message, "CreateOrderEuResult")) {
                $this->response = new Svea\CreateOrderResponse($message);
            } 
            elseif (property_exists($message, "GetAddressesResult")) {
                $this->response = new Svea\GetAddressesResponse($message);
            } 
            elseif (property_exists($message, "GetPaymentPlanParamsEuResult")) {
                $this->response = new Svea\PaymentPlanParamsResponse($message);
            } 
            elseif (property_exists($message, "DeliverOrderEuResult")) {
                $this->response = new Svea\DeliverOrderResult($message);
            } 
            elseif (property_exists($message, "CloseOrderEuResult")) {
                $this->response = new Svea\CloseOrderResult($message);
            }
            //webservice from hosted_admin
            elseif (property_exists($message, "message"))   {
                 $this->response = new Svea\HostedAdminResponse($message,$countryCode,$config);
            }
        } 
        elseif ($message != NULL) {
            $this->response = new Svea\HostedPaymentResponse($message,$countryCode,$config);
        } 
        else {
            $this->response = "Response is not recognized.";
        }
    }
    
    /**
     * @returns an instance of the corresponding Response class (see constructor above) contained in the ->response attribute
     */
    public function getResponse() {
        return $this->response;
    }
}
