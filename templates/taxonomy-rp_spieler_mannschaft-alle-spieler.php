<?php

get_header(); ?>
  <section id="content" class="clearfix page-widh-sidebar">
    <div class="content-header-sep"></div>
      <div class="page">
        <?php
          // TODO: Bild (Pfeil) nach dem Link einfuegen, zur Darstellung der Sortierung
          // Sortierfunktionalitaet
          $sortierung = get_query_var('sortierung');
          $metaQuery = '';
          $metaKey = '';
          $sortierungName = false;

          switch ($sortierung) {
            case 'nameAbsteigend':
              $sortierungBilanz = 'bilanzAbsteigend';
              $order = 'DESC';
              $orderby = 'title';
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
              $metaKey = 'bilanz';
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
              $metaKey = 'bilanz';
              break;
            default:
              $sortierungName = 'nameAbsteigend';
              $sortierungBilanz = 'bilanzAbsteigend';
              $order = 'ASC';
              $orderby = 'title';
              break;
          }

        ?>
        <h5>Sortieren nach:</h5>
        <h6 style="float: left;"><a href="<?php echo add_query_arg('sortierung', $sortierungName); ?>">Name<span class="sortierung-indikator"></span></a></h6>
        <h6 style="float: right;"><a href="<?php echo add_query_arg('sortierung', $sortierungBilanz); ?>">Bilanz<span class="sortierung-indikator"></span></a></h6>

        <hr>

        <?php
          // Zeige Spieler an
          $mannschaft = get_query_var('term');

          $args = array(
            'meta_query' => $metaQuery,
            'meta_key' => $metaKey,
            'post_type' => 'rp_spieler',
            'order' => $order,
            'orderby' => $orderby
          );
          $the_query = new WP_Query($args);
        ?>

        <?php if ($the_query->have_posts()) : ?>
          <!-- the loop -->
          <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>

            <h4><?php the_title(); ?></h4>
            <a href="<?php echo get_permalink(); ?>">Link</a>
            Bilanz: <?php echo get_post_meta(get_the_ID(), 'bilanz')[0]; ?>

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
