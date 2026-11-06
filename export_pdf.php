<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/catalog_design_store.php';

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
$design = catalog_design_load($catalogId, $catalog);
$generatedAt = new DateTimeImmutable('now', new DateTimeZone('America/Bogota'));
$safeCatalogName = h($catalog['name']);
$font = $design['font'];
$primary = $design['primary_color'];
$secondary = $design['secondary_color'];
$background = $design['background_color'];
$textColor = $design['text_color'];
$cardBackground = $design['card_background'];
$titleColor = $design['title_color'];
$subtitleColor = $design['subtitle_color'];
$descriptionColor = $design['description_color'];
$priceColor = $design['price_color'];
$borderColor = $design['border_color'];
$categoryColor = $design['category_color'];
$coverColor = $design['cover_color'];
$coverTitleColor = $design['cover_title_color'];
$imageHeights = ['small' => 90, 'medium' => 140, 'large' => 190];
$imageHeight = $imageHeights[$design['image_size']];
if ($design['image_ratio'] === 'square') $imageHeight = (int)round($imageHeight * 0.86);
if ($design['image_ratio'] === 'portrait') $imageHeight = (int)round($imageHeight * 1.3);
if ($design['image_ratio'] === 'landscape') $imageHeight = (int)round($imageHeight * 0.68);
$columns = (int)$design['columns'];
$dedicatedCover = count($products) > 3;
$coverPhoto = product_image_data_uri($design['cover_image']);
$logoPhoto = product_image_data_uri($design['logo']);

