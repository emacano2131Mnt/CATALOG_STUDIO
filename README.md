# Catalog Studio — versión inicial

Aplicación web académica para crear catálogos y administrar productos con PHP, MySQL y Laragon.

## Requisitos
- Laragon con Apache y MySQL activos
- PHP 8.1 o superior recomendado
- Composer (para instalar Dompdf y generar PDF directamente)

## Instalación en Laragon
1. Copia la carpeta `catalog_studio` dentro de `C:\laragon\www\`.
2. Abre Laragon e inicia **Start All**.
3. Entra a `http://localhost/phpmyadmin` o usa HeidiSQL.
4. Importa el archivo `database.sql`. Esto crea la base `catalog_studio` y sus tablas.
5. Verifica las credenciales en `config/db.php`. En Laragon normalmente el usuario es `root` y la contraseña está vacía.
6. Abre `http://localhost/catalog_studio/`.

## Exportar a PDF
Desde el detalle de un catálogo pulsa **Exportar PDF**.
- Para generar y descargar el PDF desde PHP, abre una terminal en la carpeta del proyecto y ejecuta `composer install`.
- Si todavía no instalas Composer/Dompdf, la aplicación abre una vista imprimible; selecciona **Guardar como PDF** en el diálogo de impresión del navegador.

## Funciones incluidas
- Dashboard con indicadores.
- CRUD de catálogos.
- CRUD de productos.
- Categorías iniciales.
- Carga de fotografías.
- Búsqueda en catálogos y productos.
- Exportación de catálogo a PDF o impresión como alternativa.

## Estructura
- `config/`: conexión PDO a MySQL.
- `includes/`: cabecera y pie de la interfaz.
- `assets/`: CSS y JavaScript.
- `uploads/`: fotografías cargadas.
- `database.sql`: estructura de base de datos.
- `export_pdf.php`: generación PDF con Dompdf.

## Nota para la entrega
Esta es una base funcional para continuar desarrollando. Antes de publicar en internet deben añadirse autenticación, protección CSRF, validación más estricta de archivos y controles de permisos. La carga de imágenes está pensada para el entorno local de clase.
