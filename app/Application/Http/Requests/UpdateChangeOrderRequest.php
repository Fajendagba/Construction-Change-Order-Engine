<?php

declare(strict_types=1);

namespace App\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateChangeOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'title'         => ['sometimes', 'string', 'max:255'],
            'description'   => ['sometimes', 'string'],
            'reason'        => ['sometimes', 'string', 'max:255'],
            'cost_code'     => ['sometimes', 'string', 'max:50'],
            'labor_cost'    => ['sometimes', 'numeric', 'min:0'],
            'material_cost' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
