<?php

/*
 *  (c) 2016. SMSGH
 */

namespace UssdFramework\Stores;

/**
 * A thread-safe in-memory session store that expires its entries after a specified
 * time period. Designed to be used as a singleton per Ussd application.
 * 
 * @author Aaron Baffour-Awuah
 */
class DatabaseSessionStore implements SessionStore {
    
    /**
     *
     * @var string
     */
    protected $dsn;
    
    /**
     *
     * @var string
     */
    protected $username;
    
    /**
     *
     * @var string
     */
    protected $password;
    
    /**
     *
     * @var string
     */
    protected $tableName;
    
    /**
     *
     * @var \PDO
     */
    protected $db;
    
    /**
     *
     * @var \UssdFramework\UssdRequest
     */
    protected $request;
    
    /**
     * @var array associative array keyed by strings. Values are either
     *            strings, or associative arrays of string to string
     *            mappings.
     */
    private $_backingStore;

    /**
     * Creates a new session store.
     */
    function __construct($dsn, $username, $password, $tableName = 'ussd_sessions') {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->tableName = $tableName;
        $this->_backingStore = array();        
    }

    /**
     * @param ..\UssdRequest
     */
    function open($request) {
        $this->request = $request;
        
        $this->db = new \PDO($this->dsn, $this->username, $this->password);
        $clientState = $this->loadFromDb();
        if ($clientState) {
            $deserialized = json_decode($clientState, true);
            if ($deserialized) {
                $this->_backingStore = $deserialized;
            }
        }
    }
    
    function loadFromDb() {
        $sql = $this->createSelectStatement($this->request->getSessionId());
        $stmt = $this->db->query($sql);
        if ( ! $stmt) {
            self::throwDbError($this->db->errorInfo());
        }
        $clientState = null;
        $row = $stmt->fetch();
        if ($row) {
            $clientState = $row[0];
        }
        $stmt->closeCursor();
        return $clientState;
    }
    
    static function throwDbError($errorInfo) {        
        throw new \UssdFramework\FrameworkException(sprintf(
                'SQLSTATE[%s] %s %s', $errorInfo[0],
                $errorInfo[1], $errorInfo[2]));
    }
    
    function createSelectStatement($sessionId) {
        return sprintf("SELECT client_state FROM %s WHERE session_id = %s " .
                " ORDER BY sequence DESC", $this->tableName, 
                $this->db->quote($sessionId));
    }

    /**
     * Saves session data.
     * @return string client state.
     */
    function close() {
        $serialized = null;
        if ($this->_backingStore) {
            $serialized = json_encode($this->_backingStore);
        }
        $this->saveToDb($serialized);
        return null;
    }
    
    function saveToDb($clientState) {
        if ($clientState) {
            $insertSql = $this->createInsertStatement(
                    $this->request->getSessionId(),
                    $this->request->getSequence(),
                    $clientState);
            $ret = $this->db->exec($insertSql);
            if ($ret === false) {
                self::throwDbError($this->db->errorInfo());
            }
        }
        else {
            $deleteSql = $this->createDeleteStatement(
                    $this->request->getSessionId());
            $ret = $this->db->exec($deleteSql);
            if ($ret === false) {
                self::throwDbError($this->db->errorInfo());           
            }
        }
    }
    
    function createInsertStatement($sessionId, $sequence, $clientState) {
        return sprintf("INSERT INTO %s (session_id, sequence, client_state) " .
                " VALUES (%s, %s, %s)", $this->tableName, 
                $this->db->quote($sessionId),
                $this->db->quote($sequence),
                $this->db->quote($clientState));
    }
    
    function createDeleteStatement($sessionId) {
        return sprintf("DELETE FROM %s WHERE session_id = %s",
                $this->tableName, $this->db->quote($sessionId));
    }
    
    // Hash store implementation.
    
    /**
     *{@inheritDoc} 
     */
    function getHashValue($name, $key) {
        if (array_key_exists($name, $this->_backingStore)) {
            $hash = $this->_backingStore[$name];
            if (array_key_exists($key, $hash)) {
                return $hash[$key];
            }
        }
        return null;
    }

    /**
     *{@inheritDoc} 
     */
    function setHashValue($name, $key, $value) {
        if (array_key_exists($name, $this->_backingStore)) {
            $hash =& $this->_backingStore[$name];
            $hash[$key] = $value;
        }
        else {
            $hash = array($key => $value);
            $this->_backingStore[$name] = $hash;
        }
    }

    /**
     *{@inheritDoc} 
     */
    function hashExists($name) {
        return array_key_exists($name, $this->_backingStore);
    }

    /**
     *{@inheritDoc} 
     */
    function hashValueExists($name, $key) {
        if (array_key_exists($name, $this->_backingStore)) {
            $hash = $this->_backingStore[$name];
            return array_key_exists($key, $hash);
        }
        return false;
    }

    /**
     *{@inheritDoc} 
     */
    function deleteHash($name) {
        unset($this->_backingStore[$name]);
    }

    /**
     *{@inheritDoc} 
     */
    function deleteHashValue($name, $key) {
        if (array_key_exists($name, $this->_backingStore)) {
            $hash = $this->_backingStore[$name];
            unset($hash[$key]);
        }
    }
    
    // Key-Value store implementation.

    /**
     *{@inheritDoc} 
     */
    function setValue($key, $value) {
        $this->_backingStore[$key] = $value;
    }

    /**
     *{@inheritDoc} 
     */
    function getValue($key) {
        if (array_key_exists($key, $this->_backingStore)) {
            return $this->_backingStore[$key];
        }
        return null;
    }

    /**
     *{@inheritDoc} 
     */
    function valueExists($key) {
        return array_key_exists($key, $this->_backingStore);
    }

    /**
     *{@inheritDoc} 
     */
    function deleteValue($key) {
        unset($this->_backingStore[$key]);
    }
}
