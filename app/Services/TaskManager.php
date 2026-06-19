<?php

namespace App\Services;

use App\Enums\TaskActivityType;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Support\UserContext;
use Illuminate\Support\Facades\DB;

class TaskManager
{
    public function __construct(
        private readonly UserContext $userContext,
    ) {}

    public function create(array $data, array $assignees = [], ?string $actorId = null): Task
    {
        $actorId ??= $this->userContext->getId();
        $data['created_by'] = $actorId;

        if ($data['status'] === TaskStatus::Completed->value) {
            $data['completed_at'] = now();
            $data['completed_by'] = $actorId;
        }

        $usersToNotify = [];

        $task = DB::transaction(function () use ($data, $assignees, $actorId, &$usersToNotify) {
            $task = Task::create($data);

            $task->assignees()->sync($assignees);

            $this->logActivity(
                task: $task,
                type: TaskActivityType::Created,
                description: 'Created task',
                actorId: $actorId,
            );

            if (! empty($assignees)) {
                $assignedUsers = User::whereIn('id', $assignees)->get();
                foreach ($assignedUsers as $assignee) {
                    $this->logActivity(
                        task: $task,
                        type: TaskActivityType::Assigned,
                        description: 'Assigned task to ' . $assignee->name,
                        actorId: $actorId,
                    );
                    $usersToNotify[] = $assignee;
                }
            }

            return $task;
        });

        $this->notifyAssignedUsers($usersToNotify, $task);

        return $task;
    }

    public function update(Task $task, array $data, array $assignees = [], ?string $actorId = null): Task
    {
        $actorId ??= $this->userContext->getId();
        $usersToNotify = [];

        DB::transaction(function () use ($task, $data, $assignees, $actorId, &$usersToNotify) {
            $oldAssignedIds = $task->assignees()->pluck('users.id')->toArray();
            $oldStatus = $task->status->value;

            if ($data['status'] === TaskStatus::Completed->value && $oldStatus !== TaskStatus::Completed->value) {
                $data['completed_at'] = now();
                $data['completed_by'] = $actorId;
            } elseif ($data['status'] !== TaskStatus::Completed->value && $oldStatus === TaskStatus::Completed->value) {
                $data['completed_at'] = null;
                $data['completed_by'] = null;
            }

            $task->update($data);

            $task->assignees()->sync($assignees);

            $this->logActivity(
                task: $task,
                type: TaskActivityType::Updated,
                description: 'Updated task details',
                actorId: $actorId,
            );

            if ($data['status'] !== $oldStatus) {
                $this->logActivity(
                    task: $task,
                    type: TaskActivityType::StatusChanged,
                    description: 'Changed status from ' . TaskStatus::from($oldStatus)->label() . ' to ' . TaskStatus::from($data['status'])->label(),
                    actorId: $actorId,
                    metadata: [
                        'old_status' => $oldStatus,
                        'new_status' => $data['status'],
                    ],
                );

                if ($data['status'] === TaskStatus::Completed->value) {
                    $this->logActivity(
                        task: $task,
                        type: TaskActivityType::Completed,
                        description: 'Completed task',
                        actorId: $actorId,
                    );
                }
            }

            $addedMembers = array_diff($assignees, $oldAssignedIds);
            $removedMembers = array_diff($oldAssignedIds, $assignees);

            if (! empty($addedMembers)) {
                $addedUsers = User::whereIn('id', $addedMembers)->get();
                foreach ($addedUsers as $addedUser) {
                    $this->logActivity(
                        task: $task,
                        type: TaskActivityType::Assigned,
                        description: 'Assigned task to ' . $addedUser->name,
                        actorId: $actorId,
                    );
                    $usersToNotify[] = $addedUser;
                }
            }

            if (! empty($removedMembers)) {
                $removedUsers = User::whereIn('id', $removedMembers)->get();
                foreach ($removedUsers as $removedUser) {
                    $this->logActivity(
                        task: $task,
                        type: TaskActivityType::Reassigned,
                        description: 'Removed assignment from ' . $removedUser->name,
                        actorId: $actorId,
                    );
                }
            }
        });

        $this->notifyAssignedUsers($usersToNotify, $task);

        return $task;
    }

    private function logActivity(Task $task, TaskActivityType $type, string $description, ?string $actorId = null, ?array $metadata = null): void
    {
        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => $actorId ?? $this->userContext->getId(),
            'type' => $type->value,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    private function notifyAssignedUsers(iterable $users, Task $task): void
    {
        foreach ($users as $user) {
            $user->notify(new TaskAssignedNotification($task));
        }
    }
}
