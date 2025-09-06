<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\MemoryBank;
use App\Models\MemorizeLater;
use App\Models\AuditLog;

class SuperAdminController extends Controller
{
    public function index()
    {
        // Check if user is authorized
        if (!$this->isAuthorized()) {
            return redirect('/');
        }

        $statistics = $this->getStatistics();
        
        return view('super-admin.index', compact('statistics'));
    }

    private function isAuthorized(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        return $user->id === 23 && $user->email === 'rileygweb@gmail.com';
    }

    private function getStatistics(): array
    {
        return [
            'total_users' => User::count(),
            'total_memory_verses' => MemoryBank::count(),
            'total_memorize_later' => MemorizeLater::count(),
            'total_audit_logs' => AuditLog::count(),
            'recent_users' => User::latest()->take(5)->get(),
            'recent_audit_logs' => AuditLog::with('user')->latest()->take(10)->get()
        ];
    }
}
