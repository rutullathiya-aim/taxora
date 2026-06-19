<?php

namespace App\Livewire;

use App\Enums\TaskStatus;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectChecklist;
use App\Models\Service;
use App\Models\Task;
use Livewire\Attributes\On;
use Livewire\Component;

class Dashboard extends Component
{
    #[On('tasks.saved')]
    public function refreshDashboard(): void {}

    public function render()
    {
        $totalClients = Client::count();
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'in_progress')->count();

        $statusCounts = ProjectChecklist::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalChecklists = (int) $statusCounts->sum();
        $pendingDocuments = (int) $statusCounts->get('Pending', 0);
        $approvedCount = (int) $statusCounts->get('Approved', 0);
        $completedFilings = $approvedCount + (int) $statusCounts->get('Not Applicable', 0);
        $submittedCount = (int) $statusCounts->get('Submitted', 0);
        $rejectedCount = (int) $statusCounts->get('Rejected', 0);
        $complianceRate = $totalChecklists > 0 ? round(($completedFilings / $totalChecklists) * 100) : 0;

        $upcomingDeadlines = Project::with(['client', 'service'])
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now()->startOfDay())
            ->orderBy('due_date')
            ->take(5)
            ->get();

        $upcomingCount = Project::whereNotNull('due_date')
            ->where('due_date', '>=', now()->startOfDay())
            ->count();

        $recentProjects = Project::with(['client', 'service', 'checklists'])
            ->latest()
            ->take(7)
            ->get();

        $recentActivity = ProjectChecklist::with(['project.client', 'project.service'])
            ->latest('updated_at')
            ->take(10)
            ->get();

        $serviceHealth = Service::where('is_active', true)
            ->withCount('projects')
            ->get()
            ->map(function (Service $service) {
                $projectIds = $service->projects()->pluck('projects.id');
                $total = $projectIds->isNotEmpty()
                    ? ProjectChecklist::whereIn('project_id', $projectIds)->count()
                    : 0;
                $completed = $projectIds->isNotEmpty()
                    ? ProjectChecklist::whereIn('project_id', $projectIds)
                        ->whereIn('status', ['Approved', 'Not Applicable'])
                        ->count()
                    : 0;

                return [
                    'name' => $service->name,
                    'icon' => $service->icon,
                    'total' => $total,
                    'completed' => $completed,
                    'rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
                    'projects_count' => $service->projects_count,
                ];
            });

        $mandatoryTotal = ProjectChecklist::where('is_mandatory', true)->count();
        $mandatoryCompleted = ProjectChecklist::where('is_mandatory', true)
            ->whereIn('status', ['Approved', 'Not Applicable'])
            ->count();
        $mandatoryRate = $mandatoryTotal > 0 ? round(($mandatoryCompleted / $mandatoryTotal) * 100) : 0;

        $approvalRate = ($approvedCount + $rejectedCount) > 0
            ? round(($approvedCount / ($approvedCount + $rejectedCount)) * 100)
            : 0;

        $submissionRate = $totalChecklists > 0
            ? round((($submittedCount + $completedFilings + $rejectedCount) / $totalChecklists) * 100)
            : 0;

        $activeServicesCount = Service::where('is_active', true)->count();

        // Tasks Metrics
        $tasksQuery = Task::query();
        if (auth()->user()->hasRole('staff')) {
            $tasksQuery->whereHas('assignees', fn ($q) => $q->where('users.id', auth()->id()));
        }

        $totalTasks = (clone $tasksQuery)->count();
        $todoTasks = (clone $tasksQuery)->where('status', TaskStatus::Todo)->count();
        $inProgressTasks = (clone $tasksQuery)->where('status', TaskStatus::InProgress)->count();
        $completedTasks = (clone $tasksQuery)->where('status', TaskStatus::Completed)->count();
        $overdueTasks = (clone $tasksQuery)
            ->where('due_at', '<', now())
            ->whereNotIn('status', [TaskStatus::Completed, TaskStatus::Cancelled])
            ->count();

        $recentTasks = (clone $tasksQuery)
            ->with(['client', 'project', 'assignees'])
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.dashboard', [
            'totalClients' => $totalClients,
            'totalProjects' => $totalProjects,
            'activeProjects' => $activeProjects,
            'pendingDocuments' => $pendingDocuments,
            'completedFilings' => $completedFilings,
            'totalChecklists' => $totalChecklists,
            'complianceRate' => $complianceRate,
            'upcomingDeadlines' => $upcomingDeadlines,
            'upcomingCount' => $upcomingCount,
            'recentProjects' => $recentProjects,
            'recentActivity' => $recentActivity,
            'serviceHealth' => $serviceHealth,
            'statusCounts' => $statusCounts,
            'mandatoryRate' => $mandatoryRate,
            'approvalRate' => $approvalRate,
            'submissionRate' => $submissionRate,
            'activeServicesCount' => $activeServicesCount,

            // Task variables
            'totalTasks' => $totalTasks,
            'todoTasks' => $todoTasks,
            'inProgressTasks' => $inProgressTasks,
            'completedTasks' => $completedTasks,
            'overdueTasks' => $overdueTasks,
            'recentTasks' => $recentTasks,
        ])->layout('components.layouts.app');
    }
}
