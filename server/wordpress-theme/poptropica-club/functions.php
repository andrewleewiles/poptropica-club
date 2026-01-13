<?php
/**
 * Poptropica Club Theme Functions
 */

// Theme Setup
function poptropica_club_setup() {
    // Add title tag support
    add_theme_support('title-tag');

    // Add featured image support
    add_theme_support('post-thumbnails');

    // Add HTML5 support
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));

    // Register navigation menu
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'poptropica-club'),
    ));

    // Custom logo support
    add_theme_support('custom-logo', array(
        'height'      => 200,
        'width'       => 600,
        'flex-height' => true,
        'flex-width'  => true,
    ));
}
add_action('after_setup_theme', 'poptropica_club_setup');

// Enqueue styles and scripts
function poptropica_club_scripts() {
    wp_enqueue_style('poptropica-club-style', get_stylesheet_uri(), array(), '1.3');

    // Google Fonts (optional)
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap', array(), null);

    // Logo animation script
    wp_enqueue_script('logo-animation', get_template_directory_uri() . '/js/logo-animation.js', array(), '1.0', true);
}
add_action('wp_enqueue_scripts', 'poptropica_club_scripts');

// Register sidebar
function poptropica_club_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'poptropica-club'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here.', 'poptropica-club'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'poptropica_club_widgets_init');

// Custom excerpt length
function poptropica_club_excerpt_length($length) {
    return 40;
}
add_filter('excerpt_length', 'poptropica_club_excerpt_length');

// Custom excerpt more
function poptropica_club_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'poptropica_club_excerpt_more');
