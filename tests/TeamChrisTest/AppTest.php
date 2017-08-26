<?php

namespace TeamChrisTest;

use PDO;
use TeamChris\App;

class AppTest extends \PHPUnit\Framework\TestCase
{
    private $placeId;

    /** @var PDO */
    private $db;

    /** @var App */
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

        $this->assertNotEquals($response, false);

        $this->assertEquals(1, $response[$this->placeId]['ratings']['lgbt']['upAmount']);
    }

    public function testNonExistentPlaces()
    {
        $id1 = uniqid();
        $id2 = uniqid();
        $response = $this->app->checkPlaces(['categories' => ['lgbt'], 'placeIds' => [$id1, $id2]]);
        $this->assertEquals(
            $response,
            [
                $id1 => [
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
                $id2 => [
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
                '599d8f522626b' => [
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

        $this->app->rateAPlace('599d8f522626b', 'lgbt', 1);

        $oldRating = $initial['599d8f522626b']['ratings']['lgbt']['upAmount'];
        $expected = $this->app->checkPlaces(['categories' => ['lgbt'], 'placeIds' => ['599d8f522626b']]);
        $newRating = $expected['599d8f522626b']['ratings']['lgbt']['upAmount'];
        $this->assertEquals($oldRating + 1, $newRating);
    }
}