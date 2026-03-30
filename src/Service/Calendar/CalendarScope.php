<?php

namespace App\Service\Calendar;

final class CalendarScope
{
    /**
     * @param list<int> $calendarIds
     */
    public function __construct(
        private readonly int $primaryCalendarId,
        private readonly array $calendarIds,
        private readonly string $mode,
    ) {
    }

    public function primaryCalendarId(): int
    {
        return $this->primaryCalendarId;
    }

    /**
     * @return list<int>
     */
    public function ids(): array
    {
        return $this->calendarIds;
    }

    public function restrictsResults(): bool
    {
        return $this->calendarIds !== [];
    }

    public function isMeta(): bool
    {
        return $this->mode === 'meta';
    }

    public function isGroup(): bool
    {
        return $this->mode === 'group';
    }

    public function isSingleCalendar(): bool
    {
        return $this->mode === 'single';
    }
}
