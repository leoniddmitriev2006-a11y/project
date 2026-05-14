<?php
require __DIR__ . '/config/db.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($productId <= 0) {
    http_response_code(404);
    $pageTitle = 'Товар не найден';
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="card">
        <p style="color:#888;">Товар не найден.</p>
        <a href="products.php" class="btn btn-ghost">← Вернуться к товарам</a>
    </div>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$stmt = $pdo->prepare('SELECT p.id, p.name, p.description, p.price, p.image_path, p.created_at, p.gender, p.category, u.name AS author_name FROM products p JOIN users u ON u.id = p.user_id WHERE p.id = :id LIMIT 1');
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Товар не найден';
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="card">
        <p style="color:#888;">Товар не найден.</p>
        <a href="products.php" class="btn btn-ghost">← Вернуться к товарам</a>
    </div>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $product['name'];
$bodyClass = 'product-detail-body';
$topMenuExtraClass = 'product-detail-page__menu';
$wrapMain = false;

$backUrl = 'products.php';
if (!empty($product['gender']) && !empty($product['category'])) {
    $backUrl = 'products.php?gender=' . urlencode($product['gender']) . '&category=' . urlencode($product['category']);
}

require __DIR__ . '/includes/header.php';
?>
<div class="page-shell product-detail-shell">
<section class="product-detail-page" aria-label="Карточка товара">
    <div class="product-detail">
        <div class="product-detail__gallery">
            <button class="product-detail__nav product-detail__nav--prev" type="button" aria-label="Предыдущее изображение">‹</button>
            <div class="product-detail__image-wrap">
                <?php if (!empty($product['image_path'])): ?>
                    <img class="product-detail__image" src="<?php echo htmlspecialchars($product['image_path'], ENT_QUOTES, 'UTF-8'); ?>"
                         alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php else: ?>
                    <div class="product-detail__image-empty">Нет изображения</div>
                <?php endif; ?>
            </div>
            <button class="product-detail__nav product-detail__nav--next" type="button" aria-label="Следующее изображение">›</button>
        </div>

        <div class="product-detail__info">
            <h1 class="product-detail__title"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="product-detail__price"><?php echo number_format((float)$product['price'], 0, '.', ' '); ?> ₽</p>

            <div class="product-detail__desc">
                <?php echo nl2br(htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8')); ?>
            </div>

            <div class="product-detail__sizes">
                <div class="product-detail__sizes-label">Размер</div>
                <div class="product-detail__sizes-row">
                    <button class="product-detail__size" type="button">S</button>
                    <button class="product-detail__size" type="button">M</button>
                    <button class="product-detail__size" type="button">L</button>
                    <button class="product-detail__size" type="button">XL</button>
                </div>
            </div>

            <a href="cart.php?action=add&id=<?php echo (int)$product['id']; ?>" class="product-detail__buy">КУПИТЬ</a>
        </div>
    </div>

    <div class="product-detail__footer">
        <a href="<?php echo htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-ghost product-detail__back">← Вернуться к товарам</a>
    </div>
</section>
</div>

<?php
require __DIR__ . '/includes/footer.php';
