<?php

namespace Basemkhirat\Elasticsearch\Commands;

use Illuminate\Console\Command;

class UpdateIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:indices:update {index?}{--connection= : Elasticsearch connection}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update index using defined setting and mapping in config file';

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

        $client = $this->es->connection($connection)->raw();

        $indices = !is_null($this->argument('index')) ?
            [$this->argument('index')] :
            array_keys(config('es.indices'));

        foreach ($indices as $index) {

            $config = config("es.indices.{$index}");

            if (is_null($config)) {
                $this->warn("Missing configuration for index: {$index}");
                continue;
            }

            if (!$client->indices()->exists(['index' => $index])) {

                return $this->call("es:indices:create", [
                    "index" => $index
                ]);

            }

            // The index is already exists. update aliases and setting

            // Remove all index aliases

            $this->info("Removing aliases for index: {$index}");

            $client->indices()->updateAliases([
                "body" => [
                    'actions' => [
                        [
                            'remove' => [
                                'index' => $index,
                                'alias' => "*"
                            ]
                        ]
                    ]

                ],

                'client' => ['ignore' => [404]]
            ]);

            if (isset($config['aliases'])) {

                // Update index aliases from config

                foreach ($config['aliases'] as $alias) {

                    $this->info("Creating alias: {$alias} for index: {$index}");

                    $client->indices()->updateAliases([
                        "body" => [
                            'actions' => [
                                [
                                    'add' => [
                                        'index' => $index,
                                        'alias' => $alias
                                    ]
                                ]
                            ]

                        ]
                    ]);

                }

            }

            if (isset($config['mappings'])) {

                foreach ($config['mappings'] as $type => $mapping) {

                    // Create mapping for type from config file

                    $this->info("Creating mapping for type: {$type} in index: {$index}");

                    $client->indices()->putMapping([
                        'index' => $index,
                        'type' => $type,
                        'body' => $mapping
                    ]);

                }
            }
        }
    }
}
