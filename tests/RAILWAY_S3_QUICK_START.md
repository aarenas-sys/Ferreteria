# Railway Storage - Configuración Rápida para S3

## Para usar S3 (AWS) en Railway

### 1. En tu cuenta AWS:
- Crea un bucket S3 llamado: `tuapp-productos`
- Region: `us-east-1` (o la más cercana)

### 2. En tu Dashboard de Railway:

Ve a **Variables** y agrega:

```
FILESYSTEM_DISK=s3

AWS_ACCESS_KEY_ID=tu_access_key_aqui
AWS_SECRET_ACCESS_KEY=tu_secret_key_aqui
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=tuapp-productos
AWS_URL=https://tuapp-productos.s3.amazonaws.com
```

### 3. En tu terminal local:

```bash
# Instalar AWS SDK
composer require aws/aws-sdk-php

# Deploy a Railway
git add .
git commit -m "Add AWS S3 support"
git push
```

### 4. El ProductoController ya está listo:

Los cambios que hicimos agregan:
- ✅ Manejo de errores mejor
- ✅ Logging detallado
- ✅ Valida que directorio existe

Ahora solo cambia `'public'` a `'s3'`:

```php
// En ProductoController.php, línea ~217
Storage::disk('s3')->put($path, (string) $img);
Storage::disk('s3')->delete($producto->imagen);
Storage::disk('s3')->exists($producto->imagen);
```

### 5. En las vistas, cambia la URL:

```blade
<!-- Antes -->
<img src="{{ asset('storage/' . $producto->imagen) }}">

<!-- Después -->
<img src="{{ Storage::disk('s3')->url($producto->imagen) }}">
```

---

## Rápido: Con DigitalOcean Spaces

Exactamente lo mismo pero cambia en Railway:

```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=nyc3
AWS_BUCKET=tu-app-productos
AWS_URL=https://tu-app-productos.nyc3.digitaloceanspaces.com
AWS_ENDPOINT=https://nyc3.digitaloceanspaces.com
```

---

## ¿Tienes la configuración lista? 

Avísame y te ayudo a:
1. Hacer los cambios en ProductoController
2. Actualizar las vistas
3. Testear que funcione
