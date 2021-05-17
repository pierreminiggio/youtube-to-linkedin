<?php

namespace PierreMiniggio\YoutubeToLinkedin\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class NonUploadedVideoRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function findByFacebookAndYoutubeChannelIds(int $linkedinAccountId, int $youtubeChannelId): array
    {
        $this->connection->start();

        $postedFacebookPostIds = $this->connection->query('
            SELECT l.id
            FROM linkedin_post as l
            RIGHT JOIN linkedin_post_youtube_video as fpyv
            ON l.id = fpyv.linkedin_id
            WHERE l.account_id = :account_id
        ', ['account_id' => $linkedinAccountId]);
        $postedFacebookPostIds = array_map(fn ($entry) => (int) $entry['id'], $postedFacebookPostIds);

        $postsToPost = $this->connection->query('
            SELECT
                y.id,
                y.title,
                y.url
            FROM youtube_video as y
            ' . (
                $postedFacebookPostIds
                    ? 'LEFT JOIN linkedin_post_youtube_video as fpyv
                    ON y.id = fpyv.youtube_id
                    AND fpyv.linkedin_id IN (' . implode(', ', $postedFacebookPostIds) . ')'
                    : ''
            ) . '
            
            WHERE y.channel_id = :channel_id
            ' . ($postedFacebookPostIds ? 'AND fpyv.id IS NULL' : '') . '
            ;
        ', [
            'channel_id' => $youtubeChannelId
        ]);
        $this->connection->stop();

        return $postsToPost;
    }
}
