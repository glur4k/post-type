<?php
/*
 * Template fuer einen einzelnen Spieler
 */
?>

<?php
  $zeige = get_query_var('rp_spieler_mannschaft');
?>

<?php get_header(); ?>
  <section id="content" class="clearfix page-widh-sidebar">
    <div class="content-header-sep"></div>
    <div class="page">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <?php if (($src = wp_get_attachment_image_src(get_post_thumbnail_id(), 'post-thumbnail')[0]) !== false): ?>
          <a title="<?php the_title() ?>" href="<?php echo $src ?>" class="spieler-portrait-wrapper" rel="modal-window"><?php the_post_thumbnail(); ?></a>
        <?php else: ?>
          <div class="spieler-portrait-wrapper portrait-wrapper-no-img"><?php echo ParserUtils::baueTitelKuerzel(get_the_title()); ?></div>
        <?php endif; ?>

        <table>
          <thead>
            <tr>
              <th>test</th>
              <th>test</th>
              <th>test</th>
              <th>test</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>blas</td>
              <td>blas</td>
              <td>blas</td>
              <td>blas</td>
            </tr>
          </tbody>
        </table>

        <?php
          echo $zeige;
        ?>

        <?php echo get_the_content(); ?>

      <?php endwhile; ?>
      <?php endif; ?>
    </div>
  <!-- end of content -->
  <?php get_sidebar(); ?>
  </section>
<?php get_footer(); ?>
