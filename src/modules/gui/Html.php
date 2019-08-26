<?php

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
 */
class HTMLModule extends DefaultModule
{
    private $_acl;
    private $_httpGetVars;
    private $_errors;
    private $_messages;

    private $_defaultSorting = null;
    private $_numOfEntities = 0;
    private $_title = "Pakiti";
    private $_menuActiveItem = null;

    private $error403 = "
<html><head><title>403 Forbidden</title></head>
<body>
<h1>Forbidden</h1>
<p>You don't have permission to get the information</p>
</body></html>
";

    public function __construct($pakiti)
    {
        parent::__construct($pakiti);

        $this->_time = microtime(true);

        $this->_htmlAttributes = array();

        $this->_httpGetVars = array();
        $this->_acl = new Acl($pakiti);
        $this->_errors = array();
        $this->_messages = array();
    }

    /**
     * Print fatal error and exits
     */
    public function fatalError($msg = "")
    {
        $this->setError($msg);
        $html = $this;
        include(realpath(dirname(__FILE__)) . "/common/header.php");
        include(realpath(dirname(__FILE__)) . "/common/footer.php");
        exit;
    }

    /**
     * Gets the HTTP var value from the GET query string and sets it's value for further use.
     * If there is no value, return the default value.
     */
    public function getHttpGetVar($varName, $defaultValue = null)
    {
        $varValue = Utils::getHttpGetVar($varName);
        if ($varValue == null)
            return($defaultValue);

        $this->_httpGetVars[$varName] = $varValue;
        return(htmlspecialchars($varValue, ENT_QUOTES, 'UTF-8'));
    }

    public function getHttpPostVar($varName, $defaultValue = null)
    {
         $varValue = Utils::getHttpPostVar($varName);
         if ($varValue == null)
             return($defaultValue);

         return(htmlspecialchars($varValue, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Creates the query string, if the array containing httpGetVarName => httpGetVarValue is supplied,
     * the value of the supplied variables will be overwritten by the new value
     */
    public function getQueryString($httpGetVars = null)
    {
        $queryString = "?";
        # Add supplied variables to the query string
        if ($httpGetVars != null) {
            foreach ($httpGetVars as $httpGetVarName => $httpGetVarValue) {
                $queryString .= "&{$httpGetVarName}={$httpGetVarValue}";
            }
        }

        foreach ($this->_httpGetVars as $varName => $varValue) {
            if ($httpGetVars != null && array_key_exists($varName, $httpGetVars)) {
                # Skip it because it was set by previous statement
                continue;
            }
            # Add the rest which was stored in the _httpGetVars
            $queryString .= "&{$varName}={$varValue}";
        }
        
        return $queryString;
    }

    public function setError($msg)
    {
        if ($msg != "") {
            array_push($this->_errors, $msg);
        }
    }

    public function getErrors()
    {
        return $this->_errors;
    }


    # Access control
    public function checkPermission($source)
    {
        if (!$this->_acl->permission($source)) {
            print($this->error403);
            header('HTTP/1.0 403 Forbidden');
            exit;
        }
    }

    public function permission($source)
    {
        return $this->_acl->permission($source);
    }

    public function getUserId()
    {
        return $this->_acl->getUserId();
    }

    public function getUserName()
    {
        if ($this->_acl->getUser() != null) {
            return $this->_acl->getUser()->getName();
        }
        return null;
    }

    # Pagination
    public function setDefaultSorting($sortBy)
    {
        return $this->_defaultSorting = $sortBy;
    }

    public function getSortBy()
    {
        return $this->getHttpGetVar("sortBy", $this->_defaultSorting);
    }

    public function getPageSize()
    {
        return 100;
    }

    public function getPaginationSize()
    {
        return 11;
    }

    public function getPageNum()
    {
        return $this->getHttpGetVar("pageNum", 0);
    }

    public function setNumOfEntities($value)
    {
        $this->_numOfEntities = $value;
    }

    public function getNumOfPages()
    {
        return ceil($this->_numOfEntities / $this->getPageSize());
    }

    # Header
    public function getMenuItems()
    {
        $menu = array();
        if ($this->permission("hosts")) {
            $menu["hosts.php"] = "Hosts";
        }
        if ($this->permission("cve")) {
            $menu["vulnerabilities.php"] = "Vulnerabilities";
        }
        if ($this->permission("groups")) {
            $menu["groups.php"] = "Host groups";
        }
        if ($this->permission("packages")) {
            $menu["packages.php"] = "Packages";
        }
        if ($this->permission("oses")) {
            $menu["oses.php"] = "Oses";
        }
        if ($this->permission("vds")) {
            $menu["vds.php"] = "VDS";
        }
        if ($this->permission("tags")) {
            $menu["tags.php"] = "CVE Tags";
        }
        if ($this->permission("exceptions")) {
            $menu["exceptions.php"] = "Exceptions";
        }
        if ($this->permission("users")) {
            $menu["users.php"] = "Users";
        }
        if ($this->permission("stats")) {
            $menu["stats.php"] = "Statistics";
        }
        return $menu;
    }

    public function setMenuActiveItem($value)
    {
        $this->_menuActiveItem = $value;
    }

    public function getMenuActiveItem()
    {
        return $this->_menuActiveItem;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setTitle($value)
    {
        $this->_title = $value;
    }
}
