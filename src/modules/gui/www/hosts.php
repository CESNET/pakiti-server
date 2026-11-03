<?php
// --- PHP LOGIC (UNCHANGED FROM YOUR ORIGINAL) ---
require(realpath(dirname(__FILE__)) . '/../../../common/Loader.php');
require(realpath(dirname(__FILE__)) . '/../Html.php');

$html = new HtmlModule($pakiti);
$html->checkPermission("hosts");

$_hostGroupId = $html->getHttpGetVar("hostGroupId", -1);
$_hostGroupName = $html->getHttpGetVar("HostGroup", null);
// If HostGroup name is provided, convert it to ID
if ($_hostGroupName != null && $_hostGroupName != "") {
    $hostGroupsTemp = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroups(null, -1, -1, $html->getUserId());
    foreach ($hostGroupsTemp as $g) {
        if ($g->getName() == $_hostGroupName) {
            $_hostGroupId = $g->getId();
            break;
        }
    }
}
$_search = $html->getHttpGetVar("search", null);
$_tag = $html->getHttpGetVar("tag", null); if ($_tag == "true") { $_tag = true; }
$_cveName = $html->getHttpGetVar("cveName", null);
$_activity = $html->getHttpGetVar("activity", null);
$_listTaggedCves = $html->getHttpGetVar("listTaggedCves", false); if ($_listTaggedCves !== false) { $_listTaggedCves = true; }
$_source = $html->getHttpGetVar("source", null);

#// Apply default filter from config if no parameters provided
#  if (isset(Config::$GUI_HOSTS_DEFAULT_FILTER) && Config::$GUI_HOSTS_DEFAULT_FILTER !== '' &&
#      $_hostGroupId == -1 && $_search === null && $_tag === null &&
#      $_cveName === null && $_activity === null && $_listTaggedCves === false && $_source === null) {
#      parse_str(Config::$GUI_HOSTS_DEFAULT_FILTER, $defaultParams);
#      if (isset($defaultParams['hostGroupId'])) { $_hostGroupId = $defaultParams['hostGroupId']; }
#      if (isset($defaultParams['HostGroup'])) { $_hostGroupName = $defaultParams['HostGroup']; }
#      if (isset($defaultParams['search'])) { $_search = $defaultParams['search']; }
#      if (isset($defaultParams['tag'])) { $_tag = $defaultParams['tag']; if ($_tag == "true") { $_tag = true; } }
#      if (isset($defaultParams['cveName'])) { $_cveName = $defaultParams['cveName']; }
#      if (isset($defaultParams['activity'])) { $_activity = $defaultParams['activity']; }
#      if (isset($defaultParams['listTaggedCves'])) { $_listTaggedCves = true; }
#      if (isset($defaultParams['source'])) { $_source = $defaultParams['source']; }
#  }

// Process Operations
switch ($html->getHttpPostVar("act")) {
  case "delete":
    $host = $pakiti->getManager("HostsManager")->getHostById($html->getHttpPostVar("id"), $html->getUserId());
    if ($host) {
        $pakiti->getManager("HostsManager")->deleteHost($host->getId());
        // Reload page to avoid POST resubmission
        header("Refresh:0");
        exit;
    } else {
        $html->setError("Cannot delete host. ID does not exist or access denied.");
    }
    break;
}

// Check if entered CVE exists in database
$_cveExists = null;
$_cveMessage = '';
if ($_cveName != null && $_cveName != '') {
    $_cveExists = $pakiti->getManager("CvesManager")->cveExists($_cveName);
    if ($_cveExists) {
        $_cveMessage = '<svg xmlns="http://www.w3.org/2000/svg" class="inline w-3 h-3 mr-1 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg><span class="text-emerald-600">CVE exists in database - <a href="cve.php?cveName=' . htmlspecialchars($_cveName) . '">' . htmlspecialchars($_cveName) . '</a></span>';
    } else {
        $_cveMessage = '<svg xmlns="http://www.w3.org/2000/svg" class="inline w-3 h-3 mr-1 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg><span class="text-red-600">CVE not found in database</span>';
    }
}

// Fetch Data
$hostsCount = $html->getPakiti()->getManager("HostsManager")->getHostsCount($_search, $_cveName, $_tag, $_hostGroupId, $_activity, -1, $html->getUserId(), $_source);
$html->setTitle("Host Inventory");
$html->setMenuActiveItem("hosts.php");
$html->setDefaultSorting("lastReport");
$html->setNumOfEntities($hostsCount);

// Default sort direction for lastReport and kernel is DESC
$sortByDirection = $html->getSortByDirection();
if ($sortByDirection == null && ($html->getSortBy() == "lastReport" || $html->getSortBy() == "kernel")) {
    $sortByDirection = "desc";
}

$hosts = $html->getPakiti()->getManager("HostsManager")->getHosts(
    $html->getSortBy(), $sortByDirection, $html->getPageSize(), $html->getPageNum(),
    $_search, $_cveName, $_tag, $_hostGroupId, $_activity, -1, $html->getUserId(), false, $_source
);

