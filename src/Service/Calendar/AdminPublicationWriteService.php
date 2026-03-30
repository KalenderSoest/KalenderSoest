<?php

namespace App\Service\Calendar;

use App\Entity\DfxKonf;
use Doctrine\ORM\EntityManagerInterface;

final class AdminPublicationWriteService
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function setPublished(object $entity, bool $published, ?DfxKonf $konf = null): void
    {
        if (!method_exists($entity, 'setPub')) {
            throw new \LogicException('Entity does not support publication state.');
        }

        $entity->setPub($published ? 1 : 0);

        if ($published && $konf instanceof DfxKonf) {
            if ($konf->getPubMetaAll() === true || $konf->getIsMeta() === true) {
                $this->setMetaPublished($entity, true, false);
            }

            if (($konf->getPubGroupAll() === true && $konf->getToGroup() !== []) || $konf->getIsGroup() === true) {
                $this->setGroupPublished($entity, true, false);
            }
        }

        $this->em->persist($entity);
        $this->em->flush();
    }

    public function setMetaPublished(object $entity, bool $published, bool $flush = true): void
    {
        if (!method_exists($entity, 'setPubMeta')) {
            throw new \LogicException('Entity does not support meta publication state.');
        }

        $entity->setPubMeta($published ? 1 : 0);

        if ($flush) {
            $this->em->persist($entity);
            $this->em->flush();
        }
    }

    public function setGroupPublished(object $entity, bool $published, bool $flush = true): void
    {
        if (!method_exists($entity, 'setPubGroup')) {
            throw new \LogicException('Entity does not support group publication state.');
        }

        $entity->setPubGroup($published ? 1 : 0);

        if ($flush) {
            $this->em->persist($entity);
            $this->em->flush();
        }
    }

    public function bulkSetFieldByCode(string $entityClass, string $field, string $code, bool $value): void
    {
        $this->em->createQueryBuilder()
            ->update($entityClass, 'e')
            ->set('e.' . $field, ':value')
            ->where('e.code = :code')
            ->setParameter('value', $value ? 1 : 0)
            ->setParameter('code', $code)
            ->getQuery()
            ->execute();
    }

    public function bulkPublishByCode(string $entityClass, string $code, DfxKonf $konf): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->update($entityClass, 'e')
            ->set('e.pub', ':pub')
            ->where('e.code = :code')
            ->setParameter('pub', 1)
            ->setParameter('code', $code);

        if ($konf->getPubMetaAll() === true || $konf->getIsMeta() === true) {
            $qb->set('e.pubMeta', ':pubMeta')
                ->setParameter('pubMeta', 1);
        }

        if (($konf->getPubGroupAll() === true && $konf->getToGroup() !== []) || $konf->getIsGroup() === true) {
            $qb->set('e.pubGroup', ':pubGroup')
                ->setParameter('pubGroup', 1);
        }

        $qb->getQuery()->execute();
    }
}
