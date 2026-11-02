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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Link to External CSS -->
    <link rel="stylesheet" href="styles-pakiti.css">
</head>
<body>

    <div class="main-container">
        <!-- Header -->
        <div class="site-header">
            <h1 class="site-title"><span class="logo-badge">P</span>akiti</h1>
        </div>

        <!-- Hero Section -->
        <div class="card-hero">
            <p>
                Pakiti provides a monitoring mechanism to check the patching status of Linux systems.
            </p>
            <p>
                Pakiti uses the client/server model, with clients running on monitored machines and sending reports to the Pakiti server for evaluation. The report contains a list of packages installed on the client system, which is subject to analysis done by the server. The Pakiti server compares versions against other versions which are obtained from various distribution vendors. Detected vulnerabilities identified using CVE identifiers are reported as the outcome, together with affected packages that need to be updated.
            </p>
            <p>
                Pakiti has a web based GUI which provides a list of registered systems. The collected information help system administrators maintain proper patch management and quickly identify machine vulnerable to particular vulnerabilites. The information processed is also available via programmatic interfaces.
            </p>
            <p>
                <a class="hero-link" href="<?php echo Constants::$PAKITI_GITHUB; ?>" target="_blank">View on GitHub</a>
            </p>
        </div>

        <!-- OS Logos -->
        <h3 class="section-title">Supported operating systems</h3>
        <div class="logo-grid">
            <div class="logo-item"><img src="debian.png" alt="Debian"></div>
            <div class="logo-item"><img src="redhat.png" alt="RedHat"></div>
            <div class="logo-item"><img src="scientific.png" alt="Scientific"></div>
            <div class="logo-item"><img src="centos.png" alt="CentOS"></div>
            <div class="logo-item"><img src="ubuntu.png" alt="Ubuntu"></div>
            <div class="logo-item"><img src="suse.png" alt="SUSE"></div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <!-- Watched Hosts -->
            <div class="card-stat">
                <h1><?php echo $html->getPakiti()->getManager("HostsManager")->getHostsCount(); ?></h1>
                <p>Watched hosts</p>
            </div>

            <!-- CVEs Monitored -->
            <div class="card-stat">
                <h1><?php echo sizeof($html->getPakiti()->getManager("CvesManager")->getCvesNames()); ?></h1>
                <p>CVEs being monitored</p>
            </div>

            <!-- Reports Stored -->
            <div class="card-stat">
                <h1>
                    <?php $savedReports = $html->getPakiti()->getManager("StatsManager")->get("savedReports"); ?>
                    <?php echo ($savedReports == null) ? 0 : $savedReports->getValue(); ?>
                </h1>
                <p>Reports stored in database</p>
            </div>

            <!-- Checked Packages -->
            <div class="card-stat">
                <h1>
                    <?php $checkedPkgs = $html->getPakiti()->getManager("StatsManager")->get("checkedPkgs"); ?>
                    <?php echo ($checkedPkgs == null) ? 0 : $checkedPkgs->getValue(); ?>
                </h1>
                <p>Checked packages in all reports</p>
            </div>

            <!-- Version -->
            <div class="card-stat">
                <h1><?php echo Utils::pakitiVersion(); ?></h1>
                <p>Pakiti version</p>
            </div>

            <!-- Enter Button (CTA) -->
            <a class="card-cta" href="protected/hosts.php">
                <h2>Enter</h2>
                <p><?php echo Config::$PAKITI_NAME; ?> Pakiti instance</p>
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p><?php echo Config::$GUI_FOOTER; ?></p>
        <p>Copyright &copy; CESNET, <a href="<?php echo Constants::$PAKITI_GITHUB; ?>" target="_blank">GitHub</a></p>
    </footer>

</body>
</html>
