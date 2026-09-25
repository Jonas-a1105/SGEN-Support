<?php

declare(strict_types=1);

namespace Modules\Support\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AddCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'comentario' => ['required', 'string', 'min:1'],
            'es_interno' => ['nullable', 'boolean'],
        ];
    }
}
