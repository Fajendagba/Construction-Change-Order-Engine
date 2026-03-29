<?php

declare(strict_types=1);

namespace App\Application\Http\Controllers\Api;

use App\Application\Http\Resources\AuditLogResource;
use App\Domain\AuditLog\Models\AuditLog;
use App\Domain\ChangeOrder\Models\ChangeOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class AuditLogController extends Controller
{
    public function index(string $projectId, string $changeOrderId): JsonResponse
    {
        // Verify the change order belongs to this project before returning its logs
        ChangeOrder::where('project_id', $projectId)->findOrFail($changeOrderId);

        $logs = AuditLog::with('user')
            ->where('change_order_id', $changeOrderId)
            ->orderBy('created_at')
            ->get();

        return AuditLogResource::collection($logs)->response();
    }
}
