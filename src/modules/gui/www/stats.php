<?php
require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

$html = new HtmlModule($pakiti);
$html->checkPermission("stats");

$html->setTitle("Statistics");
$html->setMenuActiveItem("stats.php");

// Get stats values
$savedReports = $html->getPakiti()->getManager("StatsManager")->get("savedReports");
$savedReportsValue = ($savedReports == null) ? 0 : $savedReports->getValue();

$unsavedReports = $html->getPakiti()->getManager("StatsManager")->get("unsavedReports");
$unsavedReportsValue = ($unsavedReports == null) ? 0 : $unsavedReports->getValue();

$checkedPkgs = $html->getPakiti()->getManager("StatsManager")->get("checkedPkgs");
$checkedPkgsValue = ($checkedPkgs == null) ? 0 : $checkedPkgs->getValue();

$sameReports = $html->getPakiti()->getManager("StatsManager")->get("sameReports");
$sameReportsValue = ($sameReports == null) ? 0 : $sameReports->getValue();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics | Pakiti</title>
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
                <a href="stats.php" class="bg-brand-50 text-brand-700 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-brand-600">
                    <i data-feather="bar-chart-2" class="flex-shrink-0 h-5 w-5 text-brand-600"></i>
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
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Current Statistics -->
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-slate-900 mb-4">Current statistics</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            <div class="bg-white shadow rounded-lg border border-slate-200 p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-brand-100 rounded-lg p-3">
                                        <i data-feather="server" class="w-5 h-5 text-brand-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-slate-500">Number of hosts</p>
                                        <p class="text-2xl font-bold text-slate-900"><?php echo $html->getPakiti()->getManager("HostsManager")->getHostsCount(); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white shadow rounded-lg border border-slate-200 p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-brand-100 rounded-lg p-3">
                                        <i data-feather="layers" class="w-5 h-5 text-brand-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-slate-500">Host groups</p>
                                        <p class="text-2xl font-bold text-slate-900"><?php echo $html->getPakiti()->getManager("HostGroupsManager")->getHostGroupsCount(); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white shadow rounded-lg border border-slate-200 p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-brand-100 rounded-lg p-3">
                                        <i data-feather="file-text" class="w-5 h-5 text-brand-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-slate-500">Number of reports</p>
                                        <p class="text-2xl font-bold text-slate-900"><?php echo $html->getPakiti()->getManager("ReportsManager")->getHostReportsCount(); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white shadow rounded-lg border border-slate-200 p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                                        <i data-feather="alert-triangle" class="w-5 h-5 text-red-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-slate-500">Number of CVEs</p>
                                        <p class="text-2xl font-bold text-slate-900"><?php echo sizeof($html->getPakiti()->getManager("CvesManager")->getCvesNames()); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white shadow rounded-lg border border-slate-200 p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-brand-100 rounded-lg p-3">
                                        <i data-feather="package" class="w-5 h-5 text-brand-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-slate-500">Number of packages</p>
                                        <p class="text-2xl font-bold text-slate-900"><?php echo $html->getPakiti()->getManager("PkgsManager")->getPkgsCount(); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white shadow rounded-lg border border-slate-200 p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-brand-100 rounded-lg p-3">
                                        <i data-feather="tag" class="w-5 h-5 text-brand-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-slate-500">CVE tags</p>
                                        <p class="text-2xl font-bold text-slate-900"><?php echo $html->getPakiti()->getManager("CveTagsManager")->getCveTagsCount(); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white shadow rounded-lg border border-slate-200 p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-brand-100 rounded-lg p-3">
                                        <i data-feather="shield" class="w-5 h-5 text-brand-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-slate-500">CVE exceptions</p>
                                        <p class="text-2xl font-bold text-slate-900"><?php echo $html->getPakiti()->getManager("CveExceptionsManager")->getCveExceptionsCountByCveName(); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cumulative Statistics -->
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-slate-900 mb-4">Cumulative statistics</h2>
                        <div class="bg-white shadow rounded-lg border border-slate-200">
                            <div class="divide-y divide-slate-200">
                                <div class="p-5 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                                            <i data-feather="check-circle" class="w-5 h-5 text-green-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-slate-500">Total reports stored</p>
                                            <p class="text-xs text-slate-400">Reports successfully stored in database</p>
                                        </div>
                                    </div>
                                    <p class="text-xl font-bold text-slate-900"><?php echo $savedReportsValue; ?></p>
                                </div>
                                <div class="p-5 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                                            <i data-feather="x-circle" class="w-5 h-5 text-red-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-slate-500">Total reports not stored</p>
                                            <p class="text-xs text-slate-400">Reports that failed to store in database</p>
                                        </div>
                                    </div>
                                    <p class="text-xl font-bold text-slate-900"><?php echo $unsavedReportsValue; ?></p>
                                </div>
                                <div class="p-5 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-brand-100 rounded-lg p-3">
                                            <i data-feather="box" class="w-5 h-5 text-brand-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-slate-500">Total checked packages</p>
                                            <p class="text-xs text-slate-400">All received packages in all reports</p>
                                        </div>
                                    </div>
                                    <p class="text-xl font-bold text-slate-900"><?php echo $checkedPkgsValue; ?></p>
                                </div>
                                <div class="p-5 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-brand-100 rounded-lg p-3">
                                            <i data-feather="refresh-cw" class="w-5 h-5 text-brand-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-slate-500">Unchanged reports</p>
                                            <p class="text-xs text-slate-400">Reports when host did not send new data</p>
                                        </div>
                                    </div>
                                    <p class="text-xl font-bold text-slate-900"><?php echo $sameReportsValue; ?></p>
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
