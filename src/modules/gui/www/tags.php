<?php
require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

$html = new HtmlModule($pakiti);
$html->checkPermission("tags");

// AJAX endpoint: check if a CVE name exists (used by the add-tag modal)
if ($html->getHttpPostVar("act") == "checkCve") {
    header('Content-Type: application/json');
    $cveName = trim($html->getHttpPostVar("cveName", ""));
    $exists = false;
    $tagId = -1;
    if (preg_match('/^CVE-\d{4}-\d{4,}$/i', $cveName)) {
        $exists = ($pakiti->getDao("Cve")->getIdByName($cveName) != -1);
        $tagId = $pakiti->getManager("CveTagsManager")->getCveTagIdByCveNameTagName(
            $cveName, $html->getHttpPostVar("tagName", ""));
    }
    echo json_encode(array("exists" => $exists, "tagId" => $tagId));
    exit;
}

// Process operations
switch ($html->getHttpPostVar("act")) {
    case "create":
        $cveName = $html->getHttpPostVar("cveName");
        $tagName = $html->getHttpPostVar("tagName");
        if ($cveName != "N/A") {
            $id = $pakiti->getManager("CveTagsManager")->getCveTagIdByCveNameTagName($cveName, $tagName);
            if ($id == -1) {
                $cveTag = new CveTag();
                $cveTag->setCveName($cveName);
                $cveTag->setTagName($tagName);
                $cveTag->setReason($html->getHttpPostVar("reason"));
                $cveTag->setInfoUrl($html->getHttpPostVar("infoUrl"));
                $cveTag->setModifier($html->getUsername());
                $pakiti->getManager("CveTagsManager")->storeCveTag($cveTag);
            } else {
                $html->setError("Cve [" . $cveName . "] with tag [" . $tagName . "] already exists");
            }
        }
        break;
    case "update":
        $id = $html->getHttpPostVar("id");
        $isAjax = $html->getHttpPostVar("ajax") == "1";
        $error = null;
        $cveTag = $pakiti->getManager("CveTagsManager")->getCveTagById($id);
        if ($cveTag != null) {
            $newTag = $html->getHttpPostVar("tagName");
            if ($newTag !== null && $newTag !== $cveTag->getTagName()) {
                // Re-tag: go straight to the DAO update keyed by id —
                // storeCveTag() would resolve by (cveName, tagName) and insert a duplicate row
                $existingId = $pakiti->getManager("CveTagsManager")->getCveTagIdByCveNameTagName($cveTag->getCveName(), $newTag);
                if ($existingId != -1) {
                    $error = "Cve [" . $cveTag->getCveName() . "] already has the tag [" . $newTag . "]";
                } else {
                    $cveTag->setTagName($newTag);
                    $cveTag->setModifier($html->getUsername());
                    $pakiti->getDao("CveTag")->update($cveTag);
                }
            } else {
                $cveTag->setEnabled($html->getHttpPostVar("enabled") == "true");
                $pakiti->getManager("CveTagsManager")->storeCveTag($cveTag);
            }
        } else {
            $error = "CveTag [" . $id . "] doesn't exists";
        }
        if ($error != null) {
            $html->setError($error);
        }
        if ($isAjax) {
            header('Content-Type: application/json');
            if ($error != null) {
                http_response_code(400);
                echo json_encode(array("ok" => false, "error" => $error));
            } else {
                echo json_encode(array("ok" => true, "tagName" => $cveTag->getTagName()));
            }
            exit;
        }
        break;
    case "delete":
        $id = $html->getHttpPostVar("id");
        $cveTag = $pakiti->getManager("CveTagsManager")->getCveTagById($id);
        if ($cveTag != null) {
            $pakiti->getManager("CveTagsManager")->deleteCveTagById($id);
        } else {
            $html->setError("CveTag [" . $id . "] doesn't exists");
        }
        break;
}

$html->setTitle("CVE Tags");
$html->setMenuActiveItem("tags.php");
$html->setNumOfEntities($html->getPakiti()->getManager("CveTagsManager")->getCveTagsCount());

$cveTags = $html->getPakiti()->getManager("CveTagsManager")->getCveTags($html->getSortBy(), $html->getPageSize(), $html->getPageNum());

// Only load CVEs that already have tags (much faster than loading all 58k+ CVEs)
$tagNames = Config::$TAGS;

// Check if add modal should be open
$showAddModal = isset($_GET['add']) && $_GET['add'] == 'true';

