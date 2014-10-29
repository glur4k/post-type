<?php
header("charset=utf-8");
require_once('helpers/utils.php');

/**
* Parst die Spieler auf Click-TT...
*
* TODO: meta-rang als JSON Objekt
*/
class SpielerParser {

  private $vereinsID;
  private $nummer_spieler = 1;

  /**
   * Parst die Spieler als Post in die Wordpress Datenbank mit der click-tt-id zur Einfachheit.
   * Zusaetzlich wird in rp_spieler_daten fuer jeden Spieler ein Eintrag pro Mannschaft
   * mit den entsprechenden Werten erstellt
   */
  function __construct() {
    $this->settings = get_option('rp_results_parser_einstellungen');
    $this->vereinsID = $this->settings['rp_vereins_id'];
    $output = $this->rp_parse_spieler($this->rp_hole_mannschaftsmeldungen_links());

    if (!$output) {
      throw new Exception('Das Importieren der Spieler ist Fehlgeschlagen!');
    } else {
      echo "<div class='updated'><p>Alle Spieler erfolgreich importiert!</p></div>";
    }
  }

  /**
   * Baut die Links zu den Mannschaftsmeldungen auf
   * Ehemals rp_parseBilanzLinks()
   * @return array Array mit den Links zu den Mannschaftsmeldungen. z.B Link zu den
   *               Mannschaftsmeldungen der Herren, also auch Herren 1 und Herren 2
   */
  private function rp_hole_mannschaftsmeldungen_links() {
    $mannschftsmeldungen_link = 'http://ttvbw.click-tt.de/cgi-bin/WebObjects/nuLigaTTDE.woa/wa/clubPools?club=' . $this->vereinsID;
    $table = file_get_html($mannschftsmeldungen_link)->find('table', 0);
    $rows = $table->find('tr');
    $bilanzLinks = array();
    foreach ($rows as $index => $row) {
      if ($index === 0 || $index === 1) {
        continue;
      } elseif ($row->class === 'table-split') {
        break;
      }
      $bilanzLinks[] = $row->find('a', -1)->href;
    }

    $this->rp_free_mem(array($table, $rows));
    return $bilanzLinks;
  }

  /**
   * Baut anhand der Mannschaftsmeldungen-Seiten die Spieler-Objekte und traegt sie als Posts in
   * die Wordpress-Datenbank ein
   * @param  array $mannschaftsmeldungen Array mit den Links zu den Mannschaftsmeldungen-Seite
   * @param  [type] $mannschaftsLinks     [description]
   * @return [type]                       [description]
   */
  private function rp_parse_spieler($mannschaftsmeldungen) {
    // DATENBANKEINTRAG BAUEN UND EINTRAGEN //

    foreach ($mannschaftsmeldungen as $mannschaft) {
      $html = file_get_html(ParserUtils::rp_clean_amp('http://ttvbw.click-tt.de' . $mannschaft));

      // Hole die Mannschaften aus der Wordpress-Datenbank mit den Spalten:
      // Name
      // Gegner
      // um mehrere queries zu verhindern
      global $wpdb;
      $table_name = $wpdb->prefix . 'rp_mannschaften_daten';
      $query = 'SELECT name, gegner FROM ' . $table_name;
      $alleMannschaften = $wpdb->get_results($query, ARRAY_A);

      $tabellen = $html->find('#content', 0)->find('table');
      foreach ($tabellen as $count => $tabelle) {
        $mannschaftsname = $this->rp_clean_mannschafts_namen($html->find('h2')[$count]->plaintext);

        $schluessel = array(); // Array mit den Spalten z.b. Mannschaft, Bilanzwert etc
        $reihen = $tabelle->find('tr');
        foreach ($reihen as $index => $reihe) {

          // Hole Spalten
          if ($index === 0) {
            $spalten = $reihe->find('th');
            foreach ($spalten as $key => $value) {
              if ($key === 1) {
                $schluessel[] = 'vorname';
                $schluessel[] = 'nachname';
                continue;
              } else if ($key === 2) { // Ueberspringe Rang, Name & Leerspalte
                continue;
              }
              $schluessel[] = ParserUtils::rp_clean_umlaute($value->plaintext);
            }
            continue;
          }

          // Baue das Spieler-Objekt
          $elemente = $reihe->find('td');
          $linkInfos = $this->baueInfosVonSpielerLink($elemente[1]);
          $spielerID = $linkInfos[0];

          $link = 'http://ttvbw.click-tt.de' . ParserUtils::rp_clean_amp(trim($elemente[1]->find('a', 0)->href));

          foreach ($schluessel as $key => $value) {
            if ($key === 0) {
              continue;
            } else if ($key === 1 || $key === 2) {
              $spieler[$spielerID][$value] = $linkInfos[$key];
            } else if ($key === 3) {
              foreach ($alleMannschaften as $mannschaft) {
                if (strcmp($mannschaftsname, $mannschaft['name']) === 0) {
                  $spieler[$spielerID]['mannschaft'] = $mannschaft['name'];
                  $spieler[$spielerID]['rang'] = $elemente[0]->plaintext;
                  $spieler[$spielerID]['position'] = $index;
                  $spieler[$spielerID]['einsaetze'] .= ParserUtils::rp_clean_umlaute($elemente[$key]->plaintext) . '/' . ($mannschaft['gegner'] * 2);
                }
              }
            } else {
              $spieler[$spielerID][$value] = ParserUtils::rp_clean_umlaute($elemente[$key]->plaintext);
            }
          }
          $spieler[$spielerID]['click_tt_id'] = $linkInfos[0];
          $spieler[$spielerID]['link'] = $link;


          // Saeubere Bilanzwert zu int
          $spieler[$spielerID]['bilanzwert'] = str_replace('+', '', $spieler[$spielerID]['bilanzwert']);

          // Erstelle den WP-Post
          if (!$this->rp_create_post($spieler[$spielerID])) {
            echo "Fehler beim Erstellen des Spieler-Posts!";
          }
        }
        unset($spieler);
      }
    }

    return true;
  }

