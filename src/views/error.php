<div class="error">
  <h3><?php printf('%s: %s', __('Pitta Migration', 'pitta-migration'), __('Error')); ?></h3>
  <h4><?php _e('Error message'); ?></h4>
  <pre><?php $wpdb->print_error(); ?></pre>
  <h4><?php _e('SQL'); ?></h4>
  <pre><?php echo $sql; ?></pre>
</div>
