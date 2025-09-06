<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\MemoryBank;
use App\Models\MemorizeLater;
use App\Models\AuditLog;
use Carbon\Carbon;

class SuperAdminController extends Controller
{
    public function index()
    {
        // Log the access attempt first
        $this->logAccessAttempt();

        // Check if user is authorized
        if (!$this->isAuthorized()) {
            return redirect('/');
        }

        $statistics = $this->getStatistics();
        $chartData = $this->getChartData();
        
        return view('super-admin.index', compact('statistics', 'chartData'));
    }

    public function getAllUsers()
    {
        // Log the access attempt first
        $this->logAccessAttempt('users-api');

        // Check if user is authorized
        if (!$this->isAuthorized()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $users = User::orderBy('created_at', 'desc')
                    ->get(['id', 'name', 'email', 'created_at', 'last_login_date']);

        return response()->json([
            'users' => $users,
            'total' => $users->count()
        ]);
    }

    private function isAuthorized(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        return $user->email === 'rileygweb@gmail.com';
    }

    private function logAccessAttempt(string $endpoint = 'index'): void
    {
        $user = Auth::user();
        $isAuthorized = $this->isAuthorized();
        
        // Determine access reason
        if (!$user) {
            $reason = 'Access denied: User not authenticated';
        } elseif ($user->email !== 'rileygweb@gmail.com') {
            $reason = "Access denied: User email '{$user->email}' is not authorized (required: rileygweb@gmail.com)";
        } else {
            $reason = 'Access granted: User authorized as super admin';
        }

        // Create audit log entry
        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $isAuthorized ? 'SUPER_ADMIN_ACCESS_GRANTED' : 'SUPER_ADMIN_ACCESS_DENIED',
            'table_name' => 'super_admin',
            'record_id' => null,
            'old_values' => null,
            'new_values' => [
                'endpoint' => $endpoint,
                'reason' => $reason,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'authenticated_user_email' => $user?->email,
            ],
            'performed_at' => now(),
        ]);
    }

    private function getStatistics(): array
    {
        return [
            'total_users' => User::count(),
            'total_memory_verses' => MemoryBank::count(),
            'total_memorize_later' => MemorizeLater::count(),
            'total_audit_logs' => AuditLog::count(),
            'recent_users' => User::latest()->take(3)->get(),
            'recent_audit_logs' => AuditLog::with('user')->latest()->take(5)->get(), // Keep for dashboard recent activity
            'active_users_30_days' => User::where('last_login_date', '>=', Carbon::now()->subDays(30))->count(),
            'active_users_7_days' => User::where('last_login_date', '>=', Carbon::now()->subDays(7))->count(),
        ];
    }

    private function getChartData(): array
    {
        // User registration over time (last 30 days)
        $userRegistrations = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', Carbon::now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // Activity over time (last 30 days from audit logs)
        $activityData = AuditLog::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', Carbon::now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // Action breakdown
        $actionBreakdown = AuditLog::select('action', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('action')
            ->get();

        return [
            'user_registrations' => $userRegistrations,
            'activity_data' => $activityData,
            'action_breakdown' => $actionBreakdown,
        ];
    }
}
