/**
 * ARCHIVO PRINCIPAL DE JAVASCRIPT (SGEN-SUPPORT)
 * Contiene la lógica global para alertas, confirmaciones y animaciones.
 */

// Usamos un solo "escucha" global en modo captura (el 'true' al final)
// para interceptar TODOS los clics antes que cualquier otro script.
document.addEventListener('click', function (e) {

    // Buscamos el elemento enlace (<a>) más cercano al clic
    // (por si el usuario hizo clic en el icono <i> dentro del enlace)
    const link = e.target.closest('a');

    // Si no se hizo clic en un enlace, no hacemos nada
    if (!link) return;

    // ----------------------------------------------------------------
    // 1. LÓGICA PARA BOTONES DE "ELIMINAR"
    // ----------------------------------------------------------------

    // Criterios para identificar un botón de eliminar:
    const href = link.getAttribute('href') || '';
    const onclickTexto = link.getAttribute('onclick') || '';

    const esBorrarPorUrl = href.includes('/eliminar/');       // ¿La URL dice "eliminar"?
    const esBorrarPorClase = link.classList.contains('btn-delete'); // ¿Tiene la clase btn-delete?
    const esBorrarPorOnclick = onclickTexto.includes('confirm'); // ¿Tiene el código viejo?

    if (esBorrarPorUrl || esBorrarPorClase || esBorrarPorOnclick) {

        // --- EXCEPCIÓN: Si el botón tiene 'data-no-global-delete', dejamos que su propio script lo maneje
        if (link.hasAttribute('data-no-global-delete')) {
            return;
        }

        // ¡IMPORTANTE! Detenemos el evento inmediatamente

        e.preventDefault();
        e.stopPropagation();

        // Intentamos obtener el nombre del registro para el mensaje
        let mensajeDetalle = '';

        if (link.getAttribute('data-name')) {
            // Opción A: Usamos el atributo data-name (lo ideal)
            mensajeDetalle = `<strong class="text-danger">${link.getAttribute('data-name')}</strong>`;
        }
        else if (esBorrarPorOnclick) {
            // Opción B: Extraemos el texto del onclick antiguo
            const match = onclickTexto.match(/confirm\(['"](.*?)['"]\)/);
            if (match && match[1]) {
                let textoLimpio = match[1].replace(/^[¿?]+|[?]+$/g, '');
                mensajeDetalle = `<span class="text-muted">${textoLimpio}</span>`;
            }
        }

        // Mensaje por defecto si no encontramos nada
        if (!mensajeDetalle) {
            mensajeDetalle = '<span class="text-muted">Este registro se borrará permanentemente.</span>';
        }

        // Mostramos la alerta bonita
        Swal.fire({
            html: `
                <div class="mb-3">
                    <i class="bi bi-trash3-fill trash-animate" style="font-size: 5rem;"></i>
                </div>
                <h3 class="fw-bold text-dark">¿Estás seguro?</h3>
                <div class="fs-5 mt-2">${mensajeDetalle}</div>
                <p class="text-muted small mt-2">¡Esta acción no se puede deshacer!</p>
            `,
            showCancelButton: true,
            confirmButtonColor: '#dc3545', // Rojo
            cancelButtonColor: '#6c757d',  // Gris
            confirmButtonText: 'Sí, ¡elimínalo!',
            cancelButtonText: 'Cancelar',
            focusCancel: true,
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-danger btn-lg rounded-pill px-4 mx-2',
                cancelButton: 'btn btn-secondary btn-lg rounded-pill px-4 mx-2',
                popup: 'card' // Hereda estilo Glass
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Si confirma, redirigimos manualmente
                window.location.href = href;
            }
        });

        return; // Terminamos aquí para no procesar más lógica
    }

    // ----------------------------------------------------------------
    // 2. LÓGICA PARA EL BOTÓN DE "CERRAR SESIÓN"
    // ----------------------------------------------------------------

    if (link.classList.contains('btn-logout')) {

        e.preventDefault();
        e.stopPropagation();

        Swal.fire({
            html: `
                <div class="mb-3">
                    <span class="sad-animate" style="font-size: 5rem;">🥺</span>
                </div>
                <h3 class="fw-bold text-dark">¿Ya te vas?</h3>
                <p class="text-muted fs-5">
                    ¿Seguro que quieres cerrar sesión?
                </p>
            `,
            showCancelButton: true,
            confirmButtonColor: '#2E86DE', // Azul primario
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, salir',
            cancelButtonText: 'No, me quedo',
            reverseButtons: true, // Botón de cancelar a la izquierda (mejor UX)
            focusCancel: true,
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-primary btn-lg rounded-pill px-4 mx-2',
                cancelButton: 'btn btn-secondary btn-lg rounded-pill px-4 mx-2',
                popup: 'card'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Mensaje de despedida (Toast)
                if (window.Toast) {
                    Toast.success('¡Hasta pronto! 👋');
                }

                // Esperamos un poco para que se vea el mensaje antes de redirigir
                setTimeout(() => {
                    window.location.href = href;
                }, 1000);
            }
        });
    }

}, true); // <--- 'true' activa la fase de captura (CRUCIAL para ganar a los onclick viejos)