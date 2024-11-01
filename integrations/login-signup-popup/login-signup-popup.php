<?php

// support from
// zohoflow 2.3.0
// login-signup-popup 2.5.1
class Zoho_Flow_Login_Signup_Popup extends Zoho_Flow_Service
{

    // webhook events supported
    public static $supported_events = array(
        "user_login_success",
        "user_registration_success",
        "user_password_reset_success",
        "customer_created"
    );

    // To get user meta fields
    public function get_user_meta_keys($request)
    {
        global $wpdb;
        $query = $wpdb->prepare('
                  SELECT
                    DISTINCT meta_key
                    FROM ' . $wpdb->base_prefix . 'usermeta
                ');
        $meta_keys = $wpdb->get_results($query);
        return rest_ensure_response($meta_keys);
    }

    private function get_user_meta($user_id)
    {
        if ((! empty($user_id)) && (is_numeric($user_id))) {
            $user_meta = get_user_meta($user_id);
            $user_meta_unserialized = array();
            foreach ($user_meta as $key => $value) {
                $user_meta_unserialized[$key] = maybe_unserialize($value[0]);
            }
            return $user_meta_unserialized;
        }
    }

    // webhooks
    public function create_webhook($request)
    {
        $entry = json_decode($request->get_body());
        $name = $entry->name;
        $url = $entry->url;
        $event = $entry->event;
        if ((! empty($name)) && (! empty($url)) && (! empty($event)) && (in_array($event, self::$supported_events)) && (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url))) {
            $args = array(
                'name' => $name,
                'url' => $url,
                'event' => $event
            );
            $post_name = "Login/Signup Popup ";
            $post_id = $this->create_webhook_post($post_name, $args);
            if (is_wp_error($post_id)) {
                $errors = $post_id->get_error_messages();
                return new WP_Error('rest_bad_request', $errors, array(
                    'status' => 400
                ));
            }
            return rest_ensure_response(array(
                'webhook_id' => $post_id
            ));
        } else {
            return new WP_Error('rest_bad_request', 'Data validation failed', array(
                'status' => 400
            ));
        }
    }

    public function delete_webhook($request)
    {
        $webhook_id = $request['webhook_id'];
        if (is_numeric($webhook_id)) {
            $webhook_post = $this->get_webhook_post($webhook_id);
            if (! empty($webhook_post[0]->ID)) {
                $delete_webhook = $this->delete_webhook_post($webhook_id);
                if (is_wp_error($delete_webhook)) {
                    $errors = $delete_webhook->get_error_messages();
                    return new WP_Error('rest_bad_request', $errors, array(
                        'status' => 400
                    ));
                } else {
                    return rest_ensure_response(array(
                        'message' => 'Success'
                    ));
                }
            } else {
                return new WP_Error('rest_bad_request', 'Invalid webhook ID', array(
                    'status' => 400
                ));
            }
        } else {
            return new WP_Error('rest_bad_request', 'Invalid webhook ID', array(
                'status' => 400
            ));
        }
    }

    public function payload_login_success($user)
    {
        $args = array(
            'event' => 'user_login_success'
        );
        $webhooks = $this->get_webhook_posts($args);
        if (! empty($webhooks)) {
            $event_data = array(
                'event' => 'user_login_success',
                'data' => $user,
                'meta' => $this->get_user_meta($user->ID)
            );
            foreach ($webhooks as $webhook) {
                $url = $webhook->url;
                zoho_flow_execute_webhook($url, $event_data, array());
            }
        }
    }

    public function payload_registration_success($user)
    {
        $args = array(
            'event' => 'user_registration_success'
        );
        $webhooks = $this->get_webhook_posts($args);
        if (! empty($webhooks)) {
            $event_data = array(
                'event' => 'user_registration_success',
                'data' => get_user_by('ID', $user),
                'meta' => $this->get_user_meta($user)
            );
            foreach ($webhooks as $webhook) {
                $url = $webhook->url;
                zoho_flow_execute_webhook($url, $event_data, array());
            }
        }
    }

    public function payload_customer_created($user_id, $user)
    {
        $args = array(
            'event' => 'customer_created'
        );
        $webhooks = $this->get_webhook_posts($args);
        if (! empty($webhooks)) {
            $event_data = array(
                'event' => 'customer_created',
                'data' => get_user_by('ID', $user_id),
                'meta' => $this->get_user_meta($user_id)
            );
            foreach ($webhooks as $webhook) {
                $url = $webhook->url;
                zoho_flow_execute_webhook($url, $event_data, array());
            }
        }
    }

    public function payload_password_reset_success($user)
    {
        $args = array(
            'event' => 'user_password_reset_success'
        );
        $webhooks = $this->get_webhook_posts($args);
        if (! empty($webhooks)) {
            $event_data = array(
                'event' => 'user_password_reset_success',
                'data' => $user,
                'meta' => $this->get_user_meta($user->ID)
            );
            foreach ($webhooks as $webhook) {
                $url = $webhook->url;
                zoho_flow_execute_webhook($url, $event_data, array());
            }
        }
    }

    // default API
    public function get_system_info()
    {
        $system_info = parent::get_system_info();
        if (! function_exists('get_plugin_data')) {
            require_once (ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $plugin_dir = ABSPATH . 'wp-content/plugins/easy-login-woocommerce/xoo-el-main.php';
        if (file_exists($plugin_dir)) {
            $plugin_data = get_plugin_data($plugin_dir);
            $system_info['login_signup_popup'] = $plugin_data['Version'];
        }
        return rest_ensure_response($system_info);
    }
}
