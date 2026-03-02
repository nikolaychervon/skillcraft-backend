<?php

declare(strict_types=1);

namespace App\Infrastructure\Mentor\Observers;

use App\Domain\Mentor\Events\MentorChanged;
use App\Models\Mentor;

final class MentorObserver
{
    public function created(Mentor $mentor): void
    {
        $this->dispatchChanged($mentor);
    }

    public function updated(Mentor $mentor): void
    {
        $this->dispatchChanged($mentor);
    }

    public function deleted(Mentor $mentor): void
    {
        $this->dispatchChanged($mentor);
    }

    private function dispatchChanged(Mentor $mentor): void
    {
        event(new MentorChanged($mentor->user_id));
    }
}
