<?php
/*
 * Template fuer die Anzeige aller Spieler
 */

get_header(); ?>
  <section id="content" class="clearfix page-widh-sidebar">
    <div class="content-header-sep"></div>
      <div class="page alle-spieler">
        <?php
          // Sortierfunktionalitaet
          $sortierung = get_query_var('sortierung');
          $metaQuery = '';
          $metaKey = '';
          $sortName = '';
          $sortBilanz = '';
          $sortierungName = false;

          switch ($sortierung) {
            case 'nameAbsteigend':
              $sortierungBilanz = 'bilanzAbsteigend';
              $order = 'DESC';
              $orderby = 'title';
              $sortName = 'down';
              break;
            case 'bilanzAufsteigend':
              $sortierungBilanz = 'bilanzAbsteigend';
              $order = 'ASC';
              $orderby = 'meta_value_num';
              $metaQuery = array(
                array(
                  'type' => 'DECIMAL'
                ),
              );
              $metaKey = 'bilanzwert';
              $sortBilanz = 'up';
              break;
            case 'bilanzAbsteigend':
              $sortierungBilanz = 'bilanzAufsteigend';
              $order = 'DESC';
              $orderby = 'meta_value_num';
              $metaQuery = array(
                array(
                  'type' => 'DECIMAL'
                )
              );
              $metaKey = 'bilanzwert';
              $sortBilanz = 'down';
              break;
            default:
              $sortierungName = 'nameAbsteigend';
              $sortierungBilanz = 'bilanzAbsteigend';
              $order = 'ASC';
              $orderby = 'title';
              $sortName = 'up';
              break;
          }
        ?>

        <?php
          // Erstelle den Spieler Query
          $mannschaft = get_query_var('term');

          $args = array(
            'meta_query' => $metaQuery,
            'meta_key' => $metaKey,
            'post_type' => 'rp_spieler',
            'order' => $order,
            'orderby' => $orderby,
            'posts_per_page' => -1
          );
          $the_query = new WP_Query($args);
        ?>

        <h5 class="dt-message dt-message-info">Momentan spielen <?php echo $the_query->found_posts ?> Spieler aktiv in unseren Mannschaften:</h5>
        <h5>Sortieren nach:</h5>
        <h6 style="float: left;"><a href="<?php echo add_query_arg('sortierung', $sortierungName); ?>">Name<span class="sortierung-indikator <?php echo $sortName ?>"></span></a></h6>
        <h6 style="float: right;"><a href="<?php echo add_query_arg('sortierung', $sortierungBilanz); ?>">Bilanz<span class="sortierung-indikator <?php echo $sortBilanz ?>"></span></a></h6>

        <hr>

        <?php if ($the_query->have_posts()) : ?>
          <!-- the loop -->
          <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>

            <div class="dt-message dt-message-paragraph spieler-uebersicht">
              <?php if (($url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'post-thumbnail')) !== false): ?>
                <a title="Ausf&uuml;hliche Statistiken zu <?php the_title(); ?>" class="spieler-portrait-wrapper" href="<?php echo get_post_permalink(); ?>"><?php the_post_thumbnail(array(60, 60)); ?></a>
              <?php else: ?>
                <a title="Ausf&uuml;hliche Statistiken zu <?php the_title(); ?>" class="spieler-portrait-wrapper portrait-wrapper-no-img" href="<?php echo get_post_permalink(); ?>"><?php echo ParserUtils::baueTitelKuerzel(get_the_title()); ?></a>
              <?php endif; ?>
              <h4><?php the_title(); ?></h4>
              <p>
                <a class="dt-more-link" href="<?php echo get_post_permalink(); ?>"><span><span>(<?php echo get_post_meta(get_the_ID(), 'mannschaft', true) ?>) <strong><?php echo ($bilanzwert = get_post_meta(get_the_ID(), 'bilanzwert')[0]) > 0 ?  '+' . $bilanzwert : $bilanzwert; ?></strong></span></span></a>
              </p>
            </div>

          <?php endwhile; ?>
          <!-- end of the loop -->

          <?php wp_reset_postdata(); ?>
        <?php else : ?>
          <p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
        <?php endif; ?>

      </div>
    <?php wp_reset_postdata(); ?>
    <?php get_sidebar(); ?>
  <!-- end of content -->
  </section>
<?php get_footer(); ?>
