<?php

namespace Database\Seeders;

use App\Enums\ClientStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class PerformanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $output = new ConsoleOutput;

        $this->seedClients($output);
        $this->seedProjects($output);
        $this->seedTasks($output);
    }

    private function seedClients(ConsoleOutput $output): void
    {
        $total = 100000;
        $chunkSize = 5000;
        $output->writeln("\n<info>Seeding 100,000 Clients...</info>");
        $progressBar = new ProgressBar($output, $total);
        $progressBar->start();

        $now = now()->toDateTimeString();

        for ($i = 0; $i < $total; $i += $chunkSize) {
            $clients = [];
            for ($j = 0; $j < $chunkSize; $j++) {
                $clients[] = [
                    'id' => Str::ulid()->toString(),
                    'client_name' => 'Performance Client ' . ($i + $j),
                    'company_name' => ($i + $j) % 3 === 0 ? 'Company ' . ($i + $j) : null,
                    'email' => 'client' . ($i + $j) . '@performance.test',
                    'phone' => '555' . str_pad((string) ($i + $j), 7, '0', STR_PAD_LEFT),
                    'address' => '123 Performance Street, Test City',
                    'status' => ClientStatus::Active->value,
                    'created_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('clients')->insert($clients);
            $progressBar->advance($chunkSize);
        }
        $progressBar->finish();
        $output->writeln('');
    }

    private function seedProjects(ConsoleOutput $output): void
    {
        $total = 250000;
        $chunkSize = 5000;
        $output->writeln("\n<info>Seeding 250,000 Projects...</info>");
        $progressBar = new ProgressBar($output, $total);
        $progressBar->start();

        // Get some client IDs and service IDs to associate with projects
        $clientIds = DB::table('clients')->limit(50000)->pluck('id')->toArray();
        $serviceIds = DB::table('services')->pluck('id')->toArray();

        if (empty($clientIds) || empty($serviceIds)) {
            $output->writeln('<error>No clients or services found to associate with projects.</error>');

            return;
        }

        $now = now()->toDateTimeString();

        for ($i = 0; $i < $total; $i += $chunkSize) {
            $projects = [];
            for ($j = 0; $j < $chunkSize; $j++) {
                $projects[] = [
                    'id' => Str::ulid()->toString(),
                    'client_id' => $clientIds[array_rand($clientIds)],
                    'service_id' => $serviceIds[array_rand($serviceIds)],
                    'project_name' => 'Performance Project ' . ($i + $j),
                    'status' => 'active',
                    'due_date' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('projects')->insert($projects);
            $progressBar->advance($chunkSize);
        }
        $progressBar->finish();
        $output->writeln('');
    }

    private function seedTasks(ConsoleOutput $output): void
    {
        $total = 1000000;
        $chunkSize = 5000;
        $output->writeln("\n<info>Seeding 1,000,000 Tasks...</info>");
        $progressBar = new ProgressBar($output, $total);
        $progressBar->start();

        // Get some client IDs, project IDs, and a user ID
        $clientIds = DB::table('clients')->limit(10000)->pluck('id')->toArray();
        $projectIds = DB::table('projects')->limit(10000)->pluck('id')->toArray();
        $userId = DB::table('users')->first()->id ?? null;

        if (empty($clientIds) || empty($projectIds) || ! $userId) {
            $output->writeln('<error>No clients, projects, or users found to associate with tasks.</error>');

            return;
        }

        $now = now()->toDateTimeString();

        for ($i = 0; $i < $total; $i += $chunkSize) {
            $tasks = [];
            for ($j = 0; $j < $chunkSize; $j++) {
                $tasks[] = [
                    'id' => Str::ulid()->toString(),
                    'task_number' => 'TSK-' . uniqid() . '-' . ($i + $j),
                    'client_id' => $clientIds[array_rand($clientIds)],
                    'project_id' => $projectIds[array_rand($projectIds)],
                    'title' => 'Performance Task ' . ($i + $j),
                    'status' => 'todo',
                    'priority' => 'medium',
                    'created_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('tasks')->insert($tasks);
            $progressBar->advance($chunkSize);
        }
        $progressBar->finish();
        $output->writeln('');
    }
}
