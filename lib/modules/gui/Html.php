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

class HTMLModule extends DefaultModule
{
    private $_acl;
    private $_httpGetVars;
    private $_htmlAttributes;
    private $_errors;
    private $_messages;
    private $_time;

    public static $DEFAULTPAGESIZE = 100;

    public function __construct(&$pakiti)
    {
        parent::__construct($pakiti);

        $this->_time = microtime(true);

        $this->_htmlAttributes = array();

        $this->_httpGetVars = array();
        $this->_acl = new Acl();
        $this->_errors = array();
        $this->_messages = array();
    }

    /*
     * Print HTML header together with Loading div, menu and page title.
     */
    public function printHeader()
    {
        print "<html>\n
    <head>\n
      <title>";
        if (isset($this->_htmlAttributes["title"])) {
            print $this->_htmlAttributes["title"];
        } else {
            print "Pakiti";
        }
        print " </title>\n
      <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n
      <link rel=\"stylesheet\" href=\"pakiti.css\" media=\"all\" type=\"text/css\" />\n
      <link rel=\"shortcut icon\" type=\"image/ico\" href=\"favicon.ico\">\n
    </head>\n
    <body onLoad=\"document.getElementById('loading').style.display='none';\">\n
		
    <table id=\"headerTable\">\n
    	<tr>\n
      	<td><span id=\"mainTitle\"><span id=\"pakitiFirstLetter\">P</span>akiti</span><span id=\"subMainTitle\"> - Patching Status System</span></td>\n
    	</tr>\n
    </table>\n";

        if ($this->getAcl()->getUserIdentity() != null) {
            print "<div id=\:login\">Logged as {$this->getAcl()->getUserIdentity()}</div>\n";
        }

        print "<!-- Loading element is shown while page is loading -->\n
    <div id=\"loading\">Loading ...</div>\n
    ";

        $this->printMenu();


        $this->printErrors();

        $this->printMessages();

        print "<div class=\"space\"></div>";

        if (isset($this->_htmlAttributes["title"])) {
            print "<h1>{$this->_htmlAttributes["title"]}</h1>\n";
        }
    }

    /*
     * Prints the page footer together with page timer
     */
    public function printFooter()
    {
        $time = Utils::getTimer(floatval($this->_time));
        print "
      <div id=\"footer\">&copy; CESNET - Time: $time</div>
      </body>\n
    </html>\n";
    }

    /*
     * Print fatal error and exits
     */
    public function fatalError($msg = "")
    {
        $this->printHeader();
        $this->setError($msg);
        $this->printErrors();

        $this->printFooter();
        exit;
    }

    /*
     * Adds a HTML attributes, which can be used inside the HTML
     */
    public function addHtmlAttribute($attrName, $attrValue)
    {
        $this->_htmlAttributes[$attrName] = $attrValue;
    }

    /*
     * Print paging.
     * $entitiesCount = total count of the entities
     * $pageNum = the page which will be displayed
     * $pageSize = maximum entries on the page
     */
    public function paging($entitiesCount, $pageSize, $pageNum)
    {
        // Do not print anything if the number of entities is smaller than pageSize
        if ($entitiesCount < $pageSize) return;

        $pages = $entitiesCount / $pageSize;
        for ($i = 0; $i <= $pages; $i++) {
            $pageNumLabel = $i + 1;
            if ($i == $pageNum) print "<span>$pageNumLabel</span> | ";
            else print "<a href=\"" . $this->getQueryString(array("pageNum" => $i)) . "\">{$pageNumLabel}</a> | ";
        }
    }

    /*
     * Print alphabetic paging.
     */
    public function alphabeticPaging()
    {
        for ($i = 97; $i <= 122; $i++) {
            $letter = chr($i);
            print "<a href=\"" . $this->getQueryString(array("firstLetter" => $letter)) . "\">{$letter}</a> | ";
        }
    }


    /*
     * Gets the HTTP var value from the GET query string and sets it's value for further use.
     * If there is no value, return the default value.
     */
    public function getHttpGetVar($varName, $defaultValue = null)
    {
        $varValue = Utils::getHttpGetVar($varName);
        if ($varValue != null) {
            $this->_httpGetVars[$varName] = $varValue;
        } else {
            return $defaultValue;
        }
        return $varValue;
    }

    /*
     * Creates the query string, if the array containing httpGetVarName => httpGetVarValue is supplied,
     * the value of the supplied variables will be overwritten by the new value
     */
    public function getQueryString($httpGetVars = null)
    {
        $queryString = "?";
        // Add supplied variables to the query string
        if ($httpGetVars != null) {
            foreach ($httpGetVars as $httpGetVarName => $httpGetVarValue) {
                $queryString .= "&{$httpGetVarName}={$httpGetVarValue}";
            }
        }
        foreach ($this->_httpGetVars as $varName => $varValue) {
            if ($httpGetVars != null && array_key_exists($varName, $httpGetVars)) continue; // Skip it because it was set by previous statement
            // Add the rest which was stored in the _httpGetVars
            $queryString .= "&{$varName}={$varValue}";
        }
        return $queryString;
    }

    public function setError($msg)
    {
        if ($msg != "") {
            array_push($this->_errors, $msg);
        }
    }

    public function printErrors()
    {
        if (!empty($this->_errors)) {
            print "<div class=\"errors\">\n";
            foreach ($this->_errors as $error) {
                print "<span class=\"error\">ERROR: $error</span>\n";
            }
            print "</div>";
        }
    }

    public function setMessage($msg)
    {
        if ($msg != "") {
            array_push($this->_messages, $msg);
        }
    }

