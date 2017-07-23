<?php

namespace Basemkhirat\Elasticsearch\Commands;

use Illuminate\Console\Command;

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
     * @var object
     */
    protected $es;

    /**
     * DropIndexCommand constructor.
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
        $force = $this->option("force") ? $this->option("force") : 0;

        $client = $this->es->connection($connection)->raw();

        $indices = !is_null($this->argument('index')) ?
            [$this->argument('index')] :
            array_keys(config('es.indices'));

        foreach ($indices as $index) {

            if (!$client->indices()->exists(['index' => $index])) {
                $this->warn("Index: {$index} is not exist!");
                continue;
            }

            if ($force or $this->confirm("Are you sure to drop \"$index\" index")) {

                $this->info("Dropping index: {$index}");

                $client->indices()->delete(['index' => $index]);

            }

        }

    }

}
