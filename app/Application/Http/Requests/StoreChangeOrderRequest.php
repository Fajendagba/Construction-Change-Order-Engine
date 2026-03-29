<?php

declare(strict_types=1);

namespace App\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreChangeOrderRequest extends FormRequest
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
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string'],
            'reason'        => ['required', 'string', 'max:255'],
            'cost_code'     => ['required', 'string', 'max:50'],
            'labor_cost'    => ['required', 'numeric', 'min:0'],
            'material_cost' => ['required', 'numeric', 'min:0'],
        ];
    }
}
