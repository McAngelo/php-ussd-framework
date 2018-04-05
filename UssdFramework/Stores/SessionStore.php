<?php

/*
 *  (c) 2016. SMSGH
 */

 namespace UssdFramework\Stores;
 
/**
 * Common interface of classes for tracking ussd sessions. 
 * Stores key-value pairs and hashes/hashtables so that the 
 * values in the hashes can be retrieved directly without having
 * to first deserialize the hash.
 * 
 * @author Aaron Baffour-Awuah
 */
interface SessionStore {
    
    //
    function open($request);
    
    /**
     * Performs any necessary clean-up of resources.
     * @return string client state
     */
    function close();
    
    // Hash store
    
    /**
     * Gets the value of a hash.
     * 
     * @param string $name name of hash.
     * @param string $key key whose value is to retrieved.
     * 
     * @return string value for given key, or null if the hash does not exist in
     *                store, or if key does not exist in hash.
     */
    function getHashValue($name, $key);
    
    /**
     * Sets the value of a hash. If hash does not exist in store, 
     * it is created anew. Else if key does not exist in hash, a new 
     * entry is created.
     * 
     * @param string $name name of hash. Will be created if it does not exist 
     *                     in store.
     * @param string $key key in hash. Will be created if it does not exist 
     *                    in hash.
     * @param string $value new value for key in hash.
     */
    function setHashValue($name, $key, $value);
    
    /**
     * Checks whether a hash exists in store.
     * @param string name name of hash
     * @return bool true if and only if hash exists in store.
     */
    function hashExists($name);
    
    /**
     * Checks whether a key exists in hash.
     * @param string $name name of hash.
     * @param string $key key whose existence in hash is to be checked.
     * @return bool true if key exists in hash, or false if hash does not 
     *              exist in store, or if key does not exist in hash.
     */
    function hashValueExists($name, $key);
    
    /**
     * Deletes a hash and all of its contents from store.
     * @param string $name name of hash.
     */
    function deleteHash($name);
    
    /**
     * Deletes a hash entry.
     * @param string $name name of hash whose entry is to be deleted.
     * @param string $key key of hash entry to be deleted.
     */
    function deleteHashValue($name, $key);

    // Key-Value store
    
    /**
     * Changes the value of a key-value pair. If key does not exists,
     * creates a new key-value pair.
     * @param string $key key to be newly associated with value, or whose 
     *                    value is to be changed.
     * @param string $value new value for key.
     */
    function setValue($key, $value);
    
    /**
     * Gets the value associated with a given key.
     * @param string $key
     * @return string value for given key.
     */
    function getValue($key);
    
    /**
     * Checks if a key is associated with some value.
     * @param string $key
     * @return bool true if and only if key is associated with some value.
     */
    function valueExists($key);
    
    /**
     * Deletes a key-value pair.
     * @param string $key key of key-value pair to be deleted.
     */
    function deleteValue($key);
}
