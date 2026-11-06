<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/image_upload.php';

$catalogId = positive_id($_GET['id'] ?? null);
if ($catalogId === null) {
    reject_request('El identificador del catálogo no es válido.', 404);
}

$statement = $pdo->prepare('SELECT id, name, category, description FROM catalogs WHERE id = ?');
$statement->execute([$catalogId]);
$catalog = $statement->fetch();
if (!$catalog) {
    reject_request('No se encontró el catálogo solicitado.', 404);
}

$statement = $pdo->prepare('SELECT p.id, p.name, p.description, p.price, p.image, cat.name AS category_name FROM products p LEFT JOIN categories cat ON cat.id = p.category_id WHERE p.catalog_id = ? ORDER BY CASE WHEN cat.name IS NULL THEN 1 ELSE 0 END, cat.name, p.name');
$statement->execute([$catalogId]);
$products = $statement->fetchAll();

$groups = [];
foreach ($products as $product) {
    $groupName = $product['category_name'] ?: 'Sin categoría';
    $groups[$groupName][] = $product;
}

$generatedAt = new DateTimeImmutable('now', new DateTimeZone('America/Bogota'));
$safeCatalogName = h($catalog['name']);
$description = trim((string)$catalog['description']);
$dedicatedCover = count($products) > 3;

