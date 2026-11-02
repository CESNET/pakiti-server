<?php
require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

$html = new HtmlModule($pakiti);
$html->checkPermission("host");

$_id = $html->getHttpGetVar("hostId", -1);

$host = $pakiti->getManager("HostsManager")->getHostById($_id, $html->getUserId());
if ($host == null) {
    $html->fatalError("Host with id " . $_id . " doesn't exist or access denied");
    exit;
}

$html->setTitle("Host: " . $host->getHostname());
$html->setDefaultSorting("receivedOn");
$html->setNumOfEntities($pakiti->getManager("ReportsManager")->getHostReportsCount($host->getId()));

$reports = $pakiti->getManager("ReportsManager")->getHostReports($host->getId(), $html->getSortBy(), $html->getPageSize(), $html->getPageNum());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host Reports | Pakiti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['Inter', 'sans-serif'] },
                colors: {
                    brand: {
                        50: '#eff6ff', 100: '#dbeafe', 500: '#5d7083',
                        600: '#5d7083', 700: '#5d7083', 800: '#5d7083', 900: '#5d7083',
                    },
                    accent: {
                        50: '#fef3e7', 100: '#fde8d8', 500: '#f17a30',
                        600: '#d96220', 700: '#c24d15',
                    }
                }
            }
        }
    }
</script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <header class="bg-white border-b border-slate-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-4">
                    <a href="hosts.php?search=&cveName=&activity=2d&hostGroupId=" class="text-slate-500 hover:text-slate-700">
                        <i data-feather="arrow-left" class="w-5 h-5"></i>
                    </a>
                    <div class="w-10 h-10 bg-brand-600 rounded-lg flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                        <?php echo strtoupper(substr($host->getHostname(), 0, 1)); ?>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900"><?php echo $host->getHostname(); ?></h1>
                        <p class="text-sm text-slate-500">Report History</p>
                    </div>
                </div>
            </div>
            <!-- Nav Tabs -->
            <div class="border-b border-slate-200 -mb-px">
                <nav class="flex space-x-6">
                    <a href="host.php?hostId=<?php echo $host->getId(); ?>" class="border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 py-3 px-1 text-sm font-medium whitespace-nowrap">Summary</a>
                    <a href="host_reports.php?hostId=<?php echo $host->getId(); ?>" class="border-b-2 border-brand-600 text-brand-600 py-3 px-1 text-sm font-medium whitespace-nowrap">Reports</a>
                    <a href="host_packages.php?hostId=<?php echo $host->getId(); ?>" class="border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 py-3 px-1 text-sm font-medium whitespace-nowrap">Packages</a>
                    <a href="host_cves.php?hostId=<?php echo $host->getId(); ?>" class="border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 py-3 px-1 text-sm font-medium whitespace-nowrap">CVEs</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

        <!-- Stats Summary -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow border border-slate-200 p-4">
                <p class="text-xs font-medium text-slate-500 uppercase mb-1">Total Reports</p>
                <p class="text-2xl font-bold text-slate-900"><?php echo $pakiti->getManager("ReportsManager")->getHostReportsCount($host->getId()); ?></p>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white shadow overflow-hidden rounded-lg border border-slate-200 mb-4">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                <a href="?sortBy=id<?php echo $html->getQueryString(array("sortByDirection" => $html->getSortBy() == "id" ? ($html->getSortByDirection() == "asc" ? "desc" : "asc") : "asc")); ?>" class="hover:text-brand-600">ID <?php if ($html->getSortBy() == "id") echo ($html->getSortByDirection() == "asc" ? "▲" : "▼"); ?></a>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                <a href="?sortBy=receivedOn<?php echo $html->getQueryString(array("sortByDirection" => $html->getSortBy() == "receivedOn" ? ($html->getSortByDirection() == "asc" ? "desc" : "asc") : "asc")); ?>" class="hover:text-brand-600">Received on <?php if ($html->getSortBy() == "receivedOn") echo ($html->getSortByDirection() == "asc" ? "▲" : "▼"); ?></a>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                <a href="?sortBy=processedOn<?php echo $html->getQueryString(array("sortByDirection" => $html->getSortBy() == "processedOn" ? ($html->getSortByDirection() == "asc" ? "desc" : "asc") : "asc")); ?>" class="hover:text-brand-600">Processed on <?php if ($html->getSortBy() == "processedOn") echo ($html->getSortByDirection() == "asc" ? "▲" : "▼"); ?></a>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Through proxy</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Host Group</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Source</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">#Installed pkgs</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">#CVEs</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">#CVE with tag</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <?php foreach ($reports as $report) { ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 whitespace-nowrap font-mono text-slate-500">#<?php echo $report->getId(); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-700"><?php echo $report->getReceivedOn(); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-500"><?php echo $report->getProcessedOn(); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <?php if ($report->getThroughProxy() == 0) { ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-slate-100 text-slate-800">No</span>
                                    <?php } else { ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-brand-100 text-brand-800"><?php echo $report->getProxyHostname(); ?></span>
                                    <?php } ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-700"><?php echo $report->getHostGroup(); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-500"><?php echo $report->getSource(); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-700"><?php echo $report->getNumOfInstalledPkgs(); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs <?php echo ($report->getNumOfCves() > 0) ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700'; ?>">
                                        <?php echo $report->getNumOfCves(); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs <?php echo ($report->getNumOfCvesWithTag() > 0) ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700'; ?>">
                                        <?php echo $report->getNumOfCvesWithTag(); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

        <footer class="mt-8 border-t border-slate-200 pt-6 pb-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-slate-400 text-sm">
                    &copy; <?php echo date("Y"); ?> CESNET. <a href="https://github.com/CESNET/pakiti-server" target="_blank" class="text-brand-600 hover:text-brand-500 underline">View on GitHub</a>
                </p>
                <p class="text-slate-400 text-sm mt-2 md:mt-0">
                    <i data-feather="shield" class="w-3 h-3 inline mr-1"></i> Secured by Pakiti
                </p>
            </div>
        </footer>
    </main>

    <script>
        feather.replace();
    </script>
</body>
</html>
