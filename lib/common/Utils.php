<?php
# Copyright (c) 2017, CESNET. All rights reserved.
#
# Redistribution and use in source and binary forms, with or
# without modification, are permitted provided that the following
# conditions are met:
#
#   o Redistributions of source code must retain the above
#     copyright notice, this list of conditions and the following
#     disclaimer.
#   o Redistributions in binary form must reproduce the above
#     copyright notice, this list of conditions and the following
#     disclaimer in the documentation and/or other materials
#     provided with the distribution.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND
# CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
# INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
# MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
# DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS
# BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
# EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
# TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
# DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
# ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
# OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
# OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
# POSSIBILITY OF SUCH DAMAGE.

/**
 * @author Michal Prochazka
 * @author Jakub Mlcak
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
   * If debug is enabled, then also LOG_DEBUG messages will be logged
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

  public static function isConnectionSecure() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off');
  }

  /*
   * Compose sql select statement
   * select, from, join, where and order could be string, string array or null
   * limit and offset could be numeric or null
   * @return sql statement
   */
  public static function sqlSelectStatement($select, $from, $join = null, $where = null, $order = null, $limit = null, $offset = null){
    $sql = "";
    $sql .= Utils::compose("select", $select, ",");
    $sql .= Utils::compose("from", $from, ",");
    $sql .= Utils::compose("", $join, "");
    $sql .= Utils::compose("where", $where, "and");
    $sql .= Utils::compose("order by", $order, ",");
    $sql .= Utils::checkNull("limit", $limit);
    $sql .= Utils::checkNull("offset", $offset);

    if ($select == null || $from == null || ($limit != null && !is_numeric($limit)) || ($offset != null && !is_numeric($offset))) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Select SQL statement error: $sql");
    }

    return $sql;
  }

  private static function compose($mean, $string, $separator){
    $sql = "";
    if($string != null){
      if(!is_array($string)){
        $sql .= "$mean $string ";
      } else {
        if(!empty($string)){
          $sql .= "$mean ";
          foreach($string as $item){
            $sql .= "$item$separator ";
          }
          $length = -1 - strlen($separator);
          $sql = substr($sql, 0, $length);
          $sql .= " ";
        }
      }
    }
    return $sql;
  }

  private static function checkNull($mean, $string){
    if($string != null){
      return "$mean $string ";
    }
    return "";
  }

}
