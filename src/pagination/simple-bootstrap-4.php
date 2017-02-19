<?php if ($paginator->hasPages()){ ?>
    <ul class="pagination">

        <?php if ($paginator->onFirstPage()){ ?>
            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
        <?php }else{ ?>
            <li class="page-item"><a class="page-link" href="<?php echo $paginator->previousPageUrl() ?>" rel="prev">&laquo;</a></li>
            <?php } ?>

        <?php if ($paginator->hasMorePages()){ ?>
            <li class="page-item"><a class="page-link" href="<?php echo $paginator->nextPageUrl() ?>" rel="next">&raquo;</a></li>
        <?php }else{ ?>
            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
        <?php } ?>

    </ul>
<?php } ?>
