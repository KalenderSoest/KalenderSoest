<?php

namespace App\EventSubscriber;

use App\Entity\DfxDozenten;
use App\Entity\DfxLocation;
use App\Entity\DfxNews;
use App\Entity\DfxTermine;
use App\Entity\DfxVeranstalter;
use App\Service\Content\HtmlContentSanitizer;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

final class HtmlContentSanitizerSubscriber implements EventSubscriber
{
    public function __construct(
        private readonly HtmlContentSanitizer $htmlContentSanitizer,
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->sanitizeEntity($args->getObject());
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$this->sanitizeEntity($entity)) {
            return;
        }

        $em = $args->getObjectManager();
        $meta = $em->getClassMetadata($entity::class);
        $em->getUnitOfWork()->recomputeSingleEntityChangeSet($meta, $entity);
    }

    private function sanitizeEntity(object $entity): bool
    {
        $changed = false;

        if ($entity instanceof DfxNews) {
            $changed = $this->sanitizeField($entity, 'getBeschreibung', 'setBeschreibung') || $changed;
        }

        if ($entity instanceof DfxTermine) {
            $changed = $this->sanitizeField($entity, 'getBeschreibung', 'setBeschreibung') || $changed;
        }

        if ($entity instanceof DfxVeranstalter) {
            $changed = $this->sanitizeField($entity, 'getZusatz', 'setZusatz') || $changed;
        }

        if ($entity instanceof DfxLocation) {
            $changed = $this->sanitizeField($entity, 'getZusatz', 'setZusatz') || $changed;
        }

        if ($entity instanceof DfxDozenten) {
            $changed = $this->sanitizeField($entity, 'getZusatz', 'setZusatz') || $changed;
        }

        return $changed;
    }

    private function sanitizeField(object $entity, string $getter, string $setter): bool
    {
        $current = $entity->$getter();
        $sanitized = $this->htmlContentSanitizer->sanitize($current);

        if ($current === $sanitized) {
            return false;
        }

        $entity->$setter($sanitized);

        return true;
    }
}
