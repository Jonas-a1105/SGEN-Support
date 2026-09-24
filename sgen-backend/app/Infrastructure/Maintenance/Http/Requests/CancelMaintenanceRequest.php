<?php

declare(strict_types=1);

namespace App\Infrastructure\Maintenance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CancelMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motivo' => 'required|string|max:500',
        ];
    }
}
