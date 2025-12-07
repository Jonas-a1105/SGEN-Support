<?php
namespace App\Services;

use App\Models\TicketArchivo;

/**
 * FileUploadService - Gestión de archivos adjuntos para tickets
 */
class FileUploadService
{
    private $ticketArchivoModel;
    
    // Extensiones permitidas
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
    
    // Tamaño máximo: 5MB
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;
    
    // Directorio base para archivos
    private const UPLOAD_DIR = 'public/uploads/tickets/';

    public function __construct()
    {
        $this->ticketArchivoModel = new TicketArchivo();
    }

    /**
     * Sube un archivo adjunto a un ticket
     * 
     * @param int $ticketId ID del ticket
     * @param array $archivo Datos de $_FILES['archivo']
     * @param int|null $usuarioId ID del usuario que sube
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function subirArchivo(int $ticketId, array $archivo, ?int $usuarioId): array
    {
        // Validar archivo
        $validacion = $this->validarArchivo($archivo);
        if (!$validacion['valid']) {
            return ['success' => false, 'error' => $validacion['error']];
        }

        $nombreOriginal = $archivo['name'];
        $tipoMime = $archivo['type'];
        $tamanoBytes = $archivo['size'];
        $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        // Generar nombre único
        $nombreUnico = 'ticket_' . $ticketId . '_' . time() . '_' . uniqid() . '.' . $ext;
        $rutaDestino = self::UPLOAD_DIR . $nombreUnico;
        $rutaAbsoluta = __DIR__ . '/../../' . $rutaDestino;

        // Asegurar que existe el directorio
        $this->asegurarDirectorio(dirname($rutaAbsoluta));

        // Mover archivo
        if (!move_uploaded_file($archivo['tmp_name'], $rutaAbsoluta)) {
            return ['success' => false, 'error' => 'Error al mover el archivo al servidor'];
        }

        // Guardar en base de datos
        $datosArchivo = [
            'ticket_id' => $ticketId,
            'nombre_archivo' => $nombreUnico,
            'nombre_original' => $nombreOriginal,
            'ruta' => $rutaDestino,
            'tipo_mime' => $tipoMime,
            'tamaño_bytes' => $tamanoBytes,
            'subido_por' => $usuarioId
        ];

        if ($this->ticketArchivoModel->create($datosArchivo)) {
            return ['success' => true, 'mensaje' => 'Archivo subido correctamente'];
        }

        // Si falla el guardado en BD, eliminar el archivo físico
        @unlink($rutaAbsoluta);
        return ['success' => false, 'error' => 'Error al guardar en base de datos'];
    }

    /**
     * Valida un archivo antes de subirlo
     */
    public function validarArchivo(array $archivo): array
    {
        if (empty($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'Error al subir el archivo'];
        }

        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, self::ALLOWED_EXTENSIONS)) {
            return ['valid' => false, 'error' => 'Tipo de archivo no permitido. Extensiones permitidas: ' . implode(', ', self::ALLOWED_EXTENSIONS)];
        }

        if ($archivo['size'] > self::MAX_FILE_SIZE) {
            return ['valid' => false, 'error' => 'El archivo excede el tamaño máximo de 5MB'];
        }

        return ['valid' => true];
    }

    /**
     * Sirve un archivo para descarga
     */
    public function descargarArchivo(int $archivoId): array
    {
        $archivo = $this->ticketArchivoModel->findById($archivoId);
        
        if (!$archivo) {
            return ['success' => false, 'error' => 'Archivo no encontrado'];
        }

        $rutaAbsoluta = __DIR__ . '/../../' . $archivo->ruta;

        if (!file_exists($rutaAbsoluta)) {
            return ['success' => false, 'error' => 'El archivo físico no existe'];
        }

        return [
            'success' => true,
            'archivo' => $archivo,
            'ruta' => $rutaAbsoluta
        ];
    }

    /**
     * Envía headers y contenido del archivo
     */
    public function servirArchivo(object $archivo, string $rutaAbsoluta): void
    {
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $archivo->tipo_mime);
        header('Content-Disposition: attachment; filename="' . $archivo->nombre_original . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($rutaAbsoluta));
        readfile($rutaAbsoluta);
    }

    /**
     * Elimina un archivo del sistema
     */
    public function eliminarArchivo(int $archivoId): bool
    {
        $archivo = $this->ticketArchivoModel->findById($archivoId);
        
        if (!$archivo) {
            return false;
        }

        $rutaAbsoluta = __DIR__ . '/../../' . $archivo->ruta;
        
        // Eliminar archivo físico si existe
        if (file_exists($rutaAbsoluta)) {
            @unlink($rutaAbsoluta);
        }

        // Eliminar de base de datos
        return $this->ticketArchivoModel->delete($archivoId);
    }

    /**
     * Obtiene todos los archivos de un ticket
     */
    public function obtenerArchivosTicket(int $ticketId): array
    {
        return $this->ticketArchivoModel->findByTicketId($ticketId);
    }

    /**
     * Asegura que el directorio exista
     */
    private function asegurarDirectorio(string $directorio): void
    {
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
    }

    /**
     * Obtiene las extensiones permitidas
     */
    public static function getExtensionesPermitidas(): array
    {
        return self::ALLOWED_EXTENSIONS;
    }

    /**
     * Obtiene el tamaño máximo permitido en bytes
     */
    public static function getMaxFileSize(): int
    {
        return self::MAX_FILE_SIZE;
    }
}