    public function printMessages()
    {

        if (!empty($this->_messages)) {

            print "<div class=\"messages\">\n";

            foreach ($this->_messages as $message) {
                print "<span class=\"message\">Message: $message</span>\n";
            }

            print "</div>";
        }
    }

    public function printCveTags(&$cves)
    {
        print "
  	<table class=\"tableList\">
        <tr>
            <th>Enable</th>
            <th>CVE</th>
            <th>CVE Tag</th>
            <th>Reason</th>
            <th>Modifier</th>
            <th>Timestamp</th>
            <th>&nbsp;</th>
        </tr>";
        $i = 0;
        foreach ($cves as $cveId => $cve) {
            foreach ($cve->getTag() as $tag) {
                print "<tr class=\"a" . ($i & 1) . "\">";
                if ($tag->getEnabled() == 1) {
                    print "<td> <input type=" . "checkbox" . " checked> </td>";
                } else {
                    print "<td> <input type=" . "checkbox" . "> </td>";
                }


                print "<td>";
                print "<span";
                if ($tag->getName() == "Critical") {
                    print " class=\"critical_cve\"";
                }

                if ($tag->getName() == "High") {
                    print " class=\"high_cve\"";
                }

                print ">" . $cve->getName() . "</span>";
                print "</td>";
                print "<td>" . $tag->getName() . "</td>";
                print "<td>" . $tag->getReason() . "</td>";
                print "<td>" . $tag->getModifier() . "</td>";
                print "<td>" . $tag->getTimestamp() . "</td>";
                print "</tr>";
            }

        }

        print "</table>";
    }

    public function printHosts(&$hosts)
    {
        print "
  	<table class=\"tableList\">
        <tr>
                <th>CVEs</a></th>
                <th><a href=\"" . $this->getQueryString(array("sortBy" => "hostname")) . "\">Hostname</a></th>
                <th><a href=\"" . $this->getQueryString(array("sortBy" => "hostGroup")) . "\">HostGroup</a></th>
                <th><a href=\"" . $this->getQueryString(array("sortBy" => "os")) . "\">Os</a></th>
                <th><a href=\"" . $this->getQueryString(array("sortBy" => "kernel")) . "\">Kernel</a></th>
                <th><a href=\"" . $this->getQueryString(array("sortBy" => "arch")) . "\">Architecture</a></th>
                <th>#Reports</th>
                <th>Last report</th>
                <th>&nbsp;</th>
        </tr>";
        $i = 0;
        foreach ($hosts as $host) {
            $i++;
            // Get last report
            $cvesCount = $this->getPakiti()->getManager("CveDefsManager")->getCvesCount($host);
            $lastReportId = $host->getLastReportId();
            $report = $this->getPakiti()->getManager("ReportsManager")->getReportById($lastReportId);
            $reportsCount = $this->getPakiti()->getManager("ReportsManager")->getHostReportsCount($host);

            $hostGroups = $this->getPakiti()->getManager("HostGroupsManager")->getHostGroupsByHost($host);


            print "
        	<tr class=\"a" . ($i & 1) . "\">
                <td>" . $cvesCount . "</td>
                <td><a href=\"host.php?hostId=" . $host->getId() . "\">" . $host->getHostname() . "</a></td>
                <td>";



            foreach ($hostGroups as $hostGroup) {
                print "<a href=\"hostGroup.php?hostGroupId={$hostGroup->getId()}\">{$hostGroup->getName()}</a>";
            }


            print "</td>
                <td>" . $host->getOs()->getName() . "</td>";
            // Create MD5 from the kernel version and gets only first 6 symbols to create unique color for each kernel version
            print "<td style=\"color: #" . substr(md5($host->getKernel()), 0, 6) . "\">" . $host->getKernel() . "</td>
                <td>" . $host->getArch()->getName() . "</td>
                <td><a href=\"reports.php?hostId=" . $host->getId() . "\">" . $reportsCount . "</a></td>
                <td";

            # Color dates older then 1,2,3 days
            $reportDaysSince1970 = strtotime($report->getReceivedOn()) / (60 * 60 * 24);
            $today = strtotime("now") / (60 * 60 * 24);
            if ($reportDaysSince1970 > ($today - 1) && $reportDaysSince1970 > ($today)) {
                print " style=\"color: #480000;\"";
            } elseif ($reportDaysSince1970 > ($today - 2) && $reportDaysSince1970 > ($today - 1)) {
                print " style=\"color: #900000;\"";
            } elseif ($reportDaysSince1970 > ($today - 3) && $reportDaysSince1970 > ($today - 2)) {
                print " style=\"color: #E00000;\"";
            } elseif ($reportDaysSince1970 < ($today - 3)) {
                print " style=\"color: #FF0000; font-weight: bold;\"";
            }

            print ">" . $report->getReceivedOn() . "</td>
		<td><a href=\"" . $this->getQueryString(array("op" => "del", "hostId" => $host->getId())) . "\">Delete</a></td>
          </tr>";
        }

        // if zeero hosts found, display message
        if (count($hosts) == 0) {
            print "<tr>
                    <td colspan=\"6\">No hosts found in selected range.</td>
                </tr>
        ";
        }

        print "</table>";
    }

    /*
     * Prints menu. Menu entries are filtred according to the ACL
     */
    protected function printMenu()
    {
        print "<div id=\"menu\">
    	<a href=\"hosts.php\">Hosts</a>
    	<a href=\"hostGroups.php\">Host Groups</a>
    	<a href=\"oses.php\">Oses</a>
    	<a href=\"archs.php\">Archs</a>
    	<a href=\"vds.php\">VDS</a>
    	<a href=\"tags.php\">Tags</a>
    	<a href=\"cve_tags.php\">CVE Tags</a>
    </div>";
    }

    protected function getAcl()
    {
        return $this->_acl;
    }

}

?>
