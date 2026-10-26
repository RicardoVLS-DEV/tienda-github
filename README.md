# Mi Tienda (PHP puro + MySQL)

## Instalación
1. Crear la base de datos: `mysql -u root -p < database/schema.sql`
2. Copiar `config/config.local.example.php` a `config/config.local.php` y poner tus credenciales (este archivo no se sube a Git). También sirven las variables de entorno `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
3. Levantar el servidor: `php -S localhost:8000 -t public`
4. Abrir http://localhost:8000

## Arquitectura
- `public/index.php`      Front controller y rutas (composición de dependencias).
- `src/Controller`        Manejo de HTTP (request/response, sesión, redirects).
- `src/Service`           Lógica de negocio y validaciones (sin PDO ni superglobales → testeable).
- `src/Repository`        Interfaces + implementaciones PDO (acceso a MySQL).
- `src/Support`           Utilidades (Database, Session, CSRF, View).
- `templates`             Vistas PHP.
- `tests`                 PHPUnit con repositorios en memoria (`tests/Fake`).

## Pruebas unitarias
    composer install
    composer test

## Seguridad incluida
Consultas preparadas (PDO), `password_hash`, regeneración de sesión al login, tokens CSRF en todos los POST y escape de salida con `htmlspecialchars`.

## Pruebas
| Qué comprueba | Comando |
|---|---|
| Sintaxis de todos los PHP (compilación) + clases cargan + unitarias | `composer test` |
| Análisis estático | `composer stan` |
| Flujo completo con MySQL real (registro, login, CRUD) | `php -S 127.0.0.1:8000 -t public` y en otra terminal `bash tests/smoke.sh` |

## Integración continua
El workflow `.github/workflows/ci.yml` corre en cada push y pull request a `main`: instala dependencias, revisa la sintaxis y ejecuta PHPUnit en PHP 8.1, 8.2 y 8.3; además corre la prueba de humo contra un MySQL real y PHPStan.
