## stuff to do
### Use TDD! When doing a new feature, write tests for how it should function first. Only once tests are passing, then implement the feature.
### Always create test coverage for new features!
### Always run all tests after every change is completed!
### Check off items as you complete them.
### Do not stop iterating until all tests are complete!

[x] Can you create a streak feature to encourage daily logins? Replace the 44 days placeholder with the real number. If the user has a 0 day streak, don't display it. IF the user is not logged in at all, also don't display it.

[x] Let's upgrade to the latest version of Laravel. Then, prohibit destructive commands on production. 
namespace App\Providers;

use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        DB::prohibitDestructiveCommands(
            $this->app->isProduction()
        );
    }
}

[x] Can we implement an audit log? A new audit_log table that stores any interaction that takes place with the database - create, update, or delete. It should store the user id that took the action, what action it was (CRUD), the record id and table name, what the state of the record was before the action, and what the state of the record is after the action. And of course time stamps.

[x] Can you implement a super admin panel for me? Just create a new page at /super-admin. Only user id = 1 and user email = rileygweb@gmail.com should be able to access it. If anyone else tries, redirect home quietly. On this page, I want to see statistics about users, verses memorized, and just everything we store. Also the audit log, I want to be able to view that. For now, just build out the pages with tab functionality using placeholders. 