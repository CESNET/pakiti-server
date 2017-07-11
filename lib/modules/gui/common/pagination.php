<?php
# Copyright (c) 2017, CESNET. All rights reserved.
#
# Redistribution and use in source and binary forms, with or
# without modification, are permitted provided that the following
# conditions are met:
#
#   o Redistributions of source code must retain the above
#     copyright notice, this list of conditions and the following
#     disclaimer.
#   o Redistributions in binary form must reproduce the above
#     copyright notice, this list of conditions and the following
#     disclaimer in the documentation and/or other materials
#     provided with the distribution.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND
# CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
# INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
# MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
# DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS
# BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
# EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
# TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
# DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
# ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
# OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
# OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
# POSSIBILITY OF SUCH DAMAGE.
?>

<?php
# getNumOfPages
# getPageNum
# getQueryString
?>

<?php
    # Calculate start and end pagination
    $long = 10;
    if($html->getPageNum() - 6 > 0){
        $long -= 2;
        $start = $html->getPageNum() - 3;
    } else {
        $start = 0;
    }

    if($start + $long < $html->getNumOfPages() - 1){
        $long -= 2;
        $end = $start + $long;
    } else {
        $end = $html->getNumOfPages() - 1;
        if ($end - 10 > 0) {
            $start = $end - 8;
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
