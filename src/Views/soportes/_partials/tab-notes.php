<?php
/**
 * Tab: Bitácora Técnica (Observaciones) - V2 Ultra Moderna
 * Editor con timeline de historial
 */

use App\Helpers\ViewHelper;

$canEdit = isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'tecnico']);
$observaciones = $soporte->observaciones ?? '';

// Técnico actual
$techName = $soporte->tecnico_asignado ?? ($_SESSION['usuario_nombre'] ?? 'Usuario');
$techParts = explode(' ', $techName);
$techInitials = strtoupper(substr($techParts[0] ?? '', 0, 1) . substr($techParts[1] ?? '', 0, 1));
if (strlen($techInitials) < 2) $techInitials = strtoupper(substr($techName, 0, 2));
?>

<div class="row g-4">
    <!-- Columna Izquierda: Editor + Historial -->
    <div class="col-lg-8">
        
        <?php if ($canEdit && $soporte->estado !== 'cerrado'): ?>
        <!-- Editor de Nueva Observación -->
        <form action="<?= BASE_URL ?>soportes/guardar_observaciones" method="POST" class="td-obs-editor">
            <input type="hidden" name="soporte_id" value="<?= $soporte->id ?>">
            
            <div class="td-obs-editor-header">
                <div class="td-obs-editor-title">
                    <i class="bi bi-clipboard-check"></i>
                    Nueva Entrada Técnica
                </div>
                
                <button type="button" class="td-obs-visibility-btn private" id="obsVisibilityBtn">
                    <i class="bi bi-lock-fill" id="obsVisIcon"></i>
                    <span id="obsVisText">Privado</span>
                </button>
            </div>
            
            <!-- Barra de Herramientas -->
            <div class="td-obs-toolbar">
                <button type="button" class="td-obs-toolbar-btn" title="Negrita" onclick="insertFormat('**', '**')">
                    <i class="bi bi-type-bold"></i>
                </button>
                <button type="button" class="td-obs-toolbar-btn" title="Cursiva" onclick="insertFormat('_', '_')">
                    <i class="bi bi-type-italic"></i>
                </button>
                <span class="td-obs-toolbar-separator"></span>
                <button type="button" class="td-obs-toolbar-btn" title="Lista" onclick="insertFormat('• ', '')">
                    <i class="bi bi-list-ul"></i>
                </button>
                <button type="button" class="td-obs-toolbar-btn" title="Código" onclick="insertFormat('`', '`')">
                    <i class="bi bi-code"></i>
                </button>
            </div>
            
            <textarea 
                name="observaciones" 
                id="obsTextarea"
                class="td-obs-textarea" 
                placeholder="Describa el procedimiento realizado, hallazgos técnicos o recomendaciones..."
            ><?= htmlspecialchars($observaciones) ?></textarea>
            
            <!-- Macros Rápidos -->
            <div class="td-obs-templates">
                <button type="button" class="td-obs-template-btn blue" onclick="insertTemplate('diagnóstico')">
                    + Diagnóstico
                </button>
                <button type="button" class="td-obs-template-btn indigo" onclick="insertTemplate('reparación')">
                    + Reparación
                </button>
                <button type="button" class="td-obs-template-btn orange" onclick="insertTemplate('piezas')">
                    + Piezas
                </button>
                <button type="button" class="td-obs-template-btn rose" onclick="insertTemplate('pruebas')">
                    + Pruebas
                </button>
            </div>
            
            <div class="td-obs-footer">
                <button type="submit" class="td-obs-submit-btn">
                    <i class="bi bi-check-circle"></i>
                    Registrar Avance
                </button>
            </div>
        </form>
        <?php endif; ?>
        
        <!-- Historial de Observaciones (Timeline) -->
        <?php if (!empty($observaciones)): ?>
        <div class="td-obs-timeline">
            <div class="td-obs-entry">
                <div class="td-obs-entry-node action">
                    <i class="bi bi-tools"></i>
                </div>
                <div class="td-obs-entry-card">
                    <div class="td-obs-entry-meta">
                        <div class="td-obs-entry-author">
                            <span class="td-obs-entry-author-name"><?= htmlspecialchars($techName) ?></span>
                            <span class="td-obs-entry-author-role">Técnico</span>
                        </div>
                        <span class="td-obs-entry-time">
                            <i class="bi bi-clock"></i>
                            <?= ViewHelper::timeAgo($soporte->updated_at ?? $soporte->fecha) ?>
                        </span>
                    </div>
                    <p class="td-obs-entry-content"><?= nl2br(htmlspecialchars($observaciones)) ?></p>
                </div>
            </div>
        </div>
        <?php elseif (!$canEdit || $soporte->estado === 'cerrado'): ?>
        <div class="td-card" id="empty-notes-container">
            <div class="td-card-body text-center py-5">
                <div class="td-materials-empty-icon mb-3">
                    <i class="bi bi-clipboard-check"></i>
                </div>
                <p class="text-muted mb-0">No hay observaciones técnicas registradas.</p>
            </div>
        </div>
        <?php endif; ?>
        
    </div>

    <!-- Columna Derecha: Guía + Técnicos -->
    <div class="col-lg-4">
        
        <!-- Protocolo Técnico -->
        <div class="td-protocol-card">
            <div class="td-protocol-decoration">
                <i class="bi bi-shield-check"></i>
            </div>
            <h3 class="td-protocol-title">Protocolo Técnico</h3>
            <p class="td-protocol-desc">Sigue estos pasos para garantizar la calidad del servicio y la auditoría.</p>
            
            <div class="td-protocol-steps">
                <div class="td-protocol-step">
                    <div class="td-protocol-step-number">1</div>
                    <p>Documenta el estado inicial del equipo (daños físicos, suciedad).</p>
                </div>
                <div class="td-protocol-step">
                    <div class="td-protocol-step-number">2</div>
                    <p>Si reemplazas piezas, anota el número de parte antiguo y nuevo.</p>
                </div>
                <div class="td-protocol-step">
                    <div class="td-protocol-step-number">3</div>
                    <p>Adjunta fotos del "Antes" y "Después" en la pestaña de Archivos.</p>
                </div>
            </div>
        </div>
        
        <!-- Técnicos Activos -->
        <div class="td-technicians-card">
            <h3 class="td-technicians-title">Técnico Asignado</h3>
            
            <?php if ($soporte->tecnico_asignado): ?>
            <div class="td-technician-item">
                <div class="td-technician-avatar"><?= $techInitials ?></div>
                <div class="td-technician-info">
                    <p class="td-technician-name"><?= htmlspecialchars($techName) ?></p>
                    <p class="td-technician-status">
                        <span class="dot"></span> Asignado
                    </p>
                </div>
            </div>
            <?php else: ?>
            <p class="text-muted small mb-3"><em>Sin técnico asignado</em></p>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
            <?php 
            // Preparar datos para el modal
            $ticketJsonNotes = json_encode([
                'id' => $soporte->id,
                'title' => mb_strimwidth($soporte->descripcion ?? '', 0, 60, '...'),
                'device' => ($soporte->equipo_tipo ?? 'Equipo') . ' ' . ($soporte->equipo_serial ?? ''),
                'location' => $soporte->departamento_nombre ?? 'General',
                'category' => $soporte->categoria_nombre ?? 'General',
                'priority' => strtolower($soporte->prioridad ?? 'media'),
                'timeElapsed' => ''
            ]);
            $ticketJsonNotesAttr = htmlspecialchars($ticketJsonNotes, ENT_QUOTES, 'UTF-8');
            ?>
            <button type="button" class="td-invite-btn" onclick='AssignTechModal.open(<?= $ticketJsonNotesAttr ?>)'>
                <i class="bi bi-person-plus me-1"></i>
                Asignar Técnico
            </button>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<script>
    const techNameJs = "<?= htmlspecialchars($techName ?? 'Usuario') ?>";
