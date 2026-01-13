<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/assets/logo.png">
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

    <!-- Main Content Container -->
    <main class="cloud-container">
        <!-- Background layers -->
        <img src="<?php echo get_template_directory_uri(); ?>/assets/ocean.png" alt="" class="bg-layer bg-ocean" aria-hidden="true">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/trees.png" alt="" class="bg-layer bg-trees" aria-hidden="true">

        <!-- Cloud borders -->
        <img src="<?php echo get_template_directory_uri(); ?>/assets/cloudTop.png" alt="" class="cloud-border cloud-top" aria-hidden="true">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/cloudBottom.png" alt="" class="cloud-border cloud-bottom" aria-hidden="true">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/cloudLeft.png" alt="" class="cloud-border cloud-left" aria-hidden="true">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/cloudRight.png" alt="" class="cloud-border cloud-right" aria-hidden="true">

        <!-- Content inside cloud -->
        <div class="cloud-content">
            <div class="site-main">
