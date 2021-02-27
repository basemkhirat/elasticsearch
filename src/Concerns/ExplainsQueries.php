<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch\Concerns;

trait ExplainsQueries
{
    /**
     * Returns information about why a specific document matches (or doesn't
     * match) a query.
     *
     * The explain API computes a score explanation for a query and a specific
     * document. This can give useful feedback whether a document matches or
     * didn't match a specific query.
     *
     * > **Note:** If the Elasticsearch security features are enabled, you must
     * > have the read index privilege for the target index.
     *
     * @param string|null $id      Document ID. Defaults to the ID in the query.
     * @param bool        $lenient If `true`, format-based query failures (such
     *                             as providing text to a numeric field) will be
     *                             ignored. Defaults to `false`.
     *
     * @return array|null
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-explain.html
     */
    public function explain(?string $id = null, bool $lenient = false): ?array
    {
        $body = $this->getBody();
        $query = $body['body'] ?? null;
        $source = $body['source'] ?? null;
        $id = $id ?? $this->getId();

        if ( ! $query || ! $id) {
            return null;
        }

        return $this->getConnection()->getClient()->explain([
            'index' => $this->getIndex(),
            'lenient' => $lenient,
            'id' => $id,
            'body' => ['query' => $query],
            '_source' => $source ?? false,
        ]);
    }
}
