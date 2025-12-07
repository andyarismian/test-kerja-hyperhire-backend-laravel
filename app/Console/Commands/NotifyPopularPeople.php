<?php

namespace App\Console\Commands;

use App\Mail\PersonPopular;
use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyPopularPeople extends Command
{
    protected $signature = 'notify:popular-people';
    protected $description = 'Notify admin about people with many likes';

    public function handle(): int
    {
        $threshold = 50;
        $people = Person::where('likes_count', '>', $threshold)
            ->whereNull('notified_at')
            ->get();

        foreach ($people as $person) {
            $admin = env('MAIL_ADMIN', 'admin@example.com');
            Mail::to($admin)->send(new PersonPopular($person));
            $person->notified_at = now();
            $person->save();
            $this->info('Notified admin about person id='.$person->id);
        }

        return 0;
    }
}
