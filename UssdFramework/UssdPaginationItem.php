<?php

/*
 *  (c) 2018. MJ-Consult
 */

namespace UssdFramework;

/**
 * Represents ussd responses to be sent to SMSGH.
 * 
 * @author Michael Kwame Johnson
 */
class UssdPaginationItem {
    
    /**
     * @var string
     */
    private $_status;
    
    /**
     * @var integer
     * @access private
     */
    private $_totalPages;
    
    /**
     * @var array object
     */
    private $_pages;

    /**
     * @var string
     */
    private $_heading;
    
    /**
     * @var \Exception
     */
    private $_exception;
    
    /**
     * Creates new UssdResponse instance.
     */
    function __construct($pages) {
        $this->_pages = $pages;
    }

}
