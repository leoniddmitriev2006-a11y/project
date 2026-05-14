<?php
if (!defined('PRODUCT_APP_INCLUDED')) {
    define('PRODUCT_APP_INCLUDED', true);
}
if (!isset($pageTitle)) {
    $pageTitle = 'Soulja Clothing';
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> — Soulja Clothing</title>
    <link rel="stylesheet" href="../style.css?v=3">
    <style>
        body {
            background: #020202;
            color: #fff;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            overflow-x: hidden;
        }
        
        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .app-header {
            background: #000;
            border-bottom: 1px solid #222;
            padding: 14px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .app-header__inner {
            max-width: 1120px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }
        
        .app-header__logo {
            font-weight: 700;
            font-size: 14px;
            color: #fff;
            text-decoration: none;
            letter-spacing: 1px;
        }
        
        .app-header__nav {
            display: flex;
            gap: 20px;
            align-items: center;
            flex: 1;
        }
        
        .app-header__nav a {
            color: #ccc;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.3s;
        }
        
        .app-header__nav a:hover {
            color: #fff;
        }
        
        .app-header__user-menu {
            display: flex;
            gap: 14px;
            align-items: center;
        }
        
        .app-header__user-menu a {
            color: #ccc;
            text-decoration: none;
            font-size: 12px;
            transition: color 0.3s;
            padding: 6px 12px;
            border: 1px solid transparent;
            border-radius: 3px;
        }
        
        .app-header__user-menu a:hover {
            color: #fff;
            border-color: #333;
        }
        
        .app-content {
            flex: 1;
            max-width: 1120px;
            margin: 0 auto;
            width: 100%;
            padding: 24px 20px;
        }
        
        .card {
            background: #111;
            border: 1px solid #222;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 16px 0;
            color: #fff;
        }
        
        .card-meta {
            font-size: 12px;
            color: #888;
            margin-bottom: 14px;
        }
        
        .card-image {
            margin: 16px 0;
        }
        
        .card-image img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 2px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 18px;
            border: 1px solid #333;
            background: transparent;
            color: #ccc;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn:hover {
            background: #222;
            color: #fff;
            border-color: #555;
        }
        
        .btn-primary {
            background: #fff;
            color: #000;
            border-color: #fff;
        }
        
        .btn-primary:hover {
            background: #ccc;
            border-color: #ccc;
        }
        
        .btn-ghost {
            border-color: #444;
        }
        
        
        .error {
            background: #3a1f1f;
            border: 1px solid #8b2e2e;
            color: #ffb3b3;
            padding: 12px;
            border-radius: 3px;
            margin-bottom: 16px;
        }
        
        .error ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .error li {
            margin-bottom: 4px;
        }
        
        .success {
            background: #1f3a2f;
            border: 1px solid #2e8b5e;
            color: #b3ffb3;
            padding: 12px;
            border-radius: 3px;
            margin-bottom: 16px;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            background: #1a1a1a;
            border: 1px solid #333;
            color: #fff;
            border-radius: 3px;
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin-top: 4px;
            margin-bottom: 12px;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #555;
            background: #222;
        }
        
        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #ccc;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .pagination {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin: 20px 0;
        }
        
        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #333;
            border-radius: 3px;
            text-decoration: none;
            color: #ccc;
            font-size: 12px;
            transition: all 0.3s;
        }
        
        .pagination a:hover {
            background: #222;
            color: #fff;
        }
        
        .pagination span.active {
            background: #fff;
            color: #000;
            border-color: #fff;
        }
    </style>
</head>
<body class="<?php echo isset($bodyClass) ? htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') : ''; ?>">
    <div class="page-wrapper">
<?php
$wrapMain = $wrapMain ?? true;
$cartButtonHref = 'cart.php';
if (!empty($cartAddProductId)) {
    $cartButtonHref = 'cart.php?action=add&id=' . (int)$cartAddProductId;
}
?>
        <header class="top-menu<?php echo !empty($topMenuExtraClass) ? ' ' . htmlspecialchars($topMenuExtraClass, ENT_QUOTES, 'UTF-8') : ''; ?>" aria-label="Quick actions">
            <div class="top-menu__inner">
                <a class="top-menu__profile" href="../index.html" aria-label="На главную">
                    <img src="../img/icon.png" alt="Profile">
                </a>
                <div class="top-menu__actions">
                    <a class="top-menu__action top-menu__action--bag" href="cart.php" aria-label="Корзина">
                        <svg viewBox="0 0 74 86" aria-hidden="true">
                            <path d="M14.5 24.5h45l3.5 50.5c.4 5.4-3.8 10-9.2 10H20.2c-5.4 0-9.6-4.6-9.2-10l3.5-50.5Zm11-1.5v-5.8C25.5 7.7 30.9 2 37 2s11.5 5.7 11.5 15.2V23m-23 0h23"/>
                        </svg>
                    </a>
                </div>
            </div>
        </header>
<?php if ($wrapMain): ?>
        <main class="app-content">
<?php endif; ?>
