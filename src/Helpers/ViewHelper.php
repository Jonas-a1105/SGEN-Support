<?php
/**
 * ViewHelper - Componentes reutilizables de UI
 * 
 * @package App\Helpers
 * @author SGEN Support Team
 */

namespace App\Helpers;

class ViewHelper
{
    /**
     * Renderiza múltiples KPI cards
     * 
     * @param array $kpiData Array de datos de KPIs
     * @return string HTML de todas las cards
     */
    public static function renderKpiCards(array $kpiData): string
    {
        $html = '<div class="kpi-cards">';
        
        foreach ($kpiData as $kpi) {
            $html .= self::renderKpiCard(
                $kpi['value'],
                $kpi['label'],
                $kpi['icon'],
                $kpi['color']
            );
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Renderiza una sola KPI card
     * 
     * @param int $value Valor numérico del KPI
     * @param string $label Etiqueta descriptiva
     * @param string $icon Clase del icono Bootstrap
     * @param string $color Color del icono (orange, blue, green, red)
     * @return string HTML de la card
     */
    private static function renderKpiCard(
        int $value,
        string $label,
        string $icon,
        string $color
    ): string {
        return <<<HTML
        <div class="kpi-card">
            <div class="kpi-icon {$color}">
                <i class="bi {$icon}"></i>
            </div>
            <div class="kpi-content">
                <h3>{$value}</h3>
                <p>{$label}</p>
            </div>
        </div>
        HTML;
    }
    
    /**
     * Renderiza botones de acción para una fila de soporte
     * 
     * @param object $soporte Objeto soporte
     * @param array $session Datos de sesión
     * @return string HTML de los botones
     */
    public static function renderActionButtons(object $soporte, array $session): string
    {
        $baseUrl = BASE_URL ?? '/';
        $id = (int) $soporte->id;
        $canEdit = PermissionHelper::canEdit($soporte, $session);
        
        // Botón Ver (siempre visible)
        $buttons = [
            <<<HTML
            <button class="action-btn btn-view" title="Ver" 
                    onclick="window.location.href='{$baseUrl}soportes/ver/{$id}'">
                <i class="bi bi-eye-fill"></i>
            </button>
            HTML
        ];
        
        // Botones de edición (solo si tiene permisos)
        if ($canEdit) {
            $buttons[] = <<<HTML
            <button class="action-btn btn-edit" title="Editar" 
                    onclick="window.location.href='{$baseUrl}soportes/editar/{$id}'">
                <i class="bi bi-pencil-fill"></i>
            </button>
            HTML;
            
            $buttons[] = <<<HTML
            <button class="action-btn btn-assign" title="Asignar" 
                    onclick="window.location.href='{$baseUrl}soportes/asignar/{$id}'">
                <i class="bi bi-person-fill"></i>
            </button>
            HTML;
            
            // Botón eliminar con data-name para confirmación via app.js
            $buttons[] = <<<HTML
            <a href="{$baseUrl}soportes/eliminar/{$id}" 
               class="action-btn btn-delete" 
               title="Eliminar" 
               data-name="Ticket #{$id}">
                <i class="bi bi-trash-fill"></i>
            </a>
            HTML;
        }
        
        return '<div class="action-buttons">' . implode('', $buttons) . '</div>';
    }
    
    /**
     * Formatea fecha para display
     * 
     * @param string $datetime Fecha en formato ISO o MySQL
     * @param string $format Formato de salida (default: d/m/Y)
     * @return string Fecha formateada
     */
    public static function formatDate(string $datetime, string $format = 'd/m/Y'): string
    {
        try {
            return date($format, strtotime($datetime));
        } catch (\Exception $e) {
            return '--';
        }
    }
    
    /**
     * Escapa HTML de forma segura
     * 
     * @param mixed $value Valor a escapar
     * @param string $default Valor por defecto si es null
     * @return string Valor escapado
     */
    public static function escape($value, string $default = '--'): string
    {
        return htmlspecialchars((string) ($value ?? $default), ENT_QUOTES, 'UTF-8');
    }
}
