@php

    $activityConfig = [
        'Approved' => ['verb' => 'approved', 'dot' => 'bg-emerald-500', 'icon' => 'check-circle'],
        'Submitted' => ['verb' => 'submitted', 'dot' => 'bg-blue-500', 'icon' => 'arrow-up-tray'],
        'Rejected' => ['verb' => 'rejected', 'dot' => 'bg-rose-500', 'icon' => 'x-circle'],
        'Pending' => ['verb' => 'set to pending', 'dot' => 'bg-amber-400', 'icon' => 'clock'],
        'Not Applicable' => ['verb' => 'marked as N/A', 'dot' => 'bg-zinc-400', 'icon' => 'minus-circle'],
    ];

    $documentStatuses = [
        ['key' => 'Approved', 'label' => 'Approved', 'color' => 'bg-emerald-500', 'text' => 'text-emerald-600 dark:text-emerald-400'],
        ['key' => 'Submitted', 'label' => 'Submitted', 'color' => 'bg-blue-500', 'text' => 'text-blue-600 dark:text-blue-400'],
        ['key' => 'Pending', 'label' => 'Pending', 'color' => 'bg-amber-400', 'text' => 'text-amber-600 dark:text-amber-400'],
        ['key' => 'Rejected', 'label' => 'Rejected', 'color' => 'bg-rose-500', 'text' => 'text-rose-600 dark:text-rose-400'],
        ['key' => 'Not Applicable', 'label' => 'N/A', 'color' => 'bg-zinc-400', 'text' => 'text-zinc-500 dark:text-zinc-400'],
    ];
@endphp

