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
   * If debug is enabled in the /etc/pakiti/Config.php, the also LOG_DEBUG
   * messages will be logged
   */
  public static function log($priority, $msg, $file = "", $line = "") {
    if (Config::$DEBUG) {
      $date = date(DATE_RFC822);
      syslog($priority, "$date [$file:$line]: $msg");
    } else {
      if($priority != LOG_DEBUG){
        $date = date(DATE_RFC822);
        syslog($priority, "$date: $msg");
      }
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

    /*
     * Get content from url.
     */
  public static function getContent($url){
    Utils::log(LOG_DEBUG, "Getting content from url: ". $url, __FILE__, __LINE__);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $content = curl_exec($ch);
    if($content === false){
      Utils::log(LOG_ERROR, "Curl error: " . curl_error($ch), __FILE__, __LINE__);
    }
    curl_close($ch);
    return $content;
  }

}
