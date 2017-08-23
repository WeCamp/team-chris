<?php

namespace TeamChrisTest;

use PDO;
use TeamChris\App;

class AppTest extends \PHPUnit\Framework\TestCase
{
    private $placeIds;
    private $placeId;
    private $rating = ['category' => 'LGBT', 'upAmount' => 0, 'downAmount' => 0];

    /**
     * @var App
     */
    private $app;

    protected function setUp()
    {
        $db = new PDO('sqlite:db/test.db');
        $this->placeId = uniqid();
        $this->app = new App($db);
    }

    /*
    public function testRateANewPlace()
    {
        $response = $this->app->rateAPlace($this->placeId, 1);
        $rating = $this->rating;
        $rating['upAmount']++;
        $this->assertEquals(['placeId' => $this->placeId, 'ratings' => [$rating]], $response);
    }


    public function testNonExistentPlaces()
    {
        $id1 = uniqid();
        $id2 = uniqid();
        $response = $this->app->checkPlaces(['categories' => ['lgbt'], 'placeIds' => [$id1, $id2]]);
        $this->assertEquals(
            $response,
            [
                [
                    'placeId' => $id1,
                    'ratings' => [
                        [
                            'category' => 'lgbt',
                            'upAmount' => 0,
                            'downAmount' => 0
                        ]
                    ]
                ],
                [
                    'placeId' => $id2,
                    'ratings' => [
                        [
                            'category' => 'lgbt',
                            'upAmount' => 0,
                            'downAmount' => 0
                        ]
                    ]
                ]
            ]
        );
    }
*/

    public function testOneExistentPlace()
    {
        $response = $this->app->checkPlaces(['categories' => ['lgbt','vegan'], 'placeIds' => ['599d8f522626b']]);

        $this->assertEquals(
            $response,
            [
                [
                    'placeId' => '599d8f522626b',
                    'ratings' => [
                        [
                            'category' => 'lgbt',
                            'upAmount' => 1,
                            'downAmount' => 1
                        ],
                        [
                            'category' => 'vegan',
                            'upAmount' => 1,
                            'downAmount' => 0
                        ]
                    ]
                ]
            ]
        );
    }

    public function testRateAnExistingPlace()
    {
        $response = $this->app->rateAPlace($this->placeId, 1);
        $rating = $this->rating;
        $rating['upAmount']++;
        $this->assertEquals(['placeId' => $this->placeId, 'ratings' => [$rating]], $response);
    }


}