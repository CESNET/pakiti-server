<?php
?>

<?php
    # Calculate start and end pagination
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
?>

<div class="text-center">
    <nav aria-label="Page navigation">
        <ul class="pagination pagination-sm">
            <?php if ($html->getNumOfPages() > 1) { ?>
                <li<?php if ($html->getPageNum() <= 0) echo ' class="disabled"'; ?>>
                    <a href="<?php if ($html->getPageNum() > 0) echo $html->getQueryString(array("pageNum" => $html->getPageNum()-1)); ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php if ($start > 0) { ?>
                    <li><a href="<?php echo $html->getQueryString(array("pageNum" => 0)); ?>">1</a></li>
                <?php } ?>
                <?php if ($start > 1) { ?>
                    <li><span>...</span></li>
                <?php } ?>
                <?php for ($i = $start; $i <= $end; $i++) { ?>
                    <li<?php if ($i == $html->getPageNum()){ echo ' class="active"'; }?>>
                        <a href="<?php echo $html->getQueryString(array("pageNum" => $i)); ?>"><?php echo $i + 1; ?></a>
                    </li>
                <?php } ?>
                <?php if ($end < $html->getNumOfPages() - 2) { ?>
                    <li><span>...</span></li>
                <?php } ?>
                <?php if ($end < $html->getNumOfPages() - 1) { ?>
                    <li><a href="<?php echo $html->getQueryString(array("pageNum" => $html->getNumOfPages() - 1)); ?>"><?php echo $html->getNumOfPages(); ?></a></li>
                <?php } ?>
                <li<?php if ($html->getPageNum() >= $html->getNumOfPages()-1) echo ' class="disabled"'; ?>>
                    <a href="<?php if ($html->getPageNum() < $html->getNumOfPages()-1) echo $html->getQueryString(array("pageNum" => $html->getPageNum()+1)); ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </nav>
</div>
