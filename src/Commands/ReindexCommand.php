<?php

namespace Matchory\Elasticsearch\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Matchory\Elasticsearch\Connection;
use RuntimeException;

/**
 * Class ReindexCommand
 *
 * @package Matchory\Elasticsearch\Commands
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
     *
     * @var string
     */
    protected $connection;

    /**
     * ES object
     *
     * @var Connection
     */
    protected $es;

    /**
     * Query bulk size
     *
     * @var integer
     */
    protected $size;

    /**
     * Scroll time
     *
     * @var string
     */
    protected $scroll;

    /**
     * ReindexCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->es = app("es");
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function handle(): void
    {
        $this->connection = $this->option("connection") ?: config('es.default');
        $this->size = (int)$this->option("bulk-size");
        $this->scroll = $this->option("scroll");

        if ($this->size <= 0 || ! is_numeric($this->size)) {
            $this->warn("Invalid size value");

            return;
        }

        $originalIndex = $this->argument('index');
        $newIndex = $this->argument('new_index');

        if ( ! array_key_exists($originalIndex, config('es.indices'))) {
            $this->warn("Missing configuration for index: {$originalIndex}");

            return;
        }

        if ( ! array_key_exists($newIndex, config('es.indices'))) {
            $this->warn("Missing configuration for index: {$newIndex}");

            return;
        }

        $this->migrate($originalIndex, $newIndex);
    }

    /**
     * Migrate data with Scroll queries & Bulk API
     *
     * @param string      $originalIndex
     * @param string      $newIndex
     * @param string|null $scroll_id
     * @param int         $errors
     * @param int         $page
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function migrate(
        string $originalIndex,
        string $newIndex,
        ?string $scroll_id = null,
        int $errors = 0,
        int $page = 1
    ): void {
        $connection = $this->es->connection($this->connection);

        if ( ! $connection) {
            throw new RuntimeException('No connection');
        }

        if ($page === 1) {
            $pages = (int)ceil(
                $connection
                    ->index($originalIndex)
                    ->count() / $this->size
            );

            $this->output->progressStart($pages);

            $documents = $connection
                ->index($originalIndex)
                ->type('')
                ->scroll($this->scroll)
                ->take($this->size)
                ->response();
        } else {
            $documents = $connection
                ->index($originalIndex)
                ->type('')
                ->scroll($this->scroll)
                ->scrollID($scroll_id)
                ->response();
        }

        if (
            isset($documents['hits']['hits']) &&
            count($documents['hits']['hits'])
        ) {
            $data = $documents['hits']['hits'];
            $params = [];

            foreach ($data as $row) {
                $params['body'][] = [

                    'index' => [
                        '_index' => $newIndex,
                        '_type' => $row['_type'],
                        '_id' => $row['_id'],
                    ],

                ];

                $params['body'][] = $row['_source'];
            }

            $response = $connection->raw()->bulk($params);

            if (isset($response['errors']) && $response['errors']) {
                if ( ! $this->option('hide-errors')) {
                    $items = json_encode($response['items']);

                    if ( ! $this->option('skip-errors')) {
                        $this->warn("\n{$items}");

                        return;
                    }

                    $this->warn("\n{$items}");
                }

                $errors++;
            }

            $this->output->progressAdvance();
        } else {
            // Reindexing finished
            $this->output->progressFinish();

            $total = $connection
                ->index($originalIndex)
                ->count();

            if ($errors > 0) {
                $this->warn("{$total} documents reindexed with {$errors} errors.");

                return;
            }

            $this->info("{$total} documents reindexed successfully.");

            return;
        }

        $page++;

        $this->migrate(
            $originalIndex,
            $newIndex,
            $documents['_scroll_id'],
            $errors,
            $page
        );
    }

}