<div class="space-y-6">
    <x-slot:heading>Dashboard</x-slot>

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- KPI OVERVIEW CARDS --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-5 my-6">
            <a href="{{ route('clients.index') }}" wire:navigate class="block outline-none">
                <x-stat-card icon="users" color="violet" heading="Total Clients" :value="$totalClients" />
            </a>
            
            <a href="{{ route('projects.index') }}" wire:navigate class="block outline-none">
                <x-stat-card icon="folder-open" color="blue" heading="Active Projects" :value="$activeProjects">
                    <flux:badge color="zinc" size="sm">of {{ $totalProjects }}</flux:badge>
                </x-stat-card>
            </a>

            <a href="{{ route('projects.index') }}" wire:navigate class="block outline-none">
                <x-stat-card icon="document-text" color="amber" heading="Pending Documents" :value="$pendingDocuments" />
            </a>

            <a href="{{ route('projects.index') }}" wire:navigate class="block outline-none">
                <x-stat-card icon="clock" color="rose" heading="Upcoming Deadlines" :value="$upcomingCount" />
            </a>

            <a href="{{ route('projects.index') }}" wire:navigate class="block outline-none">
                <x-stat-card icon="check-circle" color="emerald" heading="Completed Filings" :value="$completedFilings">
                    <flux:badge color="zinc" size="sm">of {{ $totalChecklists }}</flux:badge>
                </x-stat-card>
            </a>

            <a href="{{ route('projects.index') }}" wire:navigate class="block outline-none">
                <x-stat-card icon="chart-bar" color="cyan" heading="Compliance Rate" :value="$complianceRate . '%'" />
            </a>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- TASKS OVERVIEW --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            {{-- Task Metrics --}}
            <flux:card class="lg:col-span-4 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="lg">Task Overview</flux:heading>
                    <flux:badge size="sm" color="zinc">{{ $totalTasks }} Total</flux:badge>
                </div>
                
                <div class="grid grid-cols-2 gap-4 flex-1">
                    <a href="{{ route('tasks.index') }}" wire:navigate class="block bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-3 border border-zinc-100 dark:border-zinc-700 hover:border-zinc-300 transition outline-none">
                        <div class="text-xs text-zinc-500 mb-1">To Do</div>
                        <div class="text-lg font-semibold">{{ $todoTasks }}</div>
                    </a>
                    <a href="{{ route('tasks.index') }}" wire:navigate class="block bg-blue-50 dark:bg-blue-500/10 rounded-lg p-3 border border-blue-100 dark:border-blue-500/20 hover:border-blue-300 transition outline-none">
                        <div class="text-xs text-blue-600 dark:text-blue-400 mb-1">In Progress</div>
                        <div class="text-lg font-semibold text-blue-700 dark:text-blue-300">{{ $inProgressTasks }}</div>
                    </a>
                    <a href="{{ route('tasks.index') }}" wire:navigate class="block bg-emerald-50 dark:bg-emerald-500/10 rounded-lg p-3 border border-emerald-100 dark:border-emerald-500/20 hover:border-emerald-300 transition outline-none">
                        <div class="text-xs text-emerald-600 dark:text-emerald-400 mb-1">Completed</div>
                        <div class="text-lg font-semibold text-emerald-700 dark:text-emerald-300">{{ $completedTasks }}</div>
                    </a>
                    <a href="{{ route('tasks.index') }}" wire:navigate class="block bg-rose-50 dark:bg-rose-500/10 rounded-lg p-3 border border-rose-100 dark:border-rose-500/20 hover:border-rose-300 transition outline-none">
                        <div class="text-xs text-rose-600 dark:text-rose-400 mb-1">Overdue</div>
                        <div class="text-lg font-semibold text-rose-700 dark:text-rose-300">{{ $overdueTasks }}</div>
                    </a>
                </div>
                
                <div class="mt-4">
                    <flux:button href="{{ route('tasks.index') }}" wire:navigate variant="ghost" size="sm" class="w-full">
                        View All Tasks &rarr;
                    </flux:button>
                </div>
            </flux:card>
            
            {{-- Recent Tasks --}}
            <flux:card class="lg:col-span-8 flex flex-col !p-0 overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-zinc-100 dark:border-zinc-700/50">
                    <div>
                        <flux:heading size="lg">Recent Tasks</flux:heading>
                        <flux:subheading>Latest assigned tasks</flux:subheading>
                    </div>
                    <flux:button href="{{ route('tasks.index') }}" wire:navigate size="sm" variant="ghost">See all</flux:button>
                </div>
                
                <div class="divide-y divide-zinc-50 dark:divide-zinc-700/30 flex-1">
                    @forelse($recentTasks as $task)
                        <div class="p-5 hover:bg-zinc-50/50 dark:hover:bg-zinc-700/20 transition-colors">
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-medium text-zinc-500">{{ $task->task_number }}</span>
                                        <a href="{{ route('tasks.show', $task) }}" wire:navigate class="text-sm font-medium text-zinc-800 dark:text-zinc-200 hover:text-violet-600 truncate">
                                            {{ $task->title }}
                                        </a>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-zinc-500">
                                        @if($task->client)
                                            <span class="flex items-center gap-1"><flux:icon.building-office class="size-3" /> {{ Str::limit($task->client->company_name ?? $task->client->client_name, 20) }}</span>
                                        @endif
                                        @if($task->project)
                                            <span class="flex items-center gap-1"><flux:icon.folder class="size-3" /> {{ Str::limit($task->project->project_name, 20) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 shrink-0">
                                    <flux:badge :color="$task->status->color()" size="sm">{{ $task->status->label() }}</flux:badge>
                                    <div class="text-right w-24">
                                        <div class="text-xs font-medium {{ $task->isOverdue ? 'text-rose-600' : 'text-zinc-600 dark:text-zinc-400' }}">
                                            {{ $task->due_at ? $task->due_at->format('d M') : '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-5 text-center">
                            <flux:icon name="clipboard-document-list" class="size-8 mx-auto mb-2 text-zinc-300 dark:text-zinc-600" />
                            <flux:subheading>No recent tasks</flux:subheading>
                        </div>
                    @endforelse
                </div>
            </flux:card>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- SERVICE ANALYTICS --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

            {{-- Service Health --}}
            <flux:card class="lg:col-span-8 flex flex-col !p-0 overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-zinc-100 dark:border-zinc-700/50">
                    <div>
                        <flux:heading size="lg">Service Health</flux:heading>
                        <flux:subheading>Filing progress across service types</flux:subheading>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-zinc-400">{{ $serviceHealth->count() }} active</span>
                        <flux:button href="{{ route('services.index') }}" wire:navigate size="sm" variant="ghost">See all</flux:button>
                    </div>
                </div>
                <div class="p-5 space-y-5">
                    @forelse($serviceHealth as $item)
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex items-center justify-center size-7 rounded-md bg-zinc-50 dark:bg-zinc-700/50">
                                        <flux:icon :name="$item['icon'] ?: 'briefcase'" class="size-3.5 text-zinc-500 dark:text-zinc-400" />
                                    </div>
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $item['name'] }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-zinc-400">{{ $item['projects_count'] }} {{ Str::plural('project', $item['projects_count']) }}</span>
                                    <span class="text-xs font-semibold tabular-nums {{ $item['rate'] >= 60 ? 'text-emerald-600 dark:text-emerald-400' : ($item['rate'] >= 30 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">{{ $item['rate'] }}%</span>
                                </div>
                            </div>
                            <div class="h-1.5 bg-zinc-100 dark:bg-zinc-700 rounded-full overflow-hidden">
                                @php
                                    $barColor = $item['rate'] >= 60 ? 'bg-emerald-500 dark:bg-emerald-400' : ($item['rate'] >= 30 ? 'bg-amber-500 dark:bg-amber-400' : 'bg-rose-500 dark:bg-rose-400');
                                @endphp
                                <div class="h-full {{ $barColor }} rounded-full transition-all duration-700" style="width: {{ $item['rate'] }}%"></div>
                            </div>
                            <div class="flex items-center gap-3 mt-1.5 text-xs text-zinc-400">
                                <span>{{ $item['completed'] }} completed</span>
                                <span>·</span>
                                <span>{{ $item['total'] - $item['completed'] }} remaining</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-zinc-400">
                            <flux:icon name="shield-check" class="size-8 mx-auto mb-2 text-zinc-300 dark:text-zinc-600" />
                            <p class="text-sm">No active services configured</p>
                        </div>
                    @endforelse
                </div>
            </flux:card>

            {{-- Document Status Distribution --}}
            <flux:card class="lg:col-span-4 flex flex-col !p-0 overflow-hidden">
                <div class="p-5 border-b border-zinc-100 dark:border-zinc-700/50">
                    <flux:heading size="lg">Document Status</flux:heading>
                    <flux:subheading>Breakdown of all filings</flux:subheading>
                </div>
                <div class="p-5">
                    {{-- Stacked bar overview --}}
                    @if($totalChecklists > 0)
                        <div class="flex h-2.5 rounded-full overflow-hidden mb-5">
                            @foreach($documentStatuses as $ds)
                                @php $dsWidth = round(((int) $statusCounts->get($ds['key'], 0) / $totalChecklists) * 100, 1); @endphp
                                @if($dsWidth > 0)
                                    <div class="{{ $ds['color'] }}" style="width: {{ $dsWidth }}%" title="{{ $ds['label'] }}: {{ $statusCounts->get($ds['key'], 0) }}"></div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <div class="space-y-3.5">
                        @foreach($documentStatuses as $ds)
                            @php
                                $dsCount = (int) $statusCounts->get($ds['key'], 0);
                                $dsPct = $totalChecklists > 0 ? round(($dsCount / $totalChecklists) * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="size-2 rounded-full {{ $ds['color'] }}"></span>
                                        <span class="text-xs font-medium text-zinc-600 dark:text-zinc-300">{{ $ds['label'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold {{ $ds['text'] }} tabular-nums">{{ $dsCount }}</span>
                                        <span class="text-xs text-zinc-400 tabular-nums w-8 text-right">{{ $dsPct }}%</span>
                                    </div>
                                </div>
                                <div class="h-1 bg-zinc-100 dark:bg-zinc-700 rounded-full overflow-hidden">
                                    <div class="{{ $ds['color'] }} h-full rounded-full transition-all duration-500" style="width: {{ $dsPct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 pt-4 border-t border-zinc-100 dark:border-zinc-700/50">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-zinc-500 dark:text-zinc-400">Total documents</span>
                            <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $totalChecklists }}</span>
                        </div>
                    </div>
                </div>
            </flux:card>

        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- PROJECT STATUS TABLE --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}

        <flux:card class="!p-0 overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-zinc-100 dark:border-zinc-700/50">
                <div>
                    <flux:heading size="lg">Project Status</flux:heading>
                    <flux:subheading>Overview of recent project activity</flux:subheading>
                </div>
                <span class="text-xs text-zinc-400">{{ $recentProjects->count() }} {{ Str::plural('project', $recentProjects->count()) }}</span>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Project</flux:table.column>
                    <flux:table.column>Client</flux:table.column>
                    <flux:table.column>Service</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Progress</flux:table.column>
                    <flux:table.column>Deadline</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($recentProjects as $project)
                        @php
                            $pTotal = $project->checklists->count();
                            $pDone = $project->checklists->whereIn('status', ['Approved', 'Not Applicable'])->count();
                            $pPct = $pTotal > 0 ? round(($pDone / $pTotal) * 100) : 0;
                        @endphp
                        <flux:table.row>
                            <flux:table.cell>
                                <a href="{{ route('projects.show', $project) }}"
                                    class="font-medium text-zinc-900 dark:text-zinc-100 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                                    wire:navigate>
                                    {{ $project->project_name }}
                                </a>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:avatar circle size="xs" name="{{ $project->client?->client_name ?? 'Unknown Client' }}" color="auto" />
                                    <span
                                        class="text-zinc-600 dark:text-zinc-400">{{ $project->client?->client_name ?? 'Unknown Client' }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span
                                    class="text-xs text-zinc-500 dark:text-zinc-400">{{ $project->service?->name ?? '—' }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$project->status?->color()" size="sm">
                                    {{ $project->status?->label() ?? 'Unknown' }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2 min-w-[100px]">
                                    <div class="flex-1 h-1.5 bg-zinc-100 dark:bg-zinc-700 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500 {{ $pPct >= 80 ? 'bg-emerald-500' : ($pPct >= 40 ? 'bg-blue-500' : 'bg-amber-500') }}"
                                            style="width: {{ $pPct }}%"></div>
                                    </div>
                                    <span
                                        class="text-xs font-medium text-zinc-500 tabular-nums w-8 text-right">{{ $pPct }}%</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($project->due_date)
                                    @php
                                        $deadline = \Carbon\Carbon::parse($project->due_date);
                                        $daysAway = (int) now()->startOfDay()->diffInDays($deadline, false);
                                    @endphp
                                    <div class="flex items-center gap-1.5">
                                        @if($daysAway < 0)
                                            <span class="size-1.5 rounded-full bg-rose-500"></span>
                                        @elseif($daysAway <= 7)
                                            <span class="size-1.5 rounded-full bg-amber-500"></span>
                                        @else
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        @endif
                                        <span
                                            class="text-xs text-zinc-600 dark:text-zinc-400">{{ $deadline->format('d M Y') }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6">
                                <div class="text-center py-8 text-zinc-400">
                                    <flux:icon name="folder-open"
                                        class="size-8 mx-auto mb-2 text-zinc-300 dark:text-zinc-600" />
                                    <p class="text-sm">No projects yet</p>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- UPCOMING DEADLINES & RECENT ACTIVITY --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

            {{-- Upcoming Deadlines --}}
            {{-- Upcoming Deadlines --}}
            <flux:card class="lg:col-span-5 flex flex-col !p-0 overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-zinc-100 dark:border-zinc-700/50">
                    <div>
                        <flux:heading size="lg">Upcoming Deadlines</flux:heading>
                        <flux:subheading>Due dates approaching</flux:subheading>
                    </div>
                </div>
                <div class="divide-y divide-zinc-50 dark:divide-zinc-700/30">
                    @forelse($upcomingDeadlines as $dl)
                        @php
                            $dlDate = \Carbon\Carbon::parse($dl->due_date);
                            $dlDays = (int) now()->startOfDay()->diffInDays($dlDate, false);
                            $urgencyBorder = match (true) {
                                $dlDays < 0 => 'border-l-rose-500',
                                $dlDays <= 7 => 'border-l-amber-500',
                                $dlDays <= 14 => 'border-l-yellow-400',
                                default => 'border-l-emerald-500',
                            };
                        @endphp
                        <a href="{{ route('projects.show', $dl) }}" wire:navigate
                            class="block px-5 py-3.5 border-l-2 {{ $urgencyBorder }} hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors cursor-pointer outline-none">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200 truncate group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                                        {{ $dl->project_name }}
                                    </p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                        {{ $dl->client?->client_name ?? 'Unknown Client' }} · {{ $dl->service?->name ?? '—' }}
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-xs font-semibold text-zinc-600 dark:text-zinc-300 tabular-nums">
                                        {{ $dlDate->format('d M') }}
                                    </p>
                                    <p class="text-xs mt-0.5 tabular-nums
                                                                    @if($dlDays < 0) text-rose-600 dark:text-rose-400
                                                                    @elseif($dlDays <= 7) text-amber-600 dark:text-amber-400
                                                                    @else text-zinc-400
                                                                    @endif
                                                                ">
                                        @if($dlDays < 0) {{ abs($dlDays) }}d overdue
                                        @elseif($dlDays === 0) Due today
                                        @else {{ $dlDays }}d away
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-5 text-center">
                            <flux:icon name="calendar" class="size-8 mx-auto mb-2 text-zinc-300 dark:text-zinc-600" />
                            <p class="text-sm text-zinc-400">No upcoming deadlines</p>
                        </div>
                    @endforelse
                </div>
            </flux:card>

            {{-- Recent Activity Feed --}}
            <flux:card class="lg:col-span-7 flex flex-col !p-0 overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-zinc-100 dark:border-zinc-700/50">
                    <div>
                        <flux:heading size="lg">Recent Activity</flux:heading>
                        <flux:subheading>Latest service and document updates</flux:subheading>
                    </div>
                </div>
                <div class="p-5">
                    <div class="relative pl-6">
                        {{-- Timeline line --}}
                        <div class="absolute left-[4.5px] top-1 bottom-1 w-px bg-zinc-100 dark:bg-zinc-700">
                        </div>

                        <div class="space-y-5">
                            @forelse($recentActivity as $activity)
                                @php
                                    $statusValue = $activity->status instanceof \UnitEnum ? $activity->status->value : $activity->status;
                                    $ac = $activityConfig[$statusValue] ?? ['verb' => 'updated', 'dot' => 'bg-zinc-400', 'icon' => 'document-text'];
                                @endphp
                                <div class="relative">
                                    {{-- Timeline dot --}}
                                    <div class="absolute -left-6 top-0.5 size-[9px] rounded-full ring-2 ring-white dark:ring-zinc-800 {{ $ac['dot'] }}">
                                    </div>

                                    <a @if($activity->project) href="{{ route('projects.show', $activity->project) }}" wire:navigate @endif class="block group outline-none">
                                        <p class="text-sm text-zinc-700 dark:text-zinc-300">
                                            <span class="font-medium text-zinc-900 dark:text-zinc-100 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">{{ Str::limit($activity->name, 30) }}</span>
                                            <span class="text-zinc-500 dark:text-zinc-400"> {{ $ac['verb'] }}</span>
                                        </p>
                                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">
                                            {{ $activity->project?->project_name ?? 'Unknown project' }}
                                            <span class="mx-1">·</span>
                                            {{ $activity->updated_at->diffForHumans() }}
                                        </p>
                                    </a>
                                </div>
                            @empty
                                <div class="text-center py-6">
                                    <flux:icon name="clock" class="size-8 mx-auto mb-2 text-zinc-300 dark:text-zinc-600" />
                                    <p class="text-sm text-zinc-400">No recent activity</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </flux:card>

        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- PERFORMANCE INSIGHTS --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}

        <flux:card class="!p-0 overflow-hidden">
            <div class="p-5 border-b border-zinc-100 dark:border-zinc-700/50">
                <flux:heading size="lg">Performance Insights</flux:heading>
                <flux:subheading>Key operational metrics and filing analytics</flux:subheading>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-zinc-100 dark:divide-zinc-700/50">

                {{-- Submission Rate --}}
                <div class="p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="flex items-center justify-center size-7 rounded-md bg-blue-50 dark:bg-blue-500/10">
                            <flux:icon name="arrow-up-tray" class="size-3.5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Submission
                            Rate</span>
                    </div>
                    <div class="text-xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight tabular-nums">
                        {{ $submissionRate }}<span class="text-sm font-semibold text-zinc-400">%</span>
                    </div>
                    <div class="mt-3">
                        <div class="h-1.5 bg-zinc-100 dark:bg-zinc-700 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full transition-all duration-500"
                                style="width: {{ $submissionRate }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- Approval Rate --}}
                <div class="p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div
                            class="flex items-center justify-center size-7 rounded-md bg-emerald-50 dark:bg-emerald-500/10">
                            <flux:icon name="check-badge" class="size-3.5 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Approval Rate</span>
                    </div>
                    <div class="text-xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight tabular-nums">
                        {{ $approvalRate }}<span class="text-sm font-semibold text-zinc-400">%</span>
                    </div>
                    <div class="mt-3">
                        <div class="h-1.5 bg-zinc-100 dark:bg-zinc-700 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                                style="width: {{ $approvalRate }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- Mandatory Completion --}}
                <div class="p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="flex items-center justify-center size-7 rounded-md bg-rose-50 dark:bg-rose-500/10">
                            <flux:icon name="shield-check" class="size-3.5 text-rose-600 dark:text-rose-400" />
                        </div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Mandatory Done</span>
                    </div>
                    <div class="text-xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight tabular-nums">
                        {{ $mandatoryRate }}<span class="text-sm font-semibold text-zinc-400">%</span>
                    </div>
                    <div class="mt-3">
                        <div class="h-1.5 bg-zinc-100 dark:bg-zinc-700 rounded-full overflow-hidden">
                            <div class="h-full {{ $mandatoryRate >= 60 ? 'bg-emerald-500' : ($mandatoryRate >= 30 ? 'bg-amber-500' : 'bg-rose-500') }} rounded-full transition-all duration-500"
                                style="width: {{ $mandatoryRate }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- Active Services --}}
                <div class="p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div
                            class="flex items-center justify-center size-7 rounded-md bg-violet-50 dark:bg-violet-500/10">
                            <flux:icon name="squares-2x2" class="size-3.5 text-violet-600 dark:text-violet-400" />
                        </div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Active
                            Services</span>
                    </div>
                    <div class="text-xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight tabular-nums">
                        {{ $activeServicesCount }}
                    </div>
                    <div class="mt-3">
                        <div class="flex gap-1">
                            @for($i = 0; $i < $activeServicesCount; $i++)
                                <div class="h-1.5 flex-1 bg-violet-500 dark:bg-violet-400 rounded-full"></div>
                            @endfor
                            @for($i = $activeServicesCount; $i < 8; $i++)
                                <div class="h-1.5 flex-1 bg-zinc-100 dark:bg-zinc-700 rounded-full"></div>
                            @endfor
                        </div>
                    </div>
                </div>

            </div>
        </flux:card>

</div>