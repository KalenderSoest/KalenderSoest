<?php

namespace App\Service\Calendar;

use DateTimeInterface;
use Doctrine\ORM\QueryBuilder;

final class NewsDateWindowQueryApplier
{
    public function apply(QueryBuilder $query, string $alias, ?DateTimeInterface $datumVon, ?DateTimeInterface $datumBis): void
    {
        if ($datumVon === null && $datumBis === null) {
            $query->andWhere(sprintf('(%1$s.datumVon IS NULL OR %1$s.datumVon <= CURRENT_DATE())', $alias))
                ->andWhere(sprintf('(%1$s.datumBis IS NULL OR %1$s.datumBis >= CURRENT_DATE())', $alias));

            return;
        }

        if ($datumVon !== null) {
            $query->andWhere(sprintf('(%1$s.datumBis IS NULL OR %1$s.datumBis >= :news_datum_von)', $alias))
                ->setParameter('news_datum_von', $datumVon);
        }

        if ($datumBis !== null) {
            $query->andWhere(sprintf('(%1$s.datumVon IS NULL OR %1$s.datumVon <= :news_datum_bis)', $alias))
                ->setParameter('news_datum_bis', $datumBis);
        }
    }
}
