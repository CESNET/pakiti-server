<?php
require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

$html = new HtmlModule($pakiti);
$html->checkPermission("exceptions");

// Process operations
switch ($html->getHttpPostVar("act")) {
    case "create":
        $cveName = $html->getHttpGetVar("cveName");
        $reason = $html->getHttpPostVar("reason");
        $pkgs = $html->getHttpPostVar("pkgs");
        if (!empty($pkgs)) {
            foreach ($pkgs as $pkg) {
                @list ($pkgId, $osGroupId) = explode(' ', $pkg);
                $exception = new CveException();
                $exception->setCveName($cveName);
                $exception->setPkgId($pkgId);
                $exception->setOsGroupId($osGroupId);
                $exception->setReason($reason);
                $exception->setModifier($html->getUsername());
                $pakiti->getManager("CveExceptionsManager")->storeCveException($exception);
            }
        } else {
            $html->setError("No selected packages");
        }
        break;
    case "delete":
        $id = $html->getHttpPostVar("id");
        $cveTag = $pakiti->getManager("CveExceptionsManager")->getCveExceptionById($id);
        if ($cveTag != null) {
            $pakiti->getManager("CveExceptionsManager")->removeCveExceptionById($id);
        } else {
            $html->setError("CveEexception [" . $id . "] doesn't exists");
        }
        break;
}

$html->setTitle("CVE Exceptions");
$html->setMenuActiveItem("exceptions.php");

$selectedCveName = $html->getHttpGetVar("cveName", null);

// Check if entered CVE exists in database
$_cveExists = null;
$_cveMessage = '';
if ($selectedCveName != null && $selectedCveName != '') {
    $_cveExists = $pakiti->getManager("CvesManager")->cveExists($selectedCveName);
    if ($_cveExists) {
        $_cveMessage = '<svg xmlns="http://www.w3.org/2000/svg" class="inline w-3 h-3 mr-1 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg><span class="text-emerald-600">CVE exists in database</span>';
    } else {
        $_cveMessage = '<svg xmlns="http://www.w3.org/2000/svg" class="inline w-3 h-3 mr-1 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg><span class="text-red-600">CVE not found in database</span>';
    }
}

$osGroups = $pakiti->getManager("OsGroupsManager")->getOsGroups();
$cveExceptions = $pakiti->getManager("CveExceptionsManager")->getCveExceptionsByCveName($selectedCveName);

