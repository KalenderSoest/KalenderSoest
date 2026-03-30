<?php

namespace App\Service\Calendar;

use App\Entity\DfxNfxUser;
use App\Entity\DfxTermine;
use App\Security\CurrentContext;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CalendarFieldChoiceProvider
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CurrentContext $currentContext,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function forScope(CalendarScope $calendarScope, string $field, bool $limitToCurrentUser = false): array
    {
        $repository = $this->em->getRepository(DfxTermine::class);
        $params = [];

        $query = $repository->createQueryBuilder('t')
            ->select('t.' . $field);

        if ($calendarScope->restrictsResults()) {
            $query->where('t.datefix IN (:kids)');
            $params[] = new Parameter('kids', $calendarScope->ids());
        }

        if ($limitToCurrentUser && !$this->authorizationChecker->isGranted('ROLE_DFX_ALL')) {
            /** @var DfxNfxUser $user */
            $user = $this->currentContext->getUser();
            $query->andWhere('t.user = :uid');
            $params[] = new Parameter('uid', $user->getId());
        }

        $query->setParameters(new ArrayCollection($params))
            ->groupBy('t.' . $field . ', t.user, t.id, t.user, t.idLocation, t.idVeranstalter, t.idOrt, t.region, t.datefix')
            ->orderBy('t.' . $field, 'ASC');

        $entities = $query->getQuery()->getResult();
        $choices = [];
        foreach ($entities as $entity) {
            if (($entity[$field] ?? '') !== '') {
                $choices[$entity[$field]] = $entity[$field];
            }
        }

        return $choices;
    }
}
