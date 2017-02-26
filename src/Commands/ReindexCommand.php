<?php

namespace Basemkhirat\Elasticsearch\Commands;

use Basemkhirat\Elasticsearch\Facades\ES;
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
    protected $signature = 'es:indices:reindex {index}{new_index}{--size=1000 : Scroll size}{--connection= : Elasticsearch connection}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex indices data';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->connection = $this->option("connection") ? $this->option("connection") : config("es.default");

        $this->size = (int)$this->option("size");

        if($this->size <= 0 or !is_numeric($this->size)){
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
     * @param $original_index
     * @param $new_index
     * @param null $scroll_id
     * @param int $page
     */
    function migrate($original_index, $new_index, $scroll_id = null, $page = 1)
    {

        if ($page == 1) {

            $documents = ES::connection($this->connection)->index($original_index)->type("")
                ->scroll("2m")
                ->take($this->size)
                ->response();

        } else {

            $documents = ES::connection($this->connection)->index($original_index)->type("")
                ->scroll("2m")
                ->scrollID($scroll_id)
                ->response();

        }

        if (isset($documents["hits"]["hits"]) and count($documents["hits"]["hits"])) {

            $reindexed = $page * $this->size;
            $reindexed = $reindexed > $documents["hits"]["total"] ? $documents["hits"]["total"]: $reindexed;
            $percentage = round($reindexed / $documents["hits"]["total"] * 100);

            $this->info("Migrating data: " . $reindexed . "/" . $documents["hits"]["total"]." ($percentage%)" );

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

            $response = ES::connection($this->connection)->raw()->bulk($params);

            if (isset($response["errors"]) and $response["errors"]) {
                return $this->error(json_encode($response["items"]));
            }

        } else {

            return $this->info("Done.");

        }

        $page++;

        $this->migrate($original_index, $new_index, $documents["_scroll_id"], $page);

    }

}
