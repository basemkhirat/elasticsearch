<?php

namespace Basemkhirat\Elasticsearch\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;

/**
 * Class ListIndicesCommand
 * @package Basemkhirat\Elasticsearch\Commands
 */
class ListIndicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:indices:list {--connection= : Elasticsearch connection}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all indices';

    /**
     * Indices headers
     * @var array
     */
    protected $headers = ["configured (es.php)", "health", "status", "index", "uuid", "pri", "rep", "docs.count", "docs.deleted", "store.size", "pri.store.size"];

    /**
     * ES object
     * @var object
     */
    protected $es;

    /**
     * ListIndicesCommand constructor.
     */
    function __construct()
    {
        parent::__construct();
        $this->es = app("es");
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $connection = $this->option("connection") ? $this->option("connection") : config("es.default");

        $indices = $this->es->connection($connection)->raw()->cat()->indices();

        if (is_array($indices)) {
            $indices = $this->getIndicesFromArrayResponse($indices);
        } else {
            $indices = $this->getIndicesFromStringResponse($indices);
        }

        if (count($indices)) {
            $this->table($this->headers, $indices);
        } else {
            $this->warn('No indices found.');
        }

    }


    /**
     * Get a list of indices data
     * Match newer versions of elasticsearch/elasticsearch package (5.1.1 or higher)
     * @param $indices
     * @return array
     */
    function getIndicesFromArrayResponse($indices)
    {

        $data = [];

        foreach ($indices as $row) {

            if (in_array($row['index'], array_keys(config("es.indices")))) {
                $row = Arr::prepend($row, "yes");
            } else {
                $row = Arr::prepend($row, "no");
            }

            $data[] = $row;

        }

        return $data;

    }

    /**
     * Get list of indices data
     * Match older versions of elasticsearch/elasticsearch package.
     * @param $indices
     * @return array
     */
    function getIndicesFromStringResponse($indices)
    {

        $lines = explode("\n", trim($indices));

        $data = [];

        foreach ($lines as $line) {

            $row = [];

            $line_array = explode(" ", trim($line));

            foreach ($line_array as $item) {
                if (trim($item) != "") {
                    $row[] = $item;
                }
            }

            if (in_array($row[2], array_keys(config("es.indices")))) {
                $row = Arr::prepend($row, "yes");
            } else {
                $row = Arr::prepend($row, "no");
            }

            $data[] = $row;
        }

        return $data;
    }
}
