<?php
/**
 * Plugin Name: Webhook Monitor
 * Description: A plugin to monitor and activate all disabled webhooks.
 * Version: 1.0
 * Author: David Oak
 */

function activate_all_disabled_tackletarts_webhooks() {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        return;
    }

    // Fetch all webhook IDs with 'disabled' status
    $data_store = WC_Data_Store::load('webhook');
    $webhook_ids = $data_store->get_webhooks_ids('disabled'); // Use 'disabled' status directly

    if (empty($webhook_ids)) {
        return;
    }

    // Loop through each disabled webhook ID and activate it
    foreach ($webhook_ids as $webhook_id) {
        $webhook = new WC_Webhook($webhook_id);
        if ($webhook->get_status() !== 'active') {
            // Activate the webhook
            $webhook->set_status('active');
            $webhook->save();

            // Update the last check time
            update_post_meta($webhook_id, '_last_check_time', time()); // Use time() to store the current timestamp
        }
    }
}

// Run the function on every page load/refresh
add_action('init', 'activate_all_disabled_tackletarts_webhooks');

// Schedule the activation task to run every 60 seconds
function schedule_webhook_activation() {
    if (!wp_next_scheduled('activate_disabled_webhooks')) {
        wp_schedule_event(time(), 'every_minute', 'activate_disabled_webhooks');
    }
}
add_action('init', 'schedule_webhook_activation');

// Hook the activation function to the scheduled event
function activate_disabled_webhooks_event() {
    activate_all_disabled_tackletarts_webhooks();
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

// Add a submenu item under the Tools menu
function add_webhook_monitor_submenu() {
    add_submenu_page(
        'tools.php',
        'TackleTarts Webhook Status',
        'Webhook Status',
        'manage_options',
        'tackletarts-webhook-status',
        'tackletarts_webhook_status_page'
    );
}
add_action('admin_menu', 'add_webhook_monitor_submenu');

// Callback function to render the TackleTarts webhook status page
function tackletarts_webhook_status_page() {
    ?>
    <div class="wrap">
        <h1 style="margin-bottom: 20px">Webhook Status</h1>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Webhook ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Last Time Reactivated</th>
                    <th>Delivery URL</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch webhook data and loop through to display
                $data_store = WC_Data_Store::load('webhook');
                $webhook_ids = $data_store->get_webhooks_ids();
                foreach ($webhook_ids as $webhook_id) {
                    $webhook = new WC_Webhook($webhook_id);
                    $webhook_name = $webhook->get_name();
                    $status = $webhook->get_status();
                    $last_check_time_unix = get_post_meta($webhook_id, '_last_check_time', true);
                    $last_check_time = $last_check_time_unix ? date('Y-m-d H:i:s', $last_check_time_unix) : 'N/A';
                    $delivery_url = $webhook->get_delivery_url();
                    $status_color = ($status === 'active') ? 'green' : 'red';
                    ?>
                    <tr>
                        <td><?php echo $webhook_id; ?></td>
                        <td><?php echo $webhook_name; ?></td>
                        <td><span style="color: <?php echo $status_color; ?>;"><?php echo $status; ?></span></td>
                        <td><?php echo $last_check_time; ?></td>
                        <td><?php echo $delivery_url; ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <h2>By David Oak</h2>
    </div>
    <?php
}

?>
