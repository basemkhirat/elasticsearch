<?php

namespace Basemkhirat\Elasticsearch\Tests;

use PHPUnit\Framework\TestCase;
use Basemkhirat\Elasticsearch\Query;

class QueryTest extends TestCase
{
    protected Query $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = new Query([]);
    }

    /** @test */
    public function it_can_build_some_query()
    {
        $query = $this->query->where("views", ">", 14)
            ->some(function($query) {
                $query->where("id", '=', "1")
                    ->where("id", '=', "2");
            })
            ->getBody();

        $expected = [
            '_source' => [
                'include' => [],
                'exclude' => []
            ],
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'views' => [
                                    'gt' => 14
                                ]
                            ]
                        ]
                    ],
                    'should' => [
                        [
                            'term' => [
                                'id' => '1'
                            ]
                        ],
                        [
                            'term' => [
                                'id' => '2'
                            ]
                        ]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        ];

        $this->assertEquals($expected, $query);
    }

    /** @test */
    public function it_can_build_every_query()
    {
        $query = $this->query->where("views", ">", 14)
            ->every(function($query) {
                $query->where("id", '=', "1")
                    ->where("id", '=', "2");
            })
            ->getBody();

        $expected = [
            '_source' => [
                'include' => [],
                'exclude' => []
            ],
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'views' => [
                                    'gt' => 14
                                ]
                            ]
                        ],
                        [
                            'term' => [
                                'id' => '1'
                            ]
                        ],
                        [
                            'term' => [
                                'id' => '2'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $query);
    }

    /** @test */
    public function it_can_build_nested_some_every_query()
    {
        $query = $this->query->where("views", ">", 14)
            ->some(function($query) {
                $query->where("id", '=', "1")
                    ->every(function($query) {
                        $query->where("title.keyword", '=', "المحيط في اللغة")
                            ->where("id", '=', "4");
                    });
            })
            ->getBody();

        $expected = [
            '_source' => [
                'include' => [],
                'exclude' => []
            ],
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'views' => [
                                    'gt' => 14
                                ]
                            ]
                        ]
                    ],
                    'should' => [
                        [
                            'term' => [
                                'id' => '1'
                            ]
                        ],
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'term' => [
                                            'title.keyword' => 'المحيط في اللغة'
                                        ]
                                    ],
                                    [
                                        'term' => [
                                            'id' => '4'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        ];

        $this->assertEquals($expected, $query);
    }

    /** @test */
    public function it_can_build_complex_nested_query()
    {
        $query = $this->query->where("views", ">", 14)
            ->every(function($query) {
                $query->where("id", '=', "1")
                    ->where("id", '=', "4")
                    ->some(function($query) {
                        $query->where("id", '=', "5")
                            ->every(function($query) {
                                $query->where("is_active", '=', 1)
                                    ->where("is_deleted", '=', 0);
                            });
                    });
            })
            ->getBody();

        $expected = [
            '_source' => [
                'include' => [],
                'exclude' => []
            ],
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'views' => [
                                    'gt' => 14
                                ]
                            ]
                        ],
                        [
                            'term' => [
                                'id' => '1'
                            ]
                        ],
                        [
                            'term' => [
                                'id' => '4'
                            ]
                        ],
                        [
                            'bool' => [
                                'should' => [
                                    [
                                        'bool' => [
                                            'filter' => [
                                                [
                                                    'term' => [
                                                        'id' => '5'
                                                    ]
                                                ],
                                                [
                                                    'bool' => [
                                                        'filter' => [
                                                            [
                                                                'term' => [
                                                                    'is_active' => 1
                                                                ]
                                                            ],
                                                            [
                                                                'term' => [
                                                                    'is_deleted' => 0
                                                                ]
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                'minimum_should_match' => 1
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $query);
    }
}
