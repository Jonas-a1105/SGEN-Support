<?php

declare(strict_types=1);

namespace Modules\Support\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('calificacion') && !$this->has('rating')) {
            $val = (int) $this->input('calificacion');
            $rating = match ($val) {
                5 => 'excelente',
                4 => 'bueno',
                3 => 'regular',
                default => 'malo',
            };
            $this->merge(['rating' => $rating]);
        }

        if ($this->has('comment') && !$this->has('comentario')) {
            $this->merge(['comentario' => $this->input('comment')]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'string', 'in:excelente,bueno,regular,malo'],
            'comentario' => ['nullable', 'string', 'max:500'],
        ];
    }
}
