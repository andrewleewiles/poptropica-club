<?php get_header(); ?>

<article class="error-404 not-found">
    <header class="page-header">
        <h1 class="page-title">Oops! Page Not Found</h1>
    </header>

    <div class="page-content">
        <p>It looks like this page got lost on one of the islands! Try heading back to the <a href="<?php echo esc_url(home_url('/')); ?>">blog home</a> or use the navigation above.</p>

        <div class="search-form-container">
            <?php get_search_form(); ?>
        </div>
    </div>
</article>

<?php get_footer(); ?>