$marginPoints = (int)round($design['page_margin'] * 2.83465);
$sectionSpacing = (int)round($design['section_spacing'] * 2.83465);
$productGap = (int)$design['product_gap'];
$radius = $design['card_corners'] === 'square' ? 0 : (int)$design['card_radius'];
$coverPosition = ['center' => 'center center', 'top' => 'center top', 'bottom' => 'center bottom', 'left' => 'left center', 'right' => 'right center'][$design['cover_image_position']];
$coverVerticalSpace = ['top' => 0, 'center' => 65, 'bottom' => 130][$design['cover_title_position']];
$html = '<!doctype html><html lang="es"><head><meta charset="UTF-8"><title>' . $safeCatalogName . '</title><style>
@page{margin:' . $marginPoints . 'px;size:' . h($design['page_size']) . ' ' . h($design['page_orientation']) . '}
body{font-family:"' . h($font) . '",sans-serif;color:' . $textColor . ';font-size:9.5px;line-height:1.45;background:' . $background . '}
.cover{position:relative;overflow:hidden;background:' . $coverColor . ';color:' . $coverTitleColor . ';padding:34px;margin-bottom:' . $sectionSpacing . 'px;border-left:5px solid ' . $secondary . ';text-align:' . $design['cover_alignment'] . '}
.cover-photo-bg{position:absolute;top:0;right:0;bottom:0;left:0;background-image:' . ($coverPhoto !== null ? 'url("' . h($coverPhoto) . '")' : 'none') . ';background-position:' . h($coverPosition) . ';background-size:' . h($design['cover_image_fit']) . ';background-repeat:no-repeat;opacity:.32}
.cover-content{position:relative;z-index:1;min-height:250px;padding-top:' . $coverVerticalSpace . 'px}
.cover.dedicated{page-break-after:always}
.cover.template-premium{background:' . $coverColor . ';color:' . $coverTitleColor . ';border-left-color:' . $secondary . '}
.cover.cover-style-premium{background:' . $coverColor . ';border:1px solid ' . $secondary . '}
.cover.cover-style-corporativa{border-left:0;border-top:5px solid ' . $secondary . '}
.cover.cover-style-fotografica{padding:25px}
.cover.cover-style-minimalista{background:' . $coverColor . ';color:' . $coverTitleColor . ';border-left:0;border-top:2px solid ' . $secondary . '}
.cover.cover-style-minimalista h1{color:' . $coverTitleColor . '}
.cover-style-centered{text-align:center}
.cover-style-centered .cover-logo,.cover-style-centered .cover-photo{margin-left:auto;margin-right:auto}
.cover-style-banner{border-left:0;border-bottom:5px solid ' . $secondary . '}
.cover-mark{font-size:10px;letter-spacing:3px;color:' . $secondary . ';font-weight:bold}
.cover-rule{width:54px;height:3px;background:' . $secondary . ';margin:19px 0}
.cover-style-centered .cover-rule{margin-left:auto;margin-right:auto}
.cover-kicker{font-size:8px;letter-spacing:2px;text-transform:uppercase;margin:16px 0 0;color:#eee9ff}
.cover h1{font-family:"' . h($design['title_font']) . '",serif;font-size:' . (int)$design['cover_title_size'] . 'pt;line-height:' . (float)$design['title_line_height'] . ';letter-spacing:' . (float)$design['title_letter_spacing'] . 'px;text-transform:' . ($design['title_transform'] === 'none' ? 'none' : $design['title_transform']) . ';font-weight:' . h($design['title_weight']) . ';margin:10px 0 14px;color:' . $coverTitleColor . ';text-align:' . h($design['title_align']) . ';word-wrap:break-word}
.cover-description{font-family:"' . h($design['subtitle_font']) . '",sans-serif;font-size:' . (int)$design['subtitle_size'] . 'pt;line-height:' . (float)$design['subtitle_line_height'] . ';letter-spacing:' . (float)$design['subtitle_letter_spacing'] . 'px;text-transform:' . ($design['subtitle_transform'] === 'none' ? 'none' : $design['subtitle_transform']) . ';font-weight:' . h($design['subtitle_weight']) . ';color:' . $subtitleColor . ';text-align:' . h($design['subtitle_align']) . ';max-width:490px}
.cover-meta{margin-top:24px;border-top:1px solid rgba(255,255,255,.33);padding-top:10px;color:' . $coverTitleColor . ';font-size:8px}
.cover-meta span{margin-right:15px}
.cover-logo{display:block;max-width:' . (int)$design['logo_size'] . 'px;max-height:' . (int)round($design['logo_size'] * .55) . 'px;width:auto;height:auto;margin:0 auto 9px}
.cover-logo.logo-left{margin-left:0;margin-right:auto}.cover-logo.logo-right{margin-left:auto;margin-right:0}
.cover-photo{display:block;max-width:100%;max-height:210px;width:auto;height:auto;margin:0 0 15px}
.cover-style-centered .cover-photo{margin-bottom:18px}
.empty-catalog{margin-top:17px;padding:13px;border:1px solid ' . $secondary . ';color:' . $textColor . ';background:#ffffff}
.category-group{margin:0 0 ' . $sectionSpacing . 'px}
.product-grid{width:100%;border-collapse:separate;border-spacing:' . $productGap . 'px ' . $productGap . 'px;table-layout:fixed}
.category-title{font-family:"' . h($design['title_font']) . '",serif;font-size:' . max(13, (int)$design['title_size']) . 'pt;text-align:' . h($design['title_align']) . ';color:' . $titleColor . ';border-bottom:' . ($design['show_dividers'] ? '2px solid ' . $secondary : 'none') . ';padding:0 0 6px;margin:11px 0 2px;page-break-after:avoid}
.product-cell{vertical-align:top;padding:0}
.product-card{padding:9px;border:1px solid ' . $borderColor . ';border-radius:' . $radius . 'px;background:' . $cardBackground . ';color:' . $textColor . ';page-break-inside:avoid;overflow:hidden}
.card-style-minimal .product-card{border:0;border-bottom:1px solid ' . $secondary . ';background:transparent}
.card-style-filled .product-card{border:0;background:' . $cardBackground . '}
.template-premium .product-card{border-color:' . $borderColor . ';background:' . $cardBackground . '}
.template-comercial .product-card{border-color:' . $borderColor . ';background:' . $cardBackground . '}
.photo-frame{height:' . $imageHeight . 'px;margin:0 0 8px;background:' . $background . ';text-align:center;line-height:' . $imageHeight . 'px;overflow:hidden;border:1px solid ' . $borderColor . '}
.product-photo{max-width:100%;max-height:' . max(45, $imageHeight - 6) . 'px;width:auto;height:auto;vertical-align:middle}
.photo-placeholder{font-size:8px;color:#89869a}
.product-category{font-family:"' . h($design['category_font']) . '",sans-serif;font-size:' . (int)$design['category_size'] . 'pt;font-weight:' . h($design['category_weight']) . ';letter-spacing:.7px;color:' . $categoryColor . ';text-transform:' . ($design['category_style'] === 'uppercase' ? 'uppercase' : 'none') . ';margin:0 0 4px}
.product-category.category-pill{display:inline-block;padding:2px 5px;border-radius:10px;background:' . $background . '}
.product-name{font-family:"' . h($design['title_font']) . '",serif;font-size:' . (int)$design['title_size'] . 'pt;line-height:' . (float)$design['title_line_height'] . ';letter-spacing:' . (float)$design['title_letter_spacing'] . 'px;text-transform:' . ($design['title_transform'] === 'none' ? 'none' : $design['title_transform']) . ';font-weight:' . h($design['title_weight']) . ';color:' . $titleColor . ';text-align:' . h($design['title_align']) . ';margin:0 0 5px;word-wrap:break-word}
.product-description{font-family:"' . h($design['description_font']) . '",sans-serif;font-size:' . (int)$design['description_size'] . 'pt;line-height:' . (float)$design['description_line_height'] . ';font-weight:' . h($design['description_weight']) . ';color:' . $descriptionColor . ';text-align:' . h($design['description_align']) . ';word-wrap:break-word}
.product-price{font-family:"' . h($design['price_font']) . '",sans-serif;font-size:' . (int)$design['price_size'] . 'pt;font-weight:' . h($design['price_weight']) . ';color:' . $priceColor . ';border-top:1px solid ' . $borderColor . ';padding-top:' . ($design['price_style'] === 'highlight' ? '7px' : '3px') . ';margin-top:' . ($design['price_style'] === 'highlight' ? '8px' : '4px') . ';text-align:' . h($design['price_align']) . '}
.photo-side-layout{width:100%;border-collapse:collapse;table-layout:fixed}.photo-side-layout td{vertical-align:top}.photo-side-layout .photo-side{width:38%;padding-right:8px}.photo-side-layout .photo-frame{height:' . max(70, (int)round($imageHeight * .72)) . 'px;line-height:' . max(70, (int)round($imageHeight * .72)) . 'px}
.divider{height:1px;background:' . $borderColor . ';margin:8px 0}
.product-card-wide{padding:11px}
.product-card-wide .photo-frame{height:' . ($imageHeight + 20) . 'px;line-height:' . ($imageHeight + 20) . 'px}
.product-card-wide .product-photo{max-height:' . max(45, $imageHeight + 14) . 'px}
.product-card-wide .product-name{font-size:14px}
.product-card-wide .product-description{font-size:9px;line-height:1.5}
.product-card-wide .product-price{font-size:15px}
.page-header{position:fixed;left:0;right:0;top:-' . max(8, $marginPoints - 12) . 'px;border-bottom:1px solid ' . $borderColor . ';padding-bottom:4px;font-size:7px;color:' . $textColor . '}
.page-footer{position:fixed;left:0;right:0;bottom:-' . max(14, $marginPoints - 3) . 'px;height:18px;border-top:1px solid ' . $borderColor . ';padding-top:6px;font-size:7px;color:' . $textColor . '}
.page-footer-name{float:left;max-width:75%;white-space:nowrap}
.page-number{float:right}
.page-number:after{content:"Página " counter(page)}
</style></head><body>';

$coverClass = 'cover template-' . $design['template'] . ' cover-style-' . $design['cover_style'] . ' cover-position-' . $design['cover_title_position'] . ($dedicatedCover ? ' dedicated' : '');
if ($design['show_page_header']) $html .= '<header class="page-header">' . $safeCatalogName . '</header>';
$html .= '<section class="' . h($coverClass) . '">';
if ($coverPhoto !== null) $html .= '<div class="cover-photo-bg" aria-hidden="true"></div>';
$html .= '<div class="cover-content">';
$logoClass = 'cover-logo logo-' . $design['logo_position'];
if ($logoPhoto !== null) $html .= '<img class="' . h($logoClass) . '" src="' . h($logoPhoto) . '" alt="">';
if ($design['show_decorations']) $html .= '<div class="cover-mark">CATALOG STUDIO</div><div class="cover-rule"></div>';
if ($design['show_cover_title']) {
    $html .= '<div class="cover-title-wrap">';
    if ($design['show_decorations']) $html .= '<div class="cover-kicker">CATALOGO COMERCIAL</div>';
    $html .= '<h1>' . h($design['cover_title']) . '</h1></div>';
}
if ($design['show_cover_subtitle'] && trim($design['cover_subtitle']) !== '') {
    $html .= '<div class="cover-description">' . nl2br(h($design['cover_subtitle'])) . '</div>';
}
$coverMeta = [];
if ($design['show_cover_date']) $coverMeta[] = 'Generado el ' . $generatedAt->format('d/m/Y');
if ($design['show_cover_category'] && trim((string)$catalog['category']) !== '') $coverMeta[] = h($catalog['category']);
if ($design['show_cover_product_count']) $coverMeta[] = count($products) . ' productos';
if ($coverMeta) $html .= '<div class="cover-meta"><span>' . implode('</span><span>', $coverMeta) . '</span></div>';
if (!$products) $html .= '<div class="empty-catalog">Este catalogo todavia no tiene productos.</div>';
$html .= '</div></section>';
$renderProductCard = static function (array $product, array $design, int $imageHeight, bool $wide = false) use ($primary): string {
    $class = 'product-card ' . ($wide || $design['photo_position'] === 'editorial' ? 'product-card-wide ' : '') . 'card-style-' . $design['card_style'] . ' template-' . $design['template'] . ' photo-position-' . $design['photo_position'] . ' ratio-' . $design['image_ratio'];
    $html = '<article class="' . h($class) . '" style="text-align:' . h($design['text_alignment']) . '">';
    $photo = product_image_data_uri($product['image']);
    $photoHtml = '<div class="photo-frame">';
    if ($photo !== null) $photoHtml .= '<img class="product-photo" src="' . h($photo) . '" alt="">';
    else $photoHtml .= '<span class="photo-placeholder">Sin fotografia</span>';
    $photoHtml .= '</div>';

    $info = '';
    if ($design['show_categories']) {
        $categoryClass = $design['category_style'] === 'pill' ? 'product-category category-pill' : 'product-category';
        $info .= '<div class="' . h($categoryClass) . '">' . h($product['category_name'] ?: 'Sin categoria') . '</div>';
    }
    $info .= '<h3 class="product-name">' . h($product['name']) . '</h3>';
    $productDescription = trim((string)$product['description']);
    if ($design['show_descriptions'] && $productDescription !== '') $info .= '<div class="product-description">' . nl2br(h($productDescription)) . '</div>';
    if ($design['show_dividers'] && $design['show_descriptions'] && $design['show_prices']) $info .= '<div class="divider"></div>';
    if ($design['show_prices']) {
        $price = (float)$product['price'];
        $decimals = floor($price) === $price ? 0 : 2;
        $info .= '<div class="product-price">COP $' . number_format($price, $decimals, ',', '.') . '</div>';
    }

    if (!$wide && in_array($design['photo_position'], ['left', 'right'], true)) {
        $photoCell = '<td class="photo-side">' . $photoHtml . '</td>';
        $infoCell = '<td class="info-side">' . $info . '</td>';
        $html .= '<table class="photo-side-layout"><tr>' . ($design['photo_position'] === 'left' ? $photoCell . $infoCell : $infoCell . $photoCell) . '</tr></table>';
    } else {
        $html .= $photoHtml . $info;
    }
    return $html . '</article>';
};
if ($products) {
    $html .= '<main class="catalog-content">';
    $groups = [];
    if ($design['show_categories']) {
        foreach ($products as $product) {
            $groupName = $product['category_name'] ?: 'Sin categoría';
            $groups[$groupName][] = $product;
        }
    } else {
        $groups[''] = $products;
    }
    foreach ($groups as $groupName => $groupProducts) {
        $html .= '<section class="category-group"><table class="product-grid">';
        if ($design['show_categories']) {
            $html .= '<thead><tr><th colspan="' . $columns . '" class="category-title">' . h($groupName) . '</th></tr></thead>';
        }
        $html .= '<tbody>';
        $productCount = count($groupProducts);
        for ($index = 0; $index < $productCount;) {
            $product = $groupProducts[$index];
            $descriptionLength = mb_strlen(trim((string)$product['description']), 'UTF-8');
            if ($descriptionLength > 400) {
                $html .= '<tr><td class="product-cell" colspan="' . $columns . '">' . $renderProductCard($product, $design, $imageHeight, true) . '</td></tr>';
                $index++;
                continue;
            }

            $html .= '<tr>';
            $slots = 0;
            while ($slots < $columns && $index < $productCount) {
                $nextProduct = $groupProducts[$index];
                if ($slots > 0 && mb_strlen(trim((string)$nextProduct['description']), 'UTF-8') > 400) break;
                if ($slots === 0 && mb_strlen(trim((string)$nextProduct['description']), 'UTF-8') > 400) break;
                $html .= '<td class="product-cell">' . $renderProductCard($nextProduct, $design, $imageHeight) . '</td>';
                $slots++;
                $index++;
            }
            while ($slots < $columns) {
                $html .= '<td class="product-cell"></td>';
                $slots++;
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></section>';
    }
    $html .= '</main>';
}

if ($design['show_page_footer']) {
    $html .= '<footer class="page-footer"><span class="page-footer-name">' . $safeCatalogName . '</span>';
    if ($design['show_page_numbers']) $html .= '<span class="page-number"></span>';
    $html .= '</footer>';
}
$html .= '</body></html>';

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
    $options->set('defaultFont', $font);
    $options->set('chroot', realpath(__DIR__));

    $dompdf = new Dompdf\Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper($design['page_size'], $design['page_orientation']);
    $dompdf->render();
    $download = ($_GET['download'] ?? null) === '1';
    $dompdf->stream('catalogo-' . $catalogId . '.pdf', ['Attachment' => $download]);
} catch (Throwable $e) {
    error_log('Catalog Studio could not render catalog PDF (catalog id ' . $catalogId . ').');
    http_response_code(500);
    exit('No fue posible generar el PDF. Inténtalo de nuevo o vuelve al catálogo.');
}
