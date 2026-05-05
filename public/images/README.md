# Logo de FerreNet

## Cómo agregar el logo al comprobante de venta

### Paso 1: Preparar el logo
1. Crea o obtén tu logo de empresa
2. Formatos recomendados: PNG (con transparencia), JPG, SVG
3. Tamaño recomendado: 200x200 píxeles (máximo)
4. Resolución: 300 DPI para impresión de calidad
5. Nombre del archivo: `logo.png`

### Paso 2: Ubicación
Coloca el archivo `logo.png` en esta carpeta: `public/images/`

### Paso 3: Verificación
- Si el archivo existe, se mostrará automáticamente en el comprobante
- Si no existe, se mostrará "FN" como placeholder azul

### Ejemplo de estructura de archivos:
```
public/
├── images/
│   ├── logo.png          ← Tu logo aquí
│   └── README.md         ← Este archivo
```

### Personalización adicional
Para personalizar más la información de la empresa, puedes editar:
- `resources/views/cajero/ventas/factura.blade.php`
- Busca las secciones de "Dirección", "Teléfono", "NIT", etc.

### Logo de ejemplo
Si necesitas un logo temporal, puedes crear uno simple con herramientas como:
- Canva
- LogoMaker
- O cualquier editor de imágenes

El sistema está preparado para mostrar tu logo profesionalmente en todos los comprobantes de venta.