<?php
  if (!function_exists('gwolle_gb_format_value_for_output')) {
    //  Function to format entry values for output
    function gwolle_gb_format_value_for_output($value) {
      $value = html_entity_decode($value);
      $value = stripslashes($value);
      $value = htmlspecialchars($value);
      return $value;
    }
  }
?>