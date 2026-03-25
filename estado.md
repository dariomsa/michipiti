## Estado Actual

Fecha de referencia: 2026-03-25

### Objetivo

Levantar esta app Laravel 12 en un servidor Debian 10 que solo tenía Apache con `mod_php` 7.3, sin bajar Laravel ni recompilar el proyecto para PHP 7.3.

### Conclusiones importantes

- El proyecto requiere `PHP ^8.2` y `Laravel 12`.
- Debian 10 no permite instalar PHP 8.2 limpio desde Sury porque `buster` ya no está soportado.
- La estrategia viable es:
  - dejar Apache en el host
  - correr PHP 8.2 en Docker
  - hacer que Apache use `proxy_fcgi` hacia el contenedor

### Hallazgos del servidor de producción

- SO: Debian 10 (`buster`)
- Apache estaba usando:
  - `mpm_prefork`
  - `php7_module`
  - `mod_php 7.3`
- `php7.3-fpm` no existía.
- Docker se instaló correctamente.
- El contenedor PHP 8.2 respondió correctamente con `phpinfo()` por puerto 80.

### Docker

Se montó un contenedor PHP 8.2 FPM con:

- imagen basada en `php:8.2-fpm`
- extensiones:
  - `pdo_mysql`
  - `mbstring`
  - `intl`
  - `gd`
  - `zip`
  - `soap`
  - `bcmath`
  - `imagick`

Se validó dentro del contenedor:

- `php -v` -> `PHP 8.2.30`
- `Server API` -> `FPM/FastCGI`

### Apache

Se confirmó que:

- `proxy_module` está cargado
- `proxy_fcgi_module` está cargado
- Apache puede hablar con el contenedor PHP 8.2

Se validó que `phpinfo()` sí funciona en HTTP puerto 80.

### Problema detectado en la app

Cuando se subió la app real, apareció `403 Forbidden`.

El log mostró:

- Apache buscaba `DirectoryIndex info.php`
- la app real usa `index.php`

Eso significa que para la app real el VirtualHost debe volver a:

- `DirectoryIndex index.php`

### Problema detectado en storage público

En producción, el symlink actual es:

`public/storage -> /var/www/codex/storage/app/public`

Eso está mal para ese servidor, porque la app está en:

`/var/www/deploy/segec/codex`

Hay que corregir ese enlace en producción.

### Estado del código en producción

Ruta del proyecto:

`/var/www/deploy/segec/codex`

Estructura validada:

- `app/`
- `bootstrap/`
- `config/`
- `database/`
- `public/`
- `resources/`
- `routes/`
- `storage/`
- `vendor/`
- `composer.json`
- `artisan`

### Configuración útil validada

Host:

- ruta pública host: `/var/www/deploy/segec/codex/public`

Contenedor:

- ruta interna del proyecto: `/var/www/html`
- ruta pública interna: `/var/www/html/public`

### Punto exacto donde continuar

1. Dejar el VirtualHost HTTP en puerto 80 con:
   - `DocumentRoot /var/www/deploy/segec/codex/public`
   - `DirectoryIndex index.php`
   - `AllowOverride All`
   - FastCGI hacia el contenedor PHP 8.2
2. Confirmar que la app Laravel responde correctamente en `http://...`
3. Corregir `public/storage`
4. Después replicar el mismo esquema en `443`
5. Cuando todo esté estable, documentar despliegue por Git

### Nota sobre Codex CLI

En este entorno, `codex` está instalado como paquete global npm:

- binario: `/usr/bin/codex`
- resolución real: `/usr/lib/node_modules/@openai/codex/bin/codex.js`

Instalación inferida:

- `npm install -g @openai/codex`

### Recomendación operativa

Seguir trabajando los cambios en este repo y desplegar por Git a producción.
