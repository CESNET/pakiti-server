<?php
// CSV Export for Hosts
require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

// Check permission
$html = new HtmlModule($pakiti);
$html->checkPermission("hosts");

// Get parameters
$columns = explode(',', $_GET['columns'] ?? 'hostname,groups,os,kernel,arch,pkgs,cves,taggedCves,lastReport');
$columns = array_filter($columns); // Remove empty values
if (empty($columns)) {
    die('No columns selected for export.');
}

// Get filter parameters
$_search = $_GET['search'] ?? null;
$_cveName = $_GET['cveName'] ?? null;
$_tag = $_GET['tag'] ?? null;
if ($_tag == "true") { $_tag = true; }
$_hostGroupId = $_GET['hostGroupId'] ?? ($_GET['HostGroup'] ?? -1);
$_activity = $_GET['activity'] ?? null;
$_listTaggedCves = isset($_GET['listTaggedCves']) && $_GET['listTaggedCves'] == 'true';

// If HostGroup name is provided, convert it to ID
if ($_hostGroupId == -1 && isset($_GET['HostGroup']) && $_GET['HostGroup'] != '') {
    $hostGroupsTemp = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroups(null, -1, -1, $html->getUserId());
    foreach ($hostGroupsTemp as $g) {
        if ($g->getName() == $_GET['HostGroup']) {
            $_hostGroupId = $g->getId();
            break;
        }
    }
}

// Fetch hosts data
$hosts = $html->getPakiti()->getManager("HostsManager")->getHosts(
    null, null, -1, -1, // Get all hosts
    $_search, $_cveName, $_tag, $_hostGroupId, $_activity, -1, $html->getUserId()
);

// Set CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="hosts_export_' . date('Y-m-d_His') . '.csv"');

$output = fopen('php://output', 'w');

// Add BOM for UTF-8 to support special characters in Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Column header mapping
$columnHeaders = [
    'hostname' => 'Hostname',
    'groups' => 'Groups',
    'os' => 'OS',
    'kernel' => 'Kernel',
    'arch' => 'Architecture',
    'pkgs' => 'Packages Count',
    'cves' => 'CVEs Count',
    'taggedCves' => 'Tagged CVEs',
    'lastReport' => 'Last Report',
    'filterCve' => 'CVE Name'
];

// Write header row
$headers = [];
foreach ($columns as $col) {
    if (isset($columnHeaders[$col])) {
        $headers[] = $columnHeaders[$col];
    }
}
fputcsv($output, $headers);

// Write data rows
foreach ($hosts as $host) {
    $row = [];
    $report = $html->getPakiti()->getManager("ReportsManager")->getReportById($host->getLastReportId());
    $hostGroupsList = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroupsByHost($host);

    foreach ($columns as $col) {
        switch ($col) {
            case 'hostname':
                $row[] = $host->getHostname();
                break;
            case 'groups':
                $groupNames = [];
                foreach ($hostGroupsList as $g) {
                    $groupNames[] = $g->getName();
                }
                $row[] = implode('; ', $groupNames);
                break;
            case 'os':
                $row[] = $host->getOsName();
                break;
            case 'kernel':
                $row[] = $host->getKernel();
                break;
            case 'arch':
                $row[] = $host->getArchName();
                break;
            case 'pkgs':
                $row[] = $report->getNumOfInstalledPkgs();
                break;
            case 'cves':
                $row[] = $host->getNumOfCves();
                break;
            case 'taggedCves':
                if ($_listTaggedCves) {
                    $taggedCves = $html->getPakiti()->getManager("CvesManager")->getCvesNamesForHost($host->getId(), ($_tag == null ? true : $_tag));
                    $row[] = implode('; ', $taggedCves);
                } else {
                    $taggedCount = $host->getNumOfCvesWithTag();
                    $row[] = $taggedCount;
                }
                break;
            case 'lastReport':
                $row[] = $report->getReceivedOn();
                break;
            case 'filterCve':
                $row[] = $_cveName ?? '';
                break;
        }
    }
    fputcsv($output, $row);
}

fclose($output);
exit;
