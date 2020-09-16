<?php

namespace Matchory\Elasticsearch;

use Illuminate\Pagination\LengthAwarePaginator;

use const EXTR_OVERWRITE;

class Pagination extends LengthAwarePaginator
{

    /**
     * Render the paginator using the given view.
     *
     * @param string $view
     * @param array  $data
     *
     * @return string
     * @noinspection PhpUnusedLocalVariableInspection
     */
    public function links($view = 'default', $data = []): string
    {
        extract($data, EXTR_OVERWRITE);

        $paginator = $this;
        $elements = $this->elements();

        switch ($view) {
            case 'bootstrap-4':
                return require __DIR__ . '/pagination/bootstrap-4.php';

            case 'default':
                return require __DIR__ . '/pagination/default.php';

            case 'simple-bootstrap-4':
                return require __DIR__ . '/pagination/simple-bootstrap-4.php';

            case 'simple-default':
                return require __DIR__ . '/pagination/simple-default.php';

            default:
                return '';
        }
    }
}
