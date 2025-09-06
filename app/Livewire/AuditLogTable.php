<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AuditLog;

class AuditLogTable extends Component
{
    use WithPagination;

    public $perPage = 15;
    public $search = '';
    public $actionFilter = '';
    public $tableFilter = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'actionFilter' => ['except' => ''],
        'tableFilter' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingActionFilter()
    {
        $this->resetPage();
    }

    public function updatingTableFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'desc';
        }

        $this->sortField = $field;
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->actionFilter = '';
        $this->tableFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = AuditLog::with('user');

        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('user', function($userQuery) {
                    $userQuery->where('name', 'like', '%' . $this->search . '%')
                             ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->orWhere('table_name', 'like', '%' . $this->search . '%')
                ->orWhere('action', 'like', '%' . $this->search . '%')
                ->orWhere('record_id', 'like', '%' . $this->search . '%');
            });
        }

        // Apply action filter
        if ($this->actionFilter) {
            $query->where('action', $this->actionFilter);
        }

        // Apply table filter
        if ($this->tableFilter) {
            $query->where('table_name', $this->tableFilter);
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $auditLogs = $query->paginate($this->perPage);

        // Get unique actions and tables for filter dropdowns
        $actions = AuditLog::distinct()->pluck('action')->sort();
        $tables = AuditLog::distinct()->pluck('table_name')->sort();

        return view('livewire.audit-log-table', [
            'auditLogs' => $auditLogs,
            'actions' => $actions,
            'tables' => $tables,
        ]);
    }
}
