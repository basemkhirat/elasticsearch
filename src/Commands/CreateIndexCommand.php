<?php

namespace Matchory\Elasticsearch\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Matchory\Elasticsearch\Connection;
use RuntimeException;

use function app;
use function array_keys;
use function config;
use function is_null;

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
     *
     * @var Connection
     */
    protected $es;

    public function __construct()
    {
        parent::__construct();

        $this->es = app("es");
    }

    /**
     * Execute the console command.
     *
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function handle(): void
    {
        $connectionName = $this->option("connection") ?: config('es.default');
        $connection = $this->es->connection($connectionName);
        $client = $connection->raw();

        /** @var string[] $indices */
        $indices = ! is_null($this->argument('index'))
            ? [$this->argument('index')]
            : array_keys(config('es.indices'));

        foreach ($indices as $index) {
            $config = config("es.indices.{$index}");

            if (is_null($config)) {
                $this->warn("Missing configuration for index: {$index}");

                continue;
            }

            if ($client->indices()->exists(['index' => $index])) {
                $this->warn("Index {$index} already exists!");

                continue;
            }

            // Create index with settings from config file

            $this->info("Creating index: {$index}");

            $client->indices()->create([
                'index' => $index,
                'body' => [
                    "settings" => $config['settings'],
                ],

            ]);

            if (isset($config['aliases'])) {
                foreach ($config['aliases'] as $alias) {
                    $this->info(
                        "Creating alias: {$alias} for index: {$index}"
                    );

                    $client->indices()->updateAliases([
                        "body" => [
                            'actions' => [
                                [
                                    'add' => [
                                        'index' => $index,
                                        'alias' => $alias,
                                    ],
                                ],
                            ],

                        ],
                    ]);
                }
            }

            if (isset($config['mappings'])) {
                foreach ($config['mappings'] as $type => $mapping) {
                    $this->info(
                        "Creating mapping for type: {$type} in index: {$index}"
                    );

                    // Create mapping for type from config file
                    $client->indices()->putMapping([
                        'index' => $index,
                        'type' => $type,
                        'body' => $mapping,
                        "include_type_name" => true,
                    ]);
                }
            }
        }
    }
}
