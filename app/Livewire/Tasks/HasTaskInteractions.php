<?php

namespace App\Livewire\Tasks;

use App\Enums\TaskStatus;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

trait HasTaskInteractions
{
    public string $newComment = '';

    public function updateStatus(string $status): void
    {
        $this->authorize('updateStatus', $this->task);

        $newStatus = TaskStatus::tryFrom($status);
        abort_if(! $newStatus, 400);

        $oldStatus = $this->task->status;

        if ($newStatus === $oldStatus) {
            return;
        }

        $updates = ['status' => $newStatus];

        if ($newStatus === TaskStatus::Completed && $oldStatus !== TaskStatus::Completed) {
            $updates['completed_at'] = now();
            $updates['completed_by'] = auth()->id();
        } elseif ($newStatus !== TaskStatus::Completed && $oldStatus === TaskStatus::Completed) {
            $updates['completed_at'] = null;
            $updates['completed_by'] = null;
        }

        DB::transaction(function () use ($updates, $oldStatus, $newStatus) {
            $this->task->update($updates);

            $this->task->activities()->create([
                'user_id' => auth()->id(),
                'type' => 'status_changed',
                'description' => 'Changed status from ' . $oldStatus->label() . ' to ' . $newStatus->label(),
                'metadata' => [
                    'old_status' => $oldStatus->value,
                    'new_status' => $newStatus->value,
                ],
            ]);
        });

        $this->task->load([
            'comments.user',
            'activities' => fn ($q) => $q->with('user')->latest(),
        ]);
        Flux::toast('Task status updated.', variant: 'success');
    }

    public function addComment(): void
    {
        $this->authorize('addComment', $this->task);

        $validated = $this->validate([
            'newComment' => 'required|string|min:2|max:5000',
        ], [
            'newComment.required' => 'Please enter a comment.',
            'newComment.min' => 'The comment must be at least 2 characters.',
            'newComment.max' => 'The comment cannot exceed 5000 characters.',
        ]);

        DB::transaction(function () use ($validated) {
            $this->task->comments()->create([
                'user_id' => auth()->id(),
                'comment' => $validated['newComment'],
            ]);

            $this->task->activities()->create([
                'user_id' => auth()->id(),
                'type' => 'comment_added',
                'description' => 'Added a comment',
            ]);
        });

        $this->reset('newComment');
        $this->task->load([
            'comments.user',
            'activities' => fn ($q) => $q->with('user')->latest(),
        ]);
        Flux::toast('Comment added.', variant: 'success');
    }

    #[On('delete-comment')]
    public function deleteComment(string $id): void
    {
        $comment = $this->task->comments()->findOrFail($id);

        $this->authorize('delete', $comment);

        DB::transaction(function () use ($comment) {
            $comment->delete();

            $this->task->activities()->create([
                'user_id' => auth()->id(),
                'type' => 'comment_deleted',
                'description' => 'Deleted a comment',
            ]);
        });

        $this->task->load([
            'comments.user',
            'activities' => fn ($q) => $q->with('user')->latest(),
        ]);
        Flux::toast('Comment deleted.', variant: 'success');
    }
}
