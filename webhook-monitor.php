<?php
/**
 * Plugin Name: Activate Webhooks
 * Description: A plugin to automatically activate all disabled TackleTarts webhooks.
 * Version: 1.0
 * Author: David Oak
 */

// Function to write to the custom log file
function custom_log($message) {
    $log_file = plugin_dir_path(__FILE__) . 'webhook_activation_log.txt';
    $current_time = date('Y-m-d H:i:s');
    file_put_contents($log_file, $current_time . ': ' . $message . PHP_EOL, FILE_APPEND);
}

// Function to activate all disabled WooCommerce webhooks
function activate_all_disabled_woocommerce_webhooks() {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        custom_log("WooCommerce is not active.");
        return;
    }

    // Fetch all webhook IDs with 'disabled' status
    $data_store = WC_Data_Store::load('webhook');
    $webhook_ids = $data_store->get_webhooks_ids('disabled'); // Use 'disabled' status directly

    if (empty($webhook_ids)) {
        custom_log("No disabled webhooks found.");
        return;
    }

    // Loop through each disabled webhook ID and activate it
    foreach ($webhook_ids as $webhook_id) {
        $webhook = new WC_Webhook($webhook_id);
        if ($webhook->get_status() !== 'active') {
            $webhook->set_status('active');
            $webhook->save();
            custom_log("Webhook ID " . $webhook->get_id() . " activated.");
        }
    }
}

// Schedule the activation task to run every 60 seconds
function schedule_webhook_activation() {
    if (!wp_next_scheduled('activate_disabled_webhooks')) {
        wp_schedule_event(time(), 'every_minute', 'activate_disabled_webhooks');
    }
}
add_action('init', 'schedule_webhook_activation');

// Hook the activation function to the scheduled event
function activate_disabled_webhooks_event() {
    activate_all_disabled_woocommerce_webhooks();
}
add_action('activate_disabled_webhooks', 'activate_disabled_webhooks_event');

// Add a custom cron interval for every 60 seconds
function add_cron_intervals($schedules) {
    $schedules['every_minute'] = array(
        'interval' => 60, // 60 seconds
        'display' => __('Every 60 Seconds')
    );
    return $schedules;
}
add_filter('cron_schedules', 'add_cron_intervals');

?>
