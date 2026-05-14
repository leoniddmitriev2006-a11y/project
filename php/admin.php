<?php
require __DIR__ . '/config/db.php';

$pageTitle = 'Админка';
$errors = [];
$success = '';

$allowedGenders = [
    'men' => 'Мужские товары',
    'women' => 'Женские товары',
];

$allowedCategories = [
    'jeans' => 'Джинсы',
    'hoodie' => 'Худи',
    'bottom' => 'Низ',
    'top' => 'Верх',
];

$adminLogin = 'admin123';
$adminPassword = '22loltop22';
$adminUserId = 1;

$stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $adminUserId]);
if (!$stmt->fetch()) {
    $stmt = $pdo->prepare('INSERT INTO users (id, name, email, password_hash, role) VALUES (:id, :name, :email, :password_hash, :role)');
    $stmt->execute([
        'id' => $adminUserId,
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password_hash' => password_hash($adminPassword, PASSWORD_DEFAULT),
        'role' => 'admin',
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_action'])) {
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($login === $adminLogin && $password === $adminPassword) {
        $_SESSION['user_role'] = 'admin';
        $_SESSION['user_id'] = $adminUserId;
        $_SESSION['user_name'] = 'Admin';
        header('Location: admin.php');
        exit;
    }

    $errors[] = 'Неверный логин или пароль.';
}

if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $pageTitle = 'Вход в админку';
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="card" style="max-width:420px;margin:30px auto;padding:24px;">
        <h1 class="card-title">Вход в админку</h1>
        <p style="color:#888;margin-bottom:20px;">Используйте только логин и пароль администратора.</p>
        <?php if ($errors): ?>
            <div class="error"><ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul></div>
        <?php endif; ?>
        <form method="post" action="admin.php" novalidate>
            <input type="hidden" name="login_action" value="1">
            <div>
                <label for="login">Логин</label>
                <input id="login" name="login" type="text" required>
            </div>
            <div style="margin-top:16px;">
                <label for="password">Пароль</label>
                <input id="password" name="password" type="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:20px;">Войти</button>
        </form>
    </div>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($action === 'delete' && $productId > 0) {
        $delete = $pdo->prepare('DELETE FROM products WHERE id = :id');
        $delete->execute(['id' => $productId]);
        unset($_SESSION['cart'][$productId]);
        $success = 'Товар удалён.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $imagePath = '';

        if ($name === '') {
            $errors[] = 'Введите название товара.';
        }
        if ($description === '') {
            $errors[] = 'Введите описание товара.';
        }
        if (!isset($allowedGenders[$gender])) {
            $errors[] = 'Выберите пол товара.';
        }
        if (!isset($allowedCategories[$category])) {
            $errors[] = 'Выберите категорию товара.';
        }

        $normalizedPrice = str_replace([' ', ','], ['', '.'], $price);
        if ($price === '' || !is_numeric($normalizedPrice) || (float)$normalizedPrice <= 0) {
            $errors[] = 'Введите корректную цену.';
        }

        if (!empty($_FILES['image_file']['name'])) {
        $uploadDir = __DIR__ . '/../img/uploads';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
            $errors[] = 'Не удалось создать папку для загрузки изображений.';
        }

        if (!$errors) {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $fileType = $_FILES['image_file']['type'];
            if (!isset($allowed[$fileType])) {
                $errors[] = 'Неподдерживаемый формат изображения. Используйте JPG, PNG или WEBP.';
            } elseif ($_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Ошибка загрузки изображения.';
            } else {
                $filename = uniqid('prod_', true) . '.' . $allowed[$fileType];
                $targetPath = $uploadDir . '/' . $filename;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetPath)) {
                    $imagePath = '../img/uploads/' . $filename;
                } else {
                    $errors[] = 'Не удалось сохранить изображение.';
                }
            }
        }
    }

    if (!$errors) {
        $insert = $pdo->prepare('INSERT INTO products (user_id, name, description, price, image_path, gender, category) VALUES (:user_id, :name, :description, :price, :image_path, :gender, :category)');
        $insert->execute([
            'user_id' => $adminUserId,
            'name' => $name,
            'description' => $description,
            'price' => $normalizedPrice,
            'image_path' => $imagePath ?: null,
            'gender' => $gender,
            'category' => $category,
        ]);

        $success = 'Товар успешно добавлен в каталог.';
    }
    }
}

