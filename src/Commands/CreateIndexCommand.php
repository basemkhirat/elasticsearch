<?php

namespace Basemkhirat\Elasticsearch\Commands;

use Illuminate\Console\Command;


use Elasticsearch\ClientBuilder;

class CreateIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new index';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


        $host = config('elasticsearch.hosts');
        $client = ClientBuilder::create()->build();
        $indices = $client->cat()->indices();
        if(count($indices) > 0) {

            //dd($indices);

            //$headers = array_keys(current($indices));
            $this->table([], $indices);
        } else {
            $this->warn('No indices found.');
        }

    }
}
