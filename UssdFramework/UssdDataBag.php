<?php

/*
 *  (c) 2016. SMSGH
 */

namespace UssdFramework;

/**
 * Map-like class for use inside ussd controllers for persisting data
 * across ussd requests, but within the same ussd session.
 * 
 * @author Aaron Baffour-Awuah
 */
class UssdDataBag 
{
    /**
     * @var string
     */
    private $_dataBagKey;
    
    /**
     * @var object Should be instance of Stores\SessionStore class.
     */
    private $_store;

    /**
     * Creates new SMSGH_USSD_UssdDataBag instance.
     * 
     * @param object SMSGH_USSD_Stores_SessionStore instance backing
     *               the UssdDataBag contents.
     * @param string dataBagKey the key under which the UssdDataBag is 
     *               stored in the backing store.
     */
    function __construct($store, $dataBagKey) 
    {
        $this->_store = $store;
        $this->_dataBagKey = $dataBagKey;
    }

    /**
     * Gets the value associated with a key.
     * 
     * @param string key
     * 
     * @return string value of existing key-value pair, or null if key does 
     *         not exist.
     */
    function get($key) 
    {
        return $this->_store->getHashValue($this->_dataBagKey, $key);
    }

    /**
     * Determines whether a key is associated with any value in the
     * UssdDataBag instance.
     * 
     * @param string key 
     * 
     * @return bool true if and only if key exists as part of 
     *              some key-value pair.
     */
    function exists($key) 
    {
        return $this->_store->hashValueExists($this->_dataBagKey, $key);
    }

    /**
     * Sets or changes the value of a key-value pair in the
     * UssdDataBag instance.
     * 
     * @param string key the key to be associated with a new value, or the 
     *               key of the key-value pair whose value is to be changed.
     * 
     * @param string value the new value to be associated with the key.
     */
    function set($key, $value) 
    {
        $this->_store->setHashValue($this->_dataBagKey, $key, $value);
    }

    /**
     * Deletes a key-value pair in the UssdDataBag instance.
     * 
     * @param string key the key of the pair to delete.
     */
    function delete($key) 
    {
        $this->_store->deleteHashValue($this->_dataBagKey, $key);
    }

    /**
     * Deletes all the contents of the UssdDataBag instance.
     */
    function clear() 
    {
        $this->_store->deleteHash($this->_dataBagKey);
    }
}
