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

$time = microtime(true);
require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');

try {
  # Initialize 
  $feeder = new FeederModule($pakiti);

  # Asynchronous mode - only store the results and exit
  #----------------------------------------------------
  if (Config::$FEEDER_MODE == Constants::$FEEDER_ASYNCHRONOUS_MODE) {
    Utils::log(LOG_DEBUG, "Processing report in asynchronous mode");
    $feeder->storeReportToFile();
  }

  # Synchronous mode - process data immediatelly
  #---------------------------------------------
  elseif (Config::$FEEDER_MODE ==Constants::$FEEDER_SYNCHRONOUS_MODE) {
    Utils::log(LOG_DEBUG, "Processing report in synchronous mode");
    # Process incomming data
    $feeder->processReport();
        
    # Should we send the results back to the client?
    if (Utils::getHttpVar(Constants::$REPORT_REPORT) == Constants::$SEND_REPORT) {
     $feeder->sendResultsBack();
    }
  }

  # Something is wrong here
  #------------------------
  else {
    Utils::log(LOG_ERROR, "Undefined feeder mode");
    print Constants::$RETURN_ERROR;
    exit;
  }
  
  # End
  Utils::log(LOG_INFO, "Report done for [host=".Utils::getHttpVar(Constants::$REPORT_HOSTNAME).
  	"] in ".Utils::getTimer($time)."s\n");
  print Constants::$RETURN_OK;

  # Do we need backup?
  #-------------------
  if (Config::$BACKUP === TRUE) {
    $feeder->backupReport();  
  }

  exit;
} catch (Exception $e) {
  Utils::log(LOG_ERR, $e->getMessage());
  print Constants::$RETURN_ERROR;
  if ($e->getMessage() != "") {
    print "\nPakiti server error message: {$e->getMessage()}";
  }
  exit;
}
?>
