<?php
  if (!function_exists('gwolle_gb_output_to_input_field')) {
    //  Function to format a form value for an input field (strip '<' etc.)
    function gwolle_gb_output_to_input_field($value) {
      $value = stripslashes($value);
      $value = html_entity_decode($value);
      $value = htmlspecialchars($value);
      return $value;
    }
  }
?>