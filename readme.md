### Flexible elasticseach builder to run complex queries with an easier way.

#####1) Install package via composer:

	composer require basemkhirat/elasticsearch

#####2) Add package service provider:

	Basemkhirat\Elasticsearch\ElasticsearchServiceProvider::class
	
#####3) Add package alias:

	'ES' => Basemkhirat\Elasticsearch\Facades\ES::class
	
#####4) Publishing:
    
    php artisan vendor:publish --provider="Basemkhirat\Elasticsearch\ElasticsearchServiceProvider"
	
### Usage:

#### Setting your connections

  
  After publishing, the config file is placed here `config/es.php`
  where you can add more than one elasticsearch node.


#### Creating a new index

    ES::index("my_index")->create();
    
    # or 
    
    ES::create("my_index");
    
    
>

    # [optional] you can create index with custom options
    
    ES::index("my_index")->create(function($index){
            
        $index->shards(5)->replicas(1)->mappping([
            'my_type' => [
                'properties' => [
                    'first_name' => [
                        'type' => 'string',
                    ],
                    'age' => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ])
        
    });
    
    # or
    
    ES::create("my_index", function($index){
      
          $index->shards(5)->replicas(1)->mappping([
              'my_type' => [
                  'properties' => [
                      'first_name' => [
                          'type' => 'string',
                      ],
                      'age' => [
                          'type' => 'integer'
                      ]
                  ]
              ]
          ])
      
    });


#### Dropping index

    ES::index("my_index")->drop();
        
    # or
    
    ES::drop("my_index");
    
#### Running queries:

    $documents = ES::connection("default")
                    ->index("my_index")
                    ->type("my_type")
                    ->get();    // return collection of results

you can rewite the above query to

    $documents = ES::get();    // return collection of results
    
the query builder will use the default connection, index, and type names setted in configuration file `es.php`. 
 
Index and type names setted in query will override values the configuration file


#### Available methods:

##### Getting document by id

    $documents = ES::_id(3)->get();

##### Sorting
    
    $documents = ES::orderBy("created_at", "desc")->get();
    $documents = ES::orderBy("_score")->get();
    
##### Limit and offset
    
    $documents = ES::take(10)->skip(5)->get();
    
##### Select only specific fields
    
    $documents = ES::select("title", "content")->take(10)->skip(5)->get();
    
##### Where clause
    
    ES::where("views", 150)->get(); or ES::where("views", "=", 150)->get();

##### Where greater than

    ES::where("views", ">", 150)->get();
    
##### Where greater than or equal

    ES::where("views", ">=", 150)->get();
    
##### Where less than

    ES::where("views", "<", 150)->get();
    
##### Where greater than or equal

    ES::where("views", "<=", 150)->get();
    
##### Where like

    ES::where("title", "like", "foo")->get();
    
##### Where field exists

    ES::where("hobbies", "exists", true)->get(); or ES::whereExists("hobbies", true)->get();
    
##### Where in clause
    
    ES::whereIn("id", [100, 150])->get();
    
##### Where between clause 
    
    ES::whereBetween("id", 100, 150)->get();
   
  >
    
##### Where not clause
    
    ES::whereNot("views", 150)->get(); or ES::where("views", "=", 150)->get();

##### Where not greater than

    ES::whereNot("views", ">", 150)->get();

##### Where not greater than or equal

    ES::whereNot("views", ">=", 150)->get();
    
##### Where not less than

    ES::whereNot("views", "<", 150)->get();
    
##### Where not less than or equal

    ES::whereNot("views", "<=", 150)->get();
    
##### Where not like

    ES::whereNot("title", "like", "foo")->get();
    
##### Where not field exists

    ES::whereNot("hobbies", "exists", true)->get(); or ES::whereExists("hobbies", true)->get();
    
##### Where not in clause
    
    ES::whereNotIn("id", [100, 150])->get();
    
##### Where not between clause 
    
    ES::whereNotBetween("id", 100, 150)->get();
    
    
  >
  
##### Search the entire document
    
    
    ES::search("bar")->get();
    
    # search with Boost = 2
    
    ES::search("bar", 2)->get();
    
  >
  
##### Return only first record
    
    ES::search("bar")->first();
    
  >
  
##### Return only count
    
    ES::search("bar")->count();
    
  >
    
##### Paginate results with per_page = 5
      
    $documents = ES::search("bar")->paginate(5);
    
    # getting pagination links
    
    $documents->links();
    
    
  >
  
##### Executing elasticsearch raw queries
    
    ES::raw()->search([
        "index" => "my_index",
        "type"  => "my_type",
        "body"  => [
            "query": [
            "bool": [
                    "must": [
                        [ "match": [ "address": "mill" ] ],
                        [ "match": [ "address": "lane" ] ] 
                    ]
                ]
            ]
        ]
    ]);
  
  
   >
   
##### Insert a new document
    
    ES::_id(3)->insert([
        "title" => "Test document",
        "content" => "Sample content"
    ]);
    
    
    A new document will be inserted with _id = 3.
  
    [id is optional] if not specified, a unique hash key will be generated 

  
  >
    
##### Bulk insert a multiple of documents at once using multidimensional array of [id => data] pairs
     
     ES::bulk(
         10 => [
            "title" => "Test document 1",
            "content" => "Sample content 1"
         ],
         11 => [
            "title" => "Test document 2",
            "content" => "Sample content 2"
         ],
     );
     
     The two given documents will be inserted with its associated ids
  
   >
   
##### Update an existing document
       
    ES::_id(3)->update([
       "title" => "Test document",
       "content" => "sample content"
    ]);
        
    Document has _id = 3 will be updated.
    
    [id is required]
    
   >
   
##### Incrementing field
       
    ES::_id(3)->increment("views");
        
    Document has _id = 3 will be incremented by 1.
    
    ES::_id(3)->increment("views", 3);
    
    Document has _id = 3 will be incremented by 3.

    [id is required]
    
   >
   
##### Decrementing field
       
    ES::_id(3)->decrement("views");
        
    Document has _id = 3 will be decremented by 1.
    
    ES::_id(3)->decrement("views", 3);
    
    Document has _id = 3 will be decremented by 3.

    [id is required]
    
   >
   
##### Update using script
       

    # icrement field by script
    
    ES::_id(3)->script(
        "ctx._source.$field -= params.count",
        ["count" => 1]
    );
    
    # add php tag to tags array list
    
    ES::_id(3)->script(
        "ctx._source.tags.add(params.tag)",
        ["tag" => "php"]
    );
    
    # delete the doc if the tags field contain mongodb, otherwise it does nothing (noop)
    
    ES::_id(3)->script(
        "if (ctx._source.tags.contains(params.tag)) { ctx.op = \"delete\" } else { ctx.op = \"none\" }",
        ["tag" => "mongodb"]
    );
    
   >
   
##### Delete a document
       
    ES::_id(3)->delete();
        
    Document has _id = 3 will be deleted.
    
    [id is required]
    

`Good luck`

`Dont forget to send a feedback..`