<?php

/*
 * Template fuer die Anzeige einer einzelnen Mannschaft
 */

get_header(); ?>
  <section id="content" class="clearfix page-widh-sidebar">
    <div class="content-header-sep"></div>
      <div class="page">

        <table>
          <tr>
            <th>Bilanz</th>
            <th>Statistik</th>
          </tr>
          <tr>
            <td>Bla</td>
            <td>Blubb</td>
          </tr>
        </table>

        <?php
          // Zeige die einzelnen Spieler an
          $mannschaft = get_query_var('term');

          $args = array(
            'post_type' => 'rp_spieler',
            'tax_query' => array(
              array(
                'taxonomy' => 'rp_spieler_mannschaft',
                'field'    => 'slug',
                'terms'    => $mannschaft
              ),
            ),
          );
          $the_query = new WP_Query($args);
        ?>

        <?php if ($the_query->have_posts()) : ?>
          <!-- the loop -->
          <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>


            <h2><?php the_title(); ?></h2>
            <a href="<?php echo get_permalink(); ?>">Link</a>


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
