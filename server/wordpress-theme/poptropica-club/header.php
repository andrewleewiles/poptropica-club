<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/assets/logo.png">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4055787725414950" crossorigin="anonymous"></script>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="page-wrapper">
    <!-- Header with Logo -->
    <header class="site-header">
        <div id="logo-container" class="logo-container" data-svg-url="<?php echo get_template_directory_uri(); ?>/assets/pcLogo2.svg" role="img" aria-label="Messy Sinker's Poptropica Club">
            <!-- SVG will be injected here by JavaScript -->
            <noscript>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/pcLogo2.svg" alt="Poptropica Club" style="max-width: 100%; height: auto;">
            </noscript>
        </div>

        <?php if (has_nav_menu('primary')) : ?>
        <nav class="main-navigation">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'menu_id'        => 'primary-menu',
                'container'      => false,
            ));
            ?>
        </nav>
        <?php endif; ?>
    </header>

    <!-- Menu Bar -->
    <nav class="menu-bar">
        <div class="menu-bar-inner">
            <a href="https://poptropica.club" class="menu-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/menu/about@3x.png" alt="About" class="menu-icon">
                <span class="menu-label">ABOUT</span>
            </a>
            <a href="https://blog.poptropica.club" class="menu-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/menu/blog@3x.png" alt="Dev Blog" class="menu-icon">
                <span class="menu-label">DEV BLOG</span>
            </a>
            <a href="https://poptropica.wiki/index.php/Category:Island_Guides" class="menu-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/menu/islandGuides@3x.png" alt="Island Guides" class="menu-icon">
                <span class="menu-label">ISLAND GUIDES</span>
            </a>
            <a href="https://legacy.poptropica.club" class="menu-item menu-item-primary">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/menu/download@3x.png" alt="Download Legacy" class="menu-icon menu-icon-large">
                <span class="menu-label">DOWNLOAD LEGACY</span>
            </a>
            <a href="https://poptropica.wiki" class="menu-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/menu/wiki@3x.png" alt="Wiki" class="menu-icon">
                <span class="menu-label">WIKI</span>
            </a>
            <a href="https://discord.gg/XkQ5ww8BhE" class="menu-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/menu/discord@3x.png" alt="Chat" class="menu-icon">
                <span class="menu-label">DISCORD</span>
            </a>
            <a href="https://ko-fi.com/wilescreative" class="menu-item">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/menu/support@3x.png" alt="Support Us" class="menu-icon">
                <span class="menu-label">SUPPORT US</span>
            </a>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="cloud-container">
        <!-- Background layers -->
        <img src="<?php echo get_template_directory_uri(); ?>/assets/ocean.png" alt="" class="bg-layer bg-ocean" aria-hidden="true">
        <div class="islands-container">
            <img src="" alt="" class="island island-1" aria-hidden="true">
            <img src="" alt="" class="island island-2" aria-hidden="true">
            <img src="" alt="" class="island island-3" aria-hidden="true">
        </div>
        <img src="<?php echo get_template_directory_uri(); ?>/assets/trees.png" alt="" class="bg-layer bg-trees" aria-hidden="true">

        <!-- Dynamic Cloud Border -->
        <svg class="cloud-border-svg" data-path-url="<?php echo get_template_directory_uri(); ?>/assets/cloudPath.svg" aria-hidden="true"></svg>

        <!-- Content inside cloud -->
        <div class="cloud-content">
            <div class="site-main">
