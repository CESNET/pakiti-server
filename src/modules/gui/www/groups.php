<?php
require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

$html = new HtmlModule($pakiti);
$html->checkPermission("groups");

// Process operations
switch ($html->getHttpPostVar("act")) {
  case "edit":
    $hostGroupId = $html->getHttpPostVar("id");
    $hostGroup = $pakiti->getManager("HostGroupsManager")->getHostGroupById($hostGroupId, $html->getUserId());
    if ($hostGroup != null) {
        $hostGroup->setUrl($html->getHttpPostVar("url"));
        $hostGroup->setContact($html->getHttpPostVar("contact"));
        $hostGroup->setNote($html->getHttpPostVar("note"));
        $pakiti->getManager("HostGroupsManager")->storeHostGroup($hostGroup);
    } else {
        $html->setError("Cannot update hostGroup, hostGroup with id " . $hostGroupId . " doesn't exist or access denied");
    }
    break;
}

$html->setTitle("Host groups");
$html->setMenuActiveItem("groups.php");
$html->setDefaultSorting("name");
$html->setNumOfEntities($html->getPakiti()->getManager("HostGroupsManager")->getHostGroupsCount($html->getUserId()));

$hostgroups = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroups($html->getSortBy(), $html->getPageSize(), $html->getPageNum(), $html->getUserId());

// Edit data for modal
$editGroupId = isset($_GET['edit']) ? (int)$_GET['edit'] : -1;
$editHostGroup = null;
if ($editGroupId > 0) {
    $editHostGroup = $pakiti->getManager("HostGroupsManager")->getHostGroupById($editGroupId, $html->getUserId());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host Groups | Pakiti</title>
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
                <a href="groups.php" class="bg-brand-50 text-brand-700 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-brand-600">
                    <i data-feather="layers" class="flex-shrink-0 h-5 w-5 text-brand-600"></i>
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
                    <?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

                    <!-- Table -->
                    <div class="bg-white shadow overflow-hidden rounded-lg border border-slate-200 mb-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-xs">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                            <a href="?sortBy=name<?php echo $html->getQueryString(array("sortByDirection" => $html->getSortBy() == "name" ? ($html->getSortByDirection() == "asc" ? "desc" : "asc") : "asc")); ?>" class="hover:text-brand-600">Name <?php if ($html->getSortBy() == "name") echo ($html->getSortByDirection() == "asc" ? "▲" : "▼"); ?></a>
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">#Hosts</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">#Vulnerable</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">URL</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Contact</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Note</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider w-20">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <?php foreach ($hostgroups as $hostgroup) {
                                        $hostsCount = $html->getPakiti()->getManager("HostsManager")->getHostsCount(null, null, null, $hostgroup->getId(), null, -1, $html->getUserId());
                                        $taggedHostsCount = $html->getPakiti()->getManager("HostsManager")->getHostsCount(null, null, true, $hostgroup->getId(), null, -1, $html->getUserId());
                                    ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-900"><?php echo $hostgroup->getName(); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a href="hosts.php?hostGroupId=<?php echo $hostgroup->getId(); ?>" class="text-slate-600 hover:text-brand-600"><?php echo $hostsCount; ?></a>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a href="hosts.php?hostGroupId=<?php echo $hostgroup->getId(); ?>&tag=true" class="<?php echo ($taggedHostsCount > 0 ? 'text-red-600 font-medium' : 'text-slate-600'); ?> hover:text-brand-600"><?php echo $taggedHostsCount; ?></a>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-slate-500 truncate max-w-xs"><?php echo $hostgroup->getUrl(); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-slate-500 truncate max-w-xs"><?php echo $hostgroup->getContact(); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-slate-500 truncate max-w-xs"><?php echo $hostgroup->getNote(); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <?php if ($hostgroup->getUrl() || $hostgroup->getContact() || $hostgroup->getNote()) { ?>
                                                <button type="button" class="text-brand-600 hover:text-brand-700 font-medium" onclick="openEditModal(<?php echo $hostgroup->getId(); ?>, '<?php echo htmlspecialchars($hostgroup->getName()); ?>', '<?php echo htmlspecialchars($hostgroup->getUrl()); ?>', '<?php echo htmlspecialchars($hostgroup->getContact()); ?>', '<?php echo htmlspecialchars($hostgroup->getNote()); ?>')">
                                                    <i data-feather="edit-2" class="w-4 h-4"></i>
                                                </button>
                                            <?php } else { ?>
                                                <span class="text-slate-300"><i data-feather="minus" class="w-4 h-4"></i></span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

                    <!-- Hidden form for delete -->
                    <form action="" name="form" method="post">
                        <input type="hidden" name="act" />
                        <input type="hidden" name="id" />
                    </form>

                    <!-- Edit Modal -->
                    <div id="edit-modal" class="fixed inset-0 bg-slate-900 bg-opacity-50 z-50 hidden flex items-center justify-center">
                        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                            <div class="px-6 py-4 border-b border-slate-200">
                                <h3 class="text-lg font-medium text-slate-900">Edit host group</h3>
                            </div>
                            <form name="editForm" method="post" class="p-6">
                                <input type="hidden" name="act" value="edit">
                                <input type="hidden" name="id" id="edit-id">
                                <div class="space-y-4">
                                    <div>
                                        <label for="edit-name" class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                                        <input type="text" class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none bg-slate-100" id="edit-name" disabled>
                                    </div>
                                    <div>
                                        <label for="edit-url" class="block text-sm font-medium text-slate-700 mb-1">URL</label>
                                        <input type="text" class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none" id="edit-url" name="url">
                                    </div>
                                    <div>
                                        <label for="edit-contact" class="block text-sm font-medium text-slate-700 mb-1">Contact</label>
                                        <input type="text" class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none" id="edit-contact" name="contact">
                                    </div>
                                    <div>
                                        <label for="edit-note" class="block text-sm font-medium text-slate-700 mb-1">Note</label>
                                        <input type="text" class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none" id="edit-note" name="note">
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 mt-6">
                                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-500">Cancel</button>
                                    <button type="submit" class="px-4 py-2 bg-brand-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500">Save</button>
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
        function openEditModal(id, name, url, contact, note) {
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-url').value = url;
            document.getElementById('edit-contact').value = contact;
            document.getElementById('edit-note').value = note;
            document.getElementById('edit-modal').classList.remove('hidden');
        }
        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
        }
        <?php if ($editGroupId > 0 && $editHostGroup) { ?>
            openEditModal(<?php echo $editHostGroup->getId(); ?>, '<?php echo htmlspecialchars($editHostGroup->getName()); ?>', '<?php echo htmlspecialchars($editHostGroup->getUrl()); ?>', '<?php echo htmlspecialchars($editHostGroup->getContact()); ?>', '<?php echo htmlspecialchars($editHostGroup->getNote()); ?>');
        <?php } ?>
    </script>
</body>
</html>