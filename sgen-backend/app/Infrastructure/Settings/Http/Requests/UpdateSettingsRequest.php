<?php

declare(strict_types=1);

namespace App\Infrastructure\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('configuracion.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'language' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'date_format' => ['nullable', 'string', 'max:20'],
            'theme' => ['nullable', 'string', 'in:dark,light'],
            'stroke_width' => ['nullable', 'string', 'in:1px,2px'],
            'accent_color' => ['nullable', 'string', 'regex:/^#[a-fA-F0-9]{3,8}$/'],
            'push_enabled' => ['nullable', 'boolean'],
            'email_tickets_enabled' => ['nullable', 'boolean'],
            'email_weekly_digest' => ['nullable', 'boolean'],
        ];
    }
}
