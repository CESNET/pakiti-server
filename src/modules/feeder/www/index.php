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

$time = microtime(true);
require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../FeederModule.php');

try {
    # Initialize
    $feeder = new FeederModule($pakiti);

    # Asynchronous mode - only store the results and exit
    #----------------------------------------------------
    if (Config::$FEEDER_MODE == Constants::$FEEDER_ASYNCHRONOUS_MODE) {
        if ($report == Constants::$STORE_ONLY || $report == null) {
            # Store incomming report
            $feeder->storeReportToFile();
        } else {
            # Pakiti-server in asynchronous mode can't send result back
            print Constants::$RETURN_ERROR;
            http_response_code(500);
            return(0);
        }
    }
    # Synchronous mode - process data immediatelly
    #---------------------------------------------
    elseif (Config::$FEEDER_MODE == Constants::$FEEDER_SYNCHRONOUS_MODE) {
        if (Config::$BACKUP === true) {
            # Store incomming report
            $feeder->storeReportToFile();
        }
        # Process incomming data
        $feeder->processReport();

        $result = $feeder->getResult();
    }

    # Something is wrong here
    #------------------------
    else {
        /* modes are checked in the Pakiti constructor, we should never get here */
        Utils::log(LOG_ERR, "Something knows more modes than I do, shuting down");
        print Constants::$RETURN_ERROR;
        http_response_code(500);
        return(0);
    }

    # End
    Utils::log(LOG_INFO, "Report done for [host=".$feeder->getReportHost()."] in ".Utils::getTimer($time)."s\n");
    print Constants::$RETURN_OK . "\n";
    print $result;
    return(0);
} catch (Exception $e) {
    Utils::log(LOG_ERR, $e->getMessage());
    print Constants::$RETURN_ERROR;
    http_response_code(500);
    return(0);
 }
