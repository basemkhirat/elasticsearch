<?php

namespace Basemkhirat\Elasticsearch\Commands;

use Illuminate\Console\Command;

class CreateIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:indices:create {index?}{--connection= : Elasticsearch connection}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new index using defined setting and mapping in config file';

    /**
     * ES object
     * @var object
     */
    protected $es;

    /**
     * CreateIndexCommand constructor.
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

            if ($client->indices()->exists(['index' => $index])) {
                $this->warn("Index {$index} is already exists!");
                continue;
            }

            // Create index with settings from config file

            $this->info("Creating index: {$index}");

            $client->indices()->create([

                'index' => $index,

                'body' => [
                    "settings" => $config['settings']
                ]

            ]);


            if (isset($config['aliases'])) {

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
                        'body' => $mapping,
                        "include_type_name" => true
                    ]);

                }
            }
        }
    }
}
