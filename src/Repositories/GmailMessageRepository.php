<?php

namespace Webkul\Google\Repositories;

use Webkul\Core\Eloquent\Repository;

class GmailMessageRepository extends Repository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return 'Webkul\Google\Contracts\GmailMessage';
    }

    /**
     * Get unread messages count for account
     */
    public function getUnreadCount(int $accountId): int
    {
        return $this->where('account_id', $accountId)
                   ->where('is_read', false)
                   ->where('is_trash', false)
                   ->where('is_spam', false)
                   ->count();
    }

    /**
     * Get recent messages for account
     */
    public function getRecentMessages(int $accountId, int $limit = 10)
    {
        return $this->where('account_id', $accountId)
                   ->where('is_trash', false)
                   ->where('is_spam', false)
                   ->orderBy('internal_date', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Search messages by query
     */
    public function searchMessages(int $accountId, string $query, array $filters = [])
    {
        $queryBuilder = $this->where('account_id', $accountId);

        // Apply search query
        if (!empty($query)) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('subject', 'LIKE', "%{$query}%")
                  ->orWhere('body_text', 'LIKE', "%{$query}%")
                  ->orWhere('body_html', 'LIKE', "%{$query}%")
                  ->orWhere('from', 'LIKE', "%{$query}%");
            });
        }

        // Apply filters
        if (isset($filters['is_read'])) {
            $queryBuilder->where('is_read', $filters['is_read']);
        }

        if (isset($filters['is_starred'])) {
            $queryBuilder->where('is_starred', $filters['is_starred']);
        }

        if (isset($filters['folder'])) {
            switch ($filters['folder']) {
                case 'sent':
                    $queryBuilder->where('is_sent', true);
                    break;
                case 'drafts':
                    $queryBuilder->where('is_draft', true);
                    break;
                case 'trash':
                    $queryBuilder->where('is_trash', true);
                    break;
                case 'spam':
                    $queryBuilder->where('is_spam', true);
                    break;
                default:
                    $queryBuilder->where('is_trash', false)
                               ->where('is_spam', false)
                               ->where('is_sent', false);
                    break;
            }
        }

        return $queryBuilder->orderBy('internal_date', 'desc');
    }
}
