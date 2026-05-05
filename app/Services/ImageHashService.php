<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * ImageHashService - Comparación de imágenes sin IA
 * 
 * Genera hashes simples y robustos de imágenes para comparación
 * Usa métodos de procesamiento de imágenes nativas de PHP
 */
class ImageHashService
{
    /**
     * Genera un hash simple pero robusto de una imagen
     * 
     * Método: Redimensiona la imagen a 8x8, calcula el promedio de grises
     * y crea un hash binario comparando cada píxel con el promedio
     * 
     * Compatible con: JPG, PNG, GIF, WebP, BMP
     * Normaliza automáticamente según formato
     * 
     * @param string|resource $ruta Ruta del archivo o recurso de imagen
     * @return string|null Hash hexadecimal de 16 caracteres o null si falla
     */
    public function generarHash($ruta): ?string
    {
        try {
            // Si es ruta de Storage Laravel
            if (is_string($ruta) && Storage::exists($ruta)) {
                $rutaReal = Storage::path($ruta);
            } else {
                $rutaReal = $ruta;
            }

            // Validar que es un archivo real
            if (!file_exists($rutaReal)) {
                \Log::error("Archivo no existe: $rutaReal");
                return null;
            }

            // Crear imagen desde archivo (normalizada)
            $imagen = $this->cargarImagen($rutaReal);
            if (!$imagen) {
                \Log::error("No se pudo cargar imagen normalizada: $rutaReal");
                return null;
            }

            // Redimensionar a 8x8 (64 píxeles = 64 bits = 16 hex)
            $imagenRedimensionada = imagecreatetruecolor(8, 8);
            
            // Usar resampling de alta calidad para mantener características
            imagecopyresampled(
                $imagenRedimensionada,
                $imagen,
                0, 0, 0, 0,
                8, 8,
                imagesx($imagen),
                imagesy($imagen)
            );

            // Calcular el promedio de grises
            $promedioGrises = $this->calcularPromedioGrises($imagenRedimensionada);

            // Generar hash comparando cada píxel con el promedio
            $hash = '';
            for ($y = 0; $y < 8; $y++) {
                for ($x = 0; $x < 8; $x++) {
                    $rgb = imagecolorat($imagenRedimensionada, $x, $y);
                    $gris = $this->obtenerGris($rgb);
                    $hash .= ($gris >= $promedioGrises) ? '1' : '0';
                }
            }

            // Limpiar recursos
            imagedestroy($imagen);
            imagedestroy($imagenRedimensionada);

            // Convertir binario a hexadecimal
            $hashHex = $this->binarioAHexadecimal($hash);
            
            \Log::debug("Hash generado exitosamente: $hashHex para $rutaReal");
            return $hashHex;
        } catch (\Exception $e) {
            \Log::error('Error generando hash de imagen: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene información de soporte de formatos
     * 
     * @return array
     */
    public static function formatosSoportados(): array
    {
        return [
            'jpg' => 'JPEG',
            'jpeg' => 'JPEG',
            'png' => 'PNG',
            'gif' => 'GIF',
            'webp' => 'WebP',
            'bmp' => 'BMP',
        ];
    }

    /**
     * Valida si un MIME type es soportado
     * 
     * @param string $mimeType
     * @return bool
     */
    public static function esFormatoValido(string $mimeType): bool
    {
        $formatosValidos = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
            'image/x-windows-bmp',
        ];

        return in_array(strtolower($mimeType), $formatosValidos);
    }

    /**
     * Carga una imagen desde archivo (soporta jpg, png, gif, webp, bmp)
     * Normaliza la imagen para comparación consistente
     * 
     * @param string $ruta
     * @return resource|false
     */
    private function cargarImagen(string $ruta)
    {
        // Verificar que el archivo existe y es legible
        if (!file_exists($ruta) || !is_readable($ruta)) {
            \Log::warning("Archivo no accesible: $ruta");
            return false;
        }

        $info = @getimagesize($ruta);
        if (!$info) {
            \Log::warning("No es una imagen válida: $ruta");
            return false;
        }

        $tipo = $info[2];
        $ancho = $info[0];
        $alto = $info[1];

        // Validar dimensiones mínimas (evitar imágenes muy pequeñas o corruptas)
        if ($ancho < 10 || $alto < 10) {
            \Log::warning("Imagen muy pequeña: {$ancho}x{$alto}");
            return false;
        }

        try {
            $imagen = match ($tipo) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($ruta),
                IMAGETYPE_PNG => imagecreatefrompng($ruta),
                IMAGETYPE_GIF => imagecreatefromgif($ruta),
                IMAGETYPE_WEBP => imagecreatefromwebp($ruta),
                IMAGETYPE_BMP => imagecreatefrombmp($ruta),
                default => false,
            };

            if (!$imagen) {
                \Log::warning("No se pudo cargar imagen tipo: $tipo");
                return false;
            }

            // Normalizar imagen: convertir a RGB si es necesario (para PNG con alpha, GIF, etc)
            return $this->normalizarImagen($imagen, $tipo);
        } catch (\Exception $e) {
            \Log::error("Error cargando imagen: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Normaliza una imagen para comparación consistente
     * - PNG con transparencia → RGB sin alpha
     * - GIF → RGB
     * - Preserva colores naturales (no convierte prematuramente a B&N)
     * 
     * @param resource $imagen
     * @param int $tipoOriginal
     * @return resource
     */
    private function normalizarImagen($imagen, int $tipoOriginal)
    {
        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);

        // Para PNG y GIF con transparencia/paleta, crear una imagen RGB limpia
        if (in_array($tipoOriginal, [IMAGETYPE_PNG, IMAGETYPE_GIF])) {
            $imagenNormalizada = imagecreatetruecolor($ancho, $alto);
            
            // Fondo blanco (mejor para comparación)
            $blanco = imagecolorallocate($imagenNormalizada, 255, 255, 255);
            imagefill($imagenNormalizada, 0, 0, $blanco);
            
            // Copiar imagen con resampling de calidad
            imagecopyresampled(
                $imagenNormalizada,
                $imagen,
                0, 0, 0, 0,
                $ancho, $alto,
                $ancho, $alto
            );
            
            imagedestroy($imagen);
            return $imagenNormalizada;
        }

        // JPEG y WebP ya son RGB nativos
        return $imagen;
    }

    /**
     * Calcula el promedio de grises de una imagen
     * 
     * @param resource $imagen
     * @return int
     */
    private function calcularPromedioGrises($imagen): int
    {
        $suma = 0;
        $total = 0;

        for ($y = 0; $y < imagesy($imagen); $y++) {
            for ($x = 0; $x < imagesx($imagen); $x++) {
                $rgb = imagecolorat($imagen, $x, $y);
                $gris = $this->obtenerGris($rgb);
                $suma += $gris;
                $total++;
            }
        }

        return $total > 0 ? intval($suma / $total) : 128;
    }

    /**
     * Obtiene el valor de gris de un color RGB
     * Usa fórmula estándar: 0.299*R + 0.587*G + 0.114*B
     * 
     * @param int $rgb Color en formato imagecolorat
     * @return int Valor de gris (0-255)
     */
    private function obtenerGris(int $rgb): int
    {
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        return intval(0.299 * $r + 0.587 * $g + 0.114 * $b);
    }

    /**
     * Convierte un string binario a hexadecimal
     * 
     * @param string $binario
     * @return string
     */
    private function binarioAHexadecimal(string $binario): string
    {
        $hex = '';
        for ($i = 0; $i < strlen($binario); $i += 4) {
            $hex .= dechex(bindec(substr($binario, $i, 4)));
        }
        return $hex;
    }

    /**
     * Calcula la distancia de Hamming entre dos hashes
     * 
     * Distancia de Hamming: cantidad de bits diferentes
     * Valores bajos = imágenes similares
     * 
     * @param string $hash1 Hash hexadecimal
     * @param string $hash2 Hash hexadecimal
     * @return int Distancia (0-64)
     */
    public function calcularDistancia(string $hash1, string $hash2): int
    {
        // Convertir a binario
        $bin1 = $this->hexadecimalABinario($hash1);
        $bin2 = $this->hexadecimalABinario($hash2);

        // Calcular bits diferentes
        $distancia = 0;
        $minLen = min(strlen($bin1), strlen($bin2));

        for ($i = 0; $i < $minLen; $i++) {
            if ($bin1[$i] !== $bin2[$i]) {
                $distancia++;
            }
        }

        // Sumar los bits faltantes
        $distancia += abs(strlen($bin1) - strlen($bin2));

        return $distancia;
    }

    /**
     * Convierte hexadecimal a binario
     * 
     * @param string $hex
     * @return string
     */
    private function hexadecimalABinario(string $hex): string
    {
        $bin = '';
        for ($i = 0; $i < strlen($hex); $i++) {
            $bin .= str_pad(base_convert($hex[$i], 16, 2), 4, '0', STR_PAD_LEFT);
        }
        return $bin;
    }
}
