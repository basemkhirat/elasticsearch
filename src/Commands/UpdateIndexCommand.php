<?php

namespace Basemkhirat\Elasticsearch\Commands;

use Illuminate\Console\Command;
use Basemkhirat\Elasticsearch\Facades\ES;

class UpdateIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:index:update {index?}{--connection= : Elasticsearch connection}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update index using defined setting and mapping in config file';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $connection = $this->option("connection") ? $this->option("connection") : config("es.default");

        $client = ES::connection($connection)->raw();

        $indices = ! is_null($this->argument('index')) ?
            [$this->argument('index')] :
            array_keys(config('es.indices'));

        foreach ($indices as $index) {

            $config = config("es.indices.{$index}");

            if(is_null($config)) {
                $this->error("Config for index \"{$index}\" not found, skipping...");
                continue;
            }

            // Delete index if it already exists

            if ($client->indices()->exists(['index' => $index])) {

                $this->warn("Index \"{$index}\" exists, dropping!");

                $this->call("es:index:drop", [
                    "index" => $index,
                    "--force" => true
                ]);

            }

            // Create index with settings from config file

            $this->call("es:index:create", [
                "index" => $index
            ]);

        }

    }

}
