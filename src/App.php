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
        try {
            $insert = "
        INSERT INTO ratings (place_id, rating, category) VALUES 
        (:place_id, :rating, :category)
        ";

            $stm = $this->db->prepare($insert);

            $data['place_id'] = $placeId;
            $data['category'] = $category;
            $data['rating'] = $rating;

            $stm->execute($data);

            //it was like this before we changed the response
            //$returnArray = $this->getRating($this->db->lastInsertId());

            $addedData = $this->checkPlaces(['placeIds' => [$placeId], 'categories' => [$category]]);

            return $addedData;

        } catch (\PDOException $e) {
            $error = new Error('400', $e->getMessage());
            return $error;
        }

    }

    public function getRating(int $id)
    {

        try {
            $select = "
        SELECT * FROM ratings WHERE id = :id
        ";
            $stm = $this->db->prepare($select);
            $stm->bindParam(':id', $id);
            $stm->execute();
            return $stm->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $error = new Error('400', $e->getMessage());
            return $error;
        }
    }

    public function deleteRating(int $id)
    {
        try {
            $delete = "
        DELETE FROM ratings WHERE id = :id
        ";
            $stm = $this->db->prepare($delete);
            $stm->bindParam(':id', $id);
            $stm->execute();
            return $stm->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $error = new Error('400', $e->getMessage());
            return $error;
        }
    }

    public function checkPlaces(array $request): array
    {

        $categories = $request['categories'];
        $placeIds = $request['placeIds'];
        $result = [];
        foreach ($placeIds as $placeId) {
            $result[$placeId] = ['placeId' => $placeId, 'ratings' => $this->countRatings($placeId, $categories)];
        }

        return $result;
    }


    private function countRatings(string $placeId, $categories): array
    {
        try {
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

            $results = $stm->fetchAll(\PDO::FETCH_ASSOC);
            if ($results == false) {
                foreach ($categories as $category) {
                    $results[$category] = ['category' => $category, 'upAmount' => 0, 'downAmount' => 0];
                }
            } else {
                $outcome = [];
                foreach ($results as $result) {
                    $outcome[$result['category']] = $result;
                }

                $results = $outcome;
            }

            return $results;
        } catch (\PDOException $e) {
            var_dump($e);
            die;
            $error = new Error('400', $e->getMessage());
            return $error;
        }
    }
}