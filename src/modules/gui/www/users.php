<?php
require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

$html = new HtmlModule($pakiti);
$html->checkPermission("users");

// Process operations
switch ($html->getHttpPostVar("act")) {
    case "store":
        if (Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_AUTOCREATE || Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_IMPORT) {
            $html->setError("Cannot create users in " . Config::$AUTHZ_MODE . "mode");
            break;
        }
        $user = new User();
        $user->setUid($html->getHttpPostVar("uid"));
        $user->setName($html->getHttpPostVar("name"));
        $user->setEmail($html->getHttpPostVar("email"));
        $pakiti->getManager("UsersManager")->storeUser($user);
        break;
    case "update":
        if (Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_IMPORT) {
            $html->setError("Cannot edit users in " . Config::$AUTHZ_MODE . "mode");
            break;
        }
        $id = $html->getHttpPostVar("id");
        $admin = $html->getHttpPostVar("admin");
        $user = $pakiti->getManager("UsersManager")->getUserById($id);
        $user->setAdmin($admin == "true");
        $pakiti->getManager("UsersManager")->storeUser($user);
        break;
    case "delete":
        if (Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_IMPORT) {
            $html->setError("Cannot delete users in " . Config::$AUTHZ_MODE . "mode");
            break;
        }
        $id = $html->getHttpPostVar("id");
        $pakiti->getManager("UsersManager")->deleteUser($id);
        break;
    case "add":
        $id = $html->getHttpPostVar("id");
        $hostId = $html->getHttpPostVar("hostId");
        $hostGroupId = $html->getHttpPostVar("hostGroupId");
        if ($hostId != -1) {
            $pakiti->getManager("UsersManager")->assignHostToUser($id, $hostId);
        }
        if ($hostGroupId != -1) {
            $pakiti->getManager("UsersManager")->assignHostGroupToUser($id, $hostGroupId);
        }
        break;
    case "remove":
        $id = $html->getHttpPostVar("id");
        $hostId = $html->getHttpPostVar("hostId");
        $hostGroupId = $html->getHttpPostVar("hostGroupId");
        if ($hostId != -1) {
            $pakiti->getManager("UsersManager")->unassignHostToUser($id, $hostId);
        }
        if ($hostGroupId != -1) {
            $pakiti->getManager("UsersManager")->unassignHostGroupToUser($id, $hostGroupId);
        }
        break;
}

$html->setTitle("User management");
$html->setMenuActiveItem("users.php");

$users = $pakiti->getManager("UsersManager")->getUsers();
$hosts = $pakiti->getManager("HostsManager")->getHosts();
$hostGroups = $pakiti->getManager("HostGroupsManager")->getHostGroups();

