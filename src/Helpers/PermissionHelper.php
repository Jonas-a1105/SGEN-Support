<?php
/**
 * PermissionHelper - Lógica centralizada de permisos
 * 
 * @package App\Helpers
 * @author SGEN Support Team
 */

namespace App\Helpers;

class PermissionHelper
{
    /**
     * Verifica si el usuario puede editar un soporte
     * 
     * @param object $soporte Objeto soporte
     * @param array $session Datos de sesión ($_SESSION)
     * @return bool True si puede editar, false otherwise
     */
    public static function canEdit(object $soporte, array $session): bool
    {
        // Si no hay rol, denegar
        if (!isset($session['rol'])) {
            return false;
        }
        
        // Admin siempre puede editar
        if ($session['rol'] === 'admin') {
            return true;
        }
        
        // Técnico/Consultor solo si es el creador del soporte
        if (in_array($session['rol'], ['tecnico', 'consultor'], true)) {
            return isset($soporte->usuario_creacion_id, $session['user_id']) &&
                   $soporte->usuario_creacion_id == $session['user_id'];
        }
        
        return false;
    }
    
    /**
     * Verifica si el usuario puede eliminar un soporte
     * Mismo criterio que editar por ahora
     * 
     * @param object $soporte
     * @param array $session
     * @return bool
     */
    public static function canDelete(object $soporte, array $session): bool
    {
        return self::canEdit($soporte, $session);
    }
    
    /**
     * Verifica si el usuario puede asignar técnicos
     * 
     * @param array $session
     * @return bool
     */
    public static function canAssignTechnician(array $session): bool
    {
        return isset($session['rol']) && 
               in_array($session['rol'], ['admin', 'supervisor'], true);
    }
}
