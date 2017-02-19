<?php if ($paginator->hasPages()) { ?>
    <ul class="pagination">

        <?php if ($paginator->onFirstPage()) { ?>
            <li class="disabled"><span>&laquo;</span></li>
        <?php } else { ?>
            <li><a href="<?php echo $paginator->previousPageUrl() ?>" rel="prev">&laquo;</a></li>
        <?php } ?>

        <?php foreach ($elements as $element) { ?>

            <?php if (is_string($element)) { ?>
                <li class="disabled"><span><?php echo $element ?></span></li>
            <?php } ?>

            <?php if (is_array($element)) { ?>
                <?php foreach ($element as $page => $url) { ?>
                    <?php if ($page == $paginator->currentPage()) { ?>
                        <li class="active"><span><?php echo $page ?></span></li>
                    <?php } else { ?>
                        <li><a href="<?php echo $url ?>"><?php echo $page ?></a></li>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>

        <?php if ($paginator->hasMorePages()) { ?>
            <li><a href="<?php echo $paginator->nextPageUrl() ?>" rel="next">&raquo;</a></li>
        <?php } else { ?>
            <li class="disabled"><span>&raquo;</span></li>
        <?php } ?>
    </ul>
<?php } ?>
