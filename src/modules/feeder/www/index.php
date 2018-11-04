<?php

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