$html = '<!doctype html><html lang="es"><head><meta charset="UTF-8"><title>' . $safeCatalogName . '</title><style>
@page{margin:42px 38px 55px}
body{font-family:"DejaVu Sans",sans-serif;color:#28263d;font-size:10px;line-height:1.45}
.cover{background:#29263f;color:#fff;padding:38px;border-left:5px solid #9a87ff;margin-bottom:18px}
.cover.dedicated{page-break-after:always}
.cover-mark{font-size:12px;letter-spacing:4px;color:#c7bfff;font-weight:bold}
.cover-rule{width:68px;height:4px;background:#9a87ff;margin:22px 0}
.cover-kicker{font-size:9px;letter-spacing:3px;color:#c9c3e2;text-transform:uppercase;margin:18px 0 0}
.cover h1{font-size:38px;line-height:1.15;margin:12px 0 16px;color:#fff;word-wrap:break-word}
.cover-description{font-size:12px;line-height:1.55;color:#e2def4;max-width:470px}
.cover-meta{margin-top:28px;border-top:1px solid #ffffff45;padding-top:12px;color:#d3cfea;font-size:9px}
.cover-count{float:right;color:#fff;font-weight:bold}
.empty-catalog{margin-top:22px;padding:16px;border:1px solid #c8c4d7;background:#35314d;color:#eee}
.category-group{margin:0 0 12px}
.product-grid{width:100%;border-collapse:separate;border-spacing:7px 6px;table-layout:fixed}
.category-title{font-size:15px;text-align:left;color:#443b78;border-bottom:2px solid #dcd7f3;padding:0 0 7px;margin:12px 0 3px;page-break-after:avoid}
.product-cell{width:50%;vertical-align:top;padding:0}
.product-card{border:1px solid #e2e0eb;padding:10px;margin:0;page-break-inside:avoid;overflow:hidden}
.product-card-wide{padding:12px}
.photo-frame{height:165px;margin:0 0 9px;background:#f4f3f8;text-align:center;line-height:165px;overflow:hidden}
.product-card-wide .photo-frame{height:190px;line-height:190px}
.product-photo{max-width:100%;max-height:160px;width:auto;height:auto;vertical-align:middle}
.product-card-wide .product-photo{max-height:185px}
.photo-placeholder{font-size:9px;color:#9290a2}
.product-category{font-size:8px;letter-spacing:1px;color:#7461c8;text-transform:uppercase;margin:0 0 5px}
.product-name{font-size:13px;line-height:1.3;font-weight:bold;color:#29263f;margin:0 0 6px;word-wrap:break-word}
.product-card-wide .product-name{font-size:16px}
.product-description{font-size:9px;line-height:1.45;color:#696779;word-wrap:break-word}
.product-price{font-size:15px;font-weight:bold;color:#51429d;border-top:1px solid #eeedf4;padding-top:8px;margin-top:9px}
.product-card-wide .product-price{font-size:17px}
.page-footer{position:fixed;left:0;right:0;bottom:-37px;height:20px;border-top:1px solid #e5e3ec;padding-top:7px;font-size:8px;color:#858399}
.page-footer-name{float:left;max-width:75%;white-space:nowrap}
.page-number{float:right}
.page-number:after{content:"Página " counter(page)}
</style></head><body>';

$html .= '<section class="cover' . ($dedicatedCover ? ' dedicated' : '') . '"><div class="cover-mark">CATALOG STUDIO</div><div class="cover-rule"></div><div class="cover-kicker">Catálogo comercial</div><h1>' . $safeCatalogName . '</h1>';
if ($description !== '') {
    $html .= '<div class="cover-description">' . nl2br(h($description)) . '</div>';
}
$html .= '<div class="cover-meta">Generado el ' . h($generatedAt->format('d/m/Y')) . '<span class="cover-count">' . count($products) . ' producto(s)</span></div>';
if (!$products) {
    $html .= '<div class="empty-catalog">Este catálogo todavía no tiene productos.</div>';
}
$html .= '</section>';

$renderProductCard = static function (array $product, bool $wide = false): string {
    $class = $wide ? 'product-card product-card-wide' : 'product-card';
    $html = '<article class="' . $class . '"><div class="photo-frame">';
    $photo = product_image_data_uri($product['image']);
    if ($photo !== null) {
        $html .= '<img class="product-photo" src="' . h($photo) . '" alt="">';
    } else {
        $html .= '<span class="photo-placeholder">Sin fotografía</span>';
    }

    $html .= '</div><div class="product-category">' . h($product['category_name'] ?: 'Sin categoría') . '</div><h3 class="product-name">' . h($product['name']) . '</h3>';
    $productDescription = trim((string)$product['description']);
    if ($productDescription !== '') {
        $html .= '<div class="product-description">' . nl2br(h($productDescription)) . '</div>';
    }

    $price = (float)$product['price'];
    $decimals = floor($price) === $price ? 0 : 2;
    $html .= '<div class="product-price">COP $' . number_format($price, $decimals, ',', '.') . '</div></article>';

    return $html;
};

if ($products) {
    $html .= '<main class="catalog-content">';
    foreach ($groups as $groupName => $groupProducts) {
        $html .= '<section class="category-group"><table class="product-grid"><thead><tr><th colspan="2" class="category-title">' . h($groupName) . '</th></tr></thead><tbody>';
        $productCount = count($groupProducts);
        for ($index = 0; $index < $productCount;) {
            $product = $groupProducts[$index];
            $descriptionLength = mb_strlen(trim((string)$product['description']), 'UTF-8');

            if ($descriptionLength > 400) {
                $html .= '<tr><td class="product-cell" colspan="2">' . $renderProductCard($product, true) . '</td></tr>';
                $index++;
                continue;
            }

            $html .= '<tr><td class="product-cell">' . $renderProductCard($product) . '</td>';
            $nextProduct = $groupProducts[$index + 1] ?? null;
            $nextDescriptionLength = $nextProduct === null ? 0 : mb_strlen(trim((string)$nextProduct['description']), 'UTF-8');
            if ($nextProduct !== null && $nextDescriptionLength <= 400) {
                $html .= '<td class="product-cell">' . $renderProductCard($nextProduct) . '</td>';
                $index += 2;
            } else {
                $html .= '<td class="product-cell"></td>';
                $index++;
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></section>';
    }
    $html .= '</main>';
}

$html .= '<footer class="page-footer"><span class="page-footer-name">' . $safeCatalogName . '</span><span class="page-number"></span></footer></body></html>';

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (!is_file($autoloadPath)) {
    http_response_code(503);
    exit('La generación de PDF no está disponible. Verifica la instalación de Dompdf.');
}

try {
    require_once $autoloadPath;
    $options = new Dompdf\Options();
    $options->set('isRemoteEnabled', false);
    $options->set('isPhpEnabled', false);
    $options->set('isJavascriptEnabled', false);
    $options->set('isFontSubsettingEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('chroot', realpath(__DIR__));

    $dompdf = new Dompdf\Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $download = ($_GET['download'] ?? null) === '1';
    $dompdf->stream('catalogo-' . $catalogId . '.pdf', ['Attachment' => $download]);
} catch (Throwable $e) {
    error_log('Catalog Studio could not render catalog PDF (catalog id ' . $catalogId . ').');
    http_response_code(500);
    exit('No fue posible generar el PDF. Inténtalo de nuevo o vuelve al catálogo.');
}
