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

    public function checkPlaces(array $listOfIds): array
    {
        $results = [];
        foreach ($listOfIds as $id)
        {
            $results[] = [
                'id'=> $id,
                'ratings' => [
                    'category' => 'LGBT',
                    'upAmount' => countVotes($id)['upAmount'],
                    'downAmount' => countVotes($id)['downAmount']
                ]
          ];
        }
        return $results;
    }


    private function countVotes($id): array
    {
        //query
    }

    private function getEntryFromDatabase($id)
    {

    }
}