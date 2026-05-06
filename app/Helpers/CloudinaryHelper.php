<?php

namespace App\Helpers;

class CloudinaryHelper
{
    /**
     * Genera una URL de Cloudinary para una imagen
     *
     * @param string|null $publicId Public ID de la imagen en Cloudinary
     * @param int $width Ancho de la imagen (con transformación)
     * @param int $height Altura de la imagen (con transformación)
     * @return string|null URL de la imagen o null si no hay public_id
     */
    public static function getImageUrl(?string $publicId, int $width = 400, int $height = 400): ?string
    {
        if (!$publicId) {
            return null;
        }

        $cloudName = env('CLOUDINARY_NAME');
        
        if (!$cloudName) {
            return null;
        }

        // Transformación: c_fill rellena la imagen al tamaño especificado
        return "https://res.cloudinary.com/{$cloudName}/image/upload/c_fill,h_{$height},w_{$width}/{$publicId}.jpg";
    }

    /**
     * Obtiene la URL de Cloudinary con tamaño personalizado
     *
     * @param string|null $publicId
     * @param array $transformations Transformaciones de Cloudinary (ej: ['width' => 800, 'height' => 600])
     * @return string|null
     */
    public static function getImageUrlWithTransformation(?string $publicId, array $transformations = []): ?string
    {
        if (!$publicId) {
            return null;
        }

        $cloudName = env('CLOUDINARY_NAME');
        
        if (!$cloudName) {
            return null;
        }

        $transform = 'c_fill';
        
        if (isset($transformations['width'])) {
            $transform .= ',w_' . $transformations['width'];
        }
        
        if (isset($transformations['height'])) {
            $transform .= ',h_' . $transformations['height'];
        }

        return "https://res.cloudinary.com/{$cloudName}/image/upload/{$transform}/{$publicId}.jpg";
    }
}
