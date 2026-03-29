<?php

declare(strict_types=1);

namespace App\Application\Http\Controllers\Api;

use App\Application\Http\Resources\ProjectResource;
use App\Domain\ProjectBudget\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        $projects = Project::withCount('changeOrders')
            ->with('budgetLineItems')
            ->get();

        return ProjectResource::collection($projects)->response();
    }

    public function show(string $id): JsonResponse
    {
        $project = Project::with(['budgetLineItems', 'changeOrders.submittedBy'])
            ->withCount('changeOrders')
            ->findOrFail($id);

        return (new ProjectResource($project))->response();
    }
}
