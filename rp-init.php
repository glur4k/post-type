<?php
  require_once('includes/vendor/simple_html_dom.php');
  require_once('includes/vendor/featured_image_box_changer.php');
  require_once('includes/helpers/utils.php');
  require_once('includes/options_page.php');
  new options_page();
  require_once('includes/widgetBesteSpieler.php');
  require_once('includes/widgetMannschaften.php');
  require_once('includes/cronjobs/cron-spieler.php');
  require_once('includes/cronjobs/cron-mannschaften.php');
?>
