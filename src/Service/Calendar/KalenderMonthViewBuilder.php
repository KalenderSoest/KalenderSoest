<?php

namespace App\Service\Calendar;

use DateTime;
use DateTimeImmutable;

final class KalenderMonthViewBuilder
{
    public function build(array $tageKal, int $year, int $month, string $basePath, array $query): array
    {
        return [
            'kaldata' => $this->buildLegacyJson($tageKal, $year, $month),
            'calendar' => $this->buildResponsiveCalendar($tageKal, $year, $month, $basePath, $query),
        ];
    }

    public function buildLegacyJson(array $tageKal, int $year, int $month): string
    {
        $json = '{"time":"' . $year . '-' . $month . '", "events":{';
        if ($tageKal) {
            foreach ($tageKal as $tag) {
                if (($tag['datumVon'] ?? null) instanceof DateTime) {
                    $json .= '"' . $tag['datumVon']->format('Y-m-d') . '":{},';
                }
            }
        } else {
            $json .= ',';
        }

        return substr($json, 0, -1) . '}}';
    }

    public function buildResponsiveCalendar(array $tageKal, int $year, int $month, string $basePath, array $query): array
    {
        $monthNames = [1 => 'januar', 2 => 'februar', 3 => 'märz', 4 => 'april', 5 => 'mai', 6 => 'juni', 7 => 'juli', 8 => 'august', 9 => 'september', 10 => 'oktober', 11 => 'november', 12 => 'dezember'];
        $currentMonth = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $prevMonth = $currentMonth->modify('-1 month');
        $nextMonth = $currentMonth->modify('+1 month');
        $visibleStart = $currentMonth->modify('-' . (((int) $currentMonth->format('N')) - 1) . ' days');

        $eventsByDate = [];
        foreach ($tageKal as $tag) {
            if (($tag['datumVon'] ?? null) instanceof DateTime) {
                $eventsByDate[$tag['datumVon']->format('Y-m-d')] = true;
            }
        }

        $days = [];
        for ($i = 0; $i < 42; $i++) {
            $day = $visibleStart->modify('+' . $i . ' days');
            $dateKey = $day->format('Y-m-d');
            $inCurrentMonth = $day->format('Y-m') === $currentMonth->format('Y-m');
            $hasEvents = isset($eventsByDate[$dateKey]);

            $dayQuery = $query;
            $dayQuery['form'] = is_array($dayQuery['form'] ?? null) ? $dayQuery['form'] : [];
            $dayQuery['form']['t'] = $dateKey;
            $dayQuery['form']['m'] = $day->format('Y-m');
            unset($dayQuery['dfxp']);

            $days[] = [
                'day' => $day->format('j'),
                'month' => $day->format('n'),
                'year' => $day->format('Y'),
                'href' => $hasEvents ? $basePath . '?' . http_build_query($dayQuery) : null,
                'aria' => 'Zur Terminliste am ' . $day->format('d.m.Y'),
                'class' => trim('rc-day' . ($hasEvents ? ' active' : '') . (!$inCurrentMonth ? ' not-current' : '')),
                'style' => '',
                'isCurrent' => $inCurrentMonth,
            ];
        }

        return [
            'year' => $currentMonth->format('Y'),
            'month' => $currentMonth->format('m'),
            'monthLabel' => ucfirst($monthNames[(int) $currentMonth->format('n')] ?? $currentMonth->format('F')),
            'prevYear' => $prevMonth->format('Y'),
            'prevMonth' => $prevMonth->format('m'),
            'nextYear' => $nextMonth->format('Y'),
            'nextMonth' => $nextMonth->format('m'),
            'prevHref' => $this->buildCalendarMonthHref($basePath, $query, $prevMonth),
            'nextHref' => $this->buildCalendarMonthHref($basePath, $query, $nextMonth),
            'prevLabel' => ucfirst($monthNames[(int) $prevMonth->format('n')] ?? $prevMonth->format('F')) . ' ' . $prevMonth->format('Y'),
            'nextLabel' => ucfirst($monthNames[(int) $nextMonth->format('n')] ?? $nextMonth->format('F')) . ' ' . $nextMonth->format('Y'),
            'days' => $days,
        ];
    }

    private function buildCalendarMonthHref(string $basePath, array $query, DateTimeImmutable $month): string
    {
        $monthQuery = $query;
        $monthQuery['form'] = is_array($monthQuery['form'] ?? null) ? $monthQuery['form'] : [];
        $monthQuery['form']['m'] = $month->format('Y-m');
        unset($monthQuery['form']['t'], $monthQuery['dfxp']);

        return $this->buildCalendarHref($basePath, $monthQuery);
    }

    private function buildCalendarHref(string $basePath, array $query): string
    {
        $separator = str_contains($basePath, '?') ? '&' : '?';

        return $basePath . $separator . http_build_query($query);
    }
}
