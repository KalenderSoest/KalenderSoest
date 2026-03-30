<?php

namespace App\Service\Calendar;

use App\Entity\DfxNfxUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class AdminAccessGuard
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function requireEntityForGroupScope(
        int $id,
        string $entityClass,
        DfxNfxUser $user,
        ?string $notFoundMessage = null,
        ?string $accountError = null,
    ): object {
        return $this->requireEntityForScope($id, $entityClass, $user, 'ROLE_DFX_GROUP', $notFoundMessage, $accountError);
    }

    public function requireEntityForMetaScope(
        int $id,
        string $entityClass,
        DfxNfxUser $user,
        ?string $notFoundMessage = null,
        ?string $accountError = null,
    ): object {
        return $this->requireEntityForScope($id, $entityClass, $user, 'ROLE_DFX_META', $notFoundMessage, $accountError);
    }

    public function requireEntityForScope(
        int $id,
        string $entityClass,
        DfxNfxUser $user,
        string $crossAccountRole,
        ?string $notFoundMessage = null,
        ?string $accountError = null,
    ): object {
        $entity = $this->em->getRepository($entityClass)->find($id);
        if (!is_object($entity)) {
            throw new NotFoundHttpException($notFoundMessage ?? 'Datensatz nicht gefunden.');
        }

        if (!method_exists($entity, 'getDatefix') || !method_exists($entity, 'getUser')) {
            throw new \LogicException($entityClass . ' is not compatible with AdminAccessGuard.');
        }

        $kid = $user->getDatefix()->getId();
        if (
            !$this->authorizationChecker->isGranted($crossAccountRole)
            && $kid !== $entity->getDatefix()->getId()
        ) {
            throw new NotFoundHttpException($accountError ?? 'Unerlaubter Zugriff auf Datensatz eines anderen Accounts.');
        }

        if (
            !$this->authorizationChecker->isGranted('ROLE_DFX_ALL')
            && $user !== $entity->getUser()
        ) {
            throw new NotFoundHttpException('Unerlaubter Zugriff auf Datensatz eines anderen Users.');
        }

        return $entity;
    }
}
