<?php

require(realpath(dirname(__FILE__)) . '/../../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../../Html.php');

// Instantiate the HTML module
$html = new HtmlModule($pakiti);

?>
<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">

    <title><?php echo Config::$PAKITI_NAME; ?> Pakiti instance</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
        crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
        crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
            <h1><span style="font-weight: bold; letter-spacing: 1px; color: #000000;"><span style="background-color: #337ab7; color: #ffffff; border-radius: 0.15em;">P</span>akiti &nbsp;</span></h1>
        <div class="jumbotron">
            <p>
                Pakiti provides a monitoring mechanism to check the patching status of Linux systems.
            </p>
            <p>
Pakiti uses the client/server model, with clients running on monitored machines and sending reports to the Pakiti server for evaluation. The report contains a list of packages installed on the client system, which is subject to analysis done by the server. The Pakiti server compares versions against other versions which are obtained from various distribution vendors. Detected vulnerabilities identified using CVE identifiers are reported as the outcome, together with affected packages that need to be updated.
</p><p>

Pakiti has a web based GUI which provides a list of registered systems. The collected information help system administrators maintain proper patch management and quickly identify machine vulnerable to particular vulnerabilites. The information processed is also available via programmatic interfaces.
</p>
            <p class="text-right">
                <a class="btn btn-primary btn-lg" href="<?php echo Constants::$PAKITI_GITHUB; ?>" role="button"><span class="glyphicon glyphicon-book" aria-hidden="true"></span> GitHub</a>
            </p>
            <br>
            <h2>Supported operating systems:</h2>
            <br>
            <div class="row">
                <div class="col-sm-2">
                    <img src="debian.png" style="width: 100%;">
                </div>
                <div class="col-sm-2">
                    <img src="redhat.png" style="width: 100%;">
                </div>
                <div class="col-sm-2">
                    <img src="scientific.png" style="width: 100%;">
                </div>
                <div class="col-sm-2">
                    <img src="centos.png" style="width: 100%;">
                </div>
                <div class="col-sm-2">
                    <img src="ubuntu.png" style="width: 100%;">
                </div>
                <div class="col-sm-2">
                    <img src="suse.png" style="width: 100%;">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h1><?php echo Config::$PAKITI_NAME; ?> Pakiti instance</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="panel panel-primary">
                    <div class="panel-heading text-center">
                        <h1><?php echo $html->getPakiti()->getManager("HostsManager")->getHostsCount(); ?></h1>
                        Watched hosts
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-primary">
                    <div class="panel-heading text-center">
                        <h1><?php echo sizeof($html->getPakiti()->getManager("CvesManager")->getCvesNames()); ?></h1>
                        CVEs being monitored
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-primary">
                    <div class="panel-heading text-center">
                        <h1>
                            <?php $savedReports = $html->getPakiti()->getManager("StatsManager")->get("savedReports"); ?>
                            <?php echo ($savedReports == null) ? 0 : $savedReports->getValue(); ?>
                        </h1>
                        Number of reports that were stored in the database
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-primary">
                    <div class="panel-heading text-center">
                        <h1>
                            <?php $checkedPkgs = $html->getPakiti()->getManager("StatsManager")->get("checkedPkgs"); ?>
                            <?php echo ($checkedPkgs == null) ? 0 : $checkedPkgs->getValue(); ?>
                        </h1>
                        Number of checked packages in all reports
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-primary">
                    <div class="panel-heading text-center">
                        <h1><?php echo Utils::pakitiVersion(); ?></h1>
                        Pakiti version
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <a class="btn btn-success btn-block" href="protected/hosts.php" role="button" style="padding: 10px 15px;">
                    <h2><span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> Enter</h2><?php echo Config::$PAKITI_NAME; ?> Pakiti instance<br>
                </a>
            </div>
        </div>
        <div class="text-center"><?php echo Config::$GUI_FOOTER; ?></div>
        <div class="text-center">Copyright &copy; CESNET, <a href="<?php echo Constants::$PAKITI_GITHUB; ?>" target="_blank">GitHub</a></div>
    </div>
</body>

</html>
