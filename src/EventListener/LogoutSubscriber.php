<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\CachingService;
use Symfony\Component\Lock\LockFactory;

class LogoutSubscriber implements EventSubscriberInterface
{
    private const ADMIN_LOGOUT_ROUTE = 'admin_logout';
    private const ADMIN_LOCK_KEY = 'ADMIN_LOGGED_IN';

    public function __construct(
        private readonly CachingService $cachingService,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack,
        private readonly LockFactory $lockFactory
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [LogoutEvent::class => 'onLogout'];
    }

    public function onLogout(LogoutEvent $event): void
    {
        if (($this->requestStack->getCurrentRequest()->attributes->get('_route')) === self::ADMIN_LOGOUT_ROUTE) {
            if (($lock = $this->lockFactory->createLock(self::ADMIN_LOCK_KEY))->isAcquired()) {
                $lock->release();
            }
        } else {
            $this->cachingService->decrementLoggedInUsers();
        }

        $event->setResponse(new RedirectResponse(
            $this->urlGenerator->generate('homepage'),
            RedirectResponse::HTTP_SEE_OTHER
        ));
    }
}
