<?php
/**
 * ColorHelper - Utilidades para conversión y manipulación de colores
 * 
 * @package App\Helpers
 * @author SGEN Support Team
 */

namespace App\Helpers;

class ColorHelper
{
    /**
     * Convierte color hexadecimal a RGBA
     * 
     * @param string $hex Color en formato hexadecimal (#RRGGBB o #RGB)
     * @param float $opacity Opacidad (0.0 a 1.0)
     * @return string Color en formato rgba(r, g, b, a)
     */
    public static function hexToRgba(string $hex, float $opacity = 1.0): string
    {
        // Remover # si existe
        $hex = ltrim($hex, '#');
        
        // Expandir formato corto (#RGB -> #RRGGBB)
        if (strlen($hex) === 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) .
                   str_repeat(substr($hex, 1, 1), 2) .
                   str_repeat(substr($hex, 2, 1), 2);
        }
        
        // Convertir a RGB
        $rgb = sscanf($hex, "%02x%02x%02x");
        
        // Fallback si conversión falla
        if (!$rgb || count($rgb) !== 3) {
            return "rgba(108, 117, 125, {$opacity})"; // Gray fallback
        }
        
        // Clamp opacity entre 0 y 1
        $opacity = max(0.0, min(1.0, $opacity));
        
        return "rgba({$rgb[0]}, {$rgb[1]}, {$rgb[2]}, {$opacity})";
    }
    
    /**
     * Genera color de fondo claro a partir de un color base
     * 
     * @param string $hex Color base
     * @return array ['background' => rgba, 'border' => rgba]
     */
    public static function getLightVariants(string $hex): array
    {
        return [
            'background' => self::hexToRgba($hex, 0.15),
            'border' => self::hexToRgba($hex, 0.3)
        ];
    }
}
