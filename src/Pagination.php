<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\HtmlString;

use const EXTR_OVERWRITE;

class Pagination extends LengthAwarePaginator
{
    /**
     * Render the paginator using the given view.
     *
     * @param string|null $view
     * @param array       $data
     *
     * @return Htmlable
     * @noinspection PhpUnusedLocalVariableInspection
     */
    public function links($view = 'default', $data = []): Htmlable
    {
        extract($data, EXTR_OVERWRITE);

        $paginator = $this;
        $elements = $this->elements();
        $html = '';

        switch ($view) {
            case 'bootstrap-4':
                $html = require __DIR__ . '/pagination/bootstrap-4.php';
                break;

            case 'default':
                $html = require __DIR__ . '/pagination/default.php';
                break;

            case 'simple-bootstrap-4':
                $html = require __DIR__ . '/pagination/simple-bootstrap-4.php';
                break;

            case 'simple-default':
                $html = require __DIR__ . '/pagination/simple-default.php';
                break;
        }

        return new HtmlString($html);
    }
}
