<?php
/**
 * Template Name: Peer Session Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="primary" class="site-main">
    <article <?php post_class(); ?>>
        <header class="entry-header">
            <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
        </header>
        <div class="entry-content">
            <?php echo do_shortcode('[peer_mentoring_showcase]'); ?>
        </div>
    </article>
</main>
<?php
get_footer();