<?php
/**
 * @var Pagination $paginator
 * @var string[][] $elements
 */

use Matchory\Elasticsearch\Pagination;

?><?php if ($paginator->hasPages()): ?>
    <ul class="pagination">

        <?php if ($paginator->onFirstPage()): ?>
            <li class="disabled">
                <span>&laquo;</span>
            </li>
        <?php else: ?>
            <li>
                <a href="<?= $paginator->previousPageUrl() ?>"
                   rel="prev">&laquo;</a>
            </li>
        <?php endif ?>

        <?php if ($paginator->hasMorePages()): ?>
            <li>
                <a href="<?= $paginator->nextPageUrl() ?>"
                   rel="next">&raquo;</a>
            </li>
        <?php else: ?>
            <li class="disabled">
                <span>&raquo;</span>
            </li>
        <?php endif ?>
    </ul>
<?php endif ?>
