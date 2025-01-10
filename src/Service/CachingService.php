<?php 

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CachingService
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function incrementLoggedInUsers(): void
    {
        $this->cache->get('COUNT_LOGGED_IN_USERS', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            $currentCount = (int)$this->cache->get('COUNT_LOGGED_IN_USERS', function() {
                return 0;
            });

            return $currentCount + 1;
        });
    }

    public function decrementLoggedInUsers(): void
    {
        $this->cache->get('COUNT_LOGGED_IN_USERS', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            $currentCount = (int)$this->cache->get('COUNT_LOGGED_IN_USERS', function() {
                return 0;
            });

            return $currentCount - 1;
        });
    }

    public function getLoggedInUsersCount(): int
    {
        return (int)$this->cache->get('COUNT_LOGGED_IN_USERS', function() {
            return 0;
        });
    }
}