// Add modal state
$showAddModal = isset($_GET['add']) && $_GET['add'] == 'true';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CVE Exceptions | Pakiti</title>
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
                <a href="exceptions.php" class="bg-brand-50 text-brand-700 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-brand-600">
                    <i data-feather="slash" class="flex-shrink-0 h-5 w-5 text-brand-600"></i>
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
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                        <div class="flex items-center gap-4 flex-wrap">
                            <h2 class="text-xl font-bold text-slate-900">CVE Exceptions</h2>
                            <div class="w-64">
                                <form method="get">
                                    <label for="cveName" class="block text-xs font-medium text-slate-500 mb-1">CVE</label>
                                    <input type="text" class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="cveName" id="cveName" value="<?php if ($selectedCveName != null) echo $selectedCveName; ?>" onchange="this.form.submit();"/>
                                    <?php if ($_cveMessage != ''): ?>
                                        <p class="text-xs" style="margin-top: 5px;"><?php echo $_cveMessage; ?></p>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                        <?php if($selectedCveName != null) { ?>
                            <button type="button" onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <i data-feather="plus" class="w-4 h-4 mr-2"></i> Add CVE exception
                            </button>
                        <?php } ?>
                    </div>

                    <!-- Total count badge -->
                    <div class="mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-brand-100 text-brand-800">
                            <i data-feather="shield" class="w-4 h-4 mr-1"></i>
                            <?php echo sizeof($cveExceptions); ?> exception<?php if(sizeof($cveExceptions) != 1) echo 's'; ?>
                        </span>
                    </div>

                    <!-- Table -->
                    <div class="bg-white shadow overflow-hidden rounded-lg border border-slate-200 mb-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-xs">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">CVE</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Package</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Reason</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Modifier</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Timestamp</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider w-16">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <?php foreach ($cveExceptions as $cveException) {
                                        $pkg = $pakiti->getManager("PkgsManager")->getPkgById($cveException->getPkgId());
                                        $osGroup = $pakiti->getManager("OsGroupsManager")->getOsGroupById($cveException->getOsGroupId());
                                    ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a href="cve.php?cveName=<?php echo $cveException->getCveName(); ?>" class="font-medium text-brand-600 hover:underline"><?php echo $cveException->getCveName(); ?></a>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-slate-600"><?php echo $pkg->getName() . " " . $pkg->getVersionRelease(); ?></div>
                                            <div class="text-slate-400 text-[10px] italic">(<?php echo $pkg->getArchName(); ?>) [<?php echo $pkg->getPkgTypeName(); ?>] <?php echo $osGroup->getName(); ?></div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-500"><?php echo $cveException->getReason(); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-slate-500"><?php echo $cveException->getModifier(); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-slate-400 text-[10px]"><?php echo $cveException->getTimestamp(); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <button type="button" class="text-red-500 hover:text-red-700" onclick="confirmDelete(<?php echo $cveException->getId(); ?>)">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Hidden forms -->
                    <form action="" name="form" method="post">
                        <input type="hidden" name="act" />
                        <input type="hidden" name="id" />
                    </form>

                    <!-- Delete Confirmation Modal -->
                    <div id="delete-modal" class="fixed inset-0 bg-slate-900 bg-opacity-50 z-50 hidden flex items-center justify-center">
                        <div class="bg-white rounded-lg shadow-xl max-w-sm w-full mx-4">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-slate-900 mb-2">Delete CVE exception?</h3>
                                <p class="text-sm text-slate-500 mb-6">Are you sure you want to delete this CVE exception?</p>
                                <div class="flex justify-end gap-3">
                                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none">Cancel</button>
                                    <button type="button" onclick="confirmDeleteAction()" class="px-4 py-2 bg-red-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700 focus:outline-none">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add CVE Exception Modal -->
                    <div id="add-modal" class="fixed inset-0 bg-slate-900 bg-opacity-50 z-50 hidden flex items-center justify-center">
                        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                            <div class="px-6 py-4 border-b border-slate-200">
                                <h3 class="text-lg font-medium text-slate-900">Add CVE exception</h3>
                            </div>
                            <form name="addForm" method="post" class="p-6">
                                <input type="hidden" name="act" value="create">
                                <div class="space-y-4">
                                    <div>
                                        <label for="cveName" class="block text-sm font-medium text-slate-700 mb-1">CVE</label>
                                        <input type="text" class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none bg-slate-100" name="cveName" id="cveName" value="<?php echo $selectedCveName; ?>" disabled>
                                    </div>
                                    <div>
                                        <label for="reason" class="block text-sm font-medium text-slate-700 mb-1">Reason</label>
                                        <input type="text" class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="reason" id="reason">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Packages</label>
                                        <div class="border border-slate-200 rounded-md p-4 max-h-60 overflow-y-auto bg-slate-50 space-y-2">
                                            <?php foreach ($osGroups as $osGroup) { ?>
                                                <?php $pkgs = $pakiti->getManager("PkgsManager")->getPkgsByCveNameAndOsGroupId($selectedCveName, $osGroup->getId()); ?>
                                                <?php if (!empty($pkgs)) { ?>
                                                    <div class="border-b border-slate-200 pb-2 mb-2 last:border-0">
                                                        <div class="text-xs font-medium text-slate-500 mb-2"><?php echo $osGroup->getName(); ?></div>
                                                        <?php foreach ($pkgs as $pkg) { ?>
                                                            <label class="flex items-start space-x-2 cursor-pointer hover:bg-white p-1 rounded">
                                                                <input type="checkbox" name="pkgs[]" value="<?php echo $pkg->getId() . ' ' . $osGroup->getId(); ?>" class="mt-0.5 focus:ring-brand-500">
                                                                <span class="text-xs text-slate-600"><?php echo $pkg->getName() . " " . $pkg->getVersionRelease() . " (" . $pkg->getArchName() . ") [" . $pkg->getPkgTypeName() . "]"; ?></span>
                                                            </label>
                                                        <?php } ?>
                                                    </div>
                                                <?php } ?>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 mt-6 sticky bottom-0 bg-white py-4 border-t border-slate-200">
                                    <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none">Cancel</button>
                                    <button type="submit" class="px-4 py-2 bg-brand-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-brand-700 focus:outline-none">Add</button>
                                </div>
                            </form>
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
        let deleteId = null;
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
        function openAddModal() {
            document.getElementById('add-modal').classList.remove('hidden');
        }
        function closeAddModal() {
            document.getElementById('add-modal').classList.add('hidden');
        }
        function confirmDelete(id) {
            deleteId = id;
            document.getElementById('delete-modal').classList.remove('hidden');
        }
        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.add('hidden');
            deleteId = null;
        }
        function confirmDeleteAction() {
            document.forms.form.act.value = "delete";
            document.forms.form.id.value = deleteId;
            document.forms.form.submit();
        }
        <?php if ($showAddModal) { ?>
            openAddModal();
        <?php } ?>
    </script>
</body>
</html>
