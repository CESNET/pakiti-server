#!/usr/bin/php
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

# Load the constants
require_once(realpath(dirname(__FILE__)) . '/../lib/common/Constants.php');

require_once(realpath(dirname(__FILE__)) . '/../lib/common/AttributesNames.php');

$shortopts = "hu:p::c:r";
$longopts = array(
      "help",
      "user:",
      "password::",
      "config:",
      "reInitialize"
);
$opt = getopt($shortopts, $longopts);

function usage() {
  die("Usage: initDB.php [OPTIONS]
    -h, --help \t Display this help and exit.
    -u, --user=name \t User for login if not root user.
    -p, --password[=name] \t Password to use when connecting to server. If password is not given it's asked from the stty.
    -c, --config=name \t Pakiti config file for use if not default file.
    -r, --reInitialize \t Drop existing database
    \n");
}

# Help
if (isset($opt["h"]) || isset($opt["help"])) {
  usage();
}

# Load the configuration file
$config_file = Constants::$PAKITI_CONFIG_FILE;
if(isset($opt["c"])){
  $config_file = $opt["c"];
} elseif(isset($opt["config"])){
  $config_file = $opt["config"];
}
if(!file_exists($config_file)){
  die("Config file [".$config_file."] does not exists.");
}
require_once($config_file);

# User
$dbUser = "root";
if(isset($opt["u"])){
  $dbUser = $opt["u"];
} elseif(isset($opt["user"])){
  $dbUser = $opt["user"];
}

# Password
$dbPassword = null;
if(isset($opt["p"]) && $opt["p"]){
  $dbPassword = $opt["p"];
} elseif(isset($opt["password"]) && $opt["password"]){
  $dbPassword = $opt["password"];
} elseif(isset($opt["password"]) || isset($opt["p"])){
  $fh = fopen('php://stdin','r')  or die($php_errormsg);
  print "Enter password:";
  `/bin/stty -echo`;
  $dbPassword = trim(fgets($fh,64)) or die($php_errormsg);
  `/bin/stty echo`;
  print "\n";
  fclose($fh);
}

# ReInitialize
$reInitialize = false;
if(isset($opt["r"]) || isset($opt["reInitialize"])){
  $reInitialize = true;
}


print "\n#########################\n";
print "# Pakiti DB Initializer #\n";
print "#########################\n\n";

# Connect to the database
print "Connection to the DB server '".Config::$DB_HOST."' ... ";
$link = new mysqli(Config::$DB_HOST, $dbUser, $dbPassword);
if ($link->connect_error) {
    print "ERROR: cannot connect to the database server: " . $link->connect_error . "\n";
    exit(1);
}
$link->autocommit(true);
print "OK\n";

# If reInitialize was enabled, drop the database
if ($reInitialize) {
  print "Droping the database '".Config::$DB_NAME."' ... ";
  if (!$link->query("drop database ".Config::$DB_NAME."")) {
    print "ERROR: cannot drop the database '".Config::$DB_NAME."': " . $link->error . "\n";
    exit(1);
  }
  print "OK\n"; 
}

print "Creating the database '".Config::$DB_NAME."' ... ";
# Create the database
if (!$link->query("create database if not exists ".Config::$DB_NAME."")) {
  print "ERROR: cannot create the database '".Config::$DB_NAME."': " . $link->error . ", you can use --reInitialize which drops existing database\n";
  exit(1);
}
print "OK\n";

print "Connection to the newly created database '".Config::$DB_NAME."' ... ";
# Select the database
if (!$link->select_db(Config::$DB_NAME)) {
  print "ERROR: cannot select the database '".Config::$DB_NAME."': " . $link->error . "\n";
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

print "Granting privileges on '" . Config::$DB_NAME . ".*' to the user '" . Config::$DB_USER . "' configured in the pakiti configuration file ... ";
if (!$link->query("grant select, insert, update, delete ON ".Config::$DB_NAME.".* TO  '".Config::$DB_USER."'@'".Config::$DB_HOST.
	"' IDENTIFIED BY '".Config::$DB_PASSWORD."'")) {
  print "ERROR: Cannot grant the privileges: " .  $link->error . "\n";
  exit(1);
}
print "OK\n";

print "Connecting to the database using connection settings from the pakiti configuration file ... ";
if (!$link->close()) {
  print "ERROR: Cannot close existing connection to the database: " .  $link->error . "\n";
  exit(1);
}
$newLink = new mysqli(Config::$DB_HOST, Config::$DB_USER, Config::$DB_PASSWORD);
if ($newLink->connect_error) {
  print "ERROR: cannot connect to the database server using connection settings from the pakiti configuration file: " . $newLink->connect_error . "\n";
  exit(1);
}
if (!mysqli_select_db($newLink, Config::$DB_NAME)) {
  print "ERROR: cannot select the database '".Config::$DB_NAME."': " . mysqli_error($newLink) . "\n";
  exit(1);
}
print "OK\n";

print "Storing Pakiti version '" . Constants::$PAKITI_VERSION. "' into the database ... ";
if (!mysqli_query($newLink, "insert into PakitiAttributes (attrName, attrValue) values ('".AttributeNames::$PAKITI_VERSION."','".Constants::$PAKITI_VERSION."')")) {
  print "ERROR: Cannot store the Pakiti version into the PakitiAttributes table: " .  mysqli_error($newLink) . "\n";
  exit(1);
}
print "OK\n";

print "Testing connection to the newly created database '" . Config::$DB_NAME . "' ... ";
if (!mysqli_query($newLink, "select attrValue from PakitiAttributes where attrName='".AttributeNames::$PAKITI_VERSION."'")) {
  print "ERROR: Cannot get the Pakiti version from the PakitiAttributes table: " .  mysqli_error($newLink) . "\n";
  exit(1);
}
print "OK\n";

print "Closing connection to the database ... ";
if (!mysqli_close($newLink)) {
  print "ERROR: Cannot close the connection to the database: " .  mysqli_error($newLink) . "\n";
  exit(1);
}
print "OK\n";

print "\nDatabase initialization successfully finished.\n";

exit(0);
?>
