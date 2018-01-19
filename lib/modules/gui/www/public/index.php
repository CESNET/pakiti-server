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
                Pakiti provides a monitoring and notification mechanism to check the patching status of systems.<br><br>
                Once installed on a client host, Pakiti will send every night the list of installed packages to the relevant Pakiti Server(s). After the client sends the list of installed packages, Pakiti server compares the versions against versions which Pakiti server obtains from OVAL definitions from MITRE. Pakiti supports definitions for RHEL, Debian, SLES, openSUSE and Ubuntu. Optionally client reports back the packages which has marked CVE by tag.<br><br>
                Pakiti has a web based GUI which provides a list of the registered systems. This helps the system admins keep multiple machines up-to-date and prevent unpatched machines to be kept silently on the network.
            </p>
            <p class="text-right">
                <a class="btn btn-primary btn-lg" href="<?php echo Constants::$PAKITI_GITHUB; ?>" role="button"><span class="glyphicon glyphicon-book" aria-hidden="true"></span> GitHub</a>
            </p>
            <br>
            <h2>Supported OSes</h2>
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
                        Observed CVEs
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
        <div class="text-center">Copyright &copy; 2018, CESNET, <a href="<?php echo Constants::$PAKITI_GITHUB; ?>" target="_blank">GitHub</a></div>
    </div>
</body>

</html>
