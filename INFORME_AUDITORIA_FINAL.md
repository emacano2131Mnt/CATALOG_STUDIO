# Informe de auditoría final — Catalog Studio

**Fecha:** 6 de noviembre de 2026  
**Proyecto:** `C:\laragon\www\catalog_studio`  
**Entorno observado:** Windows/Laragon, PHP 8.3.33, MySQL 8.4.3, Dompdf 3.1.6.  
**Alcance:** revisión de código, configuración, almacenamiento, metadatos MySQL, solicitudes HTTP locales y generación PDF. No se ejecutaron operaciones CRUD que modificaran datos durante esta auditoría.

**Estado Git:** el árbol de trabajo no está limpio. Contiene cambios de Fase 7 (búsqueda/filtros, orden, duplicación y PDF seleccionado) además de los documentos y ajustes de esta auditoría. No se hizo commit ni push; revisa y confirma estos cambios de forma deliberada antes de preparar el paquete final.

## Estado general

El proyecto está organizado por páginas PHP, código compartido en `includes/`, recursos cliente en `assets/`, persistencia relacional en MySQL y archivos en `uploads/` y `storage/`. Las páginas principales respondieron correctamente en Laragon. La generación de PDF completo y seleccionado produjo documentos válidos. Las relaciones de base de datos concuerdan con las operaciones de borrado documentadas.

Al cierre se observaron 3 catálogos, 2 productos y 5 categorías; no se encontraron registros temporales con marcadores `__P7TEST_` o `DEMO_`. Estos conteos corresponden al entorno local en el momento de la revisión.

## Archivos y áreas revisadas

- Las 28 páginas/utilidades PHP propias, excluyendo dependencias de `vendor/`.
- `config/db.php`, `includes/`, `database.sql`, `composer.json`, `composer.lock`, `.gitignore`.
- `assets/css/style.css`, `assets/js/app.js`.
- `uploads/`, `storage/catalog_designs/`, `storage/product_orders/` y sus reglas de acceso.
- Configuración Apache fuera del repositorio: `C:\laragon\etc\apache2\sites-enabled\catalog_studio_uploads.conf`.
- Tablas, columnas, claves foráneas y conteos actuales de `catalog_studio`.

## Problemas encontrados y correcciones

1. **Detalle de excepción PDO expuesto:** `config/db.php` incluía el texto de la excepción de conexión en la respuesta HTTP. Ahora registra el detalle con `error_log()` y muestra al usuario un error genérico en español.
2. **Protección HTTP de `storage/`:** los archivos PHP de diseño/orden ya tenían una guarda contra acceso directo, pero faltaba una regla Apache a nivel de directorio. Añadí `storage/.htaccess` con índices/CGI deshabilitados y `Require all denied`. PHP continúa accediendo por rutas locales.
3. **Consulta de exportación:** comprobé que la consulta del PDF completo enlaza categorías y que la ruta genera correctamente el documento.

No se cambió el esquema, MySQL, su puerto, Apache ni las fotografías. No se eliminaron datos y no se instalaron dependencias.

## Revisión técnica

### Arquitectura, MySQL y CRUD

`config/db.php` usa PDO, `utf8mb4`, host `127.0.0.1`, puerto `3307`, excepciones, modo asociativo y prepares nativos. La base real tiene tres tablas:

- `catalogs`: `id`, `name`, `category`, `description`, `created_at`, `updated_at`.
- `categories`: `id`, `name` (único).
- `products`: `id`, `catalog_id`, `category_id`, `name`, `description`, `price`, `image`, `created_at`.

`products.catalog_id` tiene FK a `catalogs.id` con `ON DELETE CASCADE`; `products.category_id` tiene FK nullable a `categories.id` con `ON DELETE SET NULL`. `catalogs.category` es texto libre, no FK. No se requiere migración para diseño ni orden manual.

La revisión estática encontró las rutas de creación, lectura, edición y eliminación de catálogos/productos, además de duplicación y reordenamiento. No se enviaron formularios POST de CRUD en esta auditoría porque ello cambiaría datos; sus pruebas integrales quedan pendientes con datos aislados.

### Seguridad y sesiones

`includes/security.php` centraliza escape HTML, token CSRF de sesión, validaciones de texto/ID/precio y mensajes flash. Las acciones de escritura revisadas validan token. Se valida que las categorías y catálogos existan al guardar un producto; el PDF seleccionado comprueba pertenencia de cada producto al catálogo solicitado.