$showAddModal = isset($_GET['add']) && $_GET['add'] == 'true';
$editUserId = isset($_GET['edit']) ? (int)$_GET['edit'] : -1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | Pakiti</title>
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
                <a href="users.php" class="bg-brand-50 text-brand-700 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-brand-600">
                    <i data-feather="users" class="flex-shrink-0 h-5 w-5 text-brand-600"></i>
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
                <div class="flex-1 flex items-center justify-between">
                    <?php if (Config::$AUTHZ_MODE != Constants::$AUTHZ_MODE_AUTOCREATE && Config::$AUTHZ_MODE != Constants::$AUTHZ_MODE_IMPORT) { ?>
                        <button type="button" onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <i data-feather="user-plus" class="w-4 h-4 mr-2"></i> Add user
                        </button>
                    <?php } else { ?>
                        <div></div>
                    <?php } ?>
                </div>
            </header>
            <main class="flex-1 relative overflow-y-auto focus:outline-none py-6">
                <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Table -->
                    <div class="bg-white shadow overflow-hidden rounded-lg border border-slate-200 mb-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-xs">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">UID</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Email</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Timestamp</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Admin</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Hosts</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Host Groups</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <?php foreach ($users as $user) {
                                        $userHosts = $pakiti->getManager("HostsManager")->getHosts(null, null, -1, -1, null, null, null, -1, null, -1, $user->getId(), true);
                                        $userHostGroups = $pakiti->getManager("HostGroupsManager")->getHostGroups(null, -1, -1, $user->getId());
                                    ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-900"><?php echo $user->getUid(); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-slate-700"><?php echo $user->getName(); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-slate-500"><?php echo $user->getEmail(); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-slate-400 text-[10px]"><?php echo $user->getTimestamp(); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <?php if (Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_IMPORT) { ?>
                                                <input type="checkbox"<?php if ($user->isAdmin()) echo ' checked'; ?> disabled class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                            <?php } else { ?>
                                                <input type="checkbox" onclick="document.form.act.value='update'; document.form.id.value='<?php echo $user->getId(); ?>'; document.form.admin.value=this.checked; document.form.submit();"<?php if ($user->isAdmin()) echo ' checked'; ?> class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                            <?php } ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-1">
                                                <?php foreach ($userHosts as $userHost) { ?>
                                                    <div class="inline-flex items-center gap-1 bg-slate-100 rounded px-2 py-1 text-xs">
                                                        <a href="host.php?hostId=<?php echo $userHost->getId(); ?>" class="text-slate-700 hover:text-brand-600"><?php echo $userHost->getHostname(); ?></a>
                                                        <?php if (Config::$AUTHZ_MODE != Constants::$AUTHZ_MODE_IMPORT) { ?>
                                                            <button type="button" onclick="removePermission(<?php echo $user->getId(); ?>, <?php echo $userHost->getId(); ?>, -1)" class="text-red-500 hover:text-red-700 ml-1">
                                                                <i data-feather="x" class="w-3 h-3"></i>
                                                            </button>
                                                        <?php } ?>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-1">
                                                <?php foreach ($userHostGroups as $userHostGroup) { ?>
                                                    <div class="inline-flex items-center gap-1 bg-slate-100 rounded px-2 py-1 text-xs">
                                                        <a href="hosts.php?hostGroupId=<?php echo $userHostGroup->getId(); ?>" class="text-slate-700 hover:text-brand-600"><?php echo $userHostGroup->getName(); ?></a>
                                                        <?php if (Config::$AUTHZ_MODE != Constants::$AUTHZ_MODE_IMPORT) { ?>
                                                            <button type="button" onclick="removePermission(<?php echo $user->getId(); ?>, -1, <?php echo $userHostGroup->getId(); ?>)" class="text-red-500 hover:text-red-700 ml-1">
                                                                <i data-feather="x" class="w-3 h-3"></i>
                                                            </button>
                                                        <?php } ?>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <div class="flex justify-end gap-2">
                                                <button type="button" onclick="openEditModal(<?php echo $user->getId(); ?>, '<?php echo htmlspecialchars($user->getUid()); ?>')" class="text-brand-600 hover:text-brand-700">
                                                    <i data-feather="edit-2" class="w-4 h-4"></i>
                                                </button>
                                                <?php if (Config::$AUTHZ_MODE != Constants::$AUTHZ_MODE_IMPORT) { ?>
                                                    <button type="button" onclick="confirmDelete(<?php echo $user->getId(); ?>, '<?php echo htmlspecialchars($user->getUid()); ?>')" class="text-red-500 hover:text-red-700">
                                                        <i data-feather="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                <?php } ?>
                                            </div>
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
                        <input type="hidden" name="admin" />
                    </form>

                    <form action="" name="editForm" method="post">
                        <input type="hidden" name="act" value="add">
                        <input type="hidden" name="id">
                        <input type="hidden" name="hostGroupId" value="-1">
                        <input type="hidden" name="hostId" value="-1">
                    </form>

                    <!-- Delete Confirmation Modal -->
                    <div id="delete-modal" class="fixed inset-0 bg-slate-900 bg-opacity-50 z-50 hidden flex items-center justify-center">
                        <div class="bg-white rounded-lg shadow-xl max-w-sm w-full mx-4">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-slate-900 mb-2">Delete user?</h3>
                                <p class="text-sm text-slate-500 mb-6">Are you sure you want to delete this user?</p>
                                <div class="flex justify-end gap-3">
                                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none">Cancel</button>
                                    <button type="button" onclick="confirmDeleteAction()" class="px-4 py-2 bg-red-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700 focus:outline-none">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add User Modal -->
                    <div id="add-modal" class="fixed inset-0 bg-slate-900 bg-opacity-50 z-50 hidden flex items-center justify-center">
                        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                            <div class="px-6 py-4 border-b border-slate-200">
                                <h3 class="text-lg font-medium text-slate-900">Add user</h3>
                            </div>
                            <form name="addUserForm" method="post" class="p-6">
                                <input type="hidden" name="act" value="store">
                                <div class="space-y-4">
                                    <div>
                                        <label for="uid" class="block text-sm font-medium text-slate-700 mb-1">UID</label>
                                        <input type="text" class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="uid" id="uid">
                                    </div>
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                                        <input type="text" class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="name" id="name">
                                    </div>
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                                        <input type="text" class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="email" id="email">
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 mt-6">
                                    <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none">Cancel</button>
                                    <button type="submit" class="px-4 py-2 bg-brand-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-brand-700 focus:outline-none">Add</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Edit Permissions Modal -->
                    <div id="edit-modal" class="fixed inset-0 bg-slate-900 bg-opacity-50 z-50 hidden flex items-center justify-center">
                        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                            <div class="px-6 py-4 border-b border-slate-200">
                                <h3 class="text-lg font-medium text-slate-900">Edit permissions</h3>
                            </div>
                            <form name="editPermsForm" method="post" class="p-6">
                                <input type="hidden" name="act" value="add">
                                <input type="hidden" name="id" id="edit-id">
                                <div class="space-y-4">
                                    <div>
                                        <label for="edit-uid" class="block text-sm font-medium text-slate-700 mb-1">UID</label>
                                        <input type="text" class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none bg-slate-100" name="uid" id="edit-uid" disabled>
                                    </div>
                                    <div>
                                        <label for="hostId" class="block text-sm font-medium text-slate-700 mb-1">Host</label>
                                        <select class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none bg-white" name="hostId" id="hostId">
                                            <option value="-1" selected></option>
                                            <?php foreach ($hosts as $host) { ?>
                                                <option value="<?php echo $host->getId(); ?>"><?php echo $host->getHostname(); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="hostGroupId" class="block text-sm font-medium text-slate-700 mb-1">Host Group</label>
                                        <select class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none bg-white" name="hostGroupId" id="hostGroupId">
                                            <option value="-1" selected></option>
                                            <?php foreach ($hostGroups as $hostGroup) { ?>
                                                <option value="<?php echo $hostGroup->getId(); ?>"><?php echo $hostGroup->getName(); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 mt-6">
                                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none">Cancel</button>
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
        function openEditModal(id, uid) {
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-uid').value = uid;
            document.getElementById('edit-modal').classList.remove('hidden');
        }
        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
        }
        function removePermission(id, hostId, hostGroupId) {
            document.forms.editForm.id.value = id;
            document.forms.editForm.hostId.value = hostId;
            document.forms.editForm.hostGroupId.value = hostGroupId;
            document.forms.editForm.submit();
        }
        function confirmDelete(id, uid) {
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
        <?php if ($editUserId > 0) {
            $editUser = $pakiti->getManager("UsersManager")->getUserById($editUserId);
            if ($editUser) { ?>
                openEditModal(<?php echo $editUser->getId(); ?>, '<?php echo htmlspecialchars($editUser->getUid()); ?>');
        <?php } } ?>
    </script>
</body>
</html>