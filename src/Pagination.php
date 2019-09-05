<?php

namespace CarlosOCarvalho\Elasticsearch;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\UrlWindow;

class Pagination extends LengthAwarePaginator
{

    /**
     * Render the paginator using the given view.
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


    /**
     * Get the array of elements to pass to the view.
     * @return array
     */
    protected function elements()
    {

        $window = UrlWindow::make($this);

        return array_filter([
            $window['first'],
            is_array($window['slider']) ? '...' : null,
            $window['slider'],
            is_array($window['last']) ? '...' : null,
            $window['last'],
        ]);
    }

    /**
     * Determine if the paginator is on the first page.
     * @return bool
     */
    public function onFirstPage()
    {
        return $this->currentPage() <= 1;
    }

    public function toArray()
    {
        $data = [
            'current_page' => $this->currentPage(),
            'data' => $this->items->toArray(),
            'first_page_url' => $this->url(1),
            'from' => $this->firstItem(),
            'last_page' => $this->lastPage(),
            'last_page_url' => $this->url($this->lastPage()),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path,
            'per_page' => $this->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'to' => $this->lastItem(),
            'total' => $this->total(),

        ];

        if( isset($this->aggregations) ){
            $data = array_merge(['aggregations' => $this->aggregations], $data);
        }
        return $data;
    }


}
