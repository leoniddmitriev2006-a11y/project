<?php
header('Location: admin.php');
exit;
$errors = [];
$name = '';
$description = '';
$price = '';
$imagePath = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $imagePath = trim($_POST['image_path'] ?? '');

    if ($name === '') {
        $errors[] = 'Введите название товара.';
    } else if (mb_strlen($name, 'UTF-8') > 200) {
        $errors[] = 'Название не должно быть длиннее 200 символов.';
    }

    if ($description === '') {
        $errors[] = 'Введите описание товара.';
    }

    if ($price === '' || !is_numeric(str_replace(',', '.', $price)) || (float)str_replace(',', '.', $price) <= 0) {
        $errors[] = 'Введите корректную цену (больше 0).';
    }

    if (!$errors) {
        $insert = $pdo->prepare('INSERT INTO products (user_id, name, description, price, image_path) VALUES (:user_id, :name, :description, :price, :image_path)');
        $insert->execute([
            'user_id' => $userId,
            'name' => $name,
            'description' => $description,
            'price' => str_replace(',', '.', $price),
            'image_path' => $imagePath ?: null,
        ]);

        $productId = (int)$pdo->lastInsertId();
        header('Location: product.php?id=' . $productId);
        exit;
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="card" style="max-width:600px;">
    <h1 class="card-title">Добавить новый товар</h1>

    <?php if ($errors): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="add_product.php" novalidate>
        <div>
            <label for="name">Название товара *</label>
            <input id="name" name="name" type="text" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" maxlength="200" required>
        </div>

        <div>
            <label for="description">Описание *</label>
            <textarea id="description" name="description" rows="8" required><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>
            <small style="color:#666;font-size:11px;">Опишите товар, его особенности и характеристики</small>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div>
                <label for="price">Цена (₽) *</label>
                <input id="price" name="price" type="text" value="<?php echo htmlspecialchars($price, ENT_QUOTES, 'UTF-8'); ?>" placeholder="1000" required>
            </div>
            <div>
                <label for="image_path">URL изображения</label>
                <input id="image_path" name="image_path" type="text" value="<?php echo htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8'); ?>" placeholder="../img/pants.png">
                <small style="color:#666;font-size:11px;">Путь от корня проекта</small>
            </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn btn-primary">Сохранить товар</button>
            <a href="products.php" class="btn btn-ghost">Отмена</a>
        </div>
    </form>
</div>

<?php
require __DIR__ . '/includes/footer.php';
