<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
let signaturePad;

function abrirModalFirma() {
    const modal = new bootstrap.Modal(document.getElementById('modalFirma'));
    modal.show();
    
    // Inicializar SignaturePad despuÃ©s de que el modal se muestre
    const canvas = document.getElementById('signature-pad');
    if (!signaturePad) {
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)'
        });
    } else {
        signaturePad.clear();
    }
    
    // Ajustar tamaÃ±o del canvas
    function resizeCanvas() {
        const ratio =  Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        signaturePad.clear();
    }
    // window.addEventListener("resize", resizeCanvas);
    // resizeCanvas();
}

function limpiarFirma() {
    if (signaturePad) {
        signaturePad.clear();
    }
}

function guardarFirma() {
    if (signaturePad.isEmpty()) {
        Swal.fire('AtenciÃ³n', 'Por favor proporcione una firma.', 'warning');
        return;
    }
    
    const dataUrl = signaturePad.toDataURL();
    document.getElementById('firma_base64').value = dataUrl;
    document.getElementById('formFirma').submit();
}
</script>
    </div>
</div>

<!-- Modal para Editar Fecha de Cierre -->
<div class="modal fade" id="modalEditarFechaCierre" tabindex="-1" aria-labelledby="modalEditarFechaCierreLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditarFechaCierreLabel">
                    <i class="bi bi-calendar-check me-2"></i>Editar Fecha de Cierre
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>soportes/actualizar_fecha_cierre" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="soporte_id" value="<?= $soporte->id ?>">
                    
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        Esta acciÃ³n actualizarÃ¡ la fecha de cierre del ticket. Use con precauciÃ³n.
                    </div>
                    
                    <div class="mb-3">
                        <label for="nueva_fecha_cierre" class="form-label">
                            <strong>Nueva Fecha de Cierre</strong>
                        </label>
                        <input type="datetime-local" 
                               class="form-control" 
                               id="nueva_fecha_cierre" 
                               name="nueva_fecha_cierre" 
                               value="<?= $soporte->fecha_cierre ? date('Y-m-d\TH:i', strtotime($soporte->fecha_cierre)) : '' ?>" 
                               required>
                        <div class="form-text">
                            Seleccione la nueva fecha y hora de cierre del ticket.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function subirArchivo(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const formData = new FormData();
        formData.append('archivo', file);
        formData.append('ticket_id', '<?= $soporte->id ?>');

        const progressBar = document.getElementById('uploadProgressBar');
        const progressContainer = document.getElementById('uploadProgressContainer');
        
        progressContainer.classList.remove('d-none');
        progressBar.style.width = '0%';

        fetch('<?= BASE_URL ?>soportes/subir_archivo', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Archivo subido',
                    text: 'El archivo se ha adjuntado correctamente.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.error || 'Error al subir archivo', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error de conexiÃ³n al subir archivo', 'error');
        })
        .finally(() => {
            progressContainer.classList.add('d-none');
            input.value = ''; // Limpiar input
        });
    }
}

function confirmarAccion(url, titulo, texto, icono, botonTexto) {
    Swal.fire({
        title: titulo,
        text: texto,
        icon: icono,
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: botonTexto,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

function abrirModalFechaCierre() {
    const modal = new bootstrap.Modal(document.getElementById('modalEditarFechaCierre'));
    modal.show();
}
</script>
