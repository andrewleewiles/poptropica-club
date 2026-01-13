<?php get_header(); ?>

<?php while (have_posts()) : the_post(); ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <header class="entry-header">
            <h1 class="entry-title"><?php the_title(); ?></h1>

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

        <?php if (has_post_thumbnail()) : ?>
            <div class="entry-thumbnail">
                <?php the_post_thumbnail('large'); ?>
            </div>
        <?php endif; ?>

        <div class="entry-content">
            <?php the_content(); ?>
        </div>

        <footer class="entry-footer">
            <?php if (has_tag()) : ?>
                <div class="tags">
                    <?php the_tags('Tags: ', ', '); ?>
                </div>
            <?php endif; ?>

            <nav class="post-navigation">
                <div class="nav-links">
                    <?php
                    previous_post_link('<div class="nav-previous">%link</div>', '&laquo; %title');
                    next_post_link('<div class="nav-next">%link</div>', '%title &raquo;');
                    ?>
                </div>
            </nav>
        </footer>
    </article>

<?php endwhile; ?>

<?php if (comments_open() || get_comments_number()) : ?>
    <?php comments_template(); ?>
<?php endif; ?>

<?php get_footer(); ?>