</script>
<script>
function insertFormat(prefix, suffix) {
    const textarea = document.getElementById('obsTextarea');
    if (!textarea) return;
    
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const selectedText = text.substring(start, end);
    
    textarea.value = text.substring(0, start) + prefix + selectedText + suffix + text.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start + prefix.length, end + prefix.length);
}

function insertTemplate(type) {
    const textarea = document.getElementById('obsTextarea');
    if (!textarea) return;
    
    const templates = {
        'diagnóstico': '**Diagnóstico:** ',
        'reparación': '**Reparación realizada:** ',
        'piezas': '**Piezas reemplazadas:** ',
        'pruebas': '**Pruebas realizadas:** '
    };
    
    const template = templates[type] || '';
    const cursorPos = textarea.selectionStart;
    const text = textarea.value;
    
    textarea.value = text.substring(0, cursorPos) + '\n' + template + text.substring(cursorPos);
    textarea.focus();
    textarea.setSelectionRange(cursorPos + template.length + 1, cursorPos + template.length + 1);
}

document.addEventListener('DOMContentLoaded', function() {
    const visBtn = document.getElementById('obsVisibilityBtn');
    const visIcon = document.getElementById('obsVisIcon');
    const visText = document.getElementById('obsVisText');
    let isPrivate = true;
    
    if (visBtn) {
        visBtn.addEventListener('click', function() {
            isPrivate = !isPrivate;
            
            if (isPrivate) {
                visBtn.classList.remove('public');
                visBtn.classList.add('private');
                visIcon.className = 'bi bi-lock-fill';
                visText.textContent = 'Privado';
            } else {
                visBtn.classList.remove('private');
                visBtn.classList.add('public');
                visIcon.className = 'bi bi-eye';
                visText.textContent = 'Público';
            }
        });
    }

    // AJAX Submission
    const form = document.querySelector('.td-obs-editor');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('.td-obs-submit-btn');
            const originalText = submitBtn.innerHTML;
            const textarea = document.getElementById('obsTextarea');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Respuesta no válida del servidor. Posible sesión expirada.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Mostrar modal de éxito moderno
                    showSuccessOverlay('¡Avance Registrado!', 'La bitácora técnica se ha actualizado correctamente');
                    
                    const timelineContent = document.querySelector('.td-obs-entry-content');
                    const emptyContainer = document.getElementById('empty-notes-container');
                    
                    if (timelineContent) {
                        // Update existing entry content
                        timelineContent.innerHTML = textarea.value.replace(/\n/g, '<br>');
                    } else if (emptyContainer) {
                        // Replace empty state with new timeline
                         const newTimelineHTML = `
                        <div class="td-obs-timeline animate-fadeIn">
                            <div class="td-obs-entry">
                                <div class="td-obs-entry-node action">
                                    <i class="bi bi-tools"></i>
                                </div>
                                <div class="td-obs-entry-card">
                                    <div class="td-obs-entry-meta">
                                        <div class="td-obs-entry-author">
                                            <span class="td-obs-entry-author-name">${techNameJs}</span>
                                            <span class="td-obs-entry-author-role">Técnico</span>
                                        </div>
                                        <span class="td-obs-entry-time">
                                            <i class="bi bi-clock"></i>
                                            Ahora mismo
                                        </span>
                                    </div>
                                    <p class="td-obs-entry-content">${textarea.value.replace(/\n/g, '<br>')}</p>
                                </div>
                            </div>
                        </div>`;
                        
                        // Insert new timeline before removing empty container to avoid layout shift
                        emptyContainer.insertAdjacentHTML('beforebegin', newTimelineHTML);
                        emptyContainer.remove();
                    }
                } else {
                    Swal.fire('Error', data.message || 'Error al guardar', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (error.message.includes('sesión expirada')) {
                     Swal.fire({
                        icon: 'warning',
                        title: 'Sesión Expirada',
                        text: 'Tu sesión ha expirado. Por favor inicia sesión nuevamente.',
                        confirmButtonText: 'Ir al Login'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', 'Ocurrió un error al procesar la solicitud.', 'error');
                }
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});
</script>

<!-- Overlay de Éxito Moderno -->
<div id="successOverlay" class="so-overlay" style="display: none;">
    <div class="so-modal">
        <div class="so-body">
            <div class="so-icon-container">
                <div class="so-icon">
                    <i class="bi bi-check-lg"></i>
                </div>
            </div>
            <h3 class="so-title" id="successTitle">¡Éxito!</h3>
            <p class="so-message" id="successMessage">Operación completada correctamente</p>
        </div>
    </div>
</div>

<style>
/* Overlay de Éxito - Mismo estilo que otros modales */
.so-overlay {
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(0.75px);
    -webkit-backdrop-filter: blur(0.75px);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.so-overlay.show {
    opacity: 1;
}
.so-modal {
    background: white;
    width: 100%;
    max-width: 340px;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    transform: scale(0.95) translateY(10px);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.so-overlay.show .so-modal {
    transform: scale(1) translateY(0);
}
.so-body {
    padding: 2rem 1.5rem;
    text-align: center;
}
.so-icon-container {
    margin-bottom: 1rem;
}
.so-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: #059669;
    font-size: 1.75rem;
    border: 3px solid #6ee7b7;
    animation: so-pulse 0.5s ease;
}
@keyframes so-pulse {
    0% { transform: scale(0.8); opacity: 0; }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}
.so-title {
    margin: 0 0 0.375rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
}
.so-message {
    margin: 0;
    font-size: 0.875rem;
    color: #64748b;
    line-height: 1.5;
}
</style>

<script>
// Función global para mostrar overlay de éxito
function showSuccessOverlay(title, message) {
    let overlay = document.getElementById('successOverlay');
    
    // Si no existe, crearlo dinámicamente
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'successOverlay';
        overlay.className = 'so-overlay';
        overlay.innerHTML = `
            <div class="so-modal">
                <div class="so-body">
                    <div class="so-icon-container">
                        <div class="so-icon">
                            <i class="bi bi-check-lg"></i>
                        </div>
                    </div>
                    <h3 class="so-title" id="successTitle">¡Éxito!</h3>
                    <p class="so-message" id="successMessage">Operación completada</p>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
    }
    
    // Mover al body si no está ahí
    if (overlay.parentNode !== document.body) {
        document.body.appendChild(overlay);
    }
    
    // Actualizar contenido
    document.getElementById('successTitle').textContent = title || '¡Éxito!';
    document.getElementById('successMessage').textContent = message || 'Operación completada correctamente';
    
    // Mostrar con animación
    overlay.style.display = 'flex';
    overlay.offsetHeight; // Force reflow
    overlay.classList.add('show');
    
    // Auto-cerrar después de 2 segundos
    setTimeout(() => {
        overlay.classList.remove('show');
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 300);
    }, 2000);
}
</script>
