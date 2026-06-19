<?php

namespace App\Models;

use App\Enums\ChecklistStatus;
use App\Enums\DocumentSort;
use App\Enums\ListFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @property string $id
 * @property string $client_id
 * @property string $name
 * @property string $path
 * @property string|null $mime_type
 * @property int|null $size
 * @property string|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Client $client
 * @property-read User|null $createdBy
 * @property-read Collection<int, ProjectChecklistDocument> $projectChecklistDocuments
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Document search(?string $search)
 * @method static \Illuminate\Database\Eloquent\Builder|Document filterStatus(?string $status)
 * @method static \Illuminate\Database\Eloquent\Builder|Document sorted(\App\Enums\DocumentSort $sort)
 */
class Document extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'client_id',
        'name',
        'path',
        'mime_type',
        'size',
        'created_by',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projectChecklistDocuments(): HasMany
    {
        return $this->hasMany(ProjectChecklistDocument::class);
    }

    public function isLockedByApprovedChecklist(?string $ignoreProjectChecklistDocumentId = null): bool
    {
        $query = $this->projectChecklistDocuments()
            ->whereHas('projectChecklist', function (Builder $query) {
                $query->whereIn('status', [ChecklistStatus::Submitted, ChecklistStatus::Approved]);
            });

        if ($ignoreProjectChecklistDocumentId) {
            $query->where('id', '!=', $ignoreProjectChecklistDocumentId);
        }

        return $query->exists();
    }

    public function extension(): string
    {
        return strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function isSpreadsheet(): bool
    {
        return in_array($this->extension(), ['xls', 'xlsx', 'csv']);
    }

    public function isWord(): bool
    {
        return in_array($this->extension(), ['doc', 'docx']);
    }

    public function previewUrl(): string
    {
        return route('documents.show', [$this, $this->name]);
    }

    public function filename(): string
    {
        return basename($this->path);
    }

    public function exists(): bool
    {
        return Storage::exists($this->path);
    }

    public function humanSize(): string
    {
        return Number::fileSize($this->size ?? 0);
    }

    public function deleteFile(): bool
    {
        if ($this->exists()) {
            return Storage::delete($this->path);
        }

        return false;
    }

    public function download(): ?StreamedResponse
    {
        if (! $this->exists()) {
            return null;
        }

        return Storage::download($this->path, $this->name);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = Str::of((string) $search)->squish();

        if ($search->isEmpty()) {
            return $query;
        }

        return $query->where('name', 'like', "%{$search}%");
    }

    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        return match ($status) {
            ListFilter::Deleted->value => $query->onlyTrashed(),
            ListFilter::All->value => $query->withTrashed(),
            default => $query,
        };
    }

    public function scopeSorted(Builder $query, DocumentSort $sort): Builder
    {
        return match ($sort) {
            DocumentSort::Oldest => $query->orderBy('created_at')->orderBy('id'),
            DocumentSort::NameAsc => $query->orderBy('name')->orderBy('id'),
            DocumentSort::NameDesc => $query->orderByDesc('name')->orderBy('id'),
            DocumentSort::SizeLargest => $query->orderByDesc('size')->orderBy('id'),
            DocumentSort::SizeSmallest => $query->orderBy('size')->orderBy('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }
}