La sesión se inicia desde `config/db.php` y se utiliza para CSRF y mensajes flash. No hay autenticación ni roles. Cualquier persona que alcance esta instalación puede operar sobre todo el espacio local: no se debe exponer en red o Internet sin control de acceso. No se encontraron flags de cookie de sesión definidos explícitamente en el código; revisar `HttpOnly`, `SameSite` y `Secure` para un despliegue HTTPS.

### Fotografías y archivos

`includes/image_upload.php` comprueba que el archivo sea una carga válida, usa Fileinfo para validar MIME real, limita a 3 MB, acepta JPEG/PNG/WEBP, genera nombres aleatorios y confina rutas con `realpath`. El borrado de fotografía consulta si hay referencias en productos; el limpiador del diseñador también revisa los ajustes guardados.

Las dos fotografías actuales están referenciadas por productos y Fileinfo las identifica como `image/webp` y `image/jpeg`; ambas rutas resuelven dentro de `uploads/`. No se probó una carga o reemplazo nuevo en esta revisión.

El `.htaccess` de `uploads/` impide listados/CGI y restringe los archivos servidos. La configuración externa existente declara `AllowOverride All`, `Options -Indexes -ExecCGI` y `Require all granted` para `C:/laragon/www/catalog_studio/uploads`. Apache local sirvió una imagen con HTTP 200 y MIME correcto.

`storage/catalog_designs/` guarda ajustes PHP por ID y `storage/product_orders/` secuencias de productos por catálogo. Los datos generados están excluidos por `.gitignore`. La ruta del diseñador verifica que el catálogo exista; el orden se filtra contra los productos consultados y el endpoint comprueba pertenencia antes de moverlos. Las solicitudes HTTP directas a un diseño y a un índice bajo `storage/` respondieron 403 tras añadir la regla. Se observaron archivos `.lock` vacíos; son auxiliares, no contienen el diseño y se conservaron.

### Diseñador, PDF y dependencias

El diseño se normaliza y persiste por catálogo en archivos, sin cambios de esquema. Dompdf 3.1.6 está bloqueado en `composer.lock`. El renderer deshabilita acceso remoto, PHP y JavaScript, y limita el acceso local mediante `chroot`. Los PDF completo y seleccionado fueron validados por su firma `%PDF-`; el envío de un producto de otro catálogo recibió HTTP 403.

`composer.json` y `composer.lock` se validaron correctamente; Composer advierte que no se especificó licencia. `composer check-platform-reqs --no-dev` pasó para PHP 8.3.33, DOM, iconv y mbstring. Composer no está en el `PATH` de la consola observada, aunque está disponible en `C:\laragon\bin\composer\composer.phar`. No se instalaron paquetes.

Los archivos revisados no contienen secuencias típicas de texto mojibake; la base y las vistas usan UTF-8/`utf8mb4`.

## Matriz de pruebas

### Comprobaciones automáticas o reproducibles ejecutadas

| Código | Módulo | Caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado |
|---|---|---|---|---|---|---|
| A-01 | PHP | `php -l` en código propio | 28 archivos PHP fuera de `vendor/` | Sin errores sintácticos | 28 correctos | Pasó |
| A-02 | JavaScript | `node --check` | `assets/js/app.js` | Sintaxis válida | Código 0 | Pasó |
| A-03 | Git | `git diff --check` | Cambios actuales | Sin errores whitespace | Sin errores; avisos LF/CRLF | Pasó |
| A-04 | Composer | `validate --no-check-publish` | `composer.json` y lock | Manifiesto válido | Válido; advertencia de licencia ausente | Pasó con advertencia |
| A-05 | Dependencias | `check-platform-reqs --no-dev` | Paquetes de `vendor/` | Requisitos satisfechos | PHP, DOM, iconv, mbstring correctos | Pasó |
| A-06 | MySQL | Versión, DDL, FK y conteos | Base local, puerto 3307 | Acceso de lectura y relaciones identificadas | MySQL 8.4.3, 3 tablas, conteos leídos | Pasó |
| A-07 | Limpieza | Búsqueda de marcadores temporales | `__P7TEST_`, `DEMO_` | Ningún test pendiente | 0 catálogos encontrados | Pasó |
| A-08 | HTTP | GET dashboard, listados, formularios, detalle y diseñador | Rutas con IDs existentes | HTTP 200 y HTML | Todas 200 | Pasó |
| A-09 | PDF | Exportar todos | Catálogo 1, datos existentes | PDF válido | `%PDF-` | Pasó |
| A-10 | PDF/CSRF | Exportar selección válida | Producto del catálogo 1 y token de sesión | PDF válido | `%PDF-` | Pasó |
| A-11 | Aislamiento PDF | Producto ajeno al catálogo enviado | Catálogo 1, producto del catálogo 2 | Rechazo 403 | HTTP 403 | Pasó |
| A-12 | Storage | Solicitar diseño e índice por HTTP | URLs dentro de `storage/` | Acceso denegado | HTTP 403 | Pasó |
| A-13 | Uploads | Abrir imagen actual | Fotografía referenciada | Servir como imagen estática | HTTP 200, `image/webp` | Pasó |
| A-14 | Fileinfo | MIME y rutas de las imágenes actuales | Dos imágenes referenciadas | Rutas seguras y MIME reconocido | WEBP y JPEG, rutas válidas | Pasó |

