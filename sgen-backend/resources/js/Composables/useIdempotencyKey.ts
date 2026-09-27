import { ref } from 'vue';

/**
 * Idempotency-Key (regla #21): el cliente genera una clave por intención de
 * acción destructiva; el servidor reproduce la respuesta ante reintentos
 * (doble clic, reenvío de red, F5), sin volver a ejecutar el efecto.
 *
 * Regla de uso: una clave por "intento", nueva al reabrir/reiniciar el formulario.
 */
export function useIdempotencyKey() {
    const key = ref<string>('');

    const regenerar = (): void => {
        key.value = crypto.randomUUID();
    };

    // Clave lista al instanciar (la intención nace con el formulario).
    regenerar();

    const headers = (): Record<string, string> => ({
        'Idempotency-Key': key.value,
    });

    return { idempotencyKey: key, headers, regenerar };
}
