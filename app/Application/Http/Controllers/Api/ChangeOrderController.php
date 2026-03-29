<?php

declare(strict_types=1);

namespace App\Application\Http\Controllers\Api;

use App\Application\Http\Requests\StoreChangeOrderRequest;
use App\Application\Http\Requests\TransitionChangeOrderRequest;
use App\Application\Http\Resources\ChangeOrderResource;
use App\Domain\ChangeOrder\Enums\ChangeOrderState;
use App\Domain\ChangeOrder\Exceptions\InvalidStateTransitionException;
use App\Domain\ChangeOrder\Models\ChangeOrder;
use App\Domain\ChangeOrder\Services\ChangeOrderService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class ChangeOrderController extends Controller
{
    public function __construct(
        private readonly ChangeOrderService $changeOrderService,
    ) {
    }

    public function index(string $projectId): JsonResponse
    {
        $changeOrders = ChangeOrder::with(['submittedBy', 'reviewedBy'])
            ->where('project_id', $projectId)
            ->orderBy('number')
            ->get();

        return ChangeOrderResource::collection($changeOrders)->response();
    }

    public function store(StoreChangeOrderRequest $request, string $projectId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->authorize('create', ChangeOrder::class);

        /** @var \stdClass $result */
        $result = $this->changeOrderService->create($projectId, $user->id, $request->validated());

        $changeOrder = ChangeOrder::with(['submittedBy', 'reviewedBy'])
            ->findOrFail($result->id);

        return (new ChangeOrderResource($changeOrder))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $projectId, string $changeOrderId): JsonResponse
    {
        $changeOrder = ChangeOrder::with(['submittedBy', 'reviewedBy', 'auditLogs.user'])
            ->where('project_id', $projectId)
            ->findOrFail($changeOrderId);

        return (new ChangeOrderResource($changeOrder))->response();
    }

    public function transition(
        TransitionChangeOrderRequest $request,
        string $projectId,
        string $changeOrderId,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        /** @var string $targetStateValue */
        $targetStateValue = $request->input('target_state');

        $changeOrder = ChangeOrder::where('project_id', $projectId)->findOrFail($changeOrderId);

        $this->authorize('transition', [$changeOrder, $targetStateValue]);

        try {
            $result = $this->changeOrderService->transitionState(
                changeOrderId:   $changeOrderId,
                targetState:     ChangeOrderState::from($targetStateValue),
                userId:          $user->id,
                rejectionReason: $request->input('rejection_reason'),
            );
        } catch (InvalidStateTransitionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($result['event'] !== null) {
            event($result['event']);
        }

        $changeOrder = ChangeOrder::with(['submittedBy', 'reviewedBy', 'auditLogs.user'])
            ->findOrFail($changeOrderId);

        return (new ChangeOrderResource($changeOrder))->response();
    }
}
