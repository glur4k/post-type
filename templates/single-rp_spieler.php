<?php
/*
 * Template fuer einen einzelnen Spieler
 */
?>

<?php
  $zeige = get_query_var('rp_spieler_mannschaft');
?>

<?php get_header(); ?>
  <section id="content" class="clearfix">
    <div class="content-header-sep"></div>
    <div class="page page-full">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <?php if (wp_get_attachment_image_src(get_post_thumbnail_id(), 'post-thumbnail') !== false): ?>
          <div class="spieler-portrait-wrapper"><?php the_post_thumbnail(); ?></div>
        <?php else: ?>
          <div class="spieler-portrait-wrapper portrait-wrapper-no-img"><?php echo ParserUtils::baueTitelKuerzel(get_the_title()); ?></div>
        <?php endif; ?>

        <?php
          echo $zeige;
        ?>

        <?php echo get_the_content(); ?>

      <?php endwhile; ?>
      <?php endif; ?>
    </div>
  <!-- end of content -->
  </section>
<?php get_footer(); ?>
