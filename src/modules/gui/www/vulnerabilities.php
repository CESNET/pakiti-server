<?php
require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

$html = new HtmlModule($pakiti);
$html->checkPermission("cve");

$_hostGroupId = $html->getHttpGetVar("hostGroupId", -1);
$_tag = $html->getHttpGetVar("tag", null);
if ($_tag == "true") { $_tag = true; }
$_cveName = $html->getHttpGetVar("cveName", null);
$_activity = $html->getHttpGetVar("activity", null);

// Check if entered CVE exists in database
$_cveExists = null;
$_cveMessage = '';
if ($_cveName != null && $_cveName != '') {
    $_cveExists = $pakiti->getManager("CvesManager")->cveExists($_cveName);
    if ($_cveExists) {
        $_cveMessage = '<svg xmlns="http://www.w3.org/2000/svg" class="inline w-3 h-3 mr-1 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg><span class="text-emerald-600">CVE exists in database</span>';
    } else {
        $_cveMessage = '<svg xmlns="http://www.w3.org/2000/svg" class="inline w-3 h-3 mr-1 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg><span class="text-red-600">CVE not found in database</span>';
    }
}

$hostGroups = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroups(null, -1, -1, $html->getUserId());
if ($_cveName != null && $_cveName != '') {
    if ($pakiti->getManager("CvesManager")->cveExists($_cveName)) {
        $cveNames = [$_cveName];
        $html->setNumOfEntities(1);
    } else {
        $cveNames = [];
        $html->setNumOfEntities(0);
    }
} else {
    $cveNames = $pakiti->getManager("CvesManager")->getCvesNamesForHosts(-1, -1, $_tag, $_hostGroupId, $_activity);
    $html->setNumOfEntities(count($cveNames));
    $pageSize = $html->getPageSize();
    $pageNum = $html->getPageNum();
    $firstIndex = $pageSize * $pageNum;
    $lastIndex = $pageSize;
    $cveNames = array_slice($cveNames, $firstIndex, $lastIndex);
}

$html->setTitle("Monitored Vulnerabilities");
$html->setMenuActiveItem("vulnerabilities.php");

$tagNames = $pakiti->getManager("CveTagsManager")->getTagNames();
$activity = array("All Activity" => "", "Last 24 hours" => "24h", "Last 2 days" => "2d", "Last week" => "1w", "Inactive 48 hours" => "-48h", "Inactive 7 days" => "-7d");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitored Vulnerabilities | Pakiti</title>
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
                <a href="vulnerabilities.php" class="bg-brand-50 text-brand-700 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-brand-600">
                    <i data-feather="alert-triangle" class="flex-shrink-0 h-5 w-5 text-brand-600"></i>
                    <span class="sidebar-text ml-3 whitespace-nowrap">Vulnerabilities</span>
                </a>
                <a href="groups.php" class="text-slate-600 hover:bg-slate-50 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-transparent">
                    <i data-feather="layers" class="flex-shrink-0 h-5 w-5 text-slate-400 group-hover:text-slate-500"></i>
                    <span class="sidebar-text ml-3 whitespace-nowrap">Host groups</span>
                </a>
                <a href="packages.php" class="text-slate-600 hover:bg-slate-50 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-transparent">
                    <i data-feather="package" class="flex-shrink-0 h-5 w-5 text-slate-400 group-hover:text-slate-500"></i>
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
                    <!-- Filters -->
                    <form action="" method="get" id="filter-form" class="bg-white shadow rounded-lg mb-6 overflow-hidden">
                        <div class="bg-slate-50 p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div>
                                <label for="cveName" class="block text-xs font-medium text-slate-500 mb-1">CVE</label>
                                <input class="block w-full sm:text-sm border-slate-300 rounded-md py-2.5 px-3 border shadow-sm bg-white focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="cveName" id="cveName" value="<?php if ($_cveName != null) echo $_cveName; ?>" onchange="this.form.submit();"/>
                                <?php if ($_cveMessage != ''): ?>
                                    <p class="text-xs" style="margin-top: 5px;"><?php echo $_cveMessage; ?></p>
                                <?php endif; ?>
                            </div>
                            <div>
                                <label for="tag" class="block text-xs font-medium text-slate-500 mb-1">Tag</label>
                                <select class="block w-full sm:text-sm border-slate-300 rounded-md py-2.5 px-3 border shadow-sm bg-white focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="tag" id="tag" onchange="this.form.submit();">
                                    <option value="">All</option>
                                    <option value="true"<?php if ($_tag === true) echo ' selected'; ?>>Any tag</option>
                                    <?php foreach ($tagNames as $tagName) { ?>
                                        <option value="<?php echo $tagName; ?>"<?php if ($_tag === $tagName) echo ' selected'; ?>><?php echo $tagName; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div>
                                <label for="activity" class="block text-xs font-medium text-slate-500 mb-1">Activity</label>
                                <select class="block w-full sm:text-sm border-slate-300 rounded-md py-2.5 px-3 border shadow-sm bg-white focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="activity" id="activity" onchange="this.form.submit();">
                                    <?php foreach ($activity as $label => $val) { ?>
                                        <option value="<?php echo $val; ?>"<?php if ($val == $_activity) echo ' selected'; ?>><?php echo $label; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div>
                                <label for="hostGroupId" class="block text-xs font-medium text-slate-500 mb-1">Host group</label>
                                <select class="block w-full sm:text-sm border-slate-300 rounded-md py-2.5 px-3 border shadow-sm bg-white focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="hostGroupId" id="hostGroupId" onchange="this.form.submit();">
                                    <option value="">All</option>
                                    <?php foreach ($hostGroups as $hostGroup) { ?>
                                        <option value="<?php echo $hostGroup->getId(); ?>"<?php if ($hostGroup->getId() == $_hostGroupId) echo ' selected'; ?>><?php echo $hostGroup->getName(); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </form>

                    <?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

                    <!-- Table -->
                    <div class="bg-white shadow overflow-hidden rounded-lg border border-slate-200 mb-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-xs">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">CVE</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Affected Hosts</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider w-24">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <?php foreach ($cveNames as $cveName) {
                                        $hostsCount = $html->getPakiti()->getManager("HostsManager")->getHostsCount(null, $cveName, $_tag, $_hostGroupId, $_activity, -1, $html->getUserId());
                                    ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a href="cve.php?cveName=<?php echo $cveName;?>" class="font-medium text-brand-600 hover:underline"><?php echo $cveName;?></a>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a href="hosts.php?cveName=<?php echo $cveName;?>&hostGroupId=<?php echo $_hostGroupId;?>&activity=<?php echo $_activity;?>" class="text-slate-600 hover:text-brand-600"><?php echo $hostsCount; ?></a>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <button type="button" class="text-slate-500 hover:text-brand-600" onclick="window.location.href='exceptions.php?cveName=<?php echo $cveName;?>'">
                                                <i data-feather="shield" class="w-4 h-4"></i> Exceptions
                                            </button>
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
