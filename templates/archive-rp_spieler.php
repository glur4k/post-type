<?php
/**
 * Template Name: Archiv Spieler
 **/

/*
 * Template fuer die Anzeige aller Mannschaften
 */

get_header(); ?>
  <section id="content" class="clearfix page-widh-sidebar">
    <div class="content-header-sep"></div>
    <div class="page">
      <?php
        $args = array(
          'orderby'           => 'name',
          'order'             => 'ASC',
          'hide_empty'        => false
        );

        $terms = get_terms(array('rp_spieler_mannschaft'), $args);
      ?>

      <?php if (!empty($terms)): ?>
        <?php foreach ($terms as $key => $mannschaft) { ?>

          <?php if (strcmp($mannschaft->name, 'Alle Spieler') !== 0) : ?>
            <h4><?php echo $mannschaft->name ?></h4>
            <p>
              <a href="<?php echo $mannschaft->slug ?>">Link</a>
            </p>
          <?php endif; ?>



        <?php } ?>




      <?php else: ?>
        Keine Mannschaft gefunden!
      <?php endif; ?>
      </div>
    <?php wp_reset_postdata(); ?>
    <?php get_sidebar(); ?>
  <!-- end of content -->
  </section>
<?php get_footer(); ?>
