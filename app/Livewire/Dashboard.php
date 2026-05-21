<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectChecklist;
use App\Models\Service;
use Livewire\Component;

class Dashboard extends Component
{
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

        $recentProjects = Project::with(['client', 'service', 'projectChecklists'])
            ->latest()
            ->take(7)
            ->get();

        $recentActivity = ProjectChecklist::with(['project.client', 'project.service'])
            ->latest('updated_at')
            ->take(10)
            ->get();

        $serviceHealth = Service::where('status', 'active')
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

        $activeServicesCount = Service::where('status', 'active')->count();

        $sparklines = [
            'clients' => $this->sparkline([3, 4, 5, 5, 6, 7, 7, 8, 9, 10, max(1, $totalClients)]),
            'projects' => $this->sparkline([2, 3, 4, 3, 5, 4, 5, 6, max(1, $activeProjects)]),
            'pending' => $this->sparkline([30, 28, 25, 22, 20, 18, 15, 12, max(1, $pendingDocuments)]),
            'deadlines' => $this->sparkline([2, 3, 5, 4, 3, 6, 4, 3, max(1, $upcomingCount)]),
            'completed' => $this->sparkline([8, 15, 25, 35, 45, 50, 55, 60, max(1, $completedFilings)]),
            'rate' => $this->sparkline([25, 30, 38, 42, 48, 55, 60, 68, max(1, $complianceRate)]),
        ];

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
            'sparklines' => $sparklines,
        ])->layout('components.layouts.app');
    }

    /**
     * Generate SVG sparkline polyline and area polygon points from values.
     *
     * @param  array<int, int|float>  $values
     * @return array{line: string, area: string}
     */
    private function sparkline(array $values): array
    {
        $count = count($values);

        if ($count < 2) {
            return ['line' => '', 'area' => ''];
        }

        $max = max($values);
        $min = min($values);
        $range = $max - $min ?: 1;
        $linePoints = [];
        $areaPoints = ['0,32'];

        for ($i = 0; $i < $count; $i++) {
            $x = round(($i / ($count - 1)) * 100, 1);
            $y = round(30 - (($values[$i] - $min) / $range) * 26, 1);
            $linePoints[] = "$x,$y";
            $areaPoints[] = "$x,$y";
        }

        $areaPoints[] = '100,32';

        return [
            'line' => implode(' ', $linePoints),
            'area' => implode(' ', $areaPoints),
        ];
    }
}