$hostGroups = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroups(null, -1, -1, $html->getUserId());
// Build host group name map for URL building
$hostGroupNameMap = [];
foreach ($hostGroups as $g) {
    $hostGroupNameMap[$g->getId()] = $g->getName();
}
#$cveNames = $pakiti->getManager("CvesManager")->getCvesNames(true);
$tagNames = $pakiti->getManager("CveTagsManager")->getTagNames();
$activity = array("All Activity" => "", "Last 24 hours" => "24h", "Last 2 days" => "2d", "Last week" => "1w", "Inactive 48 hours" => "-48h", "Inactive 7 days" => "-7d");

// Helper function to build sort URL with all filter parameters preserved
function buildSortUrl($sortBy, $sortByDirection, $appendSearch = null, $appendHostGroupId = null, $replaceSearch = false) {
    global $_hostGroupId, $_search, $_tag, $_cveName, $_activity, $_listTaggedCves, $hostGroupNameMap, $_source;

    $params = "sortBy={$sortBy}&sortByDirection={$sortByDirection}";

    // Determine which hostGroupId to use
    $usedHostGroupId = $appendHostGroupId !== null ? $appendHostGroupId : $_hostGroupId;
    if ($usedHostGroupId != -1 && $usedHostGroupId != "") {
        $params .= "&HostGroup=" . urlencode($hostGroupNameMap[$usedHostGroupId]);
    }

    // Combine existing search with appended search value, or replace if $replaceSearch is true
    $combinedSearch = $replaceSearch ? $appendSearch : $_search;
    if ($appendSearch != null && !$replaceSearch) {
        if ($_search != null && $_search != "") {
            $combinedSearch = $_search . ' ' . $appendSearch;
        } else {
            $combinedSearch = $appendSearch;
        }
    }
    if ($combinedSearch != null && $combinedSearch != "") $params .= "&search=" . urlencode($combinedSearch);

    if ($_tag != null && $_tag != "") $params .= "&tag=" . ($_tag === true ? "true" : urlencode($_tag));
    if ($_cveName != null && $_cveName != "") $params .= "&cveName=" . urlencode($_cveName);
    if ($_activity != null && $_activity != "") $params .= "&activity={$_activity}";
    if ($_listTaggedCves) $params .= "&listTaggedCves=true";
    if ($_source != null && $_source != "") $params .= "&source=" . urlencode($_source);
    return $params;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host Inventory | Pakiti</title>

    <!-- Fonts & Tailwind -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/modern-style.css">
   
    <script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                },
                colors: {
                    // Your NEW palette
                    brand: {
                        50: '#eff6ff',
                        100: '#dbeafe',
                        500: '#5d7083',   // ← Your sidebar/bar color
                        600: '#5d7083',
                        700: '#5d7083',
                        800: '#5d7083',
                        900: '#5d7083',
                    },
                    accent: {
                        50: '#fef3e7',
                        100: '#fde8d8',
                        500: '#f17a30',   // ← Your orange accent
                        600: '#d96220',
                        700: '#c24d15',
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
    <!-- Mobile Overlay -->
    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900 bg-opacity-50 z-40 hidden lg:hidden transition-opacity"></div>
    <div class="flex h-screen bg-slate-50 overflow-hidden">
        
        <!-- Sidebar -->
        <aside id="sidebar" class="absolute inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 transform -translate-x-full transition-all duration-300 ease-in-out lg:relative lg:translate-x-0 flex flex-col shadow-lg lg:shadow-none">
            <div id="sidebar-header" class="flex items-center justify-between px-2 h-16 border-b border-slate-200 bg-slate-50 flex-shrink-0">
                <a href="hosts.php" class="sidebar-brand flex items-center gap-2 w-full overflow-hidden whitespace-nowrap">
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
                <a href="hosts.php" class="bg-brand-50 text-brand-700 group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 border-brand-600">
                    <i data-feather="server" class="flex-shrink-0 h-5 w-5 text-brand-600"></i>
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

        <!-- Main Content -->
        <div class="flex flex-col w-0 flex-1 overflow-hidden">
            <header class="bg-white border-b border-slate-200 relative z-10 flex-shrink-0 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                <button type="button" class="px-4 border-r border-slate-200 text-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 lg:hidden" onclick="toggleSidebar()">
                    <i data-feather="menu" class="w-6 h-6"></i>
                </button>
            </header>
            <main class="flex-1 relative overflow-y-auto focus:outline-none py-6">
                <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                    
                    <!-- Filters -->
                    <form action="" method="get" id="filter-form" class="bg-white shadow rounded-lg mb-6 overflow-hidden autocomplete="on">
                        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                            <div class="flex flex-col sm:flex-row gap-3 items-end flex-1">
                                <!-- Search Field (with icon and padding) -->
                                <div class="flex-1 w-full">
                                    <label for="search" class="block text-xs font-medium text-slate-700 mb-1">Search</label>
                                    <div class="flex items-end gap-2">
                                        <div class="relative flex-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i data-feather="search" class="h-4 w-4 text-slate-400"></i>
                                            </div>
                                            <input type="text" name="search" id="search" class="block w-full pl-10 sm:text-sm border-slate-300 rounded-md py-2.5 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none" placeholder="Hostname / OS / Kernel" value="<?php echo htmlspecialchars($_search); ?>">
                                        </div>
                                        <button type="submit" class="bg-brand-600 text-white px-4 py-2.5 rounded-md text-sm font-medium hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 flex items-center justify-center gap-1 transition-colors whitespace-nowrap">
                                            <i data-feather="filter" class="w-4 h-4"></i> Filter
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">
                                        <i data-feather="info" class="inline w-3 h-3 mr-1"></i>
                                        Use <code class="bg-slate-100 px-1 rounded">!term</code> or <code class="bg-slate-100 px-1 rounded">not term</code> to exclude (e.g., <code class="bg-slate-100 px-1 rounded">not 6.1.0-41-amd64</code>)
                                    </p>
                                </div>
                            </div>
                            <!-- Toggle Button -->
                            <button type="button" onclick="toggleFilters()" class="ml-4 p-2 rounded-md text-slate-500 hover:bg-slate-100 transition-colors" title="Toggle filters">
                                <i id="toggle-filters-icon" data-feather="chevron-up" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <div id="filter-content" class="bg-slate-50 p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div>                                                                                    
      <label for="cveName" class="block text-xs font-medium text-slate-500 mb-1">CVE</label>
      <input class="block w-full sm:text-sm border-slate-300 rounded-md py-2.5 px-3 border shadow-sm focus:ring-brand-500 focus:border-brand-500 focus:outline-none"                                       
             type="text" name="cveName" id="cveName"
             placeholder="e.g., CVE-2024-1234"                                                                                                                                                             
             value="<?php echo htmlspecialchars($_cveName); ?>">
      <?php if ($_cveMessage != ''): ?>
          <p class="text-[10px] mt-1"><?php echo $_cveMessage; ?></p>
      <?php else: ?>
          <p class="text-[10px] text-slate-400 mt-1">
              <i data-feather="info" class="inline w-3 h-3 mr-1"></i>
              Type full CVE name (e.g., CVE-2024-5678)
          </p>
      <?php endif; ?>
  </div>
        <!-- Tag Select (CVE Tag) -->
        <div>
            <label for="tag" class="block text-xs font-medium text-slate-500 mb-1">CVE Tag</label>
            <select class="block w-full sm:text-sm border-slate-300 rounded-md py-2.5 px-3 border shadow-sm bg-white focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="tag" id="tag">
                <option value="">Any</option>
                <option value="true" <?php echo ($_tag === true ? 'selected' : ''); ?>>Tagged</option>
                <?php foreach ($tagNames as $tag) { ?>
                    <option value="<?php echo $tag; ?>" <?php echo ($_tag == $tag ? 'selected' : ''); ?>><?php echo $tag; ?></option>
                <?php } ?>
            </select>
            <p class="text-[10px] text-slate-400 mt-1">
                <i data-feather="info" class="inline w-3 h-3 mr-1"></i>
                Filters hosts by CVE vulnerability tags
            </p>
        </div>

        <!-- Activity Select -->
        <div>
            <label for="activity" class="block text-xs font-medium text-slate-500 mb-1">Activity</label>
            <select class="block w-full sm:text-sm border-slate-300 rounded-md py-2.5 px-3 border shadow-sm bg-white focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="activity" id="activity">
                <?php foreach ($activity as $label => $val) { ?>
                    <option value="<?php echo $val; ?>" <?php echo ($_activity == $val ? 'selected' : ''); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>
        </div>

        <!-- Host Group Select -->
        <div>
            <label for="hostGroupId" class="block text-xs font-medium text-slate-500 mb-1">Group</label>
            <select class="block w-full sm:text-sm border-slate-300 rounded-md py-2.5 px-3 border shadow-sm bg-white focus:ring-brand-500 focus:border-brand-500 focus:outline-none" name="hostGroupId" id="hostGroupId">
                <option value="">All</option>
                <?php foreach ($hostGroups as $group) { ?>
                    <option value="<?php echo $group->getId(); ?>" <?php echo ($_hostGroupId == $group->getId() ? 'selected' : ''); ?>><?php echo $group->getName(); ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    </form>
                    
                    <!-- Current URL Arguments Display -->
                    <?php
                    $currentArgs = [];
                    if ($_search != null && $_search != "") $currentArgs['search'] = $_search;
                    if ($_cveName != null && $_cveName != "") $currentArgs['cveName'] = $_cveName;
                    if ($_tag != null && $_tag != "") $currentArgs['tag'] = ($_tag === true ? 'true' : $_tag);
                    if ($_source != null && $_source != "") $currentArgs['source'] = $_source;
                    if ($_hostGroupId != -1 && $_hostGroupId != "") $currentArgs['HostGroup'] = $hostGroupNameMap[$_hostGroupId];
                    if ($_activity != null && $_activity != "") $currentArgs['activity'] = $_activity;
                    if ($_listTaggedCves) $currentArgs['listTaggedCves'] = 'true';
                    ?>
                    <?php if (!empty($currentArgs)): ?>
                    <div class="bg-slate-100 border border-slate-200 rounded-md p-3 mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i data-feather="filter" class="w-4 h-4 text-slate-500"></i>
                            <span class="text-xs font-medium text-slate-700">Active Filters:</span>
                            <a href="hosts.php" class="text-xs text-brand-600 hover:underline ml-auto">Clear all</a>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($currentArgs as $key => $value): ?>
                                <?php
                                // Build remove URL - keep sorting if present
                                $removeParams = [];
                                $removeParams['sortBy'] = $html->getSortBy();
                                $removeParams['sortByDirection'] = $html->getSortByDirection();
                                if ($_hostGroupId != -1 && $_hostGroupId != "" && $key !== 'HostGroup') $removeParams['HostGroup'] = $hostGroupNameMap[$_hostGroupId];
                                if ($_search != null && $_search != "" && $key !== 'search') $removeParams['search'] = $_search;
                                if ($_cveName != null && $_cveName != "" && $key !== 'cveName') $removeParams['cveName'] = $_cveName;
                                if ($_tag != null && $_tag != "" && $key !== 'tag') $removeParams['tag'] = ($_tag === true ? 'true' : $_tag);
                                if ($_source != null && $_source != "" && $key !== 'source') $removeParams['source'] = $_source;
                                if ($_activity != null && $_activity != "" && $key !== 'activity') $removeParams['activity'] = $_activity;
                                if ($_listTaggedCves && $key !== 'listTaggedCves') $removeParams['listTaggedCves'] = 'true';
                                $removeUrl = http_build_query($removeParams);
                                ?>
                                <a href="?<?php echo $removeUrl; ?>" class="inline-flex items-center gap-1 px-2 py-1 bg-white border border-slate-200 rounded text-xs text-slate-700 hover:bg-red-50 hover:border-red-200 hover:text-red-600 transition-colors">
                                    <span class="font-medium text-slate-500"><?php echo htmlspecialchars($key); ?>:</span>
                                    <span><?php echo htmlspecialchars($value); ?></span>
                                    <i data-feather="x" class="w-3 h-3"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Header with Export Button -->
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-xl font-bold text-slate-900"><?php echo $hostsCount; ?> host<?php echo ($hostsCount != 1 ? 's' : ''); ?> found</h2>
                        <div class="flex items-center gap-2">
                            <div class="relative">
                                <button type="button" onclick="toggleColumnsDropdown()" class="inline-flex items-center gap-2 bg-slate-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-colors">
                                    <i data-feather="eye" class="w-4 h-4"></i> Columns
                                </button>
                                <div id="columns-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-slate-200 z-20">
                                    <div class="p-3 border-b border-slate-200">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-slate-900">Visible Columns</span>
                                            <div class="flex gap-1">
                                                <button onclick="showAllColumns()" class="text-xs text-brand-600 hover:underline">All</button>
                                                <span class="text-slate-300">|</span>
                                                <button onclick="hideAllColumns()" class="text-xs text-brand-600 hover:underline">None</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-2 max-h-64 overflow-y-auto">
                                        <label class="flex items-center gap-2 p-2 hover:bg-slate-100 rounded cursor-pointer">
                                            <input type="checkbox" class="column-toggle rounded border-slate-300 text-brand-600 focus:ring-brand-500" data-column="hostname">
                                            <span class="text-sm text-slate-700">Hostname</span>
                                        </label>
                                        <label class="flex items-center gap-2 p-2 hover:bg-slate-100 rounded cursor-pointer">
                                            <input type="checkbox" class="column-toggle rounded border-slate-300 text-brand-600 focus:ring-brand-500" data-column="groups">
                                            <span class="text-sm text-slate-700">Groups</span>
                                        </label>
                                        <label class="flex items-center gap-2 p-2 hover:bg-slate-100 rounded cursor-pointer">
                                            <input type="checkbox" class="column-toggle rounded border-slate-300 text-brand-600 focus:ring-brand-500" data-column="os">
                                            <span class="text-sm text-slate-700">OS</span>
                                        </label>
                                        <label class="flex items-center gap-2 p-2 hover:bg-slate-100 rounded cursor-pointer">
                                            <input type="checkbox" class="column-toggle rounded border-slate-300 text-brand-600 focus:ring-brand-500" data-column="kernel">
                                            <span class="text-sm text-slate-700">Kernel</span>
                                        </label>
                                        <label class="flex items-center gap-2 p-2 hover:bg-slate-100 rounded cursor-pointer">
                                            <input type="checkbox" class="column-toggle rounded border-slate-300 text-brand-600 focus:ring-brand-500" data-column="arch">
                                            <span class="text-sm text-slate-700">Arch</span>
                                        </label>
                                        <label class="flex items-center gap-2 p-2 hover:bg-slate-100 rounded cursor-pointer">
                                            <input type="checkbox" class="column-toggle rounded border-slate-300 text-brand-600 focus:ring-brand-500" data-column="pkgs">
                                            <span class="text-sm text-slate-700">Pkgs</span>
                                        </label>
                                        <label class="flex items-center gap-2 p-2 hover:bg-slate-100 rounded cursor-pointer">
                                            <input type="checkbox" class="column-toggle rounded border-slate-300 text-brand-600 focus:ring-brand-500" data-column="cves">
                                            <span class="text-sm text-slate-700">CVEs</span>
                                        </label>
                                        <label class="flex items-center gap-2 p-2 hover:bg-slate-100 rounded cursor-pointer">
                                            <input type="checkbox" class="column-toggle rounded border-slate-300 text-brand-600 focus:ring-brand-500" data-column="tagged">
                                            <span class="text-sm text-slate-700">Tagged</span>
                                        </label>
                                        <label class="flex items-center gap-2 p-2 hover:bg-slate-100 rounded cursor-pointer">
                                            <input type="checkbox" class="column-toggle rounded border-slate-300 text-brand-600 focus:ring-brand-500" data-column="lastReport">
                                            <span class="text-sm text-slate-700">Last Report</span>
                                        </label>
                                        <label class="flex items-center gap-2 p-2 hover:bg-slate-100 rounded cursor-pointer">
                                            <input type="checkbox" class="column-toggle rounded border-slate-300 text-brand-600 focus:ring-brand-500" data-column="actions">
                                            <span class="text-sm text-slate-700">Actions</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <button type="button" onclick="openExportModal()" class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                                <i data-feather="download" class="w-4 h-4"></i> Export CSV
                            </button>
                        </div>
                    </div>
                    
                    <!-- TABLE: FULL WIDTH + COMPACT -->
                    <div class="bg-white shadow overflow-hidden rounded-lg border border-slate-200 mb-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-xs">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider" data-column="hostname">
                                            <?php
                                            $dir = ($html->getSortBy() == "hostname" && $html->getSortByDirection() == "asc") ? "desc" : "asc";
                                            $arrow = ($html->getSortBy() == "hostname" && $html->getSortByDirection() == "asc") ? "▲" : "";
                                            ?>
                                            <a href="?<?php echo buildSortUrl('hostname', $dir); ?>" class="hover:text-brand-600">Hostname <?php echo $arrow; ?></a>
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider" data-column="groups">Groups</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider" data-column="os">
                                            <?php
                                            $dir = ($html->getSortBy() == "os" && $html->getSortByDirection() == "asc") ? "desc" : "asc";
                                            $arrow = ($html->getSortBy() == "os" && $html->getSortByDirection() == "asc") ? "▲" : "";
                                            ?>
                                            <a href="?<?php echo buildSortUrl('os', $dir); ?>" class="hover:text-brand-600">OS <?php echo $arrow; ?></a>
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider" data-column="kernel">
                                            <?php
                                            $dir = ($html->getSortBy() == "kernel" && $html->getSortByDirection() != "desc") ? "desc" : "asc";
                                            $arrow = ($html->getSortBy() == "kernel" && $html->getSortByDirection() == "desc") ? "▼" : "";
                                            ?>
                                            <a href="?<?php echo buildSortUrl('kernel', $dir); ?>" class="hover:text-brand-600">Kernel <?php echo $arrow; ?></a>
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider" data-column="arch">
                                            <?php
                                            $dir = ($html->getSortBy() == "arch" && $html->getSortByDirection() == "asc") ? "desc" : "asc";
                                            $arrow = ($html->getSortBy() == "arch" && $html->getSortByDirection() == "asc") ? "▲" : "";
                                            ?>
                                            <a href="?<?php echo buildSortUrl('arch', $dir); ?>" class="hover:text-brand-600">Arch <?php echo $arrow; ?></a>
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider" data-column="pkgs">Pkgs</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider" data-column="cves">
                                            <?php
                                            $dir = ($html->getSortBy() == "cves" && $html->getSortByDirection() == "asc") ? "desc" : "asc";
                                            $arrow = ($html->getSortBy() == "cves" && $html->getSortByDirection() == "asc") ? "▲" : "";
                                            ?>
                                            <a href="?<?php echo buildSortUrl('cves', $dir); ?>" class="hover:text-brand-600">CVEs <?php echo $arrow; ?></a>
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider" data-column="tagged">
                                            <div class="flex items-center gap-2">
                                                <?php
                                                $dir = ($html->getSortBy() == "taggedCves" && $html->getSortByDirection() == "asc") ? "desc" : "asc";
                                                $arrow = ($html->getSortBy() == "taggedCves" && $html->getSortByDirection() == "asc") ? "▲" : "";
                                                ?>
                                                <a href="?<?php echo buildSortUrl('taggedCves', $dir); ?>" class="hover:text-brand-600">Tagged <?php echo $arrow; ?></a>
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="listTaggedCves" onchange="location.href='<?php echo $html->getQueryString(array("listTaggedCves" => ($_listTaggedCves ? "" : "true"))); ?>'" <?php if($_listTaggedCves) echo ' checked'; ?>>
                                                </label>
                                            </div>
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider" data-column="lastReport">
                                            <?php
                                            $dir = ($html->getSortBy() == "lastReport" && $html->getSortByDirection() == "asc") ? "desc" : "asc";
                                            $arrow = ($html->getSortBy() == "lastReport" && $html->getSortByDirection() == "asc") ? "<i data-feather='arrow-up' class='w-3 h-3'></i>" : "<i data-feather='arrow-down' class='w-3 h-3'></i>";
                                            ?>
                                            <a href="?<?php echo buildSortUrl('lastReport', $dir); ?>" class="flex items-center gap-1 hover:text-brand-600">
                                                Last Report <?php echo $arrow; ?>
                                            </a>
                                        </th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider" data-column="actions">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <?php if (count($hosts) > 0): ?>
                                        <?php foreach ($hosts as $host): ?>
                                            <?php
                                            $report = null;
                                            if ($host->getLastReportId() != null && $host->getLastReportId() != -1) {
                                                $report = $html->getPakiti()->getManager("ReportsManager")->getReportById($host->getLastReportId());
                                            }
                                            $hostGroupsList = $html->getPakiti()->getManager("HostGroupsManager")->getHostGroupsByHost($host);
                                            $reportsCount = $report ? $html->getPakiti()->getManager("ReportsManager")->getHostReportsCount($host->getId()) : 0;
                                            $isInactive = $report ? (strtotime("now") - strtotime($report->getReceivedOn()) > (2 * (60 * 60 * 24))) : true;
                                            ?>
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-3 py-1 whitespace-nowrap" data-column="hostname">
                                                    <a href="host.php?hostId=<?php echo $host->getId(); ?>" class="font-medium text-brand-600 hover:underline truncate max-w-[200px] inline-block" title="<?php echo htmlspecialchars($host->getHostname()); ?>"><?php echo htmlspecialchars($host->getHostname()); ?></a>
                                                </td>
                                                <td class="px-3 py-1 whitespace-nowrap" data-column="groups">
                                                    <?php foreach ($hostGroupsList as $g): ?>
                                                        <a href="?<?php echo buildSortUrl($html->getSortBy(), $html->getSortByDirection()) . '&hostGroupId=' . $g->getId(); ?>" class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 mr-1 mb-0.5 hover:bg-brand-100 hover:text-brand-700 transition-colors">
                                                            <?php echo $g->getName(); ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </td>
                                                <td class="px-3 py-1 whitespace-nowrap" data-column="os">
                                                    <a href="?<?php echo buildSortUrl($html->getSortBy(), $html->getSortByDirection(), $host->getOsName(), null, false); ?>" class="hover:text-brand-600 hover:underline cursor-pointer">
                                                        <?php echo htmlspecialchars($host->getOsName()); ?>
                                                    </a>
                                                </td>
                                                <td class="px-3 py-1 whitespace-nowrap" data-column="kernel">
                                                    <a href="?<?php echo buildSortUrl($html->getSortBy(), $html->getSortByDirection(), $host->getKernel(), null, false); ?>" class="hover:text-brand-600 hover:underline cursor-pointer">
                                                        <?php echo htmlspecialchars($host->getKernel()); ?>
                                                    </a>
                                                </td>
                                                <td class="px-3 py-1 whitespace-nowrap" data-column="arch"><?php echo htmlspecialchars($host->getArchName()); ?></td>
                                                <td class="px-3 py-1 whitespace-nowrap" data-column="pkgs">
                                                    <a href="host_packages.php?hostId=<?php echo $host->getId(); ?>" class="text-slate-500 hover:text-brand-600"><?php echo $report ? $report->getNumOfInstalledPkgs() : '-'; ?></a>
                                                </td>
                                                <td class="px-3 py-1 whitespace-nowrap" data-column="cves">
                                                    <a href="host_cves.php?hostId=<?php echo $host->getId(); ?>" class="text-slate-500 hover:text-brand-600"><?php echo $host->getNumOfCves(); ?></a>
                                                </td>
                                                <!-- Tagged CVEs Column -->
<td class="px-3 py-1 whitespace-nowrap" data-column="tagged">
    <?php if ($_listTaggedCves): ?>
        <?php
        $taggedCves = $html->getPakiti()->getManager("CvesManager")->getCvesNamesForHost($host->getId(), ($_tag == null ? true : $_tag));
        if (count($taggedCves) > 0):
            foreach ($taggedCves as $cve):
        ?>
            <div class="flex items-center gap-1 mb-0.5">
                <a href="cve.php?cveName=<?php echo $cve; ?>" class="text-red-600 hover:underline truncate max-w-[150px]" title="<?php echo $cve; ?>"><?php echo $cve; ?></a>
                <?php if ($_tag !== null && $_tag !== true): ?>
                    <span class="text-[10px] text-slate-500 bg-slate-100 px-1 py-0.5 rounded">only <?php echo $_tag; ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; else: ?> - <?php endif; ?>
    <?php else: ?>
        <?php
        $taggedCount = $host->getNumOfCvesWithTag();
        // Only show red if > 0, otherwise grey
        ?>
        <a href="host_cves.php?hostId=<?php echo $host->getId(); ?>&tag=true"
           class="<?php echo ($taggedCount > 0 ? 'text-red-600 font-medium' : 'text-slate-500'); ?>">
            <?php echo $taggedCount; ?>
        </a>
    <?php endif; ?>
</td>
                                                <!-- <td class="px-3 py-1 whitespace-nowrap">
                                                    <a href="host_reports.php?hostId=<?php echo $host->getId(); ?>" class="text-slate-500 hover:text-brand-600"><?php echo $reportsCount ?? 0; ?></a>
                                                </td>
                                                -->
                                                <td class="px-3 py-1 whitespace-nowrap text-[10px] text-slate-500" data-column="lastReport">
                                                    <span style="<?php echo ($isInactive ? 'color: #f59e0b;' : ''); ?>" title="<?php echo ($isInactive ? 'Inactive' : ''); ?>">
                                                        <?php echo $report ? date("Y-m-d H:i", strtotime($report->getReceivedOn())) : '-'; ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-1 whitespace-nowrap text-right" data-column="actions">
                                                    <button type="button" class="text-red-600 hover:text-red-900" onclick="confirmDelete(<?php echo $host->getId(); ?>, '<?php echo htmlspecialchars($host->getHostname()); ?>')">
                                                        <i data-feather="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="11" class="px-6 py-8 text-center text-slate-500">No hosts found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination: Fixed to show correctly -->
                        <div class="bg-white px-4 py-3 border-t border-slate-200 sm:px-6 flex items-center justify-between text-xs">
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <?php include(realpath(dirname(__FILE__)) . "/../common/pagination.php"); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
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

    <!-- Hidden Delete Form -->
    <form action="" name="form" method="post">
        <input type="hidden" name="act" />
        <input type="hidden" name="id" />
    </form>

    <!-- CSV Export Modal -->
    <div id="export-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900 bg-opacity-50 transition-opacity" onclick="closeExportModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-feather="download" class="h-6 w-6 text-emerald-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">Export CSV - Select Columns</h3>
                                <div class="mt-4">
                                    <p class="text-sm text-slate-500 mb-3">Choose which columns to include in the CSV export:</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" name="export_columns" value="hostname" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-sm text-slate-700">Hostname</span>
                                        </label>
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" name="export_columns" value="groups" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-sm text-slate-700">Groups</span>
                                        </label>
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" name="export_columns" value="os" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-sm text-slate-700">OS</span>
                                        </label>
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" name="export_columns" value="kernel" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-sm text-slate-700">Kernel</span>
                                        </label>
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" name="export_columns" value="arch" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-sm text-slate-700">Arch</span>
                                        </label>
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" name="export_columns" value="pkgs" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-sm text-slate-700">Packages</span>
                                        </label>
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" name="export_columns" value="cves" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-sm text-slate-700">CVEs</span>
                                        </label>
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" name="export_columns" value="taggedCves" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-sm text-slate-700">Tagged CVEs</span>
                                        </label>
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" name="export_columns" value="lastReport" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-sm text-slate-700">Last Report</span>
                                        </label>
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" name="export_columns" value="filterCve" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-sm text-slate-700">CVE Name</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <button type="button" onclick="exportCSV()" class="inline-flex w-full justify-center items-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 sm:ml-3 sm:w-auto transition-colors">
                            <i data-feather="download" class="w-4 h-4"></i> Download CSV
                        </button>
                        <button type="button" onclick="closeExportModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">
                            Cancel
                        </button>
                        <button type="button" onclick="selectAllColumns()" class="mt-3 inline-flex w-full justify-center rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200 sm:mt-0 sm:w-auto transition-colors">
                            Select All
                        </button>
                        <button type="button" onclick="deselectAllColumns()" class="mt-3 inline-flex w-full justify-center rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200 sm:mt-0 sm:w-auto transition-colors">
                            Deselect All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        feather.replace();

        // Column Visibility Toggle
        function toggleColumnsDropdown() {
            const dropdown = document.getElementById('columns-dropdown');
            dropdown.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('columns-dropdown');
            const button = event.target.closest('button');
            if (button && button.getAttribute('onclick') === 'toggleColumnsDropdown()') return;
            if (!dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        function updateColumnVisibility(column, visible) {
            // Toggle header
            const header = document.querySelector(`th[data-column="${column}"]`);
            if (header) header.style.display = visible ? '' : 'none';

            // Toggle all data cells in this column
            const cells = document.querySelectorAll(`td[data-column="${column}"]`);
            cells.forEach(cell => cell.style.display = visible ? '' : 'none');
        }

        function initColumnVisibility() {
            const saved = localStorage.getItem('hosts_column_visibility');
            const defaults = {
                hostname: true, groups: true, os: true, kernel: true,
                arch: false, pkgs: true, cves: true, tagged: true,
                lastReport: true, actions: true
            };

            const visibility = saved ? JSON.parse(saved) : defaults;

            document.querySelectorAll('.column-toggle').forEach(checkbox => {
                const column = checkbox.dataset.column;
                checkbox.checked = visibility[column] !== undefined ? visibility[column] : defaults[column];
                updateColumnVisibility(column, checkbox.checked);

                checkbox.addEventListener('change', function() {
                    updateColumnVisibility(column, this.checked);
                    saveColumnVisibility();
                });
            });
        }

        function saveColumnVisibility() {
            const visibility = {};
            document.querySelectorAll('.column-toggle').forEach(checkbox => {
                visibility[checkbox.dataset.column] = checkbox.checked;
            });
            localStorage.setItem('hosts_column_visibility', JSON.stringify(visibility));
        }

        function showAllColumns() {
            document.querySelectorAll('.column-toggle').forEach(cb => {
                cb.checked = true;
                updateColumnVisibility(cb.dataset.column, true);
            });
            saveColumnVisibility();
        }

        function hideAllColumns() {
            document.querySelectorAll('.column-toggle').forEach(cb => {
                cb.checked = false;
                updateColumnVisibility(cb.dataset.column, false);
            });
            saveColumnVisibility();
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', initColumnVisibility);

        // Toggle Arch Column Visibility
        function toggleArchColumn(show) {
            const style = document.getElementById('arch-column-style');
            if (show) {
                style.textContent = '';
                localStorage.setItem('hosts_show_arch', '1');
            } else {
                style.textContent = '.arch-content { display: none; }';
                localStorage.setItem('hosts_show_arch', '0');
            }
        }

        // Initialize arch column visibility from localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const archCheckbox = document.getElementById('toggle-arch-column');
            const savedPreference = localStorage.getItem('hosts_show_arch');

            // Default is hidden (unchecked), only show if explicitly set to 1
            if (savedPreference === '1') {
                archCheckbox.checked = true;
                toggleArchColumn(true);
            } else {
                toggleArchColumn(false);
            }

            archCheckbox.addEventListener('change', function() {
                toggleArchColumn(this.checked);
            });
        });

        // Toggle Filters
        function toggleFilters() {
            const content = document.getElementById('filter-content');
            const icon = document.getElementById('toggle-filters-icon');
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.setAttribute('data-feather', 'chevron-up');
            } else {
                content.classList.add('hidden');
                icon.setAttribute('data-feather', 'chevron-down');
            }
            feather.replace();
        }

        // Export Modal Functions
        function openExportModal() {
            document.getElementById('export-modal').classList.remove('hidden');
            feather.replace();
        }

        function closeExportModal() {
            document.getElementById('export-modal').classList.add('hidden');
        }

        function selectAllColumns() {
            document.querySelectorAll('input[name="export_columns"]').forEach(cb => cb.checked = true);
        }

        function deselectAllColumns() {
            document.querySelectorAll('input[name="export_columns"]').forEach(cb => cb.checked = false);
        }

        function exportCSV() {
            // Get selected columns
            const selectedColumns = [];
            document.querySelectorAll('input[name="export_columns"]:checked').forEach(cb => {
                selectedColumns.push(cb.value);
            });

            if (selectedColumns.length === 0) {
                alert('Please select at least one column to export.');
                return;
            }

            // Get all filter parameters from current URL
            const urlParams = new URLSearchParams(window.location.search);
            const params = new URLSearchParams();
            params.append('action', 'export');
            params.append('columns', selectedColumns.join(','));

            // Copy existing filter params
            if (urlParams.get('search')) params.append('search', urlParams.get('search'));
            if (urlParams.get('cveName')) params.append('cveName', urlParams.get('cveName'));
            if (urlParams.get('tag')) params.append('tag', urlParams.get('tag'));
            if (urlParams.get('HostGroup')) params.append('HostGroup', urlParams.get('HostGroup'));
            if (urlParams.get('hostGroupId')) params.append('hostGroupId', urlParams.get('hostGroupId'));
            if (urlParams.get('activity')) params.append('activity', urlParams.get('activity'));
            if (urlParams.get('listTaggedCves')) params.append('listTaggedCves', urlParams.get('listTaggedCves'));
            if (urlParams.get('sortBy')) params.append('sortBy', urlParams.get('sortBy'));
            if (urlParams.get('sortByDirection')) params.append('sortByDirection', urlParams.get('sortByDirection'));
            if (urlParams.get('page')) params.append('page', urlParams.get('page'));

            // Open export in new window/tab
            window.open('hosts_export.php?' + params.toString(), '_blank');
            closeExportModal();
        }

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
        
        function confirmDelete(id, hostname) {
            if (confirm('Are you sure you want to delete host "' + hostname + '"?')) {
                document.forms.form.act.value = "delete";
                document.forms.form.id.value = id;
                document.forms.form.submit();
            }
        }
    </script>
</body>
</html>
