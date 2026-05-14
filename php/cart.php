<?php
require __DIR__ . '/config/db.php';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart = &$_SESSION['cart'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($productId > 0) {
        if ($action === 'remove') {
            unset($cart[$productId]);
            $message = 'Товар удалён из корзине.';
        } elseif ($action === 'decrease' && isset($cart[$productId])) {
            $cart[$productId]--;
            if ($cart[$productId] <= 0) {
                unset($cart[$productId]);
                $message = 'Товар удалён из корзине.';
            }
        } elseif ($action === 'increase') {
            if (!isset($cart[$productId])) {
                $cart[$productId] = 0;
            }
            $cart[$productId]++;
        }
    }
}

if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'add') {
    $productId = (int)$_GET['id'];
    if ($productId > 0) {
        if (!isset($cart[$productId])) {
            $cart[$productId] = 0;
        }
        $cart[$productId]++;
        header('Location: cart.php');
        exit;
    }
}

$products = [];
$total = 0;

if ($cart) {
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, name, price, image_path FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    foreach ($products as $product) {
        $id = (int)$product['id'];
        $quantity = $cart[$id] ?? 0;
        $total += $quantity * (float)$product['price'];
    }
}

$pageTitle = 'Корзина';

require __DIR__ . '/includes/header.php';
?>

<div class="card cart-page" style="max-width:960px;margin:0 auto;padding:24px;">
    <h1 class="card-title">Корзина</h1>

    <?php if ($message): ?>
        <div class="success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if (empty($products)): ?>
        <p class="cart-empty">Корзина пустая.</p>
        <a href="products.php" class="btn btn-primary">Перейти к товарам</a>
    <?php else: ?>
        <div class="cart-list">
            <?php foreach ($products as $product): ?>
                <?php $id = (int)$product['id']; $quantity = $cart[$id] ?? 0; ?>
                <article class="cart-item">
                    <div class="cart-item__media">
                        <?php if (!empty($product['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php else: ?>
                            <div class="cart-item__empty">Нет фото</div>
                        <?php endif; ?>
                    </div>
                    <div class="cart-item__side">
                        <div>
                            <h2 class="cart-item__title"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                            <p class="cart-item__meta">Size: M</p>
                            <div class="cart-item__price-block">
                                <span class="cart-item__price-main"><?php echo number_format((float)$product['price'] * $quantity, 0, '.', ' '); ?> ₽</span>
                                <span class="cart-item__price-sub">/ <?php echo number_format((float)$product['price'], 0, '.', ' '); ?> ₽</span>
                            </div>
                        </div>
                        <div class="cart-item__controls">
                            <div class="cart-item__quantity-controls">
                                <form method="post" class="cart-item__quantity-form">
                                    <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                    <input type="hidden" name="action" value="decrease">
                                    <button type="submit" class="cart-item__qty-btn">−</button>
                                </form>
                                <span class="cart-item__quantity-number"><?php echo $quantity; ?></span>
                                <form method="post" class="cart-item__quantity-form">
                                    <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                    <input type="hidden" name="action" value="increase">
                                    <button type="submit" class="cart-item__qty-btn">+</button>
                                </form>
                            </div>
                            <div class="cart-item__purchase">
                                <button class="btn btn-primary cart-item__buy" type="button">Оформить заказ</button>
                            </div>
                            <div class="cart-item__actions">
                                <form method="post">
                                    <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="btn btn-ghost">Удалить</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary">
            <div class="cart-summary__total">Итого: <?php echo number_format($total, 0, '.', ' '); ?> ₽</div>
            <button class="btn btn-primary" type="button">Оформить заказ</button>
        </div>
    <?php endif; ?>
</div>

<?php
require __DIR__ . '/includes/footer.php';