  /**
   * Fuegt einen Spieler in die Post-Datenbank ein
   * @param  array spieler Array mit allen Daten zu dem Spieler
   * @return
   */
  private function rp_create_post($spieler) {
    // Pruefe ob der Spieler schon als Post existiert
    global $wpdb;
    $table_name = $wpdb->prefix . 'rp_spieler_daten';

    $postID = $wpdb->get_var($wpdb->prepare(
      "SELECT post_id
      FROM $table_name
      WHERE click_tt_id = %d",
      $spieler['click_tt_id']
    ));

    // Fuege den Standard-Taxonomy-Term "Alle Spieler" hinzu
    wp_add_object_terms($postID, 'alle-spieler', 'rp_spieler_mannschaft');

    if (!is_null($postID)) {
      // Post nur updaten
      echo "Spieler existiert schon. " . $spieler['vorname'] . ' ' . $spieler['nachname'] . ' wurde aktualisiert ' . $spieler['nachname'] . ' (' . $spieler['mannschaft'] . ")<br>";
    } else {
      // Post existiert noch nicht -> Post erstellen
      $post = array(
        'post_content' => '',
        'post_title' => $spieler['vorname'] . ' ' . $spieler['nachname'],
        'post_status' => 'publish',
        'post_type' => 'rp_spieler',
        'comment-status' => 'closed'
      );

      $postID = wp_insert_post($post, true);

      echo "Spieler erstellt: " . $this->nummer_spieler . ". " .  $spieler['vorname'] . ' ' . $spieler['nachname'] . ' (' . $spieler['mannschaft'] . ")<br>";
      $this->nummer_spieler++;

      if (is_wp_error($postID)) {
        echo 'Fehler beim einfügen:<br>' . $postID->get_error_message($postID->get_error_code()) . PHP_EOL . '<br>';
        return false;
      }
    }
    $spieler['post_id'] = $postID;

    // Checke ob Spieler schon in der rp_spieler_daten ist
    // Wenn ja, update. Wenn nein, insert!
    $exists = $wpdb->get_var($wpdb->prepare(
      "SELECT count(id)
      FROM $table_name
      WHERE click_tt_id = %d
      AND mannschaft = %s",
      $spieler['click_tt_id'],
      $spieler['mannschaft']
    ));

    if ($exists > 0) {
      // Spieler-Eintrag existiert schon -> update
      $wpdb->update(
        $table_name,
        $spieler,
        array('click_tt_id' => $spieler['click_tt_id'], 'mannschaft' => $spieler['mannschaft'])
      );
    } else {
      // Spieler-Eintrag existiert noch nicht -> insert
      $spieler = ParserUtils::rp_erstelle_valide_spalten_namen($spieler);
      $wpdb->insert($table_name, $spieler);
    }

    // Taxonomy immer hinzufuegen! Da auch welche hinzukommen koennen
    wp_add_object_terms($postID, $spieler['mannschaft'], 'rp_spieler_mannschaft');
    return true;
  }

  /**
   * Versuch moeglichst viel Speicher wieder freizugeben, wenn das Element nicht mehr gebraucht wird
   * @param  array $elemente Array mit den Elementen die geloescht werden koennen
   * @return
   */
  private function rp_free_mem($elemente) {
    foreach ($elemente as $element) {
      if (is_object($element)) {
        $element->clear();
      }
      unset($element);
    }
  }

  /**
   * Saeubert die Namen der Mannschaften auf ihren Namen (ohne die Liga etc)
   * @param  string $name Name der Mannschaft
   * @return string       sauberer Name der Mannschaft
   */
  private function rp_clean_mannschafts_namen($name) {
    return trim(substr($name, 0, strpos ($name, '&')));
  }

  private function baueInfosVonSpielerLink($element) {
    $link = $element->find('a', 0);
    $url = parse_url($link->href);
    parse_str(html_entity_decode($url['query']), $output);
    list($nachname, $vorname) = explode(',', $link->plaintext);
    return array($output["person"], trim($vorname), trim($nachname));
  }
}

?>
