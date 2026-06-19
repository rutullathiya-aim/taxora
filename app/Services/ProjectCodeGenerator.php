<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProjectCodeGenerator
{
    public const PREFIX = 'PRJ-';

    private const SEQUENCE_ID = 1;

    public static function next(): string
    {
        return DB::transaction(function () {
            $sequence = DB::table('project_sequences')->where('id', self::SEQUENCE_ID)->lockForUpdate()->first();

            if (! $sequence) {
                throw new RuntimeException('Project sequence missing.');
            }

            $nextNumber = $sequence->last_number + 1;
            DB::table('project_sequences')->where('id', self::SEQUENCE_ID)->update(['last_number' => $nextNumber]);

            return self::PREFIX . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
        });
    }
}
