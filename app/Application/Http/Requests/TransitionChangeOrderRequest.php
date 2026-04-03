<?php

declare(strict_types=1);

namespace App\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class TransitionChangeOrderRequest extends FormRequest
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
            'target_state'     => ['required', 'string', 'in:draft,submitted,under_review,approved,rejected'],
            'rejection_reason' => ['required_if:target_state,rejected', 'nullable', 'string'],
        ];
    }
}
