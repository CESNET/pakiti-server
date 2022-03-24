<?php

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class DbManager
{
    private $_dbLink;

    public function __construct()
    {
        $this->dbConnect();
    }

    /**
     * Transaction methods
     */
    public function begin()
    {
        if (!$this->_dbLink->begin_transaction()) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("SQL transaction begin failed: " . $this->_dbLink->error);
        }
        Utils::log(LOG_DEBUG, "Starting transaction", __FILE__, __LINE__);
    }

    public function commit()
    {
        if (!$res = $this->_dbLink->commit()) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("SQL transaction commit failed: " . $this->_dbLink->error);
        }
        Utils::log(LOG_DEBUG, "Commiting transaction", __FILE__, __LINE__);
    }

    public function rollback()
    {
        if (!$res = $this->_dbLink->rollback()) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("SQL transaction rollback failed: " . $this->_dbLink->error);
        }
        Utils::log(LOG_DEBUG, "Transaction rollback!", __FILE__, __LINE__);
    }

    /**
     * Escape SQL parameters
     */
    public function escape($string, $like = false)
    {
        $str = $this->_dbLink->real_escape_string(htmlspecialchars($string));
        if ($like) {
            $str = str_replace("_", "\_", str_replace("%", "\%", $str));
        }
        return $str;
    }

    /**
     * Used only for INSERT, UPDATE and DELETE
     */
    public function query($sql)
    {
        $this->rawQuery($sql);
    }

    /**
     * Returns single value
     */
    public function queryToSingleValue($sql)
    {
        $res = $this->rawQuery($sql);
        return $this->rawSingleValueFetch($res);
    }

    /**
     * Returns single row
     */
    public function queryToSingleRow($sql)
    {
        $res = $this->rawQuery($sql);
        return $this->rawSingleRowFetch($res);
    }

    /**
     * Returns single object
     */
    public function queryObject($sql, $class, $params = null)
    {
        $res = $this->rawQuery($sql);
        return $this->rawSingleObjectFetch($res, $class, $params);
    }

    /**
     * Returns multiple objects
     */
    public function queryObjects($sql, $class, $params = null)
    {
        $res = $this->rawQuery($sql);
        return $this->rawMultiObjectFetch($res, $class, $params);
    }

    public function queryToMultiRow($sql)
    {
        $res = $this->rawQuery($sql);
        return $this->rawMultiRowFetch($res);
    }

    public function queryToSingleValueMultiRow($sql)
    {
        $res = $this->rawQuery($sql);
        return $this->rawSingleValueMultiRowFetch($res);
    }

    /**
     * Get last inserted id
     */
    public function getLastInsertedId()
    {
        return $this->_dbLink->insert_id;
    }

    /**
     * Get the number of affected rows
     */
    public function getNumberOfAffectedRows()
    {
        return $this->_dbLink->affected_rows;
    }

    protected function dbConnect()
    {
        # Create DB connection
        $this->_dbLink = new mysqli(Config::$DB_HOST, Config::$DB_USER, Config::$DB_PASSWORD);
        if ($this->_dbLink->connect_errno != 0) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("Cannot establish connection with the database [host=" . Config::$DB_HOST . ",user=" . Config::$DB_USER . "], error: " . mysqli_connect_error());
        }

        # Select the DB
        if (!$this->_dbLink->select_db(Config::$DB_NAME)) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("Cannot select database [database=" . Config::$DB_NAME . "], probably database hasn't been initialized yet, run bin/initDB.php, error: " . $this->_dbLink->error);
        }
        Utils::log(LOG_DEBUG, "Successfully conected to the database [dbName=" . Config::$DB_NAME . ",dbHost=" . Config::$DB_HOST . ",dbUser=" . Config::$DB_USER . "]", __FILE__, __LINE__);
    }

    /**
     * Raw query to the SQL, just check if the query finished successfuly and return the result
     */
    protected function rawQuery($sql)
    {
        Utils::log(LOG_DEBUG, "Sql query: " . preg_replace('/\s+/', ' ', $sql), __FILE__, __LINE__);
        if (!$res = $this->_dbLink->query($sql)) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("SQL query failed: " . $this->_dbLink->error);
        }

        return $res;
    }

    /**
     * Single raw row fetch for single column, just check if there are some results
     */
    protected function rawSingleValueFetch($res)
    {
        $ret = null;
        if (($row = $res->fetch_row()) != null) {
            $ret = $row[0];
        }

        # Free the resources
        mysqli_free_result($res);

        return $ret;
    }

    /**
     * Single raw row fetch, just check if there are some results
     */
    protected function rawSingleRowFetch($res)
    {
        $ret = null;
        if (($row = $res->fetch_assoc()) != null) {
            $ret = $row;
        }

        # Free the resources
        mysqli_free_result($res);

        return $ret;
    }

    /**
     * Multi raw row fetch, just check if there are some results
     */
    protected function rawMultiRowFetch($res)
    {
        $ret = array();
        while (($row = $res->fetch_assoc()) != null) {
            array_push($ret, $row);
        }

        # Free the resources
        mysqli_free_result($res);

        return $ret;
    }

    /**
     * Multi raw row fetch for single column, just check if there are some results
     */
    protected function rawSingleValueMultiRowFetch($res)
    {
        $ret = array();
        while (($row = $res->fetch_row()) != null) {
            array_push($ret, $row[0]);
        }

        # Free the resources
        mysqli_free_result($res);

        return $ret;
    }

    /**
     * Single raw object fetch, just check if the fetch was successfull and return the result
     */
    protected function rawSingleObjectFetch($res, $class, $params)
    {
        $ret = null;
        if ($params != null) {
            if (($row = $res->fetch_object($class, $params)) != null) {
                $ret = $row;
            }
        } else {
            if (($row = $res->fetch_object($class)) != null) {
                $ret = $row;
            }
        }

        # Free the resources
        mysqli_free_result($res);

        return $ret;
    }

    /**
     * Multiple raw object fetch, just check if the fetch was successfull and return the result
     */
    protected function rawMultiObjectFetch($res, $class, $params)
    {
        $ret = array();
        if ($params != null) {
            while ($row = $res->fetch_object($class, $params)) {
                array_push($ret, $row);
            }
        } else {
            while ($row = $res->fetch_object($class)) {
                array_push($ret, $row);
            }
        }

        # Free the resources
        mysqli_free_result($res);

        return $ret;
    }
}