No existe una suite funcional automatizada en el repositorio. A-08–A-14 fueron comprobaciones reproducibles en Laragon, no pruebas cubiertas por un framework de testing.

### Pruebas manuales realizadas

No se hizo una sesión visual manual en navegador. Las solicitudes HTTP se ejecutaron por comandos y se reportan como comprobaciones de integración, no como pruebas manuales de UX.

### Pruebas pendientes

| Código | Módulo | Caso | Datos de entrada | Resultado esperado | Resultado obtenido | Estado |
|---|---|---|---|---|---|---|
| P-01 | Catálogos | Crear, editar y borrar catálogo | Registro `DEMO_` controlado | Persistencia, confirmación y cascada coherentes | No ejecutado para no modificar la BD | Pendiente |
| P-02 | Productos | Crear, editar y borrar producto | Datos y categoría válidos | Validación y relaciones correctas | No ejecutado | Pendiente |
| P-03 | Fotografías | Carga válida/inválida, >3 MB, reemplazo y archivo compartido | Archivos temporales controlados | MIME/tamaño y referencias seguras | No ejecutado | Pendiente |
| P-04 | Diseñador | Guardar, recuperar, restablecer y probar plantillas | Catálogo temporal | Ajustes aislados y PDF coherente | GET/PDF probados; persistencia completa no | Pendiente |
| P-05 | Orden | Reordenar y verificar tras recarga | Catálogo temporal | Orden durable por catálogo | No repetido en esta auditoría; realizado en Fase 7 | Pendiente de repetición |
| P-06 | Duplicación | Copiar catálogo con diseño e imágenes compartidas | Catálogo temporal | Copia coherente, sin duplicar archivos | No repetido en esta auditoría; realizado en Fase 7 | Pendiente de repetición |
| P-07 | Interfaz | Buscar, filtrar, responsive y vista previa | Escritorio/tableta/móvil | Sin desbordes y controles correctos | No se usó navegador visual | Pendiente |
| P-08 | Sesión | Flags de cookie y CSRF inválido | Sesión de prueba | Flags adecuados y POST inválido rechazado | No ejecutado; flags no explícitos en código | Pendiente |

## Riesgos y recomendaciones antes de entregar

1. Mantener el sistema en el entorno local: no tiene autenticación ni autorización por usuario.
2. Revisar flags de cookies de sesión para el entorno final y cualquier eventual HTTPS.
3. Completar manualmente los casos P-01 a P-08 en datos de demostración identificados; confirmar IDs antes de borrar cualquier registro temporal.
4. Respaldar en conjunto la base, `uploads/`, `storage/catalog_designs/` y `storage/product_orders/`; GitHub no conserva necesariamente esos archivos.
5. Definir una licencia en `composer.json` si la institución la solicita; Composer la reporta como advertencia y no se asignó una por inferencia.
6. Las advertencias LF/CRLF de Git son informativas; no impidieron lint ni ejecución.

## Recomendación final

Las rutas verificadas, la conexión local y la generación PDF se encuentran operativas. Antes de la entrega, completa pruebas visuales y CRUD aislado, conserva un respaldo conjunto y explica en la sustentación que el sistema está pensado para un único operador local y no para publicación abierta.
