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

$report = $html->getPakiti()->getManager("ReportsManager")->getReportById($host->getLastReportId());
$hostGroups = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroupsByHost($host);
$reportsCount = $html->getPakiti()->getManager("ReportsManager")->getHostReportsCount($host->getId());
$pkgs = $pakiti->getManager("PkgsManager")->getVulnerablePkgsForHost($host->getId());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host: <?php echo $host->getHostname(); ?> | Pakiti</title>
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
                        <p class="text-sm text-slate-500">Host Details</p>
                    </div>
                </div>
            </div>
            <!-- Nav Tabs -->
            <div class="border-b border-slate-200 -mb-px">
                <nav class="flex space-x-6">
                    <a href="host.php?hostId=<?php echo $host->getId(); ?>" class="border-b-2 border-brand-600 text-brand-600 py-3 px-1 text-sm font-medium whitespace-nowrap">Summary</a>
                    <a href="host_reports.php?hostId=<?php echo $host->getId(); ?>" class="border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 py-3 px-1 text-sm font-medium whitespace-nowrap">Reports</a>
                    <a href="host_packages.php?hostId=<?php echo $host->getId(); ?>" class="border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 py-3 px-1 text-sm font-medium whitespace-nowrap">Packages</a>
                    <a href="host_cves.php?hostId=<?php echo $host->getId(); ?>" class="border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 py-3 px-1 text-sm font-medium whitespace-nowrap">CVEs</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase">Host Groups</p>
                        <p class="text-2xl font-bold text-slate-900"><?php echo count($hostGroups); ?></p>
                    </div>
                    <i data-feather="layers" class="w-8 h-8 text-slate-400"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase">Installed Packages</p>
                        <p class="text-2xl font-bold text-slate-900"><a href="host_packages.php?hostId=<?php echo $host->getId(); ?>" class="hover:text-brand-600"><?php echo $report->getNumOfInstalledPkgs(); ?></a></p>
                    </div>
                    <i data-feather="package" class="w-8 h-8 text-slate-400"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase">Vulnerable Packages</p>
                        <p class="text-2xl font-bold text-red-600"><a href="host_cves.php?hostId=<?php echo $host->getId();?>" class="hover:text-red-700"><?php print(count($pkgs)); ?></a></p>
                    </div>
                    <i data-feather="alert-circle" class="w-8 h-8 text-red-400"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase">CVEs</p>
                        <p class="text-2xl font-bold text-slate-900"><a href="host_cves.php?hostId=<?php echo $host->getId(); ?>" class="hover:text-brand-600"><?php echo $host->getNumOfCves(); ?></a></p>
                    </div>
                    <i data-feather="shield" class="w-8 h-8 text-slate-400"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- System Information -->
            <div class="bg-white shadow rounded-lg border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-lg font-medium text-slate-900">System Information</h2>
                </div>
                <div class="divide-y divide-slate-200">
                    <div class="px-6 py-4 flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-500">Operating System</span>
                        <span class="text-sm text-slate-900"><?php echo $host->getOsName(); ?></span>
                    </div>
                    <div class="px-6 py-4 flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-500">Architecture</span>
                        <span class="text-sm text-slate-900"><?php echo $host->getArchName(); ?></span>
                    </div>
                    <div class="px-6 py-4 flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-500">Kernel</span>
                        <span class="text-sm text-slate-900" style="color: #<?php echo substr(md5($host->getKernel()), 0, 6); ?>; font-family: monospace;">
                            <?php echo $host->getKernel(); ?>
                        </span>
                    </div>
                    <div class="px-6 py-4 flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-500">Domain</span>
                        <span class="text-sm text-slate-900"><?php echo $host->getDomainName(); ?></span>
                    </div>
                </div>
            </div>

            <!-- Host Groups -->
            <div class="bg-white shadow rounded-lg border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-lg font-medium text-slate-900">Host Groups</h2>
                </div>
                <div class="px-6 py-4">
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($hostGroups as $hostGroup) { ?>
                            <a href="hosts.php?hostGroupId=<?php echo $hostGroup->getId(); ?>" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm bg-brand-100 text-brand-700 hover:bg-brand-200">
                                <i data-feather="layers" class="w-4 h-4 mr-1"></i>
                                <?php echo $hostGroup->getName(); ?>
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- Reporter Information -->
            <div class="bg-white shadow rounded-lg border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-lg font-medium text-slate-900">Reporter Information</h2>
                </div>
                <div class="divide-y divide-slate-200">
                    <div class="px-6 py-4 flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-500">Reporter Hostname</span>
                        <span class="text-sm text-slate-900"><?php echo $host->getReporterHostname(); ?></span>
                    </div>
                    <div class="px-6 py-4 flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-500">Reporter IP</span>
                        <span class="text-sm text-slate-900"><?php echo $host->getReporterIp(); ?></span>
                    </div>
                </div>
            </div>

            <!-- CVE Information -->
            <div class="bg-white shadow rounded-lg border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-lg font-medium text-slate-900">CVE Information</h2>
                </div>
                <div class="divide-y divide-slate-200">
                    <div class="px-6 py-4 flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-500">Total CVEs</span>
                        <span class="text-sm text-slate-900"><a href="host_cves.php?hostId=<?php echo $host->getId(); ?>"><?php echo $host->getNumOfCves(); ?></a></span>
                    </div>
                    <div class="px-6 py-4 flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-500">CVEs with Tag</span>
                        <span class="text-sm text-red-600"><a href="host_cves.php?hostId=<?php echo $host->getId(); ?>&tag=true"><?php echo $host->getNumOfCvesWithTag(); ?></a></span>
                    </div>
                </div>
            </div>

            <!-- Reports Information -->
            <div class="bg-white shadow rounded-lg border border-slate-200 overflow-hidden lg:col-span-2">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <h2 class="text-lg font-medium text-slate-900">Report Information</h2>
                    <a href="host_reports.php?hostId=<?php echo $host->getId(); ?>" class="text-sm text-brand-600 hover:text-brand-700 font-medium">
                        View all reports <i data-feather="arrow-right" class="w-4 h-4 inline ml-1"></i>
                    </a>
                </div>
                <div class="px-6 py-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase">Total Reports</p>
                        <p class="text-lg font-semibold text-slate-900"><?php echo $reportsCount; ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase">Last Report</p>
                        <p class="text-sm text-slate-900"><?php echo $report->getReceivedOn(); ?></p>
                    </div>
                </div>
            </div>
        </div>

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
