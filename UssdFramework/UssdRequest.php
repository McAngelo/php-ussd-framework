<?php

/*
 * (c) 2016. SMSGH
 */

namespace UssdFramework;

/**
 * Represents ussd request received from SMSGH.
 * 
 * @author Aaron Baffour-Awuah
 */
class UssdRequest {
    
    /**
     * @var string
     */
    const REQUEST_TYPE_INITIATION = "Initiation";
    
    /**
     * @var string
     */
    const REQUEST_TYPE_RESPONSE = "Response";
    
    /**
     * @var string
     */
    const REQUEST_TYPE_RELEASE = "Release";
    
    /**
     * @var string
     */
    const REQUEST_TYPE_TIMEOUT = "Timeout";
    
    /**
     * @var string
     */
    private $_mobile;
    
    /**
     * @var string
     */
    private $_sessionId;
    
    /**
     * @var string
     */
    private $_serviceCode;
    
    /**
     * @var string
     */
    private $_type;
    
    /**
     * @var string
     */
    private $_message;
    
    /**
     * @var string
     */
    private $_operator;
    
    /**
     * @var int
     */
    private $_sequence;
    
    /**
     * @var string
     */
    private $_clientState;
    
    /**
     * @var bool
     */
    private $_autoDialOriginated;
    
    /**
     * @var int
     */
    private $_autoDialIndex;
    
    /**
     * Creates new SMSGH_USSD_UssdRequest instance.
     */
    function __construct() {
        $this->_sequence = 0;
        $this->_autoDialOriginated = false;
        $this->_autoDialIndex = 0;
    }
    
    /**
     * @param string $json
     * 
     * @return UssdRequest
     */
    static function fromJson($json) {
        $arr = json_decode($json, true);
        if ($arr === null) {
            throw new FrameworkException('JSON is invalid: ' .
                var_export($json, true));
        }
        if ( ! array_key_exists('Mobile', $arr)) {
            throw new FrameworkException('"Mobile" field not found.');
        }
        if ( ! array_key_exists('SessionId', $arr)) {
            throw new FrameworkException('"SessionId" field not found.');
        }
        if ( ! array_key_exists('ServiceCode', $arr)) {
            throw new FrameworkException('"ServiceCode" field not found.');
        }
        if ( ! array_key_exists('Type', $arr)) {
            throw new FrameworkException('"Type" field not found.');
        }
        if ( ! array_key_exists('Message', $arr)) {
            throw new FrameworkException('"Message" field not found.');
        }
        if ( ! array_key_exists('Operator', $arr)) {
            throw new FrameworkException('"Operator" field not found.');
        }
        if ( ! array_key_exists('Sequence', $arr)) {
            throw new FrameworkException('"Sequence" field not found.');
        }
        $clientState = null;
        if (array_key_exists('ClientState', $arr)) {            
            $clientState = $arr['ClientState'];
        }
        
        $mobile = $arr['Mobile'];
        $sessionId = $arr['SessionId'];
        $serviceCode = $arr['ServiceCode'];
        $type = $arr['Type'];
        $message = $arr['Message'];
        $operator = $arr['Operator'];
        $sequence = $arr['Sequence'];
        
        $instance = new UssdRequest();
        $instance->setMobile($mobile);
        $instance->setSessionId($sessionId);
        $instance->setServiceCode($serviceCode);
        $instance->setType($type);
        $instance->setMessage($message);
        $instance->setOperator($operator);
        $instance->setSequence($sequence);
        $instance->setClientState($clientState);
        
        return $instance;
    }
    
    /**
     * Tells whether or not the ussd request was manufactured from
     * a ussd initiation message during auto dial processing.
     * 
     * @return bool true if ussd request originated from auto dial processing 
     *              rather than from SMSGH; false if it came directly from 
     *              SMSGH.
     */
    function isAutoDialOriginated() {
        return $this->_autoDialOriginated;
    }

