<?php

namespace PierreMiniggio\YoutubeToLinkedin\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class VideoToUploadRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function insertVideoIfNeeded(
        string $linkedinId,
        int $linkedinAccountId,
        int $youtubeVideoId
    ): void
    {
        $this->connection->start();
        $postQueryParams = [
            'account_id' => $linkedinAccountId,
            'linkedin_id' => $linkedinId
        ];
        $findPostIdQuery = ['
            SELECT id FROM linkedin_post
            WHERE account_id = :account_id
            AND linkedin_id = :linkedin_id
            ;
        ', $postQueryParams];
        $queriedIds = $this->connection->query(...$findPostIdQuery);
        
        if (! $queriedIds) {
            $this->connection->exec('
                INSERT INTO linkedin_post (account_id, linkedin_id)
                VALUES (:account_id, :linkedin_id)
                ;
            ', $postQueryParams);
            $queriedIds = $this->connection->query(...$findPostIdQuery);
        }

        $postId = (int) $queriedIds[0]['id'];
        
        $pivotQueryParams = [
            'linkedin_id' => $postId,
            'youtube_id' => $youtubeVideoId
        ];

        $queriedPivotIds = $this->connection->query('
            SELECT id FROM linkedin_post_youtube_video
            WHERE linkedin_id = :linkedin_id
            AND youtube_id = :youtube_id
            ;
        ', $pivotQueryParams);
        
        if (! $queriedPivotIds) {
            $this->connection->exec('
                INSERT INTO linkedin_post_youtube_video (linkedin_id, youtube_id)
                VALUES (:linkedin_id, :youtube_id)
                ;
            ', $pivotQueryParams);
        }

        $this->connection->stop();
    }
}
