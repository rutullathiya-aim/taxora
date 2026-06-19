<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\CrudAction;
use App\Enums\ResourceType;
use Flux\Flux;

final class Toast
{
    private const SUCCESS = 'success';

    private const WARNING = 'warning';

    private const INFO = 'info';

    private const DANGER = 'danger';

    private function __construct() {}

    public static function success(CrudAction $action, ResourceType $resource): void
    {
        self::show($action->message($resource), self::SUCCESS);
    }

    public static function error(string $message): void
    {
        self::show($message, self::DANGER);
    }

    public static function warning(string $message): void
    {
        self::show($message, self::WARNING);
    }

    public static function info(string $message): void
    {
        self::show($message, self::INFO);
    }

    private static function show(string $message, string $variant): void
    {
        Flux::toast($message, variant: $variant);
    }
}
