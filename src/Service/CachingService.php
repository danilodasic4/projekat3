<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;

readonly class CachingService
{
    public function __construct(
        private CacheInterface $cache,
    ) {}

    public function initializeCountLoggedInUsers(): void
    {
        $this->cache->get('COUNT_LOGGED_IN_USERS', function () {
            return 0;
        });
    }

    public function incrementLoggedInUsers(): void
    {
        $this->cache->get('COUNT_LOGGED_IN_USERS', function () {
            return 0;
        });

        $item = $this->cache->getItem('COUNT_LOGGED_IN_USERS');
        $currentCount = $item->isHit() ? $item->get() : 0;
        $item->set($currentCount + 1);
        $this->cache->save($item);
    }

    public function decrementLoggedInUsers(): void
    {
        $item = $this->cache->getItem('COUNT_LOGGED_IN_USERS');
        $currentCount = $item->isHit() ? $item->get() : 0;
        $item->set(max(0, $currentCount - 1));
        $this->cache->save($item);
    }

    public function getLoggedInUsersCount(): int
    {
        $item = $this->cache->getItem('COUNT_LOGGED_IN_USERS');
        return $item->isHit() ? $item->get() : 0;
    }
}
