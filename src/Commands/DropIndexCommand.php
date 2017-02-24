<?php

namespace Basemkhirat\Elasticsearch\Commands;

use Basemkhirat\Elasticsearch\Facades\ES;
use Illuminate\Console\Command;

class DropIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:index:drop {index?}
                            {--connection= : Elasticsearch connection}
                            {--force : Drop indices without any confirmation messages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop an index';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $connection = $this->option("connection") ? $this->option("connection") : config("es.default");
        $force = $this->option("force") ? $this->option("force") : 0;

        $client = ES::connection($connection)->raw();

        $indices = !is_null($this->argument('index')) ?
            [$this->argument('index')] :
            array_keys(config('es.indices'));

        foreach ($indices as $index) {

            if (!$client->indices()->exists(['index' => $index])) {
                $this->error("Index \"{$index}\" is not exists!");
                continue;
            }


            if($force or $this->confirm("Are you sure to drop \"$index\" index")) {

                // Create index with settings from config file

                $this->info("Dropping index: {$index}");

                $client->indices()->delete(['index' => $index]);

            }

        }

    }

}
