<?php
/**
 * @var Pagination $paginator
 * @var string[][] $elements
 */

use Matchory\Elasticsearch\Pagination;

?><?php if ($paginator->hasPages()): ?>
    <ul class="pagination">

        <?php if ($paginator->onFirstPage()): ?>
            <li class="page-item disabled">
                <span class="page-link">&laquo;</span>
            </li>
        <?php else: ?>
            <li class="page-item">
                <a class="page-link"
                   href="<?php echo $paginator->previousPageUrl() ?>"
                   rel="prev">&laquo;</a>
            </li>
            <?php endif ?>

        <?php if ($paginator->hasMorePages()): ?>
            <li class="page-item">
                <a class="page-link"
                   href="<?php echo $paginator->nextPageUrl() ?>"
                   rel="next">&raquo;</a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link">&raquo;</span>
            </li>
        <?php endif ?>
    </ul>
<?php endif ?>
