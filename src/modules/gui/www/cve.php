<?php
require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

$html = new HtmlModule($pakiti);
$html->checkPermission("cve");

$html->setTitle("CVE");

$cveName = $html->getHttpGetVar("cveName", null);
$vulnerabilities = $html->getPakiti()->getManager("VulnerabilitiesManager")->getVulnerabilities($cveName);
$exceptionsCount = $html->getPakiti()->getManager("CveExceptionsManager")->getCveExceptionsCountByCveName($cveName);

// Get unique OsGroups for filter dropdown
$osGroups = array();
foreach ($vulnerabilities as $vulnerability) {
    $osGroups[$vulnerability->getOsGroupName()] = true;
}
ksort($osGroups);

$vds_defs_list = $html->getPakiti()->getManager("CvesManager")->getSubSourceDefNames($cveName);
$vds_defs = join(", ", $vds_defs_list);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CVE | Pakiti</title>
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
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- CVE Header -->
                    <div class="bg-white shadow rounded-lg border border-slate-200 p-6 mb-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <div class="flex items-center gap-3">
                                    <h1 class="text-2xl font-bold text-slate-900"><?php echo $cveName; ?></h1>
                                    <a href="exceptions.php?cveName=<?php echo $cveName; ?>" class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-brand-100 text-brand-700 hover:bg-brand-200">
                                        <i data-feather="shield" class="w-3 h-3 mr-1"></i>
                                        <?php echo $exceptionsCount; ?> exception<?php if($exceptionsCount!= 1) echo 's'; ?>
                                    </a>
                                </div>
                                <p class="text-slate-500 text-sm mt-2">Defined by: <?php echo $vds_defs; ?> <a href="vds.php" class="text-brand-600 hover:underline">(See VDS)</a></p>
                            </div>
                            <div class="flex gap-2">
                                <a href="vulnerabilities.php" class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50 inline-flex items-center">
                                    <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i> Back
                                </a>
                            </div>
                        </div>

                        <!-- External Links -->
                        <?php if(preg_match("/^CVE-(.*)-(.*)$/", $cveName, $values) === 1) { ?>
                            <div class="border-t border-slate-200 pt-4">
                                <h3 class="text-sm font-medium text-slate-700 mb-3">External Resources</h3>
                                <div class="flex flex-wrap gap-2">
                                    <a href="https://bugzilla.redhat.com/show_bug.cgi?id=<?php echo $cveName; ?>" target="_blank" class="inline-flex items-center px-3 py-2 border border-slate-300 rounded-md text-sm text-slate-700 hover:bg-slate-50">
                                        <i data-feather="external-link" class="w-4 h-4 mr-2"></i> RedHat Bugzilla
                                    </a>
                                    <a href="https://security-tracker.debian.org/tracker/<?php echo $cveName; ?>" target="_blank" class="inline-flex items-center px-3 py-2 border border-slate-300 rounded-md text-sm text-slate-700 hover:bg-slate-50">
                                        <i data-feather="external-link" class="w-4 h-4 mr-2"></i> Debian Security Tracker
                                    </a>
                                    <a href="https://www.suse.com/security/cve/<?php echo $cveName; ?>/" target="_blank" class="inline-flex items-center px-3 py-2 border border-slate-300 rounded-md text-sm text-slate-700 hover:bg-slate-50">
                                        <i data-feather="external-link" class="w-4 h-4 mr-2"></i> SUSE Security
                                    </a>
                                    <a href="https://people.canonical.com/~ubuntu-security/cve/<?php echo $values[1]; ?>/<?php echo $cveName; ?>.html" target="_blank" class="inline-flex items-center px-3 py-2 border border-slate-300 rounded-md text-sm text-slate-700 hover:bg-slate-50">
                                        <i data-feather="external-link" class="w-4 h-4 mr-2"></i> Ubuntu Security Tracker
                                    </a>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Vulnerabilities Table -->
                    <div class="bg-white shadow overflow-hidden rounded-lg border border-slate-200">
                        <div class="px-6 py-4 border-b border-slate-200">
                            <h2 class="text-lg font-medium text-slate-900">Affected Packages</h2>
                        </div>

                        <!-- Filter Section -->
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Name</label>
                                    <input type="text" id="filterName" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent" placeholder="Filter by name..." onkeyup="filterTable()">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Version</label>
                                    <input type="text" id="filterVersion" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent" placeholder="Filter by version..." onkeyup="filterTable()">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">OS Group</label>
                                    <select id="filterOsGroup" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent" onchange="filterTable()">
                                        <option value="">-- All --</option>
                                        <?php foreach (array_keys($osGroups) as $osGroup) { ?>
                                            <option value="<?php echo htmlspecialchars($osGroup); ?>"><?php echo htmlspecialchars($osGroup); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">&nbsp;</label>
                                    <button type="button" onclick="clearFilters()" class="w-full px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                        <i data-feather="x" class="w-4 h-4 inline mr-1"></i> Clear Filters
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-xs" id="vulnerabilitiesTable">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Version</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Architecture</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">OS Group</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <?php foreach ($vulnerabilities as $vulnerability) { ?>
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-900"><?php echo htmlspecialchars($vulnerability->getName()); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs <?php echo ($vulnerability->getOperator() == 'lt') ? 'bg-red-100 text-red-800' : 'bg-brand-100 text-brand-800'; ?>">
                                                    <?php echo htmlspecialchars($vulnerability->getOperator() . ' ' . $vulnerability->getVersionRelease()); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-slate-500"><?php echo htmlspecialchars($vulnerability->getArchName()); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-slate-600"><?php echo htmlspecialchars($vulnerability->getOsGroupName()); ?></td>
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

        function filterTable() {
            var nameFilter = document.getElementById('filterName').value.toLowerCase();
            var versionFilter = document.getElementById('filterVersion').value.toLowerCase();
            var osGroupFilter = document.getElementById('filterOsGroup').value.toLowerCase();
            var table = document.getElementById('vulnerabilitiesTable');
            var rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (var i = 0; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName('td');
                var name = cells[0].textContent.toLowerCase();
                var version = cells[1].textContent.toLowerCase();
                var osGroup = cells[3].textContent.toLowerCase();

                var showRow = true;

                if (nameFilter && name.indexOf(nameFilter) === -1) {
                    showRow = false;
                }
                if (versionFilter && version.indexOf(versionFilter) === -1) {
                    showRow = false;
                }
                if (osGroupFilter && osGroup.indexOf(osGroupFilter) === -1) {
                    showRow = false;
                }

                cells[0].style.display = showRow ? '' : 'none';
                cells[1].style.display = showRow ? '' : 'none';
                cells[2].style.display = showRow ? '' : 'none';
                cells[3].style.display = showRow ? '' : 'none';
            }
        }

        function clearFilters() {
            document.getElementById('filterName').value = '';
            document.getElementById('filterVersion').value = '';
            document.getElementById('filterOsGroup').value = '';
            filterTable();
        }
    </script>
</body>
</html>