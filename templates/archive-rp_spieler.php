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
      <h5 class="dt-message dt-message-info" style="text-align: right;">Die aktuellen Ergebnisse unserer Mannschaften</h5>
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


            <h5><?php echo $mannschaft->name ?></h5>
            <?php if (function_exists('z_taxonomy_image_url') && z_taxonomy_image_url($mannschaft->term_id) !== false): ?>
              <div class="rp_mannschafts_bild">
                <img src="<?php echo z_taxonomy_image_url($mannschaft->term_id); ?>" alt="Bild der Mannschaft: <?php echo $mannschaft->name ?>">
              </div>
            <?php endif ?>
            <div class="dt-message dt-message-paragraph">

              <div class="dt-threeforths">Die <?php echo $mannschaft->name ?> belegen momentan den Tabellenplatz:</div>
              <div class="dt-oneforth dt-oneforthlast">
                <span style="font-size: x-large;">X</span>
              </div>
              <p>
                &nbsp;
                <br>
                <a class="dt-more-link" href="<?php echo $mannschaft->slug ?>"><span><span>Tabelle &amp; Ergebnisse</span></span></a>
              </p>
            </div>
            <br>

            <div class="dt-separator-top"><a class="scroll" href="#website-header">TOP</a></div>




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
