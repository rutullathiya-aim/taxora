<?php

namespace Database\Seeders;

use App\Enums\ChecklistStatus;
use App\Enums\ClientStatus;
use App\Enums\ProjectStatus;
use App\Enums\ServiceChecklistItemStatus;
use App\Enums\ServiceStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectChecklist;
use App\Models\Service;
use App\Models\ServiceChecklistItem;
use App\Models\Task;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DummyDataSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic Indian business dummy data.
     *
     * Usage: php artisan db:seed --class=DummyDataSeeder
     */
    public function run(): void
    {
        $faker = Faker::create('en_IN');
        $this->command->info('🚀 Seeding dummy data...');

        // ──────────────────────────────────────────────
        // TEAM MEMBERS
        // ──────────────────────────────────────────────
        $this->command->info('👥 Creating team members...');

        $manager = User::firstOrCreate(
            ['email' => 'rutul@taxora.in'],
            [
                'name' => 'Rutul Lathiya',
                'role' => UserRole::Manager,
                'is_active' => true,
                'email_verified_at' => now(),
                'phone' => '9876501234',
                'password' => bcrypt('taxora@4321'),
            ]
        );
        $this->assignRoleSafely($manager, 'manager');

        $staffMembers = [];
        // Generate 14 staff members to reach 15 total team members
        for ($i = 0; $i < 14; $i++) {
            $staff = User::firstOrCreate(
                ['email' => 'staff' . ($i + 1) . '@taxora.in'],
                [
                    'name' => $faker->name,
                    'role' => UserRole::Staff,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'phone' => $faker->numerify('9#########'),
                    'password' => bcrypt('taxora@4321'),
                ]
            );
            $this->assignRoleSafely($staff, 'staff');
            $staffMembers[] = $staff;
        }

        $allStaffIds = collect($staffMembers)->pluck('id')->toArray();

        // ──────────────────────────────────────────────
        // CLIENTS
        // ──────────────────────────────────────────────
        $this->command->info('🏢 Creating clients...');

        $clients = [];
        for ($i = 0; $i < 50; $i++) {
            $isCompany = $faker->boolean(70);
            $clientName = $faker->name;
            $companyName = $isCompany ? $faker->company : null;

            $client = Client::firstOrCreate(
                ['email' => 'client' . ($i + 1) . '@example.com'],
                [
                    'client_name' => $clientName,
                    'company_name' => $companyName,
                    'phone' => $faker->numerify('9#########'),
                    'address' => $faker->address,
                    'status' => $faker->boolean(90) ? ClientStatus::Active : ClientStatus::Inactive,
                    'created_by' => $manager->id,
                ]
            );
            $clients[] = $client;
        }

        // ──────────────────────────────────────────────
        // PROJECTS
        // ──────────────────────────────────────────────
        $this->command->info('📁 Creating projects...');

        $services = Service::where('status', ServiceStatus::Active->value)->get();
        if ($services->isEmpty()) {
            $this->command->error('No active services found! Please run ServiceSeeder first.');

            return;
        }

        $now = now();
        $createdProjects = [];

        for ($i = 0; $i < 300; $i++) {
            $client = $faker->randomElement($clients);
            $service = $faker->randomElement($services);

            $status = $faker->randomElement([
                ProjectStatus::Active, ProjectStatus::Active, ProjectStatus::Active, // Weighting active
                ProjectStatus::Completed, ProjectStatus::OnHold,
            ]);

            $dueDate = $status === ProjectStatus::Completed
                ? clone $faker->dateTimeBetween('-6 months', 'now')
                : clone $faker->dateTimeBetween('-1 month', '+3 months');

            $project = Project::create([
                'client_id' => $client->id,
                'project_name' => ($client->company_name ?? $client->client_name) . ' - ' . $service->name,
                'service_id' => $service->id,
                'status' => $status,
                'due_date' => $dueDate->format('Y-m-d'),
                'created_by' => $manager->id,
                'created_at' => clone $faker->dateTimeBetween('-6 months', 'now'),
            ]);

            // Assign 1 to 3 random staff members
            $assigneeCount = $faker->numberBetween(1, 3);
            $assignees = $faker->randomElements($allStaffIds, $assigneeCount);
            $project->assignees()->sync($assignees);

            // Create checklist items from service template
            $checklistItems = ServiceChecklistItem::where('service_id', $service->id)
                ->where('status', ServiceChecklistItemStatus::Active->value)
                ->orderBy('sort_order')
                ->get();

            if ($checklistItems->count() > 0) {
                $totalItems = $checklistItems->count();
                $progress = $faker->randomFloat(2, 0, 1);

                if ($status === ProjectStatus::Completed) {
                    $progress = 1.0;
                } elseif ($status === ProjectStatus::OnHold && $progress > 0.5) {
                    $progress = $faker->randomFloat(2, 0, 0.5);
                }

                $completedCount = (int) floor($totalItems * $progress);
                $checklistRecords = [];

                foreach ($checklistItems as $index => $item) {
                    $itemStatus = ChecklistStatus::Pending;
                    if ($index < $completedCount) {
                        $itemStatus = $faker->randomElement([
                            ChecklistStatus::Approved,
                            ChecklistStatus::Submitted,
                        ]);
                    }

                    $checklistRecords[] = [
                        'id' => (string) Str::ulid(),
                        'project_id' => $project->id,
                        'name' => $item->title,
                        'description' => $item->description,
                        'is_mandatory' => $item->is_mandatory,
                        'status' => $itemStatus->value,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (! empty($checklistRecords)) {
                    ProjectChecklist::insert($checklistRecords);
                }
            }

            $createdProjects[] = $project;
        }

        // ──────────────────────────────────────────────
        // TASKS
        // ──────────────────────────────────────────────
        $this->command->info('✅ Creating tasks...');

        $admin = User::where('email', 'admin@taxora.in')->first();
        $creatorId = $admin?->id ?? $manager->id;

        $taskVerbs = ['Review', 'Update', 'Draft', 'Submit', 'Follow up on', 'Collect', 'Prepare', 'Verify'];
        $taskNouns = ['documents', 'application', 'invoices', 'report', 'deed', 'agreement', 'registration', 'returns'];

        for ($i = 0; $i < 500; $i++) {
            // 70% chance to be attached to a project, 30% standalone
            $isProjectTask = $faker->boolean(70);
            $project = $isProjectTask ? $faker->randomElement($createdProjects) : null;
            $client = $project ? null : ($faker->boolean(50) ? $faker->randomElement($clients) : null);

            $status = $faker->randomElement([
                TaskStatus::Todo, TaskStatus::Todo,
                TaskStatus::InProgress, TaskStatus::InProgress,
                TaskStatus::Completed,
                TaskStatus::OnHold,
            ]);

            $title = $faker->randomElement($taskVerbs) . ' ' . $faker->randomElement($taskNouns);
            if ($project) {
                $title .= ' for ' . Str::limit($project->project_name, 30);
            }

            $task = Task::create([
                'title' => $title,
                'description' => $faker->sentence(10),
                'status' => $status,
                'priority' => $faker->randomElement(TaskPriority::cases()),
                'due_at' => $status === TaskStatus::Completed ? clone $faker->dateTimeBetween('-2 months', 'now') : clone $faker->dateTimeBetween('-1 week', '+1 month'),
                'client_id' => $client?->id,
                'project_id' => $project?->id,
                'created_by' => $creatorId,
                'completed_at' => $status === TaskStatus::Completed ? clone $faker->dateTimeBetween('-2 months', 'now') : null,
                'completed_by' => $status === TaskStatus::Completed ? $faker->randomElement($allStaffIds) : null,
                'created_at' => clone $faker->dateTimeBetween('-6 months', 'now'),
            ]);

            $assigneeCount = $faker->numberBetween(1, 2);
            $assignees = $faker->randomElements($allStaffIds, $assigneeCount);
            $task->assignees()->sync($assignees);
        }

        $this->command->info('');
        $this->command->info('✅ Dummy data seeded successfully!');
        $this->command->info('');
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Team Members', User::count()],
                ['Clients', Client::count()],
                ['Projects', Project::count()],
                ['Tasks', Task::count()],
            ]
        );
        $this->command->info('');
        $this->command->info('📌 All users password: taxora@4321');
    }

    private function assignRoleSafely(User $user, string $roleName): void
    {
        if (Role::where('name', $roleName)->exists() && ! $user->hasRole($roleName)) {
            $user->assignRole($roleName);
        }
    }
}