    /**
     * @param bool $autoDialOriginated
     */
    function setAutoDialOriginated($autoDialOriginated) {
       $this->_autoDialOriginated = $autoDialOriginated;
    }

    /**
     * Gets the index 
     * 
     * @return int
     */
    function getAutoDialIndex() {
        return $this->_autoDialIndex;
    }

    /**
     * @param int $autoDialIndex
     */
    function setAutoDialIndex($autoDialIndex) {
        $this->_autoDialIndex = $autoDialIndex;
    }

    /**
     * Gets the phone number of the ussd app user.
     * 
     * @return string phone number of ussd app user in international format.
     */
    function getMobile() {
        return $this->_mobile;
    }

    /**
     * @param string $mobile
     */
    function setMobile($mobile) {
        $this->_mobile = $mobile;
    }

    /**
     * Gets the unique session id SMSGH associates with each ussd session.
     * <p>
     * Note that session id is 32 characters long.
     * 
     * @return string ussd session id.
     */
    function getSessionId() {
        return $this->_sessionId;
    }

    /**
     * @param string $sessionId
     */
    function setSessionId($sessionId) {
        $this->_sessionId = $sessionId;
    }

    /**
     * Gets the purchased ussd code associated with ussd app. E.g.
     * 714, 714*2, 713*2*10000000
     * 
     * @return string purchased ussd code through which ussd request arrived.
     */
    function getServiceCode() {
        return $this->_serviceCode;
    }

    /**
     * @param string $serviceCode
     */
    function setServiceCode($serviceCode) {
        $this->_serviceCode = $serviceCode;
    }

    /**
     * Gets the ussd request type, which is one of the REQUEST_TYPE_*
     * constants of this class.
     * 
     * @return string ussd request type.
     */
    function getType() {
        return $this->_type;
    }

    /**
     * @param string $type
     */
    function setType($type) {
        $this->_type = $type;
    }

    /**
     * Gets the ussd request message.
     * 
     * @return string ussd request message.
     */
    function getMessage() {
        return $this->_message;
    }

    /**
     * @param string $message
     */
    function setMessage($message) {
        $this->_message = $message;
    }

    /**
     * Gets the telecommunications network that the ussd request came from.
     * E.g. vodafone, mtn, tigo, airtel, glo.
     * 
     * @return string
     */
    function getOperator() {
        return $this->_operator;
    }

    /**
     * @param string $operator
     */
    function setOperator($operator)
    {
        $this->_operator = $operator;
    }

    /**
     * Gets the sequence of the ussd request in its session. The 
     * sequence of the first request is 1.
     * 
     * @return int sequence of ussd request in ussd session.
     */
    function getSequence() {
        return $this->_sequence;
    }

    /**
     * @param int $sequence
     */
    function setSequence($sequence) {
        $this->_sequence = $sequence;
    }

    /**
     * Used internally by framework. See SMSGH USSD documentation 
     * for details.
     * 
     * @return string
     */
    function getClientState() {
        return $this->_clientState;
    }

    /**
     * @param String $clientState
     */
    function setClientState($clientState) {
        $this->_clientState = $clientState;
    }
    
    /**
     * @return string
     */
    function getTrimmedMessage() {
        if ($this->_message === null) {
            return null;
        }
        return trim($this->_message);
    }

    /**
     * @return string 
     */
    function __toString() {
        return "UssdRequest{" . "mobile=" . $this->_mobile . 
                ", sessionId=" . $this->_sessionId . 
                ", serviceCode=" . $this->_serviceCode . 
                ", type=" . $this->_type . ", message=" . $this->_message . 
                ", operator=" . $this->_operator . 
                ", sequence=" . $this->_sequence . 
                ", clientState=" . $this->_clientState . 
                ", autoDialOriginated=" . $this->_autoDialOriginated .
                ", autoDialIndex=" . $this->_autoDialIndex . '}';
    }
}
