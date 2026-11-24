<?php

namespace Kinde\KindeSDK\Frameworks\Symfony;

use Kinde\KindeSDK\Sdk\Agents\KindeAgentAuth;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Enforces #[RequireAgentAuth] (Symfony 6.2+). Register KindeAgentAuth as a service, for example
 * with KindeAgentAuthOptions::fromEnvironment(), and this subscriber with autoconfigure.
 *
 * A missing permission is a plain 403. The verified KindeAuthContext is on the request as the
 * "kinde_agent" attribute.
 */
final class KindeAgentAuthSubscriber implements EventSubscriberInterface
{
    private KindeAgentAuth $auth;

    public function __construct(KindeAgentAuth $auth)
    {
        $this->auth = $auth;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => 'onKernelController'];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $attributes = $event->getAttributes()[RequireAgentAuth::class] ?? [];
        if ($attributes === []) {
            return;
        }

        $permissions = [];
        foreach ($attributes as $attribute) {
            $permissions = array_merge($permissions, $attribute->permissions);
        }

        $request = $event->getRequest();
        $context = KindeAgentAuth::requirePermissions(
            $this->auth->verifyAuthorizationHeader($request->headers->get('Authorization')),
            ...array_values(array_unique($permissions))
        );
        if ($context->isAuthenticated()) {
            $request->attributes->set('kinde_agent', $context);
            return;
        }

        $response = new JsonResponse(
            KindeAgentAuth::errorBody($context),
            KindeAgentAuth::statusCode($context),
            $this->auth->challengeHeaders($context)
        );
        $event->setController(static function () use ($response): JsonResponse {
            return $response;
        });
    }
}
