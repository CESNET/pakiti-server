<?php

/**
 * @access public
 * @author Michal Prochazka
 */
final class Utils {

	/*
 	* Returns delay between startTime and currentTime in long format
 	*/
	public static function getTimer($startTime) {
        return sprintf("%f", (microtime(true) - $startTime));
	}

  /*
   * Return current Pakiti version (it is stored in the lib/classess/Constants.php
   */
  public static function pakitiVersion() {
    return Constants::$PAKITI_VERSION;
  }

  /*
   * If debug is enabled in the etc/Config.php, the also LOG_DEBUG
   * messages will be logged
   */
  public static function log($priority, $msg, $file = "", $line = "") {
    $date = date(DATE_RFC822);
    if ($priority == LOG_DEBUG) {
      if (Config::$DEBUG) syslog(LOG_DEBUG, "$date [$file:$line]: $msg");
    } else {
      syslog($priority, "$date: $msg");
    }
  }

    /*
     * Try to get the variable name in this order GET, POST
     */
  public static function getHttpVar($varName) {
    Utils::log(LOG_DEBUG, "Getting attribute [name=$varName] from the HTTP request", __FILE__, __LINE__);
    if (isset($_POST[$varName])) {
      return $_POST[$varName];
    } elseif (isset($_GET[$varName])) {
      return $_GET[$varName];
    } else return null;
  }

    /*
   * Try to get the variable name only from GET
   */
  public static function getHttpGetVar($varName) {
    Utils::log(LOG_DEBUG, "Getting attribute [name=$varName] from the HTTP GET request", __FILE__, __LINE__);
    if (isset($_GET[$varName])) {
      return $_GET[$varName];
    } else return null;
  }

    /*
     * Try to get the variable name only from POST
     */
  public static function getHttpPostVar($varName) {
    Utils::log(LOG_DEBUG, "Getting attribute [name=$varName] from the HTTP POST request", __FILE__, __LINE__);
    if (isset($_POST[$varName])) {
      return $_POST[$varName];
    } else return null;
  }

    /*
     * Get variable from the _SERVER array
     */
  public static function getServerVar($varName) {
    Utils::log(LOG_DEBUG, "Getting varialbe [name=$varName] from the _SERVER array", __FILE__, __LINE__);
    if (isset($_SERVER[$varName])) {
      return $_SERVER[$varName];
    }
  }

    /*
     * Removes epoch from the package version
     */
  public static function removeEpoch($pkgName) {
    return preg_replace('/^[0-9]+:(.*)$/', '\1', $pkgName);
  }

    /*
     * Get stream context. Through proxy or direct connection.
     */
  public static function getStreamContext() {
    Utils::log(LOG_DEBUG, "Getting stream context", __FILE__, __LINE__);
    if (Config::$ENABLE_OUTGOING_PROXY == 1) {
        $opts = array('http' => array('proxy' => Config::$OUTGOING_PROXY, 'request_fulluri' => true));
        Utils::log(LOG_DEBUG, "Using outgoing proxy: " . Config::$OUTGOING_PROXY);
    } else {
           $opts = array('http' => array('method'=>"GET"));
    }
    return stream_context_create($opts);
  }
}
