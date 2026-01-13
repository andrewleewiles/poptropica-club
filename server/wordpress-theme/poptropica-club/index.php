<?php get_header(); ?>

<?php if (have_posts()) : ?>

    <?php if (is_home() && !is_front_page()) : ?>
        <header class="page-header">
            <h1 class="page-title"><?php single_post_title(); ?></h1>
        </header>
    <?php endif; ?>

    <?php while (have_posts()) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <?php if (is_singular()) : ?>
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                <?php else : ?>
                    <h2 class="entry-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>
                <?php endif; ?>

                <div class="entry-meta">
                    <span class="posted-on">
                        <?php echo get_the_date(); ?>
                    </span>
                    <span class="posted-by">
                        by <?php the_author(); ?>
                    </span>
                    <?php if (has_category()) : ?>
                        <span class="categories">
                            in <?php the_category(', '); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </header>

            <?php if (has_post_thumbnail() && !is_singular()) : ?>
                <div class="entry-thumbnail">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail('large'); ?>
                    </a>
                </div>
            <?php elseif (has_post_thumbnail() && is_singular()) : ?>
                <div class="entry-thumbnail">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>

            <div class="entry-content">
                <?php
                if (is_singular()) :
                    the_content();
                else :
                    the_excerpt();
                    ?>
                    <a href="<?php the_permalink(); ?>" class="read-more">Read More</a>
                <?php endif; ?>
            </div>

            <?php if (is_singular()) : ?>
                <footer class="entry-footer">
                    <?php if (has_tag()) : ?>
                        <div class="tags">
                            <?php the_tags('Tags: ', ', '); ?>
                        </div>
                    <?php endif; ?>
                </footer>
            <?php endif; ?>
        </article>

    <?php endwhile; ?>

    <?php the_posts_pagination(array(
        'prev_text' => '&laquo; Previous',
        'next_text' => 'Next &raquo;',
    )); ?>

<?php else : ?>

    <article class="no-posts">
        <h2>No posts found</h2>
        <p>It looks like there's nothing here yet. Check back soon for updates!</p>
    </article>

<?php endif; ?>

<?php if (is_singular() && comments_open()) : ?>
    <?php comments_template(); ?>
<?php endif; ?>

<?php get_footer(); ?>
