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
  1. Genera versiones `.webp` en `img/optimized-webp/` (si `imagewebp()` está disponible en tu PHP).

Comando:

```bash
php scripts/optimize_images.php
```


## Seguridad del formulario de contacto

- Honeypot: campo oculto `website` para bloquear bots simples.
- Rate limit por IP: máximo 5 solicitudes en 10 minutos.
- Tiempo mínimo de formulario: evita envíos instantáneos automatizados.
- Turnstile obligatorio en backend: debes definir `TURNSTILE_SECRET` en tu entorno para validar captcha.

Ejemplo de configuración local:

```bash
export TURNSTILE_SECRET=tu_secret_key
```
