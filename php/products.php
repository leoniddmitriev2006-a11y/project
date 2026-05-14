<?php
require __DIR__ . '/config/db.php';

$allowedGenders = [
    'men' => 'Мужская одежда',
    'women' => 'Женская одежда',
];

$allowedCategories = [
    'jeans' => 'Джинсы',
    'hoodie' => 'Худи',
    'bottom' => 'Низ',
    'top' => 'Верх',
];

$gender = isset($_GET['gender']) ? trim($_GET['gender']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$pageTitle = 'Товары — Каталог';
$pageSubtitle = '';

$where = [];
$params = [];

if (isset($allowedGenders[$gender])) {
    $where[] = 'p.gender = :gender';
    $params['gender'] = $gender;
    $pageSubtitle = $allowedGenders[$gender];
}

if (isset($allowedCategories[$category])) {
    $where[] = 'p.category = :category';
    $params['category'] = $category;
    $pageSubtitle .= $pageSubtitle ? ' — ' . $allowedCategories[$category] : $allowedCategories[$category];
}

if ($pageSubtitle) {
    $pageTitle = 'Каталог — ' . $pageSubtitle;
}

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$order = 'p.created_at DESC';

switch ($sort) {
    case 'price_asc':
        $order = 'p.price ASC';
        break;
    case 'price_desc':
        $order = 'p.price DESC';
        break;
    case 'newest':
    default:
        $order = 'p.created_at DESC';
        break;
}

$sql = 'SELECT p.id, p.name, p.description, p.price, p.image_path, p.gender, p.category FROM products p';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY ' . $order . ' LIMIT 100';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="product-list-page" aria-label="Каталог товаров">
    <?php if (!empty($pageSubtitle)): ?>
        <div class="product-list-page__title" style="max-width:1320px;margin:0 auto 20px;color:#fff;font-size:18px;font-weight:700;"><?php echo htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <div class="product-filters" aria-label="Фильтры">
        <button class="product-filters__item" type="button" data-sort="toggle" aria-label="Сортировка по цене">
            Цена
            <span class="product-filters__arrow">⌄</span>
        </button>
        <div class="product-filters__menu" style="display: none;">
            <button class="product-filters__option" data-sort="price_asc">От меньшего к большему</button>
            <button class="product-filters__option" data-sort="price_desc">От большего к меньшему</button>
        </div>
    </div>

    <?php if (!$products): ?>
        <p style="color:#888;max-width:1320px;margin:0 auto;">Товаров ещё нет.</p>
    <?php else: ?>
        <section class="product-grid" aria-label="Список товаров">
            <?php foreach ($products as $product): ?>
                <a class="product-card" href="product.php?id=<?php echo (int)$product['id']; ?>" aria-label="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="product-card__media">
                        <?php if (!empty($product['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:#111;display:flex;align-items:center;justify-content:center;color:#555;font-size:13px;">Нет изображения</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-card__meta">
                        <h2 class="product-card__title"><?php echo htmlspecialchars(mb_strtoupper($product['name'], 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="product-card__price"><?php echo number_format((float)$product['price'], 0, '.', ' '); ?> РУБЛЕЙ</p>
                    </div>
                </a>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterBtn = document.querySelector('[data-sort="toggle"]');
    const filterMenu = document.querySelector('.product-filters__menu');
    const filterOptions = document.querySelectorAll('.product-filters__option');

    if (filterBtn) {
        filterBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (filterMenu.style.display === 'none' || !filterMenu.style.display) {
                filterMenu.style.display = 'flex';
            } else {
                filterMenu.style.display = 'none';
            }
        });
    }

    filterOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const sortValue = this.getAttribute('data-sort');
            const url = new URL(window.location);
            url.searchParams.set('sort', sortValue);
            window.location = url.toString();
        });
    });

    document.addEventListener('click', function(e) {
        if (filterMenu && !e.target.closest('.product-filters')) {
            filterMenu.style.display = 'none';
        }
    });
});
</script>

<?php
require __DIR__ . '/includes/footer.php';
