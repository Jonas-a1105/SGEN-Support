<?php
/**
 * BadgeHelper - Renderizado consistente de badges (estado, prioridad, categoría)
 * 
 * @package App\Helpers
 * @author SGEN Support Team
 */

namespace App\Helpers;

class BadgeHelper
{
    /**
     * Mapeo de estados a clases CSS
     * @var array<string, string>
     */
    private static array $estadoClasses = [
        'pendiente' => 'bg-warning text-dark',
        'en_proceso' => 'bg-info text-dark',
        'resuelto' => 'bg-success',
    ];
    
    /**
     * Mapeo de prioridades a clases CSS
     * @var array<string, string>
     */
    private static array $prioridadClasses = [
        'alta' => 'bg-danger',
        'media' => 'bg-warning text-dark',
        'baja' => 'bg-success',
    ];
    
    /**
     * Renderiza badge de estado
     * 
     * @param string $estado Estado del soporte
     * @return string HTML del badge
     */
    public static function renderEstado(string $estado): string
    {
        $class = self::$estadoClasses[$estado] ?? 'bg-secondary';
        $label = strtoupper(str_replace('_', ' ', $estado));
        
        return "<span class=\"status-badge {$class}\">{$label}</span>";
    }
    
    /**
     * Renderiza badge de prioridad  
     * 
     * @param string|null $prioridad Prioridad del soporte
     * @return string HTML del badge
     */
    public static function renderPrioridad(?string $prioridad): string
    {
        $prioridad = $prioridad ?? 'media';
        $class = self::$prioridadClasses[$prioridad] ?? 'bg-secondary';
        $label = strtoupper($prioridad);
        
        return "<span class=\"priority-badge {$class}\">{$label}</span>";
    }
    
    /**
     * Renderiza badge de categoría con icono y color
     * 
     * @param object $soporte Objeto soporte con datos de categoría
     * @return string HTML del badge
     */
    public static function renderCategoria(object $soporte): string
    {
        // Si no hay categoría, mostrar placeholder
        if (empty($soporte->categoria_nombre)) {
            return '<span class="text-muted">--</span>';
        }
        
        $color = $soporte->categoria_color ?? '#6c757d';
        $variants = ColorHelper::getLightVariants($color);
        $icon = htmlspecialchars($soporte->categoria_icono ?? 'bi bi-tag');
        $nombre = htmlspecialchars($soporte->categoria_nombre);
        
        return <<<HTML
        <span class="category-badge-light" style="background-color: {$variants['background']}; border: 1px solid {$variants['border']};">
            <i class="{$icon}" style="color: {$color}"></i>
            {$nombre}
        </span>
        HTML;
    }
}
