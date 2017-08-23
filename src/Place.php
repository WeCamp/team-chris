<?php

namespace TeamChris;


class Place
{
    public $placeId;
    public $name;
    public $address;
    public $lat;
    public $lng;
    public $type;
    public $rating;

    private function __construct(string $placeId, string $name, string $address, float $lat, float $lng, string $type, int $rating = null)
    {
        $this->placeId = $placeId;
        $this->name = $name;
        $this->address = $address;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->type = $type;
        if ($rating != null) {
            $this->rating = $rating;
        }
    }

    public static function createPlaceFromArray(array $data)
    {

    }


    public function thumbsUp()
    {
        $this->rating++;
    }

    public function thumbsDown()
    {
        $this->rating--;
    }

}