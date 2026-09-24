<?php
/**
 * DTO para creación de ticket - Validación declarativa
 */

declare(strict_types=1);

namespace App\DTO\Ticket;

use App\Exceptions\ValidationException;
use Rakit\Validation\Validator;

readonly class CreateTicketDTO
{
    public function __construct(
        public int $equipoId,
        public string $descripcion,
        public string $prioridad,
        public ?int $categoriaId,
        public int $userId,
        public ?int $departamentoId = null
    ) {}

    public static function fromRequest(array $post, int $userId, ?int $departamentoId = null): self
    {
        $validator = new Validator;
        $validation = $validator->make($post, [
            'equipo_id'    => 'required|integer|min:1',
            'descripcion'  => 'required|string|min:10|max:5000',
            'prioridad'    => 'required|in:baja,media,alta,critica',
            'categoria_id' => 'nullable|integer|min:1',
        ]);

        $validation->setAliases([
            'equipo_id'    => 'Equipo',
            'descripcion'  => 'Descripción',
            'prioridad'    => 'Prioridad',
            'categoria_id' => 'Categoría',
        ]);

        if ($validation->fails()) {
            throw new ValidationException($validation->errors()->firstOfAll());
        }

        return new self(
            equipoId: (int)$post['equipo_id'],
            descripcion: trim($post['descripcion']),
            prioridad: $post['prioridad'],
            categoriaId: $post['categoria_id'] ? (int)$post['categoria_id'] : null,
            userId: $userId,
            departamentoId: $departamentoId
        );
    }
}