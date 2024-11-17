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
                    ->where("id", '=', "4");
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
                                'id' => '4'
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
                    ->where("id", '=', "4")
                    ->every(function($query) {
                        $query->where("is_active", '=', 1)
                            ->where("is_deleted", '=', 0);
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
                            'term' => [
                                'id' => '4'
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
                                            'should' => [
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
                                            ],
                                            'minimum_should_match' => 1
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

    /** @test */
    public function it_can_build_nested_some_query()
    {
        $query = $this->query->where("views", ">", 14)
            ->where("views", "!=", 100)
            ->where("id", '=', "1")
            ->some(function($query) {
                $query->where("title.keyword", '=', "المحيط في اللغة")
                    ->where("id", '!=', "3")
                    ->search("محمود محمد محمود", function($query) {
                        $query->fields(["description" => 1]);
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
                        ]
                    ],
                    'must_not' => [
                        [
                            'term' => [
                                'views' => 100
                            ]
                        ]
                    ],
                    'should' => [
                        [
                            'term' => [
                                'title.keyword' => 'المحيط في اللغة'
                            ]
                        ],
                        [
                            'query_string' => [
                                'query' => 'محمود محمد محمود',
                                'fields' => ['description']
                            ]
                        ],
                        [
                            'bool' => [
                                'must_not' => [
                                    [
                                        'term' => [
                                            'id' => '3'
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
}
