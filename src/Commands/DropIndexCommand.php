<?php

namespace Matchory\Elasticsearch\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Matchory\Elasticsearch\Connection;
use RuntimeException;

class DropIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:indices:drop {index?}
                            {--connection= : Elasticsearch connection}
                            {--force : Drop indices without any confirmation messages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop an index';

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
     * @return mixed
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function handle()
    {
        $connectionName = $this->option("connection") ?: config('es.default');
        $connection = $this->es->connection($connectionName);

        if ( ! $connection) {
            throw new RuntimeException('No connection');
        }

        $force = $this->option("force") ?: 0;
        $client = $connection->raw();
        $indices = ! is_null($this->argument('index'))
            ? [$this->argument('index')]
            : array_keys(config('es.indices'));

        foreach ($indices as $index) {
            if ( ! $client->indices()->exists(['index' => $index])) {
                $this->warn("Index '{$index}' does not exist.");

                continue;
            }

            if (
                $force ||
                $this->confirm("Are you sure to drop '{$index}' index")
            ) {
                $this->info("Dropping index: {$index}");

                $client->indices()->delete(['index' => $index]);
            }
        }
    }

}
