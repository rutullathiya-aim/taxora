@php
    $projectStatuses = [
        'draft' => ['label' => 'Draft', 'bg' => 'bg-zinc-100 dark:bg-zinc-700', 'text' => 'text-zinc-600 dark:text-zinc-300'],
        'in_progress' => ['label' => 'In Progress', 'bg' => 'bg-blue-50 dark:bg-blue-500/10', 'text' => 'text-blue-700 dark:text-blue-400'],
        'submitted' => ['label' => 'Submitted', 'bg' => 'bg-amber-50 dark:bg-amber-500/10', 'text' => 'text-amber-700 dark:text-amber-400'],
        'approved' => ['label' => 'Approved', 'bg' => 'bg-emerald-50 dark:bg-emerald-500/10', 'text' => 'text-emerald-700 dark:text-emerald-400'],
    ];

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

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            {{-- Card 1: Total Clients --}}
            <div
                class="group bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center size-10 rounded-lg bg-violet-50 dark:bg-violet-500/10">
                        <flux:icon name="users" class="size-5 text-violet-600 dark:text-violet-400" />
                    </div>
                    <span class="text-xs font-medium text-zinc-400 dark:text-zinc-500">Registered</span>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">
                    {{ $totalClients }}
                </div>
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Total Clients</div>
                @if($sparklines['clients']['line'])
                    <div class="mt-4 text-violet-300 dark:text-violet-500/60">
                        <svg class="w-full h-8" viewBox="0 0 100 32" preserveAspectRatio="none" fill="none">
                            <polygon points="{{ $sparklines['clients']['area'] }}" fill="currentColor" opacity="0.3" />
                            <polyline points="{{ $sparklines['clients']['line'] }}" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Card 2: Active Projects --}}
            <div
                class="group bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center size-10 rounded-lg bg-blue-50 dark:bg-blue-500/10">
                        <flux:icon name="folder-open" class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <span class="text-xs font-medium text-zinc-400 dark:text-zinc-500">of {{ $totalProjects }}
                        total</span>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">
                    {{ $activeProjects }}
                </div>
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Active Projects</div>
                @if($sparklines['projects']['line'])
                    <div class="mt-4 text-blue-300 dark:text-blue-500/60">
                        <svg class="w-full h-8" viewBox="0 0 100 32" preserveAspectRatio="none" fill="none">
                            <polygon points="{{ $sparklines['projects']['area'] }}" fill="currentColor" opacity="0.3" />
                            <polyline points="{{ $sparklines['projects']['line'] }}" stroke="currentColor"
                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                vector-effect="non-scaling-stroke" />
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Card 3: Pending Documents --}}
            <div
                class="group bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center size-10 rounded-lg bg-amber-50 dark:bg-amber-500/10">
                        <flux:icon name="document-text" class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <span class="text-xs font-medium text-amber-600 dark:text-amber-400">Awaiting action</span>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">
                    {{ $pendingDocuments }}
                </div>
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Pending Documents</div>
                @if($sparklines['pending']['line'])
                    <div class="mt-4 text-amber-300 dark:text-amber-500/60">
                        <svg class="w-full h-8" viewBox="0 0 100 32" preserveAspectRatio="none" fill="none">
                            <polygon points="{{ $sparklines['pending']['area'] }}" fill="currentColor" opacity="0.3" />
                            <polyline points="{{ $sparklines['pending']['line'] }}" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Card 4: Upcoming Deadlines --}}
            <div
                class="group bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center size-10 rounded-lg bg-rose-50 dark:bg-rose-500/10">
                        <flux:icon name="clock" class="size-5 text-rose-600 dark:text-rose-400" />
                    </div>
                    <span class="text-xs font-medium text-zinc-400 dark:text-zinc-500">Next 30 days</span>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">
                    {{ $upcomingCount }}
                </div>
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Upcoming Deadlines</div>
                @if($sparklines['deadlines']['line'])
                    <div class="mt-4 text-rose-300 dark:text-rose-500/60">
                        <svg class="w-full h-8" viewBox="0 0 100 32" preserveAspectRatio="none" fill="none">
                            <polygon points="{{ $sparklines['deadlines']['area'] }}" fill="currentColor" opacity="0.3" />
                            <polyline points="{{ $sparklines['deadlines']['line'] }}" stroke="currentColor"
                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                vector-effect="non-scaling-stroke" />
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Card 5: Completed Filings --}}
            <div
                class="group bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="flex items-center justify-center size-10 rounded-lg bg-emerald-50 dark:bg-emerald-500/10">
                        <flux:icon name="check-circle" class="size-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">of
                        {{ $totalChecklists }} filings</span>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">
                    {{ $completedFilings }}
                </div>
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Completed Filings</div>
                @if($sparklines['completed']['line'])
                    <div class="mt-4 text-emerald-300 dark:text-emerald-500/60">
                        <svg class="w-full h-8" viewBox="0 0 100 32" preserveAspectRatio="none" fill="none">
                            <polygon points="{{ $sparklines['completed']['area'] }}" fill="currentColor" opacity="0.3" />
                            <polyline points="{{ $sparklines['completed']['line'] }}" stroke="currentColor"
                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                vector-effect="non-scaling-stroke" />
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Card 6: Compliance Rate --}}
            <div
                class="group bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center size-10 rounded-lg bg-cyan-50 dark:bg-cyan-500/10">
                        <flux:icon name="chart-bar" class="size-5 text-cyan-600 dark:text-cyan-400" />
                    </div>
                    <span
                        class="text-xs font-medium {{ $complianceRate >= 50 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                        {{ $complianceRate >= 70 ? 'Healthy' : ($complianceRate >= 40 ? 'Moderate' : 'Needs attention') }}
                    </span>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">
                    {{ $complianceRate }}<span class="text-lg font-semibold text-zinc-400">%</span>
                </div>
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Compliance Rate</div>
                @if($sparklines['rate']['line'])
                    <div class="mt-4 text-cyan-300 dark:text-cyan-500/60">
                        <svg class="w-full h-8" viewBox="0 0 100 32" preserveAspectRatio="none" fill="none">
                            <polygon points="{{ $sparklines['rate']['area'] }}" fill="currentColor" opacity="0.3" />
                            <polyline points="{{ $sparklines['rate']['line'] }}" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        </svg>
                    </div>
                @endif
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- SERVICE ANALYTICS --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

            {{-- Service Health --}}
            <div
                class="lg:col-span-8 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                <div
                    class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-700/50">
                    <div>
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Service Health
                        </h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Filing progress across
                            service types</p>
                    </div>
                    <span class="text-xs text-zinc-400">{{ $serviceHealth->count() }} active</span>
                </div>
                <div class="p-5 space-y-5">
                    @forelse($serviceHealth as $item)
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="flex items-center justify-center size-7 rounded-md bg-zinc-50 dark:bg-zinc-700/50">
                                        <flux:icon :name="$item['icon'] ?: 'briefcase'"
                                            class="size-3.5 text-zinc-500 dark:text-zinc-400" />
                                    </div>
                                    <span
                                        class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $item['name'] }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-zinc-400">{{ $item['projects_count'] }}
                                        {{ Str::plural('project', $item['projects_count']) }}</span>
                                    <span
                                        class="text-xs font-semibold tabular-nums {{ $item['rate'] >= 60 ? 'text-emerald-600 dark:text-emerald-400' : ($item['rate'] >= 30 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">{{ $item['rate'] }}%</span>
                                </div>
                            </div>
                            <div class="h-1.5 bg-zinc-100 dark:bg-zinc-700 rounded-full overflow-hidden">
                                @php
                                    $barColor = $item['rate'] >= 60 ? 'bg-emerald-500 dark:bg-emerald-400' : ($item['rate'] >= 30 ? 'bg-amber-500 dark:bg-amber-400' : 'bg-rose-500 dark:bg-rose-400');
                                @endphp
                                <div class="h-full {{ $barColor }} rounded-full transition-all duration-700"
                                    style="width: {{ $item['rate'] }}%"></div>
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
            </div>

            {{-- Document Status Distribution --}}
            <div
                class="lg:col-span-4 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-100 dark:border-zinc-700/50">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Document Status</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Breakdown of all filings</p>
                </div>
                <div class="p-5">
                    {{-- Stacked bar overview --}}
                    @if($totalChecklists > 0)
                        <div class="flex h-2.5 rounded-full overflow-hidden mb-5">
                            @foreach($documentStatuses as $ds)
                                @php $dsWidth = round(((int) $statusCounts->get($ds['key'], 0) / $totalChecklists) * 100, 1); @endphp
                                @if($dsWidth > 0)
                                    <div class="{{ $ds['color'] }}" style="width: {{ $dsWidth }}%"
                                        title="{{ $ds['label'] }}: {{ $statusCounts->get($ds['key'], 0) }}"></div>
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
                                        <span
                                            class="text-xs font-medium text-zinc-600 dark:text-zinc-300">{{ $ds['label'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs font-semibold {{ $ds['text'] }} tabular-nums">{{ $dsCount }}</span>
                                        <span class="text-xs text-zinc-400 tabular-nums w-8 text-right">{{ $dsPct }}%</span>
                                    </div>
                                </div>
                                <div class="h-1 bg-zinc-100 dark:bg-zinc-700 rounded-full overflow-hidden">
                                    <div class="{{ $ds['color'] }} h-full rounded-full transition-all duration-500"
                                        style="width: {{ $dsPct }}%"></div>
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
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- PROJECT STATUS TABLE --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}

        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-700/50">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Project Status</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Overview of recent project
                        activity</p>
                </div>
                <span class="text-xs text-zinc-400">{{ $recentProjects->count() }}
                    {{ Str::plural('project', $recentProjects->count()) }}</span>
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
                            $pTotal = $project->projectChecklists->count();
                            $pDone = $project->projectChecklists->whereIn('status', ['Approved', 'Not Applicable'])->count();
                            $pPct = $pTotal > 0 ? round(($pDone / $pTotal) * 100) : 0;
                            $pStatus = $projectStatuses[$project->status] ?? $projectStatuses['draft'];
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
                                    <flux:avatar circle size="xs" name="{{ $project->client->client_name }}" color="auto" />
                                    <span
                                        class="text-zinc-600 dark:text-zinc-400">{{ $project->client->client_name }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span
                                    class="text-xs text-zinc-500 dark:text-zinc-400">{{ $project->service?->name ?? '—' }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full {{ $pStatus['bg'] }} {{ $pStatus['text'] }}">
                                    {{ $pStatus['label'] }}
                                </span>
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
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- UPCOMING DEADLINES & RECENT ACTIVITY --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

            {{-- Upcoming Deadlines --}}
            <div
                class="lg:col-span-5 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                <div
                    class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-700/50">
                    <div>
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Upcoming Deadlines
                        </h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Due dates approaching
                        </p>
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
                        <div
                            class="px-5 py-3.5 border-l-2 {{ $urgencyBorder }} hover:bg-zinc-50/50 dark:hover:bg-zinc-700/20 transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200 truncate">
                                        {{ $dl->project_name }}
                                    </p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                        {{ $dl->client->client_name }} · {{ $dl->service?->name }}
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
                        </div>
                    @empty
                        <div class="p-5 text-center">
                            <flux:icon name="calendar" class="size-8 mx-auto mb-2 text-zinc-300 dark:text-zinc-600" />
                            <p class="text-sm text-zinc-400">No upcoming deadlines</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Activity Feed --}}
            <div
                class="lg:col-span-7 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                <div
                    class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-700/50">
                    <div>
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent Activity</h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Latest service and
                            document updates</p>
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
                                    $ac = $activityConfig[$activity->status] ?? ['verb' => 'updated', 'dot' => 'bg-zinc-400', 'icon' => 'document-text'];
                                @endphp
                                <div class="relative">
                                    {{-- Timeline dot --}}
                                    <div
                                        class="absolute -left-6 top-0.5 size-[9px] rounded-full ring-2 ring-white dark:ring-zinc-800 {{ $ac['dot'] }}">
                                    </div>

                                    <div>
                                        <p class="text-sm text-zinc-700 dark:text-zinc-300">
                                            <span
                                                class="font-medium text-zinc-900 dark:text-zinc-100">{{ Str::limit($activity->name, 30) }}</span>
                                            <span class="text-zinc-500 dark:text-zinc-400"> {{ $ac['verb'] }}</span>
                                        </p>
                                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">
                                            {{ $activity->project?->project_name ?? 'Unknown project' }}
                                            <span class="mx-1">·</span>
                                            {{ $activity->updated_at->diffForHumans() }}
                                        </p>
                                    </div>
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
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- PERFORMANCE INSIGHTS --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}

        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-100 dark:border-zinc-700/50">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Performance Insights</h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Key operational metrics and filing
                    analytics</p>
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
        </div>

</div>