# Ianus Event Trivia

Aplicación Laravel para una activación de evento de Ianus SA: registro mobile, trivia de 5 preguntas, pantalla de TV con QR y leaderboard, y panel administrador con Filament.

## Stack

- Laravel 9 local-compatible, PHP 8.0.2+
- PostgreSQL o MySQL configurable por `.env`
- Filament en `/admin`
- Blade + Tailwind + Alpine.js
- QR generado desde backend
- Leaderboard con polling cada 3 segundos
- Exportación CSV inicial desde admin

## Instalación local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

En XAMPP/PHP 8.0 local, si `php artisan serve` da problemas con rutas que tienen espacios, usar:

```bash
php -S 127.0.0.1:8016 -t public local-server.php
```

Credenciales seed:

- URL: `http://localhost:8000/admin`
- Email: `admin@ianus.local`
- Password: `password`

## Rutas principales

- `/` formulario del participante
- `/participants` registro
- `/play/{attempt}` trivia
- `/play/{attempt}/result` resultado
- `/screen` pantalla TV
- `/qr/print` QR imprimible
- `/api/leaderboard` endpoint JSON público sin datos personales
- `/admin` panel administrador

## Configuración

En `.env` se puede cambiar PostgreSQL por MySQL ajustando `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.

Para correr local sin crear base manual, este workspace ya quedó configurado con SQLite en `.env`.

Los textos editables, logo principal y estado del evento viven en `settings`. El seeder carga valores iniciales y `event_active=1`.

## Deploy

Se incluye `Dockerfile` y `render.yaml` como base para Render. Antes de producción:

- configurar `APP_URL`
- usar `APP_ENV=production`
- usar `APP_DEBUG=false`
- revisar `APP_KEY`
- configurar base de datos persistente
- ejecutar `php artisan migrate --force`
- ejecutar `php artisan storage:link`

## Notas de seguridad

El sistema no solicita DNI. Por eso el bloqueo de doble participación es razonable pero no absoluto: valida email + set, celular + set y cookie de dispositivo por set.

Los endpoints públicos no exponen mail ni celular. Los POST tienen CSRF y rate limit básico.
