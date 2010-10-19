<?php
  /**
   * gb-module.php
   * Handles requests for guestbook entries.
   */
  
  if (!isset($_GET['function']) || !isset($_GET['entry_id'])) {
    exit;
  }
  
  $function = $_GET['function'];
  $entry_id = (int)$_GET['entry_id'];
  if ($entry_id === 0) {
    exit;
  }
  
  include('../../../../wp-load.php');
  
  //  Check the WPnonce
  if (check_ajax_referer($function.'-'.$entry_id)) {

  }
  