// Badge colors per severity tag
function tagBadgeClasses($tagName) {
    switch ($tagName) {
        case "White": return 'bg-white-100 text-brand-700';
        case 'Blue':  return 'bg-blue-100 text-blue-700';
        case 'Yellow': return 'bg-yellow-100 text-yellow-700';
        case 'Orange': return 'bg-orange-100 text-orange-700';
        case 'Red':    return 'bg-red-100 text-red-600';
        default:       return 'bg-brand-100 text-brand-700';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CVE Tags | Pakiti</title>
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
        .sidebar-collapsed nav a, .sidebar-collapsed #sidebar-header .sidebar-brand, .sidebar-collapsed #sidebar-footer > a { justify-content: center !important; }
        .sidebar-collapsed #sidebar-footer > div { justify-content: center !important; }
        .sidebar-collapsed #sidebar-footer > div .sidebar-text { display: none; }
        .sidebar-collapsed #sidebar-footer > div i { margin-right: 0 !important; }
        .sidebar-collapsed #sidebar-toggle-btn i { transform: rotate(180deg); }
        @media (max-width: 1023px) {
            .sidebar-collapsed { width: 16rem !important; }
            .sidebar-collapsed .sidebar-text { display: block; }
            .sidebar-collapsed nav a, .sidebar-collapsed #sidebar-header .sidebar-brand, .sidebar-collapsed #sidebar-footer > a, .sidebar-collapsed #sidebar-footer > div { justify-content: flex-start !important; }
            .sidebar-collapsed #sidebar-footer > div .sidebar-text { display: inline; }
            .sidebar-collapsed #sidebar-footer > div i { margin-right: 0.5rem !important; }
            .sidebar-collapsed #sidebar-toggle-btn { display: none; }
        }
        .tag-badge { background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='currentColor'%3E%3Cpath fill-rule='evenodd' d='M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 4px center; background-size: 14px 14px; padding-right: 1.4rem; }
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
                <a href="tags.php" class="bg-brand-50 text-brand-700 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-brand-600">
                    <i data-feather="tag" class="flex-shrink-0 h-5 w-5 text-brand-600"></i>
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
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-slate-900">CVE Tags</h2>
                        <button type="button" onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <i data-feather="plus" class="w-4 h-4 mr-2"></i> Add CVE tag
                        </button>
                    </div>

                    <?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

                    <!-- Table -->
                    <div class="bg-white shadow overflow-hidden rounded-lg border border-slate-200 mb-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-xs">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                            <a href="?sortBy=enabled<?php echo $html->getQueryString(array("sortByDirection" => $html->getSortBy() == "enabled" ? ($html->getSortByDirection() == "asc" ? "desc" : "asc") : "asc")); ?>" class="hover:text-brand-600">Enabled <?php if ($html->getSortBy() == "enabled") echo ($html->getSortByDirection() == "asc" ? "▲" : "▼"); ?></a>
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                            <a href="?sortBy=cveName<?php echo $html->getQueryString(array("sortByDirection" => $html->getSortBy() == "cveName" ? ($html->getSortByDirection() == "asc" ? "desc" : "asc") : "asc")); ?>" class="hover:text-brand-600">CVE <?php if ($html->getSortBy() == "cveName") echo ($html->getSortByDirection() == "asc" ? "▲" : "▼"); ?></a>
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                            <a href="?sortBy=tagName<?php echo $html->getQueryString(array("sortByDirection" => $html->getSortBy() == "tagName" ? ($html->getSortByDirection() == "asc" ? "desc" : "asc") : "asc")); ?>" class="hover:text-brand-600">Tag <?php if ($html->getSortBy() == "tagName") echo ($html->getSortByDirection() == "asc" ? "▲" : "▼"); ?></a>
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Reason</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">InfoUrl</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                            <a href="?sortBy=modifier<?php echo $html->getQueryString(array("sortByDirection" => $html->getSortBy() == "modifier" ? ($html->getSortByDirection() == "asc" ? "desc" : "asc") : "asc")); ?>" class="hover:text-brand-600">Modifier <?php if ($html->getSortBy() == "modifier") echo ($html->getSortByDirection() == "asc" ? "▲" : "▼"); ?></a>
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                            <a href="?sortBy=timestamp<?php echo $html->getQueryString(array("sortByDirection" => $html->getSortBy() == "timestamp" ? ($html->getSortByDirection() == "asc" ? "desc" : "asc") : "asc")); ?>" class="hover:text-brand-600">Timestamp <?php if ($html->getSortBy() == "timestamp") echo ($html->getSortByDirection() == "asc" ? "▲" : "▼"); ?></a>
                                        </th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider w-16">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <?php foreach ($cveTags as $cveTag) { ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <input type="checkbox" onclick="document.form.act.value='update'; document.form.id.value='<?php echo $cveTag->getId(); ?>'; document.form.enabled.value=this.checked; document.form.submit();"<?php if ($cveTag->isEnabled()) echo ' checked'; ?>>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <a href="cve.php?cveName=<?php echo $cveTag->getCveName(); ?>" class="font-medium text-brand-600 hover:underline"><?php echo $cveTag->getCveName(); ?></a>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="relative inline-block tag-cell tag-<?php echo htmlspecialchars($cveTag->getTagName(), ENT_QUOTES); ?>">
                                                <select class="appearance-none cursor-pointer inline-flex items-center pl-2 pr-6 py-1 rounded text-xs border-0 focus:outline-none focus:ring-2 focus:ring-brand-500 tag-badge <?php echo tagBadgeClasses($cveTag->getTagName()); ?>"
                                                        title="Click to change tag"
                                                        onchange="changeTag(<?php echo $cveTag->getId(); ?>, this)">
                                                    <?php foreach ($tagNames as $t) { ?>
                                                        <option value="<?php echo $t; ?>"<?php if ($t === $cveTag->getTagName()) echo ' selected'; ?>><?php echo $t; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-slate-500 truncate max-w-xs"><?php echo $cveTag->getReason(); ?></td>
                                        <td class="px-3 py-2 text-slate-500 truncate max-w-xs">
                                            <a href="<?php echo $cveTag->getInfoUrl(); ?>" target='_blank' class="text-brand-600 hover:underline"><?php echo substr($cveTag->getInfoUrl(), 0, 30) . (strlen($cveTag->getInfoUrl()) > 30 ? '...' : ''); ?></a>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-slate-500"><?php echo $cveTag->getModifier(); ?></td>
                                        <td class="px-3 py-2 whitespace-nowrap text-slate-400 text-[10px]"><?php echo $cveTag->getTimestamp(); ?></td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right">
                                            <button type="button" class="text-red-500 hover:text-red-700" onclick="confirmDelete(<?php echo $cveTag->getId(); ?>, '<?php echo htmlspecialchars($cveTag->getCveName()); ?>')">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>

                    <!-- Hidden forms -->
                    <form action="" name="form" method="post">
                        <input type="hidden" name="act">
                        <input type="hidden" name="id">
                        <input type="hidden" name="enabled">
                    </form>

                    <!-- Delete Confirmation Modal -->
                    <div id="delete-modal" class="fixed inset-0 bg-slate-900 bg-opacity-50 z-50 hidden flex items-center justify-center">
                        <div class="bg-white rounded-lg shadow-xl max-w-sm w-full mx-4">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-slate-900 mb-2">Delete CVE tag?</h3>
                                <p class="text-sm text-slate-500 mb-6">Are you sure you want to delete this CVE tag?</p>
                                <div class="flex justify-end gap-3">
                                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none">Cancel</button>
                                    <button type="button" onclick="confirmDeleteAction()" class="px-4 py-2 bg-red-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700 focus:outline-none">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add CVE Tag Modal -->
                    <div id="add-modal" class="fixed inset-0 bg-slate-900 bg-opacity-50 z-50 hidden flex items-center justify-center">
                        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                            <div class="px-6 py-4 border-b border-slate-200">
                                <h3 class="text-lg font-medium text-slate-900">Add CVE tag</h3>
                            </div>
                            <form name="addForm" method="post" class="p-6" onsubmit="return validateCveForm();">
                                <input type="hidden" name="act" value="create">
                                <div class="space-y-4">
                                    <div>
                                        <label for="cveName" class="block text-sm font-medium text-slate-700 mb-1">CVE</label>
                                        <input type="text" class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="cveName" id="cveName" placeholder="e.g. CVE-2024-1234">
                                        <p id="cveNameFeedback" class="hidden text-xs mt-1"></p>
                                    </div>
                                    <div>
                                        <label for="tagName" class="block text-sm font-medium text-slate-700 mb-1">Tag</label>
                                        <select class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none bg-white" name="tagName" id="tagName">
                                            <?php foreach ($tagNames as $idx => $tagName) { ?>
                                                <option value="<?php echo $tagName; ?>"<?php if ($idx === 0) echo ' selected'; ?>><?php echo $tagName; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="reason" class="block text-sm font-medium text-slate-700 mb-1">Reason</label>
                                        <input type="text" class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="reason" id="reason">
                                    </div>
                                    <div>
                                        <label for="infoUrl" class="block text-sm font-medium text-slate-700 mb-1">Info URL</label>
                                        <input type="text" class="block w-full sm:text-sm border-slate-300 rounded-md py-2 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="infoUrl" id="infoUrl">
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 mt-6">
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
        const TAG_STYLES = <?php
            $styles = array();
            foreach ($tagNames as $t) { $styles[$t] = tagBadgeClasses($t); }
            echo json_encode($styles);
        ?>;
        async function changeTag(id, sel) {
            const newTag = sel.value;
            const cell = sel.closest('.tag-cell');
            const m = cell.className.match(/tag-(\S+)/);
            const prevTag = m ? m[1] : null;
            const prevBadge = sel.dataset.prevBadge || Array.from(sel.classList).filter(c => /^(bg|text)-/.test(c)).join(' ');
            sel.dataset.prevBadge = prevBadge;
            sel.disabled = true;
            try {
                const fd = new FormData();
                fd.append('act', 'update');
                fd.append('ajax', '1');
                fd.append('id', id);
                fd.append('tagName', newTag);
                const resp = await fetch('tags.php', { method: 'POST', body: fd });
                const data = await resp.json();
                if (!resp.ok || !data.ok) {
                    alert(data.error || 'Failed to update tag');
                    sel.value = prevTag;
                } else {
                    sel.classList.remove(...prevBadge.split(' ').filter(Boolean));
                    sel.classList.add(...(TAG_STYLES[newTag] || 'bg-brand-100 text-brand-700').split(' '));
                    sel.dataset.prevBadge = TAG_STYLES[newTag] || 'bg-brand-100 text-brand-700';
                    if (prevTag) cell.classList.remove('tag-' + prevTag);
                    cell.classList.add('tag-' + newTag);
                }
            } catch (e) {
                alert('Failed to update tag');
                sel.value = prevTag;
            }
            sel.disabled = false;
        }
        let deleteId = null;
        let deleteName = null;
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
        function confirmDelete(id, name) {
            deleteId = id;
            deleteName = name;
            document.getElementById('delete-modal').classList.remove('hidden');
        }
        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.add('hidden');
            deleteId = null;
            deleteName = null;
        }
        function confirmDeleteAction() {
            document.forms.form.act.value = "delete";
            document.forms.form.id.value = deleteId;
            document.forms.form.submit();
        }
        let cveCheckTimer = null;
        let cveCheckOk = false;
        const cveInput = document.getElementById('cveName');
        const cveFeedback = document.getElementById('cveNameFeedback');
        const tagSelect = document.getElementById('tagName');
        function setCveFeedback(msg, ok) {
            cveCheckOk = ok;
            cveFeedback.innerHTML = msg;
            cveFeedback.className = 'text-xs mt-1 ' + (ok ? 'text-green-600' : 'text-red-500');
            cveInput.classList.toggle('border-green-500', ok);
            cveInput.classList.toggle('border-red-500', !ok);
        }
        function clearCveFeedback() {
            cveCheckOk = false;
            cveFeedback.className = 'hidden text-xs mt-1';
            cveInput.classList.remove('border-green-500', 'border-red-500');
        }
        async function runCveCheck() {
            const name = cveInput.value.trim();
            if (!name) { clearCveFeedback(); return; }
            if (!/^CVE-\d{4}-\d{4,}$/i.test(name)) {
                setCveFeedback('<i data-feather="x-circle" class="w-3 h-3 inline -mt-0.5"></i> Not a valid CVE format (CVE-YYYY-NNNN…)', false);
                feather.replace();
                return;
            }
            try {
                const fd = new FormData();
                fd.append('act', 'checkCve');
                fd.append('cveName', name);
                fd.append('tagName', tagSelect.value);
                const resp = await fetch('tags.php', { method: 'POST', body: fd });
                const data = await resp.json();
                if (!data.exists) {
                    setCveFeedback('<i data-feather="x-circle" class="w-3 h-3 inline -mt-0.5"></i> CVE not found in the database', false);
                } else if (data.tagId != -1) {
                    setCveFeedback('<i data-feather="x-circle" class="w-3 h-3 inline -mt-0.5"></i> This CVE already has the tag "' + tagSelect.value + '"', false);
                } else {
                    setCveFeedback('<i data-feather="check-circle" class="w-3 h-3 inline -mt-0.5"></i> CVE found', true);
                }
                feather.replace();
            } catch (e) {
                setCveFeedback('Could not check CVE', false);
            }
        }
        cveInput.addEventListener('input', function () {
            clearCveFeedback();
            clearTimeout(cveCheckTimer);
            cveCheckTimer = setTimeout(runCveCheck, 400);
        });
        cveInput.addEventListener('blur', runCveCheck);
        tagSelect.addEventListener('change', function () { if (cveInput.value.trim()) runCveCheck(); });
        function validateCveForm() {
            if (!cveCheckOk) {
                if (cveInput.value.trim()) {
                    cveInput.focus();
                    return false;
                }
            }
            return true;
        }
        <?php if ($showAddModal) { ?>
            openAddModal();
        <?php } ?>
    </script>
</body>
</html>
