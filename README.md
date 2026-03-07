# EstudiosBaja

Este es un sitio web desarrollado con:

- HTML
- CSS
- JavaScript
- PHP

## Estructura del proyecto

index.html -> página principal  
/email -> Solicitudes a enviarse
/email/Sended -> Solicitudes enviadas
/img -> imágenes mostradas en las paginas
/pdf -> pdf mostrado en las paginas

## Objetivo

Sitio informativo para mostrar servicios y contacto.

## Optimización de imágenes

- Se agregó `scripts/optimize_images.php` para flujo seguro:
  1. Genera versiones `.avif` en `img/optimized-avif/` (si `imageavif()` está disponible en tu PHP).

Comando:

```bash
php scripts/optimize_images.php
```
