<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\MemoryBank;

class VerifyLegitimateUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:verify-legitimate 
                            {--auto : Automatically verify users with memorized verses or verified emails}
                            {--email=* : Verify specific users by email}
                            {--dry-run : Show what would be verified without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark legitimate users as verified based on their activity or manually by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $emails = $this->option('email');
        $auto = $this->option('auto');

        if ($auto) {
            $this->verifyActiveUsers($dryRun);
        }

        if (!empty($emails)) {
            $this->verifyUsersByEmail($emails, $dryRun);
        }

        if (!$auto && empty($emails)) {
            $this->error('Please specify --auto or --email options. Use --help for more info.');
            return 1;
        }

        return 0;
    }

    private function verifyActiveUsers(bool $dryRun): void
    {
        $this->info('🔍 Finding users with activity...');

        // Users who have memorized verses
        $usersWithVerses = User::where('verified_user', false)
            ->whereHas('memoryBank')
            ->get();

        // Users who have verified their email
        $usersWithVerifiedEmail = User::where('verified_user', false)
            ->whereNotNull('email_verified_at')
            ->get();

        $totalToVerify = $usersWithVerses->count() + $usersWithVerifiedEmail->count();

        if ($totalToVerify === 0) {
            $this->info('✅ No users found to auto-verify.');
            return;
        }

        $this->info("📊 Found {$totalToVerify} users to verify:");
        $this->info("   • {$usersWithVerses->count()} users with memorized verses");
        $this->info("   • {$usersWithVerifiedEmail->count()} users with verified emails");

        if ($dryRun) {
            $this->warn('🏃 DRY RUN - No changes will be made');
            
            foreach ($usersWithVerses as $user) {
                $verseCount = $user->memoryBank()->count();
                $this->line("   📖 {$user->name} ({$user->email}) - {$verseCount} verses");
            }
            
            foreach ($usersWithVerifiedEmail as $user) {
                $this->line("   ✉️  {$user->name} ({$user->email}) - verified email");
            }
            return;
        }

        if (!$this->confirm("Verify these {$totalToVerify} users?")) {
            $this->info('Operation cancelled.');
            return;
        }

        $verified = 0;

        foreach ($usersWithVerses as $user) {
            $user->markAsVerified();
            $verified++;
            $verseCount = $user->memoryBank()->count();
            $this->info("✅ Verified {$user->name} ({$user->email}) - {$verseCount} verses");
        }

        foreach ($usersWithVerifiedEmail as $user) {
            $user->markAsVerified();
            $verified++;
            $this->info("✅ Verified {$user->name} ({$user->email}) - verified email");
        }

        $this->info("🎉 Successfully verified {$verified} users!");
    }

    private function verifyUsersByEmail(array $emails, bool $dryRun): void
    {
        $this->info('🔍 Finding users by email...');

        $users = User::whereIn('email', $emails)->get();
        $found = $users->count();
        $notFound = count($emails) - $found;

        if ($found === 0) {
            $this->error('❌ No users found with the provided emails.');
            return;
        }

        if ($notFound > 0) {
            $this->warn("⚠️  {$notFound} email(s) not found in database.");
        }

        $this->info("📊 Found {$found} users to verify:");
        foreach ($users as $user) {
            $status = $user->verified_user ? '(already verified)' : '(will be verified)';
            $this->line("   • {$user->name} ({$user->email}) {$status}");
        }

        if ($dryRun) {
            $this->warn('🏃 DRY RUN - No changes will be made');
            return;
        }

        if (!$this->confirm("Verify these {$found} users?")) {
            $this->info('Operation cancelled.');
            return;
        }

        $verified = 0;
        foreach ($users as $user) {
            if (!$user->verified_user) {
                $user->markAsVerified();
                $verified++;
                $this->info("✅ Verified {$user->name} ({$user->email})");
            }
        }

        $this->info("🎉 Successfully verified {$verified} users!");
    }
}
