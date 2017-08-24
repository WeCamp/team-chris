<?php

namespace TeamChrisTest;

use PDO;
use TeamChris\App;

class AppTest extends \PHPUnit\Framework\TestCase
{
    private $placeId;
    private $rating = ['category' => 'LGBT', 'upAmount' => 0, 'downAmount' => 0];

    /**
     * @var PDO
     */
    private $db;

    /**
     * @var App
     */
    private $app;

    protected function setUp()
    {
        $this->db = new PDO('sqlite:db/test.db');
        $this->placeId = uniqid();
        $sql = file_get_contents('db/database.sql');
        $this->db->exec($sql);

        $this->app = new App($this->db);
    }

    protected function tearDown()
    {
        $this->db->exec('DROP TABLE ratings');
    }


    public function testRateANewPlace()
    {
        $response = $this->app->rateAPlace($this->placeId, 'lgbt', 1);
        $rating = $this->rating;
        $rating['upAmount']++;
        $this->assertNotEquals($response, false);

        $this->assertNotFalse($this->app->getRating($response['id']));

        $this->app->deleteRating($response['id']);

        $this->assertFalse($this->app->getRating($response['id']));
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
                        'lgbt' =>
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
                        'lgbt' =>
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


    public function testOneExistentPlace()
    {
        $response = $this->app->checkPlaces(['categories' => ['lgbt', 'vegan'], 'placeIds' => ['599d8f522626b']]);

        $this->assertEquals(
            $response,
            [
                [
                    'placeId' => '599d8f522626b',
                    'ratings' => [
                        'lgbt' =>
                            [
                                'category' => 'lgbt',
                                'upAmount' => 1,
                                'downAmount' => 1
                            ],
                        'vegan' =>
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
        $initial = $this->app->checkPlaces(['categories' => ['lgbt'], 'placeIds' => ['599d8f522626b']]);
        $response = $this->app->rateAPlace('599d8f522626b', 'lgbt', 1);

        $oldRating = $initial[0]['ratings']['lgbt']['upAmount'];
        $expected = $this->app->checkPlaces(['categories' => ['lgbt'], 'placeIds' => ['599d8f522626b']]);
        $newRating = $expected[0]['ratings']['lgbt']['upAmount'];
        $this->app->deleteRating($response['id']);

        $this->assertEquals($oldRating + 1, $newRating);
    }
}