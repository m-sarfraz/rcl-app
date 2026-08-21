<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Uniform JSON envelope for every /api/* response.
 *
 *   { "success": bool, "message": string|null, "data": mixed, "meta": object|null, "errors": object|null }
 *
 * The mobile client unwraps `data` automatically, so controllers should never
 * hand back a bare array — always route through one of these helpers.
 */
final class ApiResponse
{
    public static function success(mixed $data = null, ?string $message = null, int $status = 200, array $meta = []): JsonResponse
    {
        return response()->json(array_filter([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => $meta ?: null,
        ], static fn ($v) => $v !== null), $status);
    }

    public static function created(mixed $data = null, ?string $message = 'Created.'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    public static function error(string $message, int $status = 400, array $errors = [], ?string $code = null): JsonResponse
    {
        return response()->json(array_filter([
            'success' => false,
            'message' => $message,
            'code'    => $code,
            'errors'  => $errors ?: null,
        ], static fn ($v) => $v !== null), $status);
    }

    /**
     * Flatten a paginator (or a paginated resource collection) into
     * `data: [...]` plus a `meta` block, so clients never have to know
     * whether they are reading Laravel's `data.data` nesting.
     */
    public static function paginated(LengthAwarePaginator|ResourceCollection $paginator, ?string $message = null): JsonResponse
    {
        if ($paginator instanceof ResourceCollection) {
            $payload   = $paginator->response()->getData(true);
            $items     = $payload['data'] ?? [];
            $paginator = $paginator->resource;
        } else {
            $items = $paginator->items();
        }

        return self::success($items, $message, 200, [
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'has_more'     => $paginator->hasMorePages(),
        ]);
    }
}
