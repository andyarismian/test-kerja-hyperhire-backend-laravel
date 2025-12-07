<?php

namespace App\Console\Commands;

use App\Mail\PersonPopular;
use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckPopularPeople extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'people:check-popular';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if any person has more than 50 likes and send notification to admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $adminEmail = env('MAIL_ADMIN', 'admin@example.com');
        
        // Find people with more than 50 likes who haven't been notified yet
        $popularPeople = Person::where('likes_count', '>', 50)
            ->whereNull('notified_at')
            ->get();

        if ($popularPeople->isEmpty()) {
            $this->info('No popular people found that need notification.');
            return Command::SUCCESS;
        }

        foreach ($popularPeople as $person) {
            try {
                // Send email to admin
                Mail::to($adminEmail)->send(new PersonPopular($person));
                
                // Mark as notified
                $person->notified_at = now();
                $person->save();
                
                $this->info("Notification sent for: {$person->name} (ID: {$person->id}, Likes: {$person->likes_count})");
            } catch (\Exception $e) {
                $this->error("Failed to send notification for person ID {$person->id}: {$e->getMessage()}");
            }
        }

        $this->info("Total notifications sent: {$popularPeople->count()}");
        return Command::SUCCESS;
    }
}
