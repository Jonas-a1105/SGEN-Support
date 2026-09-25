<?php

declare(strict_types=1);

namespace Modules\Maintenance\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PostponeMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('mantenimientos.manage');
    }

    public function rules(): array
    {
        return [
            'nueva_fecha' => 'required|date|after:today',
        ];
    }
}
