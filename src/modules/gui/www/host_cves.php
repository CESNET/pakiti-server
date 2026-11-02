<?php
require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

$html = new HtmlModule($pakiti);
$html->checkPermission("host");

$_id = $html->getHttpGetVar("hostId", -1);
$_tag = $html->getHttpGetVar("tag", null);
if ($_tag == "true") {
    $_tag = true;
}

$host = $pakiti->getManager("HostsManager")->getHostById($_id, $html->getUserId());
if ($host == null) {
    $html->fatalError("Host with id " . $_id . " doesn't exist or access denied");
    exit;
}

$html->setTitle("Host: " . $host->getHostname());

$pkgs = $pakiti->getManager("PkgsManager")->getVulnerablePkgsForHost($host->getId(), $_tag);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host CVEs | Pakiti</title>
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
                        <p class="text-sm text-slate-500">Vulnerabilities & CVEs</p>
                    </div>
                </div>
            </div>
            <!-- Nav Tabs -->
            <div class="border-b border-slate-200 -mb-px">
                <nav class="flex space-x-6">
                    <a href="host.php?hostId=<?php echo $host->getId(); ?>" class="border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 py-3 px-1 text-sm font-medium whitespace-nowrap">Summary</a>
                    <a href="host_reports.php?hostId=<?php echo $host->getId(); ?>" class="border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 py-3 px-1 text-sm font-medium whitespace-nowrap">Reports</a>
                    <a href="host_packages.php?hostId=<?php echo $host->getId(); ?>" class="border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 py-3 px-1 text-sm font-medium whitespace-nowrap">Packages</a>
                    <a href="host_cves.php?hostId=<?php echo $host->getId(); ?>" class="border-b-2 border-brand-600 text-brand-600 py-3 px-1 text-sm font-medium whitespace-nowrap">CVEs</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Filter -->
        <div class="bg-white shadow rounded-lg mb-6 overflow-hidden p-4">
            <label class="flex items-center space-x-3 cursor-pointer">
                <input type="checkbox" onclick="location.href='?hostId=<?php echo $host->getId(); ?><?php if ($_tag !== true) echo '&tag=true'; ?>';"<?php if ($_tag === true) echo ' checked'; ?> class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                <span class="text-sm font-medium text-slate-700">Only tagged CVEs</span>
            </label>
        </div>

        <!-- Stats -->
        <div class="mb-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-brand-100 text-brand-800">
                <i data-feather="alert-triangle" class="w-4 h-4 mr-1"></i>
                <?php echo count($pkgs); ?> vulnerable package<?php echo (count($pkgs) != 1 ? 's' : ''); ?>
            </span>
        </div>

        <!-- Table -->
        <div class="bg-white shadow overflow-hidden rounded-lg border border-slate-200 mb-4">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Package</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Installed version</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Architecture</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">CVEs</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <?php foreach ($pkgs as $pkg) {
                            $cvesNames = $pakiti->getManager("CvesManager")->getCvesNamesForPkgAndOs($pkg->getId(), $host->getOsId(), $_tag);
                        ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-900"><?php echo $pkg->getName(); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <code class="inline-flex items-center px-2 py-1 rounded text-xs bg-slate-100 text-slate-800 font-mono">
                                        <?php echo $pkg->getVersionRelease(); ?>
                                    </code>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-brand-100 text-brand-700">
                                        <?php echo $pkg->getArchName(); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <?php foreach ($cvesNames as $cveName) {
                                            $tags = $pakiti->getManager("CveTagsManager")->getCveTagsByCveName($cveName);
                                        ?>
                                            <a href="cve.php?cveName=<?php echo $cveName; ?>" class="inline-flex items-center px-2 py-1 rounded text-xs <?php echo(!empty($tags) ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700'); ?> hover:opacity-80">
                                                <?php echo $cveName; ?>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
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
