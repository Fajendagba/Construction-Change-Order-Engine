<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\AuditLog\Models\AuditLog;
use App\Domain\ChangeOrder\Enums\ChangeOrderState;
use App\Domain\ChangeOrder\Models\ChangeOrder;
use App\Domain\ProjectBudget\Models\BudgetLineItem;
use App\Domain\ProjectBudget\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::factory()->owner()->create([
            'name'     => 'Nick Carter',
            'email'    => 'owner@ingenious.build',
            'password' => Hash::make('password'),
        ]);

        $contractor = User::factory()->contractor()->create([
            'name'     => 'Michał Sączek',
            'email'    => 'contractor@ingenious.build',
            'password' => Hash::make('password'),
        ]);

        User::factory()->architect()->create([
            'name'     => 'Eli Rattner',
            'email'    => 'architect@ingenious.build',
            'password' => Hash::make('password'),
        ]);

        $project = Project::create([
            'name'                   => '200 Park Avenue Tower',
            'description'            => null,
            'original_budget'        => 25000000.00,
            'approved_changes_total' => 0.00,
            'current_budget'         => 25000000.00,
        ]);

        $lineItems = [
            ['cost_code' => '03-Concrete',   'description' => 'Concrete Structure',          'original_amount' => 3500000.00],
            ['cost_code' => '05-Metals',     'description' => 'Structural Steel and Metals',  'original_amount' => 4200000.00],
            ['cost_code' => '09-Finishes',   'description' => 'Interior Finishes',            'original_amount' => 2800000.00],
            ['cost_code' => '15-Mechanical', 'description' => 'Mechanical and Plumbing',      'original_amount' => 5100000.00],
            ['cost_code' => '16-Electrical', 'description' => 'Electrical Systems',           'original_amount' => 3900000.00],
            ['cost_code' => '01-General',    'description' => 'General Requirements',         'original_amount' => 5500000.00],
        ];

        foreach ($lineItems as $item) {
            BudgetLineItem::create([
                'project_id'       => $project->id,
                'cost_code'        => $item['cost_code'],
                'description'      => $item['description'],
                'original_amount'  => $item['original_amount'],
                'approved_changes' => 0.00,
                'current_amount'   => $item['original_amount'],
            ]);
        }

        $co001 = ChangeOrder::create([
            'project_id'       => $project->id,
            'submitted_by'     => $contractor->id,
            'reviewed_by'      => $owner->id,
            'number'           => 1,
            'title'            => 'Kitchen expansion - Level 12',
            'description'      => 'Expansion of kitchen facilities on Level 12 to accommodate additional staff.',
            'reason'           => 'Client request to expand kitchen area for increased occupancy.',
            'cost_code'        => '03-Concrete',
            'labor_cost'       => 32000.00,
            'material_cost'    => 13000.00,
            'total_cost'       => 45000.00,
            'state'            => ChangeOrderState::APPROVED,
            'state_changed_at' => now()->subDays(5),
            'reviewed_at'      => now()->subDays(5),
        ]);

        ChangeOrder::create([
            'project_id'       => $project->id,
            'submitted_by'     => $contractor->id,
            'number'           => 2,
            'title'            => 'Additional fire suppression - Basement',
            'description'      => 'Installation of additional fire suppression systems in the basement parking area.',
            'reason'           => 'Fire safety compliance requirement identified during inspection.',
            'cost_code'        => '15-Mechanical',
            'labor_cost'       => 18000.00,
            'material_cost'    => 10500.00,
            'total_cost'       => 28500.00,
            'state'            => ChangeOrderState::UNDER_REVIEW,
            'state_changed_at' => now()->subDays(2),
        ]);

        ChangeOrder::create([
            'project_id'    => $project->id,
            'submitted_by'  => $contractor->id,
            'number'        => 3,
            'title'         => 'Upgraded lobby finishes',
            'description'   => 'Premium finish materials for the main lobby area including marble flooring and custom millwork.',
            'reason'        => 'Client upgraded specification to premium finish package.',
            'cost_code'     => '09-Finishes',
            'labor_cost'    => 45000.00,
            'material_cost' => 22000.00,
            'total_cost'    => 67000.00,
            'state'         => ChangeOrderState::DRAFT,
        ]);

        BudgetLineItem::where('project_id', $project->id)
            ->where('cost_code', '03-Concrete')
            ->update([
                'approved_changes' => 45000.00,
                'current_amount'   => 3500000.00 + 45000.00,
            ]);

        $project->update([
            'approved_changes_total' => 45000.00,
            'current_budget'         => 25000000.00 + 45000.00,
        ]);

        $auditEntries = [
            [
                'action'     => 'created',
                'from_state' => null,
                'to_state'   => 'draft',
                'user_id'    => $contractor->id,
                'metadata'   => null,
            ],
            [
                'action'     => 'submitted',
                'from_state' => 'draft',
                'to_state'   => 'submitted',
                'user_id'    => $contractor->id,
                'metadata'   => null,
            ],
            [
                'action'     => 'under_review',
                'from_state' => 'submitted',
                'to_state'   => 'under_review',
                'user_id'    => $owner->id,
                'metadata'   => null,
            ],
            [
                'action'     => 'approved',
                'from_state' => 'under_review',
                'to_state'   => 'approved',
                'user_id'    => $owner->id,
                'metadata'   => ['total_cost' => 45000.00, 'cost_code' => '03-Concrete'],
            ],
        ];

        foreach ($auditEntries as $entry) {
            AuditLog::create([
                'change_order_id' => $co001->id,
                'user_id'         => $entry['user_id'],
                'action'          => $entry['action'],
                'from_state'      => $entry['from_state'],
                'to_state'        => $entry['to_state'],
                'metadata'        => $entry['metadata'],
            ]);
        }
    }
}