$products = $pdo->query('SELECT id, name, price, image_path, created_at, gender, category FROM products ORDER BY created_at DESC LIMIT 50')->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="card" style="max-width:960px;margin:0 auto;padding:24px;">
    <h1 class="card-title">Админка</h1>
    <p style="color:#888;margin:0 0 20px;">Здесь можно загрузить товар, добавить фото и цену.</p>

    <?php if ($errors): ?>
        <div class="error"><ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="post" action="admin.php" enctype="multipart/form-data" novalidate>
        <div>
            <label for="name">Название товара</label>
            <input id="name" name="name" type="text" required>
        </div>
        <div>
            <label for="description">Описание</label>
            <textarea id="description" name="description" rows="5" required></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div>
                <label for="gender">Пол</label>
                <select id="gender" name="gender" required>
                    <option value="">Выберите...</option>
                    <option value="men">Мужское</option>
                    <option value="women">Женское</option>
                </select>
            </div>
            <div>
                <label for="category">Категория</label>
                <select id="category" name="category" required>
                    <option value="">Выберите...</option>
                    <option value="jeans">Джинсы</option>
                    <option value="hoodie">Худи</option>
                    <option value="bottom">Низ</option>
                    <option value="top">Верх</option>
                </select>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px; margin-top:16px;">
            <div>
                <label for="price">Цена (₽)</label>
                <input id="price" name="price" type="text" placeholder="10000" required>
            </div>
            <div>
                <label for="image_file">Изображение</label>
                <div class="file-dropzone" id="fileDropzone">
                    <input id="image_file" name="image_file" type="file" accept="image/jpeg,image/png,image/webp" style="display:none;">
                    <div class="file-dropzone__content">
                        <span class="file-dropzone__icon">📁</span>
                        <span class="file-dropzone__text">Перетащите файл сюда или кликните</span>
                        <span class="file-dropzone__helper" id="fileDropzoneLabel">Файл не выбран</span>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:20px;">Загрузить товар</button>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var fileInput = document.getElementById('image_file');
        var dropzone = document.getElementById('fileDropzone');
        var label = document.getElementById('fileDropzoneLabel');
        var priceInput = document.getElementById('price');

        function updateLabel(fileName) {
            label.textContent = fileName ? fileName : 'Файл не выбран';
        }

        function formatPrice(value) {
            var digits = value.replace(/[^0-9]/g, '');
            if (!digits) return '';
            return digits.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        }

        if (dropzone) {
            dropzone.addEventListener('click', function () {
                fileInput.click();
            });
            dropzone.addEventListener('dragover', function (event) {
                event.preventDefault();
                dropzone.classList.add('file-dropzone--active');
            });
            dropzone.addEventListener('dragleave', function () {
                dropzone.classList.remove('file-dropzone--active');
            });
            dropzone.addEventListener('drop', function (event) {
                event.preventDefault();
                dropzone.classList.remove('file-dropzone--active');
                if (event.dataTransfer.files.length) {
                    fileInput.files = event.dataTransfer.files;
                    updateLabel(event.dataTransfer.files[0].name);
                }
            });
        }

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                updateLabel(fileInput.files.length ? fileInput.files[0].name : 'Файл не выбран');
            });
        }

        if (priceInput) {
            priceInput.addEventListener('input', function () {
                var cursor = priceInput.selectionStart;
                var before = priceInput.value;
                priceInput.value = formatPrice(priceInput.value);
                if (priceInput.value.length > before.length) {
                    cursor += priceInput.value.length - before.length;
                }
                priceInput.setSelectionRange(cursor, cursor);
            });
        }
    });
    </script>

    <?php if ($products): ?>
        <div class="card" style="margin-top:30px;">
            <h2 class="card-title">Последние товары</h2>
            <div style="display:grid;gap:12px;">
                <?php foreach ($products as $product): ?>
                    <div style="display:grid;grid-template-columns:1fr auto;align-items:center;padding:14px 0;border-bottom:1px solid #222;gap:14px;">
                        <div>
                            <strong style="display:block;color:#fff;font-size:14px;"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span style="color:#888;font-size:12px;"><?php echo number_format((float)$product['price'], 0, '.', ' '); ?> ₽</span>
                            <span style="color:#888;font-size:11px;display:block;margin-top:4px;">
                                <?php echo htmlspecialchars($allowedGenders[$product['gender']] ?? '', ENT_QUOTES, 'UTF-8'); ?> / <?php echo htmlspecialchars($allowedCategories[$product['category']] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <form method="post" style="margin:0;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                                <button type="submit" class="btn btn-ghost" style="padding:10px 14px;">Удалить</button>
                            </form>
                            <a href="product.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-primary" style="padding:10px 14px;">Открыть</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require __DIR__ . '/includes/footer.php';