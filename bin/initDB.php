#!/usr/bin/php
<?php
# Copyright (c) 2011, CESNET. All rights reserved.
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

# Load global configuration file
require_once(realpath(dirname(__FILE__)) . '/../etc/Config.php');
require_once(realpath(dirname(__FILE__)) . '/../lib/common/Constants.php');
require_once(realpath(dirname(__FILE__)) . '/../lib/common/AttributesNames.php');

print "\n#########################\n";
print "# Pakiti DB Initializer #\n";
print "#########################\n\n";

if (sizeof($argv) == 1) {
  print "Usage: " . $argv[0] . " --dbHostname=[database server] --dbUsername=[username] " .
  " --dbPassword=[password] --dbName=[database name] [--reInitialize] [--useConfig]\n\n" .
  "--reInitialize - drop existing database\n" .
  "--useConfig - use connection settings from the etc/Config.php file\n\n";
  exit(0);
}

$dbHostname = "";
$dbUsername = "";
$dbPassword = "";
$dbName = "";
$reInitialize = FALSE;
$useConfig = FALSE;

foreach ($argv as $value) {
  $attrs = explode('=',$value);
  
  $attrName = trim($attrs[0]);
  if (array_key_exists(1, $attrs)) {
    $attrValue = trim($attrs[1]);
  } else {
    $attrValue = "";
  }
  
  switch ($attrName) {
    case "--dbHostname":
       $dbHostname = $attrValue;
       break;
    case "--dbUsername":
       $dbUsername = $attrValue;
       break;
    case "--dbPassword":
       $dbPassword = $attrValue;
       break;
    case "--dbName":
       $dbName = $attrValue;
       break;
    case "--reInitialize":
       $reInitialize = TRUE;
       break;
    case "--useConfig":
       $useConfig = TRUE;
       break;
  }
}

# If reInitialize was enabled, drop the database
if ($useConfig) {
  print "Loading database connection settings from etc/Config.php ... ";
  
  $dbHostname = Config::$DB_HOST;
  $dbUsername = Config::$DB_USER;
  $dbPassword = Config::$DB_PASSWORD;
  $dbName = Config::$DB_NAME;
  
  print "OK\n"; 
}

# Connect to the database
print "Connection to the DB server '$dbHostname' ... ";
if (!$link = new mysqli($dbHostname, $dbUsername, $dbPassword)) {
  print "ERROR: cannot connect to the database server: " . mysqli_connect_error() . "\n";
  exit(1);
}
$link->autocommit(true);
print "OK\n";

# If reInitialize was enabled, drop the database
if ($reInitialize) {
  print "Droping the database '$dbName' ... ";
  if (!$link->query("drop database $dbName")) {
    print "ERROR: cannot drop the database '$dbName': " . $link->error . "\n";
    exit(1);
  }
  print "OK\n"; 
}

print "Creating the database '$dbName' ... ";
# Create the database
if (!$link->query("create database $dbName")) {
  print "ERROR: cannot create the database '$dbName': " . $link->error . ", you can use --reInitialize which drops existing database\n";
  exit(1);
}
print "OK\n";

print "Connection to the newly created database '$dbName' ... ";
# Select the database
if (!$link->select_db($dbName)) {
  print "ERROR: cannot select the database '$dbName': " . $link->error . "\n";
  exit(1);
}
print "OK\n";

## Import the tables
# Get the file with the SQLs
$filename = realpath(dirname(__FILE__)) . "/../install/pakiti.sql";
print "Opening file '$filename' with the database definition ... ";
if (file_exists($filename) === FALSE) {
  print "ERROR: Cannot open the file '$filename' with the database definition\n";
  exit(1);
}
print "OK\n";

print "Loading the content of the file '$filename' ... ";
$sqlFromFile = "";
if (($sqlFromFile = file_get_contents($filename)) == FALSE) {
  print "ERROR: Cannot read the file '$filename' with the database definition\n";
  exit(1);
}
print "OK\n";

print "Running the SQL queries ... ";
$sql = explode(";",$sqlFromFile);
foreach($sql as $query) {
  $query = trim($query);
  if (!empty($query) && !$link->query($query)) {
    print "ERROR: Cannot execute the database definition SQL query ($query): " .  $link->error . "\n";
    exit(1);
  }
}
print "OK\n";

print "Granting privileges on '" . Config::$DB_NAME . ".*' to the user '" . Config::$DB_USER . "' configured in etc/Config.php ... ";
if (!$link->query("grant select, insert, update, delete ON ".Config::$DB_NAME.".* TO  '".Config::$DB_USER."'@'".Config::$DB_HOST.
	"' IDENTIFIED BY '".Config::$DB_PASSWORD."'")) {
  print "ERROR: Cannot grant the privileges: " .  $link->error . "\n";
  exit(1);
}
print "OK\n";

print "Connecting to the database using connection settings from the etc/Config.php ... ";
if (!$link->close()) {
  print "ERROR: Cannot close existing connection to the database: " .  $link->error . "\n";
  exit(1);
}
if (!$newLink = new mysqli(Config::$DB_HOST, Config::$DB_USER, Config::$DB_PASSWORD)) {
  print "ERROR: cannot connect to the database server using connection settings from the etc/Config.php: " . mysqli_connect_error() . "\n";
  exit(1);
}
if (!mysql_select_db(Config::$DB_NAME, $newLink)) {
  print "ERROR: cannot select the database '$dbName': " . mysql_error($newLink) . "\n";
  exit(1);
}
print "OK\n";

print "Storing Pakiti version '" . Constants::$PAKITI_VERSION. "' into the database ... ";
if (!mysql_query("insert into PakitiAttributes (attrName, attrValue) values ('".AttributeNames::$PAKITI_VERSION."','".Constants::$PAKITI_VERSION."')")) {
  print "ERROR: Cannot store the Pakiti version into the PakitiAttributes table: " .  mysql_error($newLink) . "\n";
  exit(1);
}
print "OK\n";

print "Testing connection to the newly created database '" . Config::$DB_NAME . "' ... ";
if (!mysql_query("select attrValue from PakitiAttributes where attrName='".AttributeNames::$PAKITI_VERSION."'")) {
  print "ERROR: Cannot get the Pakiti version from the PakitiAttributes table: " .  mysql_error($newLink) . "\n";
  exit(1);
}
print "OK\n";

print "Closing connection to the database ... ";
if (!mysql_close($newLink)) {
  print "ERROR: Cannot close the connection to the database: " .  mysql_error($newLink) . "\n";
  exit(1);
}
print "OK\n";

print "\nDatabase initialization successfully finished.\n";

exit(0);
?>
