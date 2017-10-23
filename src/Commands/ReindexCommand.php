<?php

namespace Basemkhirat\Elasticsearch\Commands;

use Illuminate\Console\Command;

/**
 * Class ReindexCommand
 * @package Basemkhirat\Elasticsearch\Commands
 */
class ReindexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:indices:reindex {index}{new_index}
                            {--bulk-size=1000 : Scroll size}
                            {--skip-errors : Skip reindexing errors}
                            {--hide-errors : Hide reindexing errors}
                            {--scroll=2m : query scroll time}
                            {--connection= : Elasticsearch connection}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex indices data';

    /**
     * ES connection name
     * @var string
     */
    protected $connection;

    /**
     * ES object
     * @var object
     */
    protected $es;

    /**
     * Query bulk size
     * @var integer
     */
    protected $size;


    /**
     * Scroll time
     * @var string
     */
    protected $scroll;

    /**
     * ReindexCommand constructor.
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

        $this->connection = $this->option("connection") ? $this->option("connection") : config("es.default");

        $this->size = (int)$this->option("bulk-size");

        $this->scroll = $this->option("scroll");

        if ($this->size <= 0 or !is_numeric($this->size)) {
            return $this->warn("Invalide size value");
        }

        $original_index = $this->argument('index');

        $new_index = $this->argument('new_index');

        if (!in_array($original_index, array_keys(config("es.indices")))) {
            return $this->warn("Missing configuration for index: {$original_index}");
        }

        if (!in_array($new_index, array_keys(config("es.indices")))) {
            return $this->warn("Missing configuration for index: {$new_index}");
        }

        $this->migrate($original_index, $new_index);
    }


    /**
     * Migrate data with Scroll queries & Bulk API
     * @param $original_index
     * @param $new_index
     * @param null $scroll_id
     * @param int $errors
     * @param int $page
     */
    function migrate($original_index, $new_index, $scroll_id = null, $errors = 0, $page = 1)
    {

        if ($page == 1) {

            $pages = (int)ceil($this->es->connection($this->connection)->index($original_index)->count() / $this->size);

            $this->output->progressStart($pages);

            $documents = $this->es->connection($this->connection)->index($original_index)->type("")
                ->scroll($this->scroll)
                ->take($this->size)
                ->response();

        } else {

            $documents = $this->es->connection($this->connection)->index($original_index)->type("")
                ->scroll($this->scroll)
                ->scrollID($scroll_id)
                ->response();

        }

        if (isset($documents["hits"]["hits"]) and count($documents["hits"]["hits"])) {

            $data = $documents["hits"]["hits"];

            $params = [];

            foreach ($data as $row) {

                $params["body"][] = [

                    'index' => [
                        '_index' => $new_index,
                        '_type' => $row["_type"],
                        '_id' => $row["_id"]
                    ]

                ];

                $params["body"][] = $row["_source"];

            }

            $response = $this->es->connection($this->connection)->raw()->bulk($params);

            if (isset($response["errors"]) and $response["errors"]) {

                if (!$this->option("hide-errors")) {

                    if ($this->option("skip-errors")) {
                        $this->warn("\n" . json_encode($response["items"]));
                    } else {
                        return $this->warn("\n" . json_encode($response["items"]));
                    }

                }

                $errors++;
            }

            $this->output->progressAdvance();

        } else {

            // Reindexing finished

            $this->output->progressFinish();

            $total = $this->es->connection($this->connection)->index($original_index)->count();

            if ($errors > 0) {
                return $this->warn("$total documents reindexed with $errors errors.");
            } else {
                return $this->info("$total documents reindexed $errors errors.");
            }

        }

        $page++;

        $this->migrate($original_index, $new_index, $documents["_scroll_id"], $errors, $page);
    }

}
