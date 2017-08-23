<?php

namespace TeamChris;

class App
{
    /**
     * @var \PDO
     */
    private $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function rateAPlace(string $id, int $vote)
    {
        //add a new entry to the db
    }

    public function checkPlaces(array $request): array
    {
        $categories = $request['categories'];
        $placeIds = $request['placeIds'];
        $result = [];


        foreach ($placeIds as $placeId) {
            array_push($result, ['placeId' => $placeId, 'ratings' => $this->countRatings($placeId, $categories)]);
        }


        return $result;
    }


    private function countRatings(string $placeId, $categories): array
    {
        $params = [':place_id' => $placeId];
        $in_params = [];

        $in = "";
        foreach ($categories as $i => $item) {
            $key = ":category" . $i;
            $in .= "$key,";
            $in_params[$key] = $item; // collecting values into key-value array
        }
        $in = rtrim($in, ","); // :id0,:id1,:id2

        $sql = "
        SELECT
        category, 
        SUM(CASE WHEN  rating > 0 THEN rating ELSE 0 END) as upAmount,
        SUM(CASE  WHEN  rating < 0 THEN rating ELSE 0 END) * -1 as downAmount
        FROM ratings  WHERE place_id = :place_id AND category IN ($in) GROUP BY category
        ";

        $stm = $this->db->prepare($sql);


        $allParams = array_merge($params, $in_params);

        $stm->execute($allParams);

        $result = $stm->fetchAll(\PDO::FETCH_ASSOC);

        if ($result == false) {
            foreach ($categories as $category) {
                array_push($result, ['category' => $category, 'upAmount' => 0, 'downAmount' => 0]);
            }
        }
        return $result;
    }

    private function getEntryFromDatabase($id)
    {

    }
}