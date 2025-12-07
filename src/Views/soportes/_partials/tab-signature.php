<?php
/**
 * Tab: Calificación/Valoración - V2 Ultra Moderna
 * Sistema de rating con estrellas
 */

use App\Helpers\ViewHelper;

$valoracion = $soporte->valoracion ?? null;
$comentarioValoracion = $soporte->comentario_valoracion ?? '';
$fechaValoracion = $soporte->fecha_valoracion ?? null;
$ticketResuelto = in_array($soporte->estado, ['resuelto', 'cerrado']);

// Mapeo de valoraciones
$ratingLabels = [
    1 => 'Muy malo',
    2 => 'Malo',
    3 => 'Regular',
    4 => 'Bueno',
    5 => 'Excelente'
];
?>

<div class="td-rating-container animate-fadeIn">
    <div class="td-rating-card">
        <!-- Header Gradient -->
        <div class="td-rating-header"></div>
        
        <!-- Icono Central -->
        <div class="td-rating-icon-wrapper">
            <i class="bi bi-star-fill"></i>
        </div>
        
        <!-- Contenido -->
        <div class="td-rating-content">
            
            <?php if ($valoracion): ?>
            <!-- Valoración ya guardada -->
            <h4 class="td-rating-title">¡Gracias por tu valoración!</h4>
            <p class="td-rating-subtitle">
                Tu opinión nos ayuda a mejorar nuestro servicio de soporte técnico.
            </p>
            
            <!-- Estrellas (solo visualización) -->
            <div class="td-rating-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="td-rating-star <?= $i <= $valoracion ? 'active' : '' ?>">
                    <i class="bi bi-star-fill"></i>
                </span>
                <?php endfor; ?>
            </div>
            
            <p class="mb-3">
                <span class="badge bg-<?= $valoracion >= 4 ? 'success' : ($valoracion >= 3 ? 'warning' : 'danger') ?>" 
                      style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                    <?= $ratingLabels[$valoracion] ?? 'Sin valoración' ?>
                </span>
            </p>
            
            <?php if ($comentarioValoracion): ?>
            <div class="td-description-box text-start mb-3" style="max-width: 24rem; margin: 0 auto;">
                <strong>Comentario:</strong><br>
                <?= nl2br(htmlspecialchars($comentarioValoracion)) ?>
            </div>
            <?php endif; ?>
            
            <?php if ($fechaValoracion): ?>
            <p class="text-muted small">
                Valorado el <?= ViewHelper::formatDate($fechaValoracion, 'd/m/Y H:i') ?>
            </p>
            <?php endif; ?>
            
            <?php elseif ($ticketResuelto): ?>
            <!-- Formulario de Valoración -->
            <form action="<?= BASE_URL ?>soportes/guardar_valoracion" method="POST" id="formValoracion">
                <input type="hidden" name="soporte_id" value="<?= $soporte->id ?>">
                <input type="hidden" name="valoracion" id="inputValoracion" value="">
                
                <h4 class="td-rating-title">¿Cómo fue tu experiencia?</h4>
                <p class="td-rating-subtitle">
                    Tu opinión es importante para nosotros y nos ayuda a mejorar constantemente.
                </p>
                
                <!-- Estrellas Interactivas -->
                <div class="td-rating-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button type="button" class="td-rating-star" data-rating="<?= $i ?>" onclick="setRating(<?= $i ?>)">
                        <i class="bi bi-star-fill"></i>
                    </button>
                    <?php endfor; ?>
                </div>
                
                <!-- Texto de Rating -->
                <p class="mb-4" id="ratingLabel" style="color: #64748b; font-weight: 500;">
                    Selecciona una calificación
                </p>
                
                <!-- Comentario -->
                <textarea 
                    name="comentario_valoracion" 
                    class="td-rating-comment" 
                    placeholder="Cuéntanos qué podemos mejorar (opcional)..." 
                    rows="3"
                ></textarea>
                
                <button type="submit" class="td-rating-submit-btn" id="btnSubmitRating" disabled>
                    Enviar Valoración
                </button>
            </form>
            
            <?php else: ?>
            <!-- Ticket pendiente -->
            <h4 class="td-rating-title">Valoración Pendiente</h4>
            <p class="td-rating-subtitle">
                Esta función estará disponible cuando el ticket sea marcado como 
                <span class="badge bg-success">Resuelto</span>. 
                Agradecemos tu paciencia.
            </p>
            
            <div class="td-rating-pending-actions">
                <h5 class="td-rating-pending-title">Mientras tanto puedes:</h5>
                
                <a href="<?= BASE_URL ?>soportes/pdf/<?= $soporte->id ?>" target="_blank" class="td-rating-action-btn">
                    <div class="td-rating-action-icon pdf">
                        <i class="bi bi-file-pdf"></i>
                    </div>
                    <span class="td-rating-action-text">Descargar Reporte PDF</span>
                </a>
                
                <button type="button" class="td-rating-action-btn" onclick="document.getElementById('comments-tab').click()">
                    <div class="td-rating-action-icon contact">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <span class="td-rating-action-text">Agregar Comentario</span>
                </button>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<script>
let currentRating = 0;

const ratingLabels = {
    1: 'Muy malo',
    2: 'Malo',
    3: 'Regular',
    4: 'Bueno',
    5: 'Excelente'
};

function setRating(rating) {
    currentRating = rating;
    document.getElementById('inputValoracion').value = rating;
    document.getElementById('ratingLabel').textContent = ratingLabels[rating];
    document.getElementById('btnSubmitRating').disabled = false;
    
    // Actualizar estrellas
    document.querySelectorAll('.td-rating-star').forEach((star, index) => {
        if (index < rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

// Validar antes de enviar
document.getElementById('formValoracion')?.addEventListener('submit', function(e) {
    if (currentRating === 0) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Selecciona una calificación',
            text: 'Por favor, selecciona al menos una estrella para enviar tu valoración.',
            confirmButtonColor: '#6366f1'
        });
    }
});
</script>
