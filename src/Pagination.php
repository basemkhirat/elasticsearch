<?php

namespace Basemkhirat\Elasticsearch;

use Illuminate\Pagination\LengthAwarePaginator;

class Pagination extends LengthAwarePaginator
{

    /**
     * Render the paginator using the given view.
     *
     * @param  string  $view
     * @param  array  $data
     * @return string
     */
    public function links($view = "default", $data = [])
    {
        extract($data);

        $paginator = $this;

        $elements = $this->elements();

        require dirname(__FILE__) . "/pagination/" . $view . ".php";
    }

}
