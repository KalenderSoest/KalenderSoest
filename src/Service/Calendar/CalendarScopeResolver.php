<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Throwable;

final class CalendarScopeResolver extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function resolveReadScope(DfxKonf $konf): CalendarScope
    {
        $kid = (int) $konf->getId();

        if ((int) $konf->getIsGroup() === 1) {
            return new CalendarScope($kid, $this->normalizeIds([$kid, ...$this->findGroupChildIds($kid)]), 'group');
        }

        if ((int) $konf->getIsMeta() === 0) {
            return new CalendarScope($kid, [$kid], 'single');
        }

        return new CalendarScope($kid, [], 'meta');
    }

    public function resolveAssignmentScope(DfxKonf $konf): CalendarScope
    {
        $ids = [(int) $this->getParameter('metaId')];
        $groupIds = $konf->getToGroup();

        if (is_array($groupIds) && $groupIds !== []) {
            $ids = [...$ids, ...$groupIds];
        }

        if ((int) $konf->getIsMeta() === 0) {
            $ids[] = (int) $konf->getId();
        }

        return new CalendarScope((int) $konf->getId(), $this->normalizeIds($ids), $this->detectMode($konf));
    }

    public function resolveAdminReadScope(DfxKonf $konf, bool $hideSubCalendars, bool $canSeeGroupChildren): CalendarScope
    {
        if ((int) $konf->getIsGroup() === 1 && !$hideSubCalendars && $canSeeGroupChildren) {
            return $this->resolveReadScope($konf);
        }

        if ((int) $konf->getIsMeta() === 0 || $hideSubCalendars) {
            $kid = (int) $konf->getId();

            return new CalendarScope($kid, [$kid], 'single');
        }

        return new CalendarScope((int) $konf->getId(), [], 'meta');
    }

    /**
     * @return list<int>
     */
    private function findGroupChildIds(int $groupId): array
    {
        $connection = $this->em->getConnection();

        try {
            $ids = $connection->fetchFirstColumn(
                "SELECT id
                 FROM pool_dfx_konf
                 WHERE toGroup IS NOT NULL
                   AND toGroup <> ''
                   AND JSON_CONTAINS(toGroup, :groupIdJson, '$') = 1",
                ['groupIdJson' => json_encode((string) $groupId)]
            );

            return $this->normalizeIds(array_map('intval', $ids));
        } catch (Throwable) {
            $ids = $connection->fetchFirstColumn(
                "SELECT id
                 FROM pool_dfx_konf
                 WHERE toGroup IS NOT NULL
                   AND toGroup <> ''
                   AND (
                        toGroup LIKE :quotedString
                   )",
                [
                    'quotedString' => '%"' . $groupId . '"%',
                ]
            );

            return $this->normalizeIds(array_map('intval', $ids));
        }
    }

    /**
     * @param list<int|string> $ids
     * @return list<int>
     */
    private function normalizeIds(array $ids): array
    {
        $normalized = array_map(static fn (int|string $id): int => (int) $id, $ids);
        $normalized = array_values(array_unique($normalized));
        sort($normalized);

        return $normalized;
    }

    private function detectMode(DfxKonf $konf): string
    {
        if ((int) $konf->getIsMeta() === 1) {
            return 'meta';
        }

        if ((int) $konf->getIsGroup() === 1) {
            return 'group';
        }

        return 'single';
    }
}
