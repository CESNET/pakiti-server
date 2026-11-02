<?php
// --- pagination2.php (Split Layout: Text Left + Pagination Right) ---
// Keeps your logic (page numbers, sorting, query params)
// Uses modern Tailwind-style HTML with left/right split

// Pages including this template may not compute an item count
if (!isset($hostsCount)) {
    $hostsCount = 0;
}

// Calculate pagination range (your existing logic)
if ($html->getNumOfPages() <= $html->getPaginationSize()) {
    $start = 0;
    $end = $html->getNumOfPages() - 1;
} else {
    $long = $html->getPaginationSize();
    if ($html->getPageNum() > floor($html->getPaginationSize() / 2)) {
        $long -= 2;
        $start = $html->getPageNum() - floor(($html->getPaginationSize() - 4) / 2);
    } else {
        $start = 0;
    }
    if ($start + $long <= $html->getNumOfPages() - 1) {
        $long -= 2;
        $end = $start + $long - 1;
    } else {
        $end = $html->getNumOfPages() - 1;
        $start = $end - $long + 1;
    }
}

// Determine Previous/Next page links
$prevPage = $html->getPageNum() > 0 ? $html->getPageNum() - 1 : null;
$nextPage = $html->getPageNum() < $html->getNumOfPages() - 1 ? $html->getPageNum() + 1 : null;
?>

<!-- Modern Pagination Container (Split Layout) -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between bg-white px-4 py-3 border-t border-slate-200 sm:px-6">
    <!-- Left: Results Count Text -->
    <div class="mb-3 sm:mb-0 sm:mr-4">
        <p class="text-sm text-slate-700">
            Showing <span class="font-medium"><?php echo min(($html->getPageNum() * $html->getPageSize()) + 1, $hostsCount); ?></span> to <span class="font-medium"><?php echo min((($html->getPageNum() + 1) * $html->getPageSize()), $hostsCount); ?></span> of <span class="font-medium"><?php echo $hostsCount; ?></span> results
        </p>
    </div>
    
    <!-- Right: Pagination Controls -->
    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
        
        <!-- Previous Button (Disabled if first page) -->
        <a href="<?php echo ($prevPage !== null) ? $html->getQueryString(array("pageNum" => $prevPage)) : '#'; ?>"
           class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-slate-300 bg-white text-sm font-medium text-slate-500 hover:bg-slate-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 <?php echo ($prevPage === null ? 'cursor-not-allowed opacity-50' : ''); ?>">
            <span class="sr-only">Previous</span>
            <i data-feather="chevron-left" class="h-5 w-5" aria-hidden="true"></i>
        </a>

        <!-- First Page Link (if needed) -->
        <?php if ($start > 0): ?>
            <a href="<?php echo $html->getQueryString(array("pageNum" => 0)); ?>"
               class="relative inline-flex items-center px-4 py-2 border border-slate-300 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500">
                1
            </a>
        <?php endif; ?>

        <!-- Ellipsis (if needed) -->
        <?php if ($start > 1): ?>
            <span class="relative inline-flex items-center px-4 py-2 border border-slate-300 bg-white text-sm font-medium text-slate-700">
                <span aria-hidden="true">&hellip;</span>
            </span>
        <?php endif; ?>

        <!-- Numbered Pages -->
        <?php for ($i = $start; $i <= $end; $i++): ?>
            <a href="<?php echo $html->getQueryString(array("pageNum" => $i)); ?>"
               class="relative inline-flex items-center px-4 py-2 border <?php echo ($i == $html->getPageNum() ? 'border-brand-500 bg-brand-50 text-brand-600 z-10 font-medium' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'); ?> focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500">
                <?php echo $i + 1; ?>
            </a>
        <?php endfor; ?>

        <!-- Ellipsis (if needed) -->
        <?php if ($end < $html->getNumOfPages() - 2): ?>
            <span class="relative inline-flex items-center px-4 py-2 border border-slate-300 bg-white text-sm font-medium text-slate-700">
                <span aria-hidden="true">&hellip;</span>
            </span>
        <?php endif; ?>

        <!-- Last Page Link (if needed) -->
        <?php if ($end < $html->getNumOfPages() - 1): ?>
            <a href="<?php echo $html->getQueryString(array("pageNum" => $html->getNumOfPages() - 1)); ?>"
               class="relative inline-flex items-center px-4 py-2 border border-slate-300 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500">
                <?php echo $html->getNumOfPages(); ?>
            </a>
        <?php endif; ?>

        <!-- Next Button (Disabled if last page) -->
        <a href="<?php echo ($nextPage !== null) ? $html->getQueryString(array("pageNum" => $nextPage)) : '#'; ?>"
           class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-slate-300 bg-white text-sm font-medium text-slate-500 hover:bg-slate-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 <?php echo ($nextPage === null ? 'cursor-not-allowed opacity-50' : ''); ?>">
            <span class="sr-only">Next</span>
            <i data-feather="chevron-right" class="h-5 w-5" aria-hidden="true"></i>
        </a>
    </nav>
</div>

<!-- Initialize Feather Icons -->
<script>feather.replace();</script>
