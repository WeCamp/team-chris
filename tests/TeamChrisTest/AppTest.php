<?php

namespace TeamChrisTest;

use PDO;
use TeamChris\App;


class AppTest extends \PHPUnit_Framework_TestCase
{
    private $placeIds;
    private $placeId = 'ChIJM104iyQdhEcRmkCU__KtSCo';
    private $rating = ['category' => 'LGBT', 'upAmount' => 0, 'downAmount' => 0];

    /**
     * @var App
     */
    private $app;

    protected function setUp()
    {
        $db = new PDO('sqlite:../db/test.db');
        $this->app = new App($db);
    }

    public function testCheckPlaces()
    {

    }

    public function testRateANewPlace()
    {
        $response = $this->app->rateAPlace($this->placeId, 1);
        $rating = $this->rating;
        $rating['upAmount']++;
        $this->assertEquals(['placeId' => $this->placeId, 'ratings' => [$rating]], $response);
    }

    public function testRateAnExistingPlace()
    {
        $response = $this->app->rateAPlace($this->placeId, 1);
        $rating = $this->rating;
        $rating['upAmount']++;
        $this->assertEquals(['placeId' => $this->placeId, 'ratings' => [$rating]], $response);
    }


}