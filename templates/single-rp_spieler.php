<?php
  /*
  * Template fuer einen einzelnen Spieler
  */
?>

<?php get_header(); ?>
  <section id="content" class="clearfix">
    <div class="content-header-sep"></div>
    <div class="page page-full">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        test2

        <?php
          $spielerID = get_post_meta(get_the_ID(), 'click_tt_id', true);
          $shortcode = '[bilanzen spieler_id=' . $spielerID . ' mannschaft="' . $mannschaft . '"]';
          echo do_shortcode($shortcode) . PHP_EOL;
        ?>

      <?php endwhile; ?>
      <?php endif; ?>
    </div>
  <!-- end of content -->
  </section>
<?php get_footer(); ?>
