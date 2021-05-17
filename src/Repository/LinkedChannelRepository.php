<?php

namespace PierreMiniggio\YoutubeToLinkedin\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class LinkedChannelRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function findAll(): array
    {
        $this->connection->start();
        $channels = $this->connection->query('
            SELECT
                lpyc.youtube_id as y_id,
                l.id as l_id,
                l.api_url,
                l.api_token
            FROM linkedin_page as l
            RIGHT JOIN linkedin_page_youtube_channel as lpyc
                ON l.id = lpyc.linkedin_id
        ', []);
        $this->connection->stop();

        return $channels;
    }
}
