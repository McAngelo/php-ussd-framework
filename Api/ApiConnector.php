<?php

namespace Api;


require 'httpful.phar';
use Httpful\Request as ApiRequest;

class ApiConnector {

	protected $token;
	protected $clientId;
	protected $clientSecret;
	protected $apiConnector;

	function __construct($authKeys=false){

		//$token, $clientId, $clientSecret
		/*$this->_token = $authKeys["token"];
		$this->_clientId = $authKeys["clientId"];
		$this->_clientSecret = $authKeys["clientSecret"];*/
		$url = "http://localhost:8080/api/health";
		try{
			$this->apiConnector = ApiRequest::get($url);
		}catch(Exception $ex){
			print_r($ex);
		}
        
	}

	# Get All Request
	public function getRequest($url){
		try{
			$response = $this->apiConnector->get($url)
				//->authenticateWith($this->_clientId, $this->_clientSecret, $this->_token)
		        ->parseWith(function($body) {
		            return explode(",", $body);
		        })
		        ->expectsJson()
				->send();

			$rawResponse = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response);

		    return $rawResponse;
		}
		catch (Exception $ex) {
			print_r($ex . "\n");
		    echo $ex->getTraceAsString();
		}
	}

	# Post Request
	public function postRequest($url, $body){
		try{
		    $response = $this->apiConnector->post($url)
		        ->sendsJson()
		        //->authenticateWith($this->_clientId, $this->_clientSecret, $this->_token)
		        ->body($body)
		        ->parseWith(function($body) {
		            return explode(",", $body);
		        })
		        ->expectsJson()
		        ->send();

		    return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response);

		}catch (Exception $ex) {
			print_r($ex . "\n");
		    return $ex->getTraceAsString();
		}
	}

	# Put Request
	public function putRequest($url, $body){
		try{
		    $response = \Httpful\Request::put($url)
		        ->sendsJson()
		        //->authenticateWith($this->_clientId, $this->_clientSecret, $this->_token)
		        ->body($body)
		        ->parseWith(function($body) {
		            return explode(",", $body);
		        })
		        ->expectsJson()
		        ->send();

		    return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response);
		    
		}catch (Exception $ex) {
			print_r($ex . "\n");
		    return $ex->getTraceAsString();
		}
	}

	# Delete Request
	public function deleteRequest($url, $body){
		try{			 
			$response = \Httpful\Request::get($uri)
			    //->authenticateWithCert($cert, $key, $passphrase)
			    ->send();
		}catch(Exception $ex){
			print_r($ex . "\n");
			return $ex->getTraceAsString();
		}
	}
}