<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch;

use BadMethodCallException;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Namespaces\AsyncSearchNamespace;
use Elasticsearch\Namespaces\AutoscalingNamespace;
use Elasticsearch\Namespaces\CatNamespace;
use Elasticsearch\Namespaces\CcrNamespace;
use Elasticsearch\Namespaces\ClusterNamespace;
use Elasticsearch\Namespaces\DanglingIndicesNamespace;
use Elasticsearch\Namespaces\EnrichNamespace;
use Elasticsearch\Namespaces\EqlNamespace;
use Elasticsearch\Namespaces\GraphNamespace;
use Elasticsearch\Namespaces\IlmNamespace;
use Elasticsearch\Namespaces\IndicesNamespace;
use Elasticsearch\Namespaces\IngestNamespace;
use Elasticsearch\Namespaces\LicenseNamespace;
use Elasticsearch\Namespaces\MigrationNamespace;
use Elasticsearch\Namespaces\MlNamespace;
use Elasticsearch\Namespaces\MonitoringNamespace;
use Elasticsearch\Namespaces\NodesNamespace;
use Elasticsearch\Namespaces\RollupNamespace;
use Elasticsearch\Namespaces\SearchableSnapshotsNamespace;
use Elasticsearch\Namespaces\SecurityNamespace;
use Elasticsearch\Namespaces\SlmNamespace;
use Elasticsearch\Namespaces\SnapshotNamespace;
use Elasticsearch\Namespaces\SqlNamespace;
use Elasticsearch\Namespaces\SslNamespace;
use Elasticsearch\Namespaces\TasksNamespace;
use Elasticsearch\Namespaces\TransformNamespace;
use Elasticsearch\Namespaces\WatcherNamespace;
use Elasticsearch\Namespaces\XpackNamespace;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Traits\ForwardsCalls;
use InvalidArgumentException;
use Matchory\Elasticsearch\Interfaces\ClientFactoryInterface;
use Matchory\Elasticsearch\Interfaces\ConnectionInterface;
use Matchory\Elasticsearch\Interfaces\ConnectionResolverInterface as Resolver;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Connection
 * ==========
 *
 * @method array bulk(array $params = [])
 * @method array clearScroll(array $params = [])
 * @method array count(array $params = [])
 * @method array _create(array $params = [])
 * @method array delete(array $params = [])
 * @method array deleteByQuery(array $params = [])
 * @method array deleteByQueryRethrottle(array $params = [])
 * @method array deleteScript(array $params = [])
 * @method array exists(array $params = [])
 * @method array existsSource(array $params = [])
 * @method array explain(array $params = [])
 * @method array fieldCaps(array $params = [])
 * @method array get(array $params = [])
 * @method array getScript(array $params = [])
 * @method array getScriptContext(array $params = [])
 * @method array getScriptLanguages(array $params = [])
 * @method array getSource(array $params = [])
 * @method array info(array $params = [])
 * @method array mget(array $params = [])
 * @method array msearch(array $params = [])
 * @method array msearchTemplate(array $params = [])
 * @method array mtermvectors(array $params = [])
 * @method array ping(array $params = [])
 * @method array putScript(array $params = [])
 * @method array rankEval(array $params = [])
 * @method array reindex(array $params = [])
 * @method array reindexRethrottle(array $params = [])
 * @method array renderSearchTemplate(array $params = [])
 * @method array scriptsPainlessExecute(array $params = [])
 * @method array scroll(array $params = [])
 * @method array search(array $params = [])
 * @method array searchShards(array $params = [])
 * @method array searchTemplate(array $params = [])
 * @method array termvectors(array $params = [])
 * @method array update(array $params = [])
 * @method array updateByQuery(array $params = [])
 * @method array updateByQueryRethrottle(array $params = [])
 * @method array closePointInTime(array $params = [])
 * @method array openPointInTime(array $params = [])
 * @method CatNamespace cat()
 * @method ClusterNamespace cluster()
 * @method DanglingIndicesNamespace danglingIndices()
 * @method IndicesNamespace indices()
 * @method IngestNamespace ingest()
 * @method NodesNamespace nodes()
 * @method SnapshotNamespace snapshot()
 * @method TasksNamespace tasks()
 * @method AsyncSearchNamespace asyncSearch()
 * @method AutoscalingNamespace autoscaling()
 * @method CcrNamespace ccr()
 * @method EnrichNamespace enrich()
 * @method EqlNamespace eql()
 * @method GraphNamespace graph()
 * @method IlmNamespace ilm()
 * @method LicenseNamespace license()
 * @method MigrationNamespace migration()
 * @method MlNamespace ml()
 * @method MonitoringNamespace monitoring()
 * @method RollupNamespace rollup()
 * @method SearchableSnapshotsNamespace searchableSnapshots()
 * @method SecurityNamespace security()
 * @method SlmNamespace slm()
 * @method SqlNamespace sql()
 * @method SslNamespace ssl()
 * @method TransformNamespace transform()
 * @method WatcherNamespace watcher()
 * @method XpackNamespace xpack()
 *
 * @package Matchory\Elasticsearch
 */
class Connection implements ConnectionInterface
{
    use ForwardsCalls;

    private const DEFAULT_LOGGER_NAME = 'elasticsearch';

    /**
     * @var Resolver
     * @todo remove in next major version
     */
    private static $resolver;

    /**
     * Elastic config content
     *
     * @var array
     */
    protected $config;

    /**
     * The current connection
     *
     * @var Client
     */
    protected $client;

    /**
     * all available connections
     *
     * @var Client[]
     */
    protected $clients = [];

    /**
     * @var string|null
     */
    protected $index;

    /**
     * Creates a new connection manager
     *
     * @param Client      $client
     * @param string|null $index
     */
    public function __construct(Client $client, ?string $index = null)
    {
        $this->client = $client;
        $this->index = $index;
    }

    /**
     * Set the connection resolver instance.
     *
     * @param Resolver $resolver
     *
     * @return void
     * @internal
     * @deprecated
     * @todo remove in next major version
     */
    public static function setConnectionResolver(Resolver $resolver): void
    {
        static::$resolver = $resolver;
    }

    /**
     * @param ClientBuilder $clientBuilder
     * @param array         $config
     *
     * @return ClientBuilder
     * @throws InvalidArgumentException
     * @deprecated Use the connection manager to create connections instead. It
     *             provides a simpler way to manage connections. This method
     *             will be removed in the next major version.
     * @see        ConnectionManager
     */
    public static function configureLogging(
        ClientBuilder $clientBuilder,
        array $config
    ): ClientBuilder {
        if (Arr::get($config, 'logging.enabled')) {
            $logger = new Logger(self::DEFAULT_LOGGER_NAME);
            $logger->pushHandler(new StreamHandler(
                Arr::get(
                    $config,
                    'logging.location'
                ),
                (int)Arr::get(
                    $config,
                    'logging.level',
                    Logger::INFO
                )
            ));

            $clientBuilder->setLogger($logger);
        }

        return $clientBuilder;
    }

    /**
     * Create a native connection suitable for any non-laravel or non-lumen apps
     * any composer based frameworks
     *
     * @param $config
     *
     * @return Query
     * @throws BindingResolutionException
     * @deprecated Use the connection manager to create connections instead. It
     *             provides a simpler way to manage connections. This method
     *             will be removed in the next major version.
     * @see        ConnectionManager
     */
    public static function create($config): Query
    {
        $app = App::getFacadeApplication();
        $client = $app
            ->make(ClientFactoryInterface::class)
            ->createClient(
                $config['servers'],
                $config['handler'] ?? null
            );

        return (new static(
            $client,
            $config['index'] ?? null
        ))->newQuery();
    }

    /**
     * Create a connection for laravel or lumen frameworks
     *
     * @param string $name
     *
     * @return Query
     * @deprecated Use the connection manager to create connections instead. It
     *             provides a simpler way to manage connections. This method
     *             will be removed in the next major version.
     * @see        ConnectionManager
     */
    public function connection(string $name): Query
    {
        return $this->newQuery($name);
    }

    /**
     * Check if the connection is already loaded
     *
     * @param string $name
     *
     * @return bool
     * @deprecated Use the connection manager to create connections instead. It
     *             provides a simpler way to manage connections. This method
     *             will be removed in the next major version.
     * @see        ConnectionManager
     */
    public function isLoaded(string $name): bool
    {
        return (bool)static::$resolver->connection($name);
    }

    /**
     * Proxy  calls to the default connection
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call(string $name, array $arguments)
    {
        return $this->forwardCallTo(
            $this->getClient(),
            $name,
            $arguments
        );
    }

    /**
     * @inheritDoc
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @inheritDoc
     */
    public function index(string $index): Query
    {
        return $this->newQuery()->index($index);
    }

    public function insert(
        array $parameters,
        ?string $index = null,
        ?string $type = null
    ): object {
        if (
            ! isset($parameters[Query::PARAM_INDEX]) &&
            $index = $index ?? $this->index
        ) {
            $parameters[Query::PARAM_INDEX] = $index;
        }

        if ($type) {
            $parameters[Query::PARAM_TYPE] = $type;
        }

        return (object)$this->client->index($parameters);
    }

    /**
     * Route the request to the query class
     *
     * @param string|null $connection Deprecated parameter: Use the proper
     *                                connection instance directly instead of
     *                                passing the name. This parameter will be
     *                                removed in the next major version.
     *
     * @return Query
     */
    public function newQuery(?string $connection = null): Query
    {
        // TODO: This is deprecated behaviour and should be removed in the next
        //       major version.
        if ($connection) {
            return static::$resolver
                ->connection($connection)
                ->newQuery();
        }

        $query = new Query($this);

        return $query->index($this->index);
    }
}
