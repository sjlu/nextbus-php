<div class="container">
   <h2>Runtime</h2>
   <pre>{elapsed_time}s</pre>
   <h2>Route Configuration</h2>
   <p><?= base_url('nextbus/get_config/rutgers') ?></p>
   <pre><?= json_encode($config); ?></pre>
   <h2>Predictions</h2>
   <p><?= base_url('nextbus/get_predictions/rutgers') ?></p>
   <pre><?= json_encode($predictions); ?></pre>
</div>
