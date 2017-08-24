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

    public function rateAPlace(string $placeId, string $category, int $rating)
    {
        $insert = "
        INSERT INTO ratings (place_id, rating, category) VALUES 
        (:place_id, :rating, :category)
        ";

        $stm = $this->db->prepare($insert);

        $stm->bindParam(':place_id', $placeId, \PDO::PARAM_STR);
        $stm->bindParam(':category', $category, \PDO::PARAM_STR);
        $stm->bindParam(':rating', $rating, \PDO::PARAM_INT);

        $stm->execute();

        return $this->getRating($this->db->lastInsertId());
    }

    private function getRating(int $id)
    {
        $select = "
        SELECT * FROM ratings WHERE id = :id
        ";
        $stm = $this->db->prepare($select);

        $stm->execute();
        return $stm->fetch(\PDO::FETCH_ASSOC);
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