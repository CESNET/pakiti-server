<?php
require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

$html = new HtmlModule($pakiti);
$html->checkPermission("packages");

$_search = $html->getHttpGetVar("search", null);
$_activity = $html->getHttpGetVar("activity", null);
$_osFilter = $html->getHttpGetVar("osFilter", null);

$html->setTitle("Packages");
$html->setMenuActiveItem("packages.php");
$html->setNumOfEntities($pakiti->getManager("PkgsManager")->getPkgsCount(-1, $_search));

$pkgs = $pakiti->getManager("PkgsManager")->getPkgs($html->getSortBy(), $html->getPageSize(), $html->getPageNum(), -1, $_search);

// Get all unique OS names for the filter and legend
$allHosts = $pakiti->getManager("HostsManager")->getHosts(null, null, -1, -1, null, null, null, -1, $_activity, null, $html->getUserId(), false);
$osList = array();
foreach ($allHosts as $h) {
    $osName = $h->getOsName();
    if ($osName && !isset($osList[$osName])) {
        $osList[$osName] = true;
    }
}
ksort($osList);

// Get OS groups and build OS group => color mapping
$osGroups = $pakiti->getManager("OsGroupsManager")->getOsGroups();
$osGroupColors = array();
$colorPalette = array(
    '#3b82f6', '#dc2626', '#f59e0b', '#10b981', '#8b5cf6',
    '#ec4899', '#f43f5e', '#f97316', '#84cc16', '#14b8a6',
    '#06b6d4', '#6366f1', '#a855f7', '#ef4444', '#0ea5e9'
);
$colorIndex = 0;
foreach ($osGroups as $osGroup) {
    $name = $osGroup->getName();
    if (!isset($osGroupColors[$name])) {
        $osGroupColors[$name] = $colorPalette[$colorIndex % count($colorPalette)];
        $colorIndex++;
    }
}

// Function to get OS color based on OS group mapping
function getOsColor($osName, $osGroups, $osGroupColors) {
    // Check which OS group this OS belongs to based on OS_GROUPS_MAPPING
    foreach ($osGroups as $osGroup) {
        $groupName = $osGroup->getName();
        if (isset(Config::$OS_GROUPS_MAPPING[$groupName])) {
            $regex = "/" . htmlspecialchars_decode(Config::$OS_GROUPS_MAPPING[$groupName]) . "/";
            if (@preg_match($regex, $osName) == 1) {
                return $osGroupColors[$groupName];
            }
        }
    }
    // No matching OS group - generate consistent color from OS name hash
    $colors = ['#8b5cf6', '#ec4899', '#f43f5e', '#f97316', '#84cc16', '#14b8a6', '#06b6d4', '#6366f1', '#a855f7'];
    $hash = 0;
    for ($i = 0; $i < strlen($osName); $i++) {
        $hash = ord($osName[$i]) + (($hash << 5) - $hash);
    }
    return $colors[abs($hash) % count($colors)];
}

$activity = array("All Activity" => "", "Last 24 hours" => "24h", "Last 2 days" => "2d", "Last week" => "1w", "Inactive 48 hours" => "-48h", "Inactive 7 days" => "-7d");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packages | Pakiti</title>
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
    <style>
        .sidebar-collapsed { width: 5rem !important; }
        .sidebar-collapsed .sidebar-text { display: none; }
        .sidebar-collapsed nav a, .sidebar-collapsed #sidebar-header .sidebar-brand, .sidebar-collapsed #sidebar-footer a { justify-content: center !important; }
        .sidebar-collapsed #sidebar-toggle-btn i { transform: rotate(180deg); }
        @media (max-width: 1023px) {
            .sidebar-collapsed { width: 16rem !important; }
            .sidebar-collapsed .sidebar-text { display: block; }
            .sidebar-collapsed nav a, .sidebar-collapsed #sidebar-header .sidebar-brand, .sidebar-collapsed #sidebar-footer a { justify-content: flex-start !important; }
            .sidebar-collapsed #sidebar-toggle-btn { display: none; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900 bg-opacity-50 z-40 hidden lg:hidden transition-opacity"></div>
    <div class="flex h-screen bg-slate-50 overflow-hidden">
        <aside id="sidebar" class="absolute inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 transform -translate-x-full transition-all duration-300 ease-in-out lg:relative lg:translate-x-0 flex flex-col shadow-lg lg:shadow-none">
            <div id="sidebar-header" class="flex items-center justify-between px-2 h-16 border-b border-slate-200 bg-slate-50 flex-shrink-0">
                <a href="index.php" class="sidebar-brand flex items-center gap-2 w-full overflow-hidden whitespace-nowrap">
                    <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center text-white font-bold text-lg shadow-sm flex-shrink-0">P</div>
                    <span class="font-bold text-xl tracking-tight text-slate-900 sidebar-text whitespace-nowrap">Pakiti</span>
                </a>
                <div class="flex items-center">
                    <button type="button" onclick="toggleSidebarCollapse()" id="sidebar-toggle-btn" class="hidden lg:block p-1.5 rounded-full text-slate-500 hover:bg-slate-200 focus:outline-none transition-transform duration-300">
                        <i data-feather="menu" class="w-4 h-4"></i>
                    </button>
                    <button type="button" onclick="toggleSidebar()" class="lg:hidden ml-1 text-slate-500 hover:text-slate-800 focus:outline-none p-1 rounded-md hover:bg-slate-100 transition-colors">
                        <i data-feather="x" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <a href="hosts.php" class="text-slate-600 hover:bg-slate-50 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-transparent">
                    <i data-feather="server" class="flex-shrink-0 h-5 w-5 text-slate-400 group-hover:text-slate-500"></i>
                    <span class="sidebar-text ml-3 whitespace-nowrap">Hosts</span>
                </a>
                <a href="vulnerabilities.php" class="text-slate-600 hover:bg-slate-50 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-transparent">
                    <i data-feather="alert-triangle" class="flex-shrink-0 h-5 w-5 text-slate-400 group-hover:text-slate-500"></i>
                    <span class="sidebar-text ml-3 whitespace-nowrap">Vulnerabilities</span>
                </a>
                <a href="groups.php" class="text-slate-600 hover:bg-slate-50 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-transparent">
                    <i data-feather="layers" class="flex-shrink-0 h-5 w-5 text-slate-400 group-hover:text-slate-500"></i>
                    <span class="sidebar-text ml-3 whitespace-nowrap">Host groups</span>
                </a>
                <a href="packages.php" class="bg-brand-50 text-brand-700 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-brand-600">
                    <i data-feather="package" class="flex-shrink-0 h-5 w-5 text-brand-600"></i>
                    <span class="sidebar-text ml-3 whitespace-nowrap">Packages</span>
                </a>
                <a href="oses.php" class="text-slate-600 hover:bg-slate-50 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-transparent">
                    <i data-feather="hard-drive" class="flex-shrink-0 h-5 w-5 text-slate-400 group-hover:text-slate-500"></i>
                    <span class="sidebar-text ml-3 whitespace-nowrap">Oses</span>
                </a>
                <a href="vds.php" class="text-slate-600 hover:bg-slate-50 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-transparent">
                    <i data-feather="database" class="flex-shrink-0 h-5 w-5 text-slate-400 group-hover:text-slate-500"></i>
                    <span class="sidebar-text ml-3 whitespace-nowrap">VDS</span>
                </a>
                <a href="tags.php" class="text-slate-600 hover:bg-slate-50 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-transparent">
                    <i data-feather="tag" class="flex-shrink-0 h-5 w-5 text-slate-400 group-hover:text-slate-500"></i>
                    <span class="sidebar-text ml-3 whitespace-nowrap">CVE Tags</span>
                </a>
                <a href="exceptions.php" class="text-slate-600 hover:bg-slate-50 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-transparent">
                    <i data-feather="slash" class="flex-shrink-0 h-5 w-5 text-slate-400 group-hover:text-slate-500"></i>
                    <span class="sidebar-text ml-3 whitespace-nowrap">Exceptions</span>
                </a>
                <a href="users.php" class="text-slate-600 hover:bg-slate-50 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-transparent">
                    <i data-feather="users" class="flex-shrink-0 h-5 w-5 text-slate-400 group-hover:text-slate-500"></i>
                    <span class="sidebar-text ml-3 whitespace-nowrap">Users</span>
                </a>
                <a href="stats.php" class="text-slate-600 hover:bg-slate-50 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-transparent">
                    <i data-feather="bar-chart-2" class="flex-shrink-0 h-5 w-5 text-slate-400 group-hover:text-slate-500"></i>
                    <span class="sidebar-text ml-3 whitespace-nowrap">Statistics</span>
                </a>
            </nav>
            <div id="sidebar-footer" class="flex-shrink-0 flex flex-col border-t border-slate-200 p-4 space-y-3">
                <div class="flex items-center gap-2 text-sm text-slate-700">
                    <i data-feather="user" class="w-4 h-4 flex-shrink-0 text-brand-600"></i>
                    <span class="sidebar-text whitespace-nowrap font-medium"><?php $userName = $html->getUserName(); echo ($userName !== null && $userName !== "") ? htmlspecialchars($userName) : "Admin"; ?></span>
                </div>
                <a href="https://github.com/CESNET/pakiti-server" target="_blank" class="flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
                    <i data-feather="github" class="w-5 h-5 flex-shrink-0"></i>
                    <span class="sidebar-text whitespace-nowrap">View on GitHub</span>
                </a>
            </div>
        </aside>

        <div class="flex flex-col w-0 flex-1 overflow-hidden">
            <header class="bg-white border-b border-slate-200 relative z-10 flex-shrink-0 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                <button type="button" class="px-4 border-r border-slate-200 text-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 lg:hidden" onclick="toggleSidebar()">
                    <i data-feather="menu" class="w-6 h-6"></i>
                </button>
            </header>
            <main class="flex-1 relative overflow-y-auto focus:outline-none py-6">
                <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Search -->
                    <form class="bg-white shadow rounded-lg mb-6 overflow-hidden">
                        <div class="p-4">
                            <div class="flex flex-col sm:flex-row gap-3 items-end">
                                <div class="relative flex-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-feather="search" class="h-4 w-4 text-slate-400"></i>
                                    </div>
                                    <input type="text" name="search" class="block w-full pl-10 sm:text-sm border-slate-300 rounded-md py-2.5 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none" placeholder="Search packages..." value="<?php if ($_search != null) echo $_search; ?>">
                                </div>
                                <div class="w-full sm:w-40">
                                    <label for="activity" class="block text-xs font-medium text-slate-500 mb-1">Activity</label>
                                    <select class="block w-full sm:text-sm border-slate-300 rounded-md py-2.5 px-3 border shadow-sm bg-white focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="activity" id="activity" onchange="this.form.submit();">
                                        <?php foreach ($activity as $label => $val) { ?>
                                            <option value="<?php echo $val; ?>" <?php echo ($_activity == $val ? 'selected' : ''); ?>><?php echo $label; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="w-full sm:w-40">
                                    <label for="osFilter" class="block text-xs font-medium text-slate-500 mb-1">OS Group</label>
                                    <select class="block w-full sm:text-sm border-slate-300 rounded-md py-2.5 px-3 border shadow-sm bg-white focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="osFilter" id="osFilter" onchange="this.form.submit();">
                                        <option value="">All Groups</option>
                                        <?php foreach ($osGroups as $osGroup) { ?>
                                            <option value="<?php echo htmlspecialchars($osGroup->getName()); ?>" <?php echo ($_osFilter == $osGroup->getName() ? 'selected' : ''); ?>><?php echo htmlspecialchars($osGroup->getName()); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-brand-600 text-white text-sm font-medium rounded-md hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 whitespace-nowrap">Search</button>
                            </div>
                        </div>
                    </form>

                    <?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

                    <!-- Table -->
                    <div class="bg-white shadow overflow-hidden rounded-lg border border-slate-200 mb-4">
                        <!-- OS Group Legend -->
                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">OS Groups:</span>
                                <?php
                                foreach ($osGroups as $osGroup) {
                                    $name = $osGroup->getName();
                                    $color = $osGroupColors[$name];
                                ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-white border border-slate-200 shadow-sm">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: <?php echo $color; ?>;"></span>
                                    <?php echo htmlspecialchars($name); ?>
                                </span>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-xs">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                            <a href="?sortBy=name<?php echo $html->getQueryString(array("sortByDirection" => $html->getSortBy() == "name" ? ($html->getSortByDirection() == "asc" ? "desc" : "asc") : "asc")); ?>" class="hover:text-brand-600">Name <?php if ($html->getSortBy() == "name") echo ($html->getSortByDirection() == "asc" ? "▲" : "▼"); ?></a>
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Installed version</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                            <a href="?sortBy=arch<?php echo $html->getQueryString(array("sortByDirection" => $html->getSortBy() == "arch" ? ($html->getSortByDirection() == "asc" ? "desc" : "asc") : "asc")); ?>" class="hover:text-brand-600">Architecture <?php if ($html->getSortBy() == "arch") echo ($html->getSortByDirection() == "asc" ? "▲" : "▼"); ?></a>
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Hosts (by OS)</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <?php
                                    foreach ($pkgs as $pkg) {
                                        $hosts = $pakiti->getManager("HostsManager")->getHosts(null, null, -1, -1, null, null, null, -1, $_activity, $pkg->getId(), $html->getUserId(), false);
                                        if (count($hosts) === 0) continue;
                                        // Filter hosts by OS group if filter is selected
                                        if ($_osFilter !== null && $_osFilter !== '') {
                                            $filteredHosts = array();
                                            foreach ($hosts as $h) {
                                                $osName = $h->getOsName();
                                                // Check if host's OS belongs to the selected OS group
                                                if (isset(Config::$OS_GROUPS_MAPPING[$_osFilter])) {
                                                    $regex = "/" . htmlspecialchars_decode(Config::$OS_GROUPS_MAPPING[$_osFilter]) . "/";
                                                    if (@preg_match($regex, $osName) == 1) {
                                                        $filteredHosts[] = $h;
                                                    }
                                                }
                                            }
                                            $hosts = $filteredHosts;
                                        }
                                        if (count($hosts) === 0) continue;
                                    ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-900"><?php echo $pkg->getName(); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-slate-600"><?php echo $pkg->getVersionRelease(); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-slate-500"><?php echo $pkg->getArchName(); ?></td>
                                        <td class="px-4 py-3">
                                            <?php foreach ($hosts as $host) {
                                                $osName = $host->getOsName();
                                                $color = getOsColor($osName, $osGroups, $osGroupColors);
                                            ?>
                                                <a href="host.php?hostId=<?php echo $host->getId(); ?>"
                                                   class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-xs font-medium border border-slate-200 shadow-sm mr-1 mb-1 transition-all hover:shadow-md"
                                                   style="background-color: <?php echo $color; ?>20; color: <?php echo $color; ?>; border-color: <?php echo $color; ?>40;"
                                                   title="<?php echo htmlspecialchars($osName); ?>">
                                                    <span class="w-2 h-2 rounded-full" style="background-color: <?php echo $color; ?>;"></span>
                                                    <?php echo htmlspecialchars($host->getHostname()); ?>
                                                </a>
                                            <?php } ?>
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
                </div>
            </main>
        </div>
    </div>
    <script>
        feather.replace();
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }
        function toggleSidebarCollapse() {
            document.getElementById('sidebar').classList.toggle('sidebar-collapsed');
        }
    </script>
</body>
</html>