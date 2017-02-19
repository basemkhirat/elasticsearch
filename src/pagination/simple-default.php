<?php if ($paginator->hasPages()) { ?>
    <ul class="pagination">
        <?php if ($paginator->onFirstPage()) { ?>
            <li class="disabled"><span>&laquo;</span></li>
        <?php } else { ?>
            <li><a href="<?php echo $paginator->previousPageUrl() ?>" rel="prev">&laquo;</a></li>
        <?php } ?>

        <?php if ($paginator->hasMorePages()) { ?>
            <li><a href="<?php echo $paginator->nextPageUrl() ?>" rel="next">&raquo;</a></li>
        <?php } else { ?>
            <li class="disabled"><span>&raquo;</span></li>
        <?php } ?>
    </ul>
<?php } ?>
