<?php

declare(strict_types=1);

namespace App\Infrastructure\Maintenance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CompleteMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'observaciones' => 'nullable|string|max:500',
            'costo' => 'nullable|numeric|min:0',
        ];
    }
}
