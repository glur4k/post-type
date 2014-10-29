<?php
header("charset=utf-8");

require_once('vendor/simple_html_dom.php');
require_once('helpers/utils.php');

/**
 * Parst die Mannschaften, allerdings nur die Namen dazu und keine Daten
 * Erstellt zu jeder Mannschaft einen Taxonomy-Term, der dann im spieler_parser dem Spieler zugewiesen wird
 * TODO: Taxonomy-Terms fuer die Mannschaften in die Datenbank schreiben
 */
class MannschaftsParser {

  private $mannschaften = array();
  private $vereinsID;

  function __construct() {
    $this->vereinsID = get_option('rp_results_parser_einstellungen')['rp_vereins_id'];
    $this->rp_parse_mannschaften_init();
  }

  /*
   * Parst die Mannschaftsnamen beim Import der Spieler
   */
  private function rp_parse_mannschaften_init() {
    $startseite = "http://ttvbw.click-tt.de/cgi-bin/WebObjects/nuLigaTTDE.woa/wa/clubTeams?club=" . $this->vereinsID;

    $mannschaftsNamen = array();

    $html = file_get_html($startseite);
    $html = $html->find('table', 0);

    $isKlasse = true;
    $zeile = 0;

    // Baut die Namen der Mannschaften und die Links der Tabellen-Uebersicht
    foreach ($html->find('tr') as $row) {
      if ($zeile === 2) {
        break;
      }
      if (strpos($row->innertext, 'Spielklassen') !== false) {
        $isKlasse = true;
        $zeile = 0;
      } else if (strpos($row->innertext, 'Pokal') !== false) {
        $isKlasse = false;
      } else {
        if ($isKlasse && $zeile === 0) {
          $zeile++;
          continue;
        } else if ($isKlasse && $zeile >= 0) {
          if (strpos($row->find('td', 1)->innertext, 'Relegation') !== false) {
            continue;
          }
          $name = $row->find('td', 0)->innertext;
          $link = 'http://ttvbw.click-tt.de' . $row->find('a', 0)->href;
          $mannschaftsNamen[] = array(
            "name" => $name,
            "link" => '',
            "gegner" => 0,
            "position" => 0,
            "liga" => '',
            "data_tabelle" => '',
            "data_ergebnisse" => ''
          );
        }
      }
      $row->clear();
      unset($row);
    }
    $html->clear();
    unset($html);

    global $wpdb;
    $table_name = $wpdb->prefix . 'rp_mannschaften_daten';

    foreach ($mannschaftsNamen as $mannschaft) {
      $exists = $wpdb->get_row($wpdb->prepare(
        "SELECT *
        FROM $table_name
        WHERE name = %s",
        $mannschaft['name']
      ), ARRAY_A);

      if ($exists == 0) {
        $wpdb->insert($table_name, $mannschaft);
        $name = ParserUtils::konvertiereMannschaftsNamen($mannschaft['name']);
        wp_insert_term($mannschaft['name'], 'rp_spieler_mannschaft', array('slug' => $name));
        echo "Mannschaft eingef&uuml;gt: " . $mannschaft['name'] . "<br>";
      }
    }

    return true;
  }
}

?>
