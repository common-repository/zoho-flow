<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

$zoho_flow_services_config = array (
    array (
        'name' => esc_html__('WordPress.org'),
        'api_path' => 'wordpress-org',
        'class_name' => 'Zoho_Flow_WordPress_org',
        'gallery_app_link' => 'wordpress-org',
        'description' => esc_html__('Connect WordPress.org to Zoho Flow to automatically post your new WordPress posts on your social media handles such as Twitter, get notifications in your team chat for new posts, and create posts for new events scheduled in your event management app.', 'zoho-flow'),
        'icon_file' => 'wordpress.png',
        'class_test' => 'WP_Comment',
        'app_documentation_link' => 'wordpress-org',
        'embed_link' => 'wordpress_org',
        'version' => 'v1',
        'rest_apis' => array (
            array (
                'type' => 'list',
                'path' => '/users',
                'method' => 'get_users',
                'capability' => 'list_users',
                'schema_method' => 'get_user_schema',
            ),
            array (
                'type' => 'list',
                'path' => '/posts',
                'method' => 'get_posts',
                'capability' => 'read',
                'schema_method' => 'get_post_schema',
            ),
            array(
                'type' => 'list',
                'path' => '/posts/(?\'post_id\'[\\d]+)',
                'method' => 'get_post',
                'capability' => 'read',
                'schema_method' => 'get_post_schema',
            ),
            array(
                'type' => 'list',
                'path' => '/posts/(?\'post_type\'[a-zA-Z_-]+)/(?\'post_id\'[\\d]+)',
                'method' => 'fetch_post_with_posttype',
                'capability' => 'read',
                'schema_method' => 'get_post_schema',
            ),
            array(
                'type' => 'list',
                'path' => '/media',
                'method' => 'get_media_files',
                'capability' => 'upload_files',
            ),
            array(
                'type' => 'create',
                'path' => '/media/new',
                'method' => 'upload_media',
                'capability' => 'upload_files',
            ),
            array(
                'type' => 'create',
                'path' => '/media',
                'method' => 'upload_media_multipart',
                'capability' => 'upload_files',
            ),
            array(
                'type' => 'delete',
                'path' => '/media/(?\'attachment_id\'[\\d]+)',
                'method' => 'remove_media',
                'capability' => 'delete_files',
            ),
            array(
                'type' => 'list',
                'path' => '/comments',
                'method' => 'get_comments',
                'capability' => 'read',
                'schema_method' => 'get_comment_schema'
            ),
            array (
                'type' => 'list',
                'path' => '/posts/(?\'post_id\'[\\d]+)/comments/webhooks',
                'method' => 'get_webhooks',
                'capability' => 'read',
            ),
            array(
                'type' => 'create',
                'path' => '/posts/(?\'post_id\'[\\d]+)/comments/webhooks',
                'method' => 'create_post_comments_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/posts/(?\'post_id\'[\\d]+)/comments/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook_deprecated',
                'capability' => 'delete_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/(?\'post_type\'[a-zA-Z_]+)/webhooks',
                'method' => 'get_webhooks_for_post',
                'capability' => 'read',
            ),
            array(
                'type' => 'create',
                'path' => '/(?\'post_type\'[a-zA-Z_]+)/webhooks',
                'method' => 'create_webhook_for_post',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/(?\'post_type\'[a-zA-Z_]+)/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook_deprecated',
                'capability' => 'delete_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/comments/webhooks',
                'method' => 'get_comments_webhooks',
                'capability' => 'read',
            ),
            array(
                'type' => 'create',
                'path' => '/comments/webhooks',
                'method' => 'create_comments_webhooks',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/comments/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook_deprecated',
                'capability' => 'delete_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/users',
                'method' => 'create_user',
                'capability' => 'create_users',
            ),
            array(
                'type' => 'update',
                'path' => '/users/(?\'user_id\'[\\d]+)',
                'method' => 'update_user',
                'capability' => 'edit_users',
            ),
            array(
                'type' => 'create',
                'path' => '/posts/upsert',
                'method' => 'create_post_insert',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/posts',
                'method' => 'create_post',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'update',
                'path' => '/posts/(?\'post_id\'[\\d]+)',
                'method' => 'update_post',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'update',
                'path' => '/posts/(?\'post_id\'[\\d]+)/tags',
                'method' => 'update_post_tag',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'update',
                'path' => '/posts/(?\'post_id\'[\\d]+)/categories',
                'method' => 'update_post_categories',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/me',
                'method' => 'get_self',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/getuser/(?P<user_id>\d+)',
                'method' => 'get_user_by',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/getuser/(?P<login>\S+)',
                'method' => 'get_user_by',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/getresetpasswordlink/(?\'user_login\'[a-zA-Z0-9_\@]\S+)',
                'method' => 'get_resetpassword_link',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/user_meta/(?\'user_id\'[\\d]+)',
                'method' => 'get_userinfo_meta',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/post_types',
                'method' => 'get_post_types',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/post_statuses',
                'method' => 'get_post_statuses',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/post_types/(?\'post_type\'[a-zA-Z_\@]\S+)/meta_keys',
                'method' => 'get_post_type_meta_keys',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/post_types/(?\'post_type\'[a-zA-Z_\@]\S+)/taxonomies',
                'method' => 'get_post_type_taxonomies',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/users/meta_keys',
                'method' => 'get_user_meta_keys',
                'capability' => 'list_users',
            ),
            array(
                'type' => 'list',
                'path' => '/comments/meta_keys',
                'method' => 'get_comment_meta_keys',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/categories',
                'method' => 'get_categories',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/tags',
                'method' => 'get_tags',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/roles',
                'method' => 'get_roles',
                'capability' => 'list_users',
            ),
            array(
                'type' => 'list',
                'path' => '/getuser',
                'method' => 'fetch_user',
                'capability' => 'list_users',
            ),
            array(
                'type' => 'update',
                'path' => '/posts/(?\'post_id\'[\\d]+)/meta',
                'method' => 'update_post_meta',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'update',
                'path' => '/posts/(?\'post_id\'[\\d]+)/taxonomy',
                'method' => 'update_post_taxonomy',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'update',
                'path' => '/users/(?\'user_id\'[\\d]+)/meta',
                'method' => 'update_user_meta',
                'capability' => 'edit_users',
            ),
            array(
                'type' => 'create',
                'path' => '/comments',
                'method' => 'create_comment',
                'capability' => 'moderate_comments',
            ),
            array(
                'type' => 'create',
                'path' => '/mail/send',
                'method' => 'send_mail',
                'capability' => 'edit_private_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_private_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'delete_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/siteinfo',
                'method' => 'get_site_details',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array (
            array (
                'action' => 'comment_post',
                'method' => 'process_comment_post',
                'args_count' => 3,
            ),
            array (
                'action' => 'spammed_comment',
                'method' => 'process_spammed_comment',
                'args_count' => 2,
            ),
            array (
                'action' => 'edit_comment',
                'method' => 'process_edit_comment',
                'args_count' => 2,
            ),
            array (
                'action' => 'wp_set_comment_status',
                'method' => 'process_set_comment_status',
                'args_count' => 2,
            ),
            array (
                'action' => 'register_new_user',
                'method' => 'process_user_register',
                'args_count' => 1,
            ),
            array(
                'action' => 'profile_update',
                'method' => 'process_profile_update',
                'args_count' => 2
            ),
            array(
                'action' => 'save_post',
                'method' => 'process_save_post',
                'args_count' => 3
            ),
            array(
                'action' => 'wp_login',
                'method' => 'process_wp_login',
                'args_count' => 2,
            ),
            array(
                'action' => 'transition_post_status',
                'method' => 'payload_post_created',
                'args_count' => 3
            ),
            array(
                'action' => 'transition_post_status',
                'method' => 'payload_post_created_or_updated',
                'args_count' => 3
            ),
            array(
                'action' => 'transition_post_status',
                'method' => 'payload_post_status_changed',
                'args_count' => 3
            ),
            array(
                'action' => 'transition_post_status',
                'method' => 'payload_post_updated',
                'args_count' => 3
            ),
            array(
                'action' => 'added_post_meta',
                'method' => 'payload_post_meta_created_or_updated_for_post_update',
                'args_count' => 4
            ),
            array(
                'action' => 'updated_post_meta',
                'method' => 'payload_post_meta_created_or_updated_for_post_update',
                'args_count' => 4
            ),
            array(
                'action' => 'added_post_meta',
                'method' => 'payload_post_meta_created_or_updated_for_post_created_or_update',
                'args_count' => 4
            ),
            array(
                'action' => 'updated_post_meta',
                'method' => 'payload_post_meta_created_or_updated_for_post_created_or_update',
                'args_count' => 4
            ),
            array(
                'action' => 'set_object_terms',
                'method' => 'payload_post_taxonomy_set_for_post_update',
                'args_count' => 6
            ),
            array(
                'action' => 'set_object_terms',
                'method' => 'payload_post_taxonomy_set_for_post_created_or_update',
                'args_count' => 6
            ),
            array(
                'action' => 'user_register',
                'method' => 'payload_user_created',
                'args_count' => 2
            ),
            array(
                'action' => 'user_register',
                'method' => 'payload_user_for_created_or_updated',
                'args_count' => 2
            ),
            array(
                'action' => 'profile_update',
                'method' => 'payload_user_created_or_updated',
                'args_count' => 3
            ),
            array(
                'action' => 'added_user_meta',
                'method' => 'payload_user_meta_added_or_updated',
                'args_count' => 4
            ),
            array(
                'action' => 'updated_user_meta',
                'method' => 'payload_user_meta_added_or_updated',
                'args_count' => 4
            ),
            array(
                'action' => 'comment_post',
                'method' => 'payload_comment_created',
                'args_count' => 3
            ),
            array(
                'action' => 'edit_comment',
                'method' => 'payload_comment_edited',
                'args_count' => 2
            ),
            array(
                'action' => 'transition_comment_status',
                'method' => 'payload_comment_status_transition',
                'args_count' => 3
            ),
            array(
                'action' => 'wp_login',
                'method' => 'payload_user_login',
                'args_count' => 2,
            ),
            array(
                'action' => 'add_attachment',
                'method' => 'payload_attachment_added',
                'args_count' => 1,
            ),
            array(
                'action' => 'wp_mail_succeeded',
                'method' => 'payload_mail_succeeded',
                'args_count' => 1,
            ),
            array(
                'action' => 'wp_mail_failed',
                'method' => 'payload_mail_failed',
                'args_count' => 1,
            ),
        ),
    ),
    array (
        'name' => esc_html__('Contact Form 7'),
        'api_path' => 'contact-form-7',
        'class_name' => 'Zoho_Flow_Contact_Form_7',
        'gallery_app_link' => 'contact-form-7',
        'description' => esc_html__('Create forms in Contact Form 7 to collect contacts, feedback, or orders. Then integrate Contact Form 7 with other apps using Zoho Flow to store, share, and analyze your form submissions automatically.', 'zoho-flow'),
        'icon_file' => 'contact-form-7.png',
        'class_test' => 'WPCF7_ContactForm',
        'app_documentation_link' => 'contact-form-7',
        'embed_link' => 'contact_form_7',
        'version' => 'v1',
        'rest_apis' => array (
            array (
                'type' => 'list',
                'path' => '/forms',
                'method' => 'list_forms',
                'capability' => 'wpcf7_read_contact_forms',
            ),
            array (
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'list_form_fields',
                'capability' => 'wpcf7_read_contact_form',
            ),
            array (
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks', //Deprecated
                'method' => 'get_webhooks',
                'capability' => 'wpcf7_read_contact_forms',
                'schema_method' => 'get_form_webhook_schema',
            ),
            array (
                'type' => 'create',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks', //Deprecated
                'method' => 'create_webhook_old',
                'capability' => 'wpcf7_edit_contact_form',
            ),
            array (
                'type' => 'delete',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks/(?\'webhook_id\'[\\d]+)', //Deprecated
                'method' => 'delete_webhook_old',
                'capability' => 'wpcf7_delete_contact_form',
            ),
            array (
                'type' => 'get',
                'path' => '/files/(?\'filename\'.+)',
                'method' => 'get_file',
                'capability' => 'wpcf7_edit_contact_form',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'wpcf7_read_contact_form',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array (
            array (
                'action' => 'wpcf7_before_send_mail',
                'method' => 'payload_form_entry_added',
                'args_count' => 1,
            ),
            array (
                'action' => 'wpcf7_before_send_mail',
                'method' => 'process_form_submission', //Deprecated
                'args_count' => 1,
            ),
        ),
    ),
    array (
        'name' => esc_html__('Elementor Pro'),
        'api_path' => 'elementor',
        'class_name' => 'Zoho_Flow_Elementor',
        'gallery_app_link' => 'elementor-pro',
        'description' => esc_html__('Elementor’s intuitive website builder and 300+ predesigned templates make it easy to build great websites. Integrate it with your favorite apps, and you can automatically send, store, and analyze form responses, contacts, and feedback.', 'zoho-flow'),
        'icon_file' => 'elementor.png',
        'class_test' => 'ElementorPro\Plugin',
        'app_documentation_link' => '',
        'embed_link' => 'elementor',
        'version' => 'v1',
        'rest_apis' => array (
            array (
                'type' => 'list',
                'path' => '/forms',
                'method' => 'get_forms',
                'capability' => 'manage_options',
                'schema_method' => 'get_form_schema',
            ),
            array (
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[a-zA-Z0-9_]+)/fields',
                'method' => 'get_fields',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[a-zA-Z0-9_]+)/webhooks',
                'method' => 'get_webhooks',
                'capability' => 'manage_options',
                'schema_method' => 'get_form_webhook_schema',
            ),
            array (
                'type' => 'create',
                'path' => '/forms/(?\'form_id\'[a-zA-Z0-9_]+)/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'delete',
                'path' => '/forms/(?\'form_id\'[a-zA-Z0-9_]+)/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'manage_options',
            ),
        ),
        'hooks' => array (
            array (
                'action' => 'elementor_pro/forms/new_record',
                'method' => 'process_form_submission',
                'args_count' => 2,
            ),
        ),
    ),
    array (
        'name' => esc_html__('WPForms'),
        'api_path' => 'wpforms',
        'class_name' => 'Zoho_Flow_WPForms',
        'gallery_app_link' => 'wpforms',
        'description' => esc_html__('Utilize WPForms’s drag-and-drop form builder to create customizable forms for subscriptions, payments, and lead generation. Connect it to Zoho Flow to automatically add your subscriber information to your email marketing platform, add your payment data as new rows in your spreadsheet, and much more.', 'zoho-flow'),
        'icon_file' => 'wpforms.png',
        'class_test' => 'WPForms',
        'app_documentation_link' => 'wpforms',
        'embed_link' => 'wpforms',
        'version' => 'v1',
        'rest_apis' => array (
            array (
                'type' => 'list',
                'path' => '/forms',
                'method' => 'get_forms',
                'capability' => 'manage_options',
                'schema_method' => 'get_form_schema',
            ),
            array (
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'get_fields',
                'capability' => 'manage_options',
                'schema_method' => 'get_field_schema',
            ),
            array (
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'get_webhooks',
                'capability' => 'manage_options',
                'schema_method' => 'get_form_webhook_schema',
            ),
            array (
                'type' => 'create',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'delete',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'get',
                'path' => '/files/(?\'filename\'.+)',
                'method' => 'get_file',
                'capability' => 'manage_options',
            ),
        ),
        'hooks' => array (
            array (
                'action' => 'wpforms_process_complete',
                'method' => 'process_form_submission',
                'args_count' => 4,
            ),
        ),
    ),
    array(
        'name' => esc_html__("Akismet"),
        'api_path' => 'akismet',
        'class_name' => 'Zoho_Flow_Akismet',
        'gallery_app_link' => 'akismet',
        'description' => esc_html__('Akismet is a spam protection application that can identify and filter spam comments, trackbacks, and contract form messages. By integrating Akismet with other applications, you will be able to get notified when you get spam comments.', 'zoho-flow'),
        'icon_file' => 'akismet.png',
        'class_test' => 'Akismet',
        'app_documentation_link' => '',
        'embed_link' => 'akismet',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'create',
                'path' => '/comment/(?\'comment_id\'[\\d]+)/recheck',
                'method' => 'recheck_comment',
                'capability' => 'moderate_comments',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_private_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'comment_post',
                'method' => 'payload_spam_comment',
                'args_count' => 3,
            ),
            array (
                'action' => 'akismet_submit_spam_comment',
                'method' => 'payload_submit_spam',
                'args_count' => 2,
            ),
            array (
                'action' => 'akismet_submit_nonspam_comment',
                'method' => 'payload_submit_nonspam',
                'args_count' => 2,
            ),
        )
    ),
    array(
        'name' => esc_html__("WP Mail SMTP"),
        'api_path' => 'wp-mail-smtp',
        'class_name' => 'Zoho_Flow_WPMailSMTP',
        'gallery_app_link' => 'wp-mail-smtp',
        'description' => esc_html__('WP Mail SMTP is an STMP mailer WordPress plugin that improves email deliverability and enhances email authentication. Integrate WP Mail SMTP with other applications to ensure that you get notified every time your mail has been delivered successfully.', 'zoho-flow'),
        'icon_file' => 'wp-mail-smtp.png',
        'class_test' => 'WPMailSMTP\Reports\Reports',
        'app_documentation_link' => '',
        'embed_link' => 'wp_mail_smtp',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/summary',
                'method' => 'get_summary',
                'capability' => 'read',
            ),
            array(
                'type' => 'create',
                'path' => '/summary/sendtoadmin',
                'method' => 'send_summary_to_admin',
                'capability' => 'read',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'wp_mail_smtp_mailcatcher_send_after',
                'method' => 'payload_send_after',
                'args_count' => 2,
            ),
        )
    ),
    array(
        'name' => esc_html__("Post SMTP"),
        'api_path' => 'post-smtp',
        'class_name' => 'Zoho_Flow_Post_SMTP',
        'gallery_app_link' => 'post-smtp',
        'description' => esc_html__('Post SMTP is an SMTP mailer WordPress plugin that enhances email deliverability, logging, authentication, and more. Integrate Post SMTP with other applications to track your email logs for marketing purposes.', 'zoho-flow'),
        'icon_file' => 'post-smtp.png',
        'class_test' => 'PostmanWpMail',
        'app_documentation_link' => '',
        'embed_link' => 'post_smtp',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/stats',
                'method' => 'get_stats',
                'capability' => 'manage_postman_logs',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_postman_logs',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'post_smtp_on_success',
                'method' => 'payload_email_success',
                'args_count' => 4,
            ),
            array (
                'action' => 'post_smtp_on_failed',
                'method' => 'payload_email_failure',
                'args_count' => 5,
            ),
        )
    ),
    array (
        'name' => esc_html__("Advanced Custom Fields"),
        'api_path' => 'advanced-custom-fields',
        'class_name' => 'Zoho_Flow_Advanced_Custom_Fields',
        'gallery_app_link' => 'advanced-custom-fields',
        'description' => esc_html__('Enhance WordPress with extra content fields like text, images, and more using Advanced Custom Fields. Connect it with Zoho Flow to dynamically update custom field content based on triggers from other apps, or save new content additions to your cloud storage.', 'zoho-flow'),
        'icon_file' => 'acf.png',
        'class_test' => 'ACF',
        'app_documentation_link' => '',
        'embed_link' => 'advanced_custom_fields',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/fieldgroups',
                'method' => 'get_field_groups',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/allfields',
                'method' => 'get_all_fields',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/fieldsbygroup/(?\'post_parent\'[\\d]+)',
                'method' => 'get_fields_by_group',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/fieldgroup_by_id/(?\'field_group_id\'[\\d]+)',
                'method' => 'get_field_group_by_id',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'get_webhooks',
                'capability' => 'read',
            ),
            array(
                'type' => 'create',
                'path' => '/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'delete_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/fetchfields',
                'method' => 'fetch_fields',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'update',
                'path' => '/updatefields',
                'method' => 'update_fields',
                'capability' => 'manage_options',
            ),
        ),
        'hooks' => array(
            array(
                'action' => 'acf/save_post',
                'method' => 'process_save_post',
                'args_count' => 1,
            )
        )
    ),
    array (
        'name' => esc_html__('Ninja Forms'),
        'api_path' => 'ninja-forms',
        'class_name' => 'Zoho_Flow_Ninja_Forms',
        'gallery_app_link' => 'ninja-forms',
        'description' => esc_html__('Ninja Forms is a beginner-friendly form builder plugin that also provides conditional logic, multistep forms, and file uploads. Integrate Ninja Forms with your favorite apps to get instant notifications in your team chat app, create tasks in your project management application, or add leads to your CRM for new form submissions.', 'zoho-flow'),
        'icon_file' => 'ninja-forms.png',
        'class_test' => 'Ninja_Forms',
        'app_documentation_link' => 'ninja-forms',
        'embed_link' => 'ninja_forms',
        'version' => 'v1',
        'rest_apis' => array (
            array (
                'type' => 'list',
                'path' => '/forms',
                'method' => 'get_forms',
                'capability' => 'manage_options',
                'schema_method' => 'get_form_schema',
            ),
            array (
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'get_fields',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'get_webhooks',
                'capability' => 'manage_options',
                'schema_method' => 'get_form_webhook_schema',
            ),
            array (
                'type' => 'create',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'delete',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'manage_options',
            ),
        ),
        'hooks' => array (
            array (
                'action' => 'ninja_forms_after_submission',
                'method' => 'process_form_submission',
                'args_count' => 4,
            ),
        ),
    ),
    array(
        'name' => esc_html__("The Events Calendar"),
        'api_path' => 'the-events-calendar',
        'class_name' => 'Zoho_Flow_TheEventsCalendar',
        'gallery_app_link' => 'the-events-calendar',
        'description' => esc_html__('Use The Events Calendar to create and manage calendar events in your WordPress site. By integrating The Events Calendar with event scheduling applications, you can have your events updated in your calendar.', 'zoho-flow'),
        'icon_file' => 'the-events-calendar.png',
        'class_test' => 'Tribe__Events__API',
        'app_documentation_link' => '',
        'embed_link' => 'the_events_calendar',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/events/meta-keys',
                'method' => 'list_event_meta_keys',
                'capability' => 'read_private_tribe_events',
            ),
            array(
                'type' => 'list',
                'path' => '/organizers/meta-keys',
                'method' => 'list_organizer_meta_keys',
                'capability' => 'read_private_tribe_organizers',
            ),
            array(
                'type' => 'list',
                'path' => '/venues/meta-keys',
                'method' => 'list_venue_meta_keys',
                'capability' => 'read_private_tribe_venues',
            ),
            array(
                'type' => 'list',
                'path' => '/event/(?\'event_id\'[\\d]+)',
                'method' => 'fetch_event',
                'capability' => 'read_private_tribe_events',
            ),
            array(
                'type' => 'list',
                'path' => '/organizer/(?\'organizer_id\'[\\d]+)',
                'method' => 'fetch_organizer',
                'capability' => 'read_private_tribe_organizers',
            ),
            array(
                'type' => 'list',
                'path' => '/venue/(?\'venue_id\'[\\d]+)',
                'method' => 'fetch_venue',
                'capability' => 'read_private_tribe_venues',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_tribe_events',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'tribe_events_update_meta',
                'method' => 'payload_event_created_or_updated',
                'args_count' => 3,
            ),
            array (
                'action' => 'tribe_events_event_status_update_post_meta',
                'method' => 'payload_event_status_updated',
                'args_count' => 2,
            ),
            array (
                'action' => 'tribe_events_organizer_updated',
                'method' => 'payload_organizer_created_or_updated',
                'args_count' => 2,
            ),
            array (
                'action' => 'tribe_events_venue_updated',
                'method' => 'payload_venue_created_or_updated',
                'args_count' => 2,
            )
        )
    ),
    array(
        'name' => esc_html__("TablePress"),
        'api_path' => 'tablepress',
        'class_name' => 'Zoho_Flow_TablePress',
        'gallery_app_link' => 'tablepress',
        'description' => esc_html__('TablePress is a table plugin with which you can create and manage tables on your website. By integrating TablePress with your applications, you\'ll be able to transfer data into your tables instantly, letting you store, view, and organize data in multiple formats.', 'zoho-flow'),
        'icon_file' => 'tablepress.png',
        'class_test' => 'TablePress',
        'app_documentation_link' => '',
        'embed_link' => 'tablepress',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/tables',
                'method' => 'list_tables',
                'capability' => 'tablepress_list_tables',
            ),
            array(
                'type' => 'list',
                'path' => '/tables/(?\'table_id\'[\\d]+)',
                'method' => 'get_table_details',
                'capability' => 'tablepress_list_tables',
            ),
            array(
                'type' => 'create',
                'path' => '/tables/import',
                'method' => 'import_table',
                'capability' => 'tablepress_import_tables',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'tablepress_list_tables',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'tablepress_list_tables',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'tablepress_event_saved_table',
                'method' => 'payload_table_save',
                'args_count' => 1,
            )
        )
    ),
    array (
        'name' => esc_html__("Popup Maker"),
        'api_path' => 'popup-maker',
        'class_name' => 'Zoho_Flow_Popup_Maker',
        'gallery_app_link' => 'easy-digital-downloads',
        'description' => esc_html__('Use Popup Maker to create different kinds of popups on your WordPress site. By integrating Popup Maker with other applications, you can manage the visibility of popups on your WordPress site easily.', 'zoho-flow'),
        'icon_file' => 'popup-maker.png',
        'class_test' => 'Popup_Maker',
        'app_documentation_link' => '',
        'embed_link' => 'popup_maker',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/popups',
                'method' => 'list_popups',
                'capability' => 'read',
            ),
            array(
                'type' => 'create',
                'path' => '/popup/(?\'popup_id\'[\\d]+)/state',
                'method' => 'update_popup_status',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'pum_sub_form_success',
                'method' => 'payload_form_entry_added',
                'args_count' => 1,
            )
        )
    ),
    array(
        'name' => esc_html__("MailPoet"),
        'api_path' => 'mailpoet',
        'class_name' => 'Zoho_Flow_MailPoet',
        'gallery_app_link' => 'mailpoet',
        'description' => esc_html__('MailPoet is a newsletter plugin that can help you compose and design emails, maintain a subscriber list, and more. By integrating MailPoet with other applications, you\'ll be able to automate sending newsletters to your subscribers.', 'zoho-flow'),
        'icon_file' => 'mailpoet.png',
        'class_test' => 'MailPoet\API\MP\v1\API',
        'app_documentation_link' => '',
        'embed_link' => 'mailpoet',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/lists',
                'method' => 'get_lists',
                'capability' => 'mailpoet_manage_subscribers',
            ),
            array(
                'type' => 'list',
                'path' => '/fields',
                'method' => 'get_fields',
                'capability' => 'mailpoet_manage_subscribers',
            ),
            array(
                'type' => 'list',
                'path' => '/subscribers',
                'method' => 'get_subscribers',
                'capability' => 'mailpoet_manage_subscribers',
            ),
            array(
                'type' => 'list',
                'path' => '/subscriber',
                'method' => 'get_subscriber',
                'capability' => 'mailpoet_manage_subscribers',
            ),
            array(
                'type' => 'create',
                'path' => '/lists',
                'method' => 'create_list',
                'capability' => 'mailpoet_manage_subscribers',
            ),
            array(
                'type' => 'create',
                'path' => '/subscriber',
                'method' => 'create_subscriber',
                'capability' => 'mailpoet_manage_subscribers',
            ),
            array(
                'type' => 'create',
                'path' => '/subscriber/(?\'subscriber_id\'[\\d]+)/unsubscribe',
                'method' => 'unsubscribe_subscriber',
                'capability' => 'mailpoet_manage_subscribers',
            ),
            array(
                'type' => 'create',
                'path' => '/subscriber/(?\'subscriber_id\'[\\d]+)/subscribetolists',
                'method' => 'subscriber_subscribetolists',
                'capability' => 'mailpoet_manage_subscribers',
            ),
            array(
                'type' => 'create',
                'path' => '/subscriber/(?\'subscriber_id\'[\\d]+)/unsubscribefromlists',
                'method' => 'subscriber_unsubscribefromlists',
                'capability' => 'mailpoet_manage_subscribers',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'mailpoet_manage_subscribers',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'mailpoet_manage_subscribers',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'mailpoet_subscriber_created',
                'method' => 'payload_subscriber_created',
                'args_count' => 1,
            ),
            array (
                'action' => 'mailpoet_subscriber_updated',
                'method' => 'payload_subscriber_updated',
                'args_count' => 1,
            ),
            array (
                'action' => 'mailpoet_subscriber_status_changed',
                'method' => 'payload_subscriber_status_changed',
                'args_count' => 1,
            ),

        ),
    ),
    array (
        'name' => esc_html__("Forminator"),
        'api_path' => 'forminator',
        'class_name' => 'Zoho_Flow_Forminator',
        'gallery_app_link' => 'forminator',
        'description' => esc_html__('Use Forminator to create quizzes, polls, and forms on your WordPress site. When connected with Zoho Flow, you can create workflows that automatically compile quiz results in a spreadsheet, inform your team of new poll responses, or create tasks based on form feedback.', 'zoho-flow'),
        'icon_file' => 'forminator.png',
        'class_test' => 'Forminator_API',
        'app_documentation_link' => '',
        'embed_link' => 'forminator',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'get_forms',
                'capability' => 'manage_forminator',
            ),
            array(
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'get_form_fields',
                'capability' => 'manage_forminator',
            ),
            array(
                'type' => 'list',
                'path' => '/polls',
                'method' => 'get_polls',
                'capability' => 'manage_forminator',
            ),
            array(
                'type' => 'list',
                'path' => '/quizzes',
                'method' => 'get_quizzes',
                'capability' => 'manage_forminator',
            ),
            array(
                'type' => 'list',
                'path' => '/quizzes/(?\'quiz_id\'[\\d]+)/fields',
                'method' => 'get_quiz_fields',
                'capability' => 'manage_forminator',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_forminator',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'forminator_custom_form_mail_before_send_mail',
                'method' => 'payload_form_entry_added',
                'args_count' => 4,
            ),
            array (
                'action' => 'forminator_poll_mail_before_send_mail',
                'method' => 'payload_poll_added',
                'args_count' => 4,
            ),
            array (
                'action' => 'forminator_quiz_mail_before_send_mail',
                'method' => 'payload_quiz_added',
                'args_count' => 4,
            )
        )
    ),
    array (
        'name' => esc_html__("Fluent Forms"),
        'api_path' => 'fluent-forms',
        'class_name' => 'Zoho_Flow_Fluent_Forms',
        'gallery_app_link' => 'fluent-forms',
        'description' => esc_html__('Fluent Forms is a form builder plugin that can help you build many types of forms. Integrate Fluent Forms with your favorite applications using Zoho Flow to automatically add prospects who fill out your forms as contacts in your CRM.', 'zoho-flow'),
        'icon_file' => 'fluent-forms.png',
        'class_test' => 'FluentForm\App\Api\Form',
        'app_documentation_link' => '',
        'embed_link' => 'fluent_forms',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'get_all_forms',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'get_all_form_fields',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_private_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'delete_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'fluentform/submission_inserted',
                'method' => 'payload_submission_inserted',
                'args_count' => 3,
            )
        )
    ),
    array (
        'name' => esc_html__('Formidable Forms'),
        'api_path' => 'formidable-forms',
        'class_name' => 'Zoho_Flow_Formidable_Forms',
        'gallery_app_link' => 'formidable-forms',
        'description' => esc_html__('Build a simple contact form or complex multipage form with conditional logic, calculations, file uploads, and more, using Formidable Forms. You can then integrate it with other apps to automatically upload the forms to your team’s cloud drive, send new submissions to your team’s chat channel, or add contacts to your CRM.', 'zoho-flow'),
        'icon_file' => 'formidable-forms.png',
        'class_test' => 'FrmSettings',
        'app_documentation_link' => 'formidable-forms',
        'embed_link' => 'formidable_forms',
        'version' => 'v1',
        'rest_apis' => array (
            array (
                'type' => 'list',
                'path' => '/forms',
                'method' => 'get_forms',
                'capability' => 'frm_view_forms',
                'schema_method' => 'get_form_schema',
            ),
            array (
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'get_fields',
                'capability' => 'frm_view_forms',
                'schema_method' => 'get_field_schema',
            ),
            array (
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'get_webhooks',
                'capability' => 'frm_view_forms',
                'schema_method' => 'get_form_webhook_schema',
            ),
            array (
                'type' => 'create',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'create_webhook_deprecated',
                'capability' => 'frm_edit_forms',
            ),
            array (
                'type' => 'delete',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook_deprecated',
                'capability' => 'frm_delete_forms',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_private_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'delete_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array (
            array (
                'action' => 'frm_after_create_entry',
                'method' => 'process_form_submission',
                'args_count' => 3,
            ),
            array (
                'action' => 'frm_after_create_entry',
                'method' => 'payload_entry_created',
                'args_count' => 3,
            ),
            array (
                'action' => 'frm_after_update_entry',
                'method' => 'payload_entry_updated',
                'args_count' => 2,
            ),
        ),
    ),
    array (
        'name' => esc_html__("MetForm"),
        'api_path' => 'metform',
        'class_name' => 'Zoho_Flow_MetForm',
        'gallery_app_link' => 'metform',
        'description' => esc_html__('MetForm, the drag-and-drop WordPress contact form builder is an addon for Elementor. This Elementor form builder plugin provides an intuitive real-time form-building experience, allowing anyone of any expertise level to create professionally designed website forms.', 'zoho-flow'),
        'icon_file' => 'metform.png',
        'class_test' => 'MetForm\Plugin',
        'app_documentation_link' => '',
        'embed_link' => 'metform',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'list_forms',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'list_form_fields',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'metform_after_store_form_data',
                'method' => 'payload_form_entry_submitted',
                'args_count' => 4,
            )
        ),
    ),
    array (
        'name' => esc_html__("Kadence Blocks"),
        'api_path' => 'kadence-blocks',
        'class_name' => 'Zoho_Flow_Kadence_Blocks',
        'gallery_app_link' => 'metform',
        'description' => esc_html__('Use Kadence Blocks to create customized content on your WordPress site. By integrating Kadence Blocks with other applications, you collect form entries from  visitors on your WordPress site.', 'zoho-flow'),
        'icon_file' => 'kadence-blocks.png',
        'class_test' => 'KB_Ajax_Form',
        'app_documentation_link' => '',
        'embed_link' => 'kadence_blocks',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'list_forms',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'list_form_fields',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'kadence_blocks_form_submission',
                'method' => 'payload_form_entry_submitted',
                'args_count' => 4,
            ),
            array (
                'action' => 'kadence_blocks_advanced_form_submission',
                'method' => 'payload_form_entry_submitted_advanced_forms',
                'args_count' => 3,
            )
        ),
    ),
    array(
        'name' => esc_html__("FluentSMTP"),
        'api_path' => 'fluentsmtp',
        'class_name' => 'Zoho_Flow_FluentSMTP',
        'gallery_app_link' => 'fluentsmtp',
        'description' => esc_html__('FluentSMTP is an SMTP WordPress plugin that lets you send transactional and marketing emails without delivery issues. By integrating Fluent SMTP with other applications, you will get notified when a mail has been delivered successfully.', 'zoho-flow'),
        'icon_file' => 'fluentsmtp.png',
        'class_test' => 'FluentMail\App\Models\Logger',
        'app_documentation_link' => '',
        'embed_link' => 'fluentsmtp',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/stats/all',
                'method' => 'get_overall_stats',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/stats/period',
                'method' => 'get_periodic_stats',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/mail/resend/(?\'log_id\'[\\d]+)',
                'method' => 'resend_mail_from_logger',
                'capability' => 'edit_private_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_private_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'fluentmail_email_sending_failed',
                'method' => 'payload_send_failure',
                'args_count' => 2,
            ),
        ),
    ),
    array (
        'name' => esc_html__("UserFeedback"),
        'api_path' => 'userfeedback',
        'class_name' => 'Zoho_Flow_UserFeedback',
        'gallery_app_link' => 'userfeedback',
        'description' => esc_html__('UserFeedback can help you collect feedback from the visitors on your WordPress site. By integrating UserFeedback with other applications, analyzing customer feedback can become easier.', 'zoho-flow'),
        'icon_file' => 'userfeedback.png',
        'class_test' => 'UserFeedback_Survey',
        'app_documentation_link' => '',
        'embed_link' => 'userfeedback',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/surveys',
                'method' => 'list_surveys',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/surveys/(?\'survey_id\'[\\d]+)/questions',
                'method' => 'list_survey_questions',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'userfeedback_survey_response',
                'method' => 'payload_response_added',
                'args_count' => 3,
            )
        ),
    ),
    array (
        'name' => esc_html__('Ultimate Member'),
        'api_path' => 'ultimate-member',
        'class_name' => 'Zoho_Flow_Ultimate_Member',
        'gallery_app_link' => 'ultimate-member',
        'description' => esc_html__('Ultimate Member’s WordPress plugin makes it a breeze for users to sign up and become members of your website. Easily manage your forms by automating follow-ups for form submissions, contact management, cloud storage, and more, using Zoho Flow.', 'zoho-flow'),
        'icon_file' => 'ultimate-member.png',
        'class_test' => 'UM',
        'app_documentation_link' => '',
        'embed_link' => 'ultimate_member',
        'version' => 'v1',
        'rest_apis' => array (
            array (
                'type' => 'list',
                'path' => '/forms',
                'method' => 'list_all_forms',
                'capability' => 'manage_options',
                'schema_method' => 'get_form_schema',
            ),
            array ( //Deprecated in version 2.9.1
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'get_fields',
                'capability' => 'manage_options',
                'schema_method' => 'get_field_schema',
            ),
            array ( //Deprecated in version 2.9.1
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'get_webhooks',
                'capability' => 'manage_options',
                'schema_method' => 'get_form_webhook_schema',
            ),
            array ( //Deprecated in version 2.9.1
                'type' => 'create',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'create_webhook_old',
                'capability' => 'manage_options',
            ),
            array ( //Deprecated in version 2.9.1
                'type' => 'delete',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook_old',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'list',
                'path' => '/custom-fields',
                'method' => 'list_all_fields',
                'capability' => 'list_users'
            ),
            array (
                'type' => 'list',
                'path' => '/user',
                'method' => 'fetch_user',
                'capability' => 'list_users'
            ),
            array (
                'type' => 'update',
                'path' => '/user/(?\'user_id\'[\\d]+)/status/(?\'status\'[a-zA-Z_-]+)',
                'method' => 'update_user_status',
                'capability' => 'edit_users'
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'list_users',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array (
            array ( //Deprecated in version 2.9.1
                'action' => 'um_after_save_registration_details',
                'method' => 'process_form_submission',
                'args_count' => 2,
            ),
            array(  //Deprecated in version 2.9.1
                'action' => 'um_after_user_updated',
                'method' => 'um_user_updated',
                'args_count' => 3,
            ),
            array(
                'action' => 'um_user_after_updating_profile',
                'method' => 'payload_user_profile_updated',
                'args_count' => 3,
            ),
            array(
                'action' => 'um_after_user_account_updated',
                'method' => 'payload_user_account_updated',
                'args_count' => 2,
            ),
            array(
                'action' => 'um_after_member_role_upgrade',
                'method' => 'payload_member_role_changed',
                'args_count' => 3,
            ),
            array(
                'action' => 'um_after_user_status_is_changed',
                'method' => 'payload_user_status_changed',
                'args_count' => 2,
            ),
            array(
                'action' => 'um_after_save_registration_details',
                'method' => 'payload_user_registered',
                'args_count' => 3,
            ),
        ),
    ),
    array (
        'name' => esc_html__('LearnDash'),
        'api_path' => 'learndash',
        'class_name' => 'Zoho_Flow_LearnDash',
        'gallery_app_link' => 'learndash',
        'description' => esc_html__('LearnDash helps you better sell your online courses by providing multiple pricing models, payment gateways, and automatic renewal notifications. Use Zoho Flow to automatically add new users enrolled in your course to your CRM, send customized emails to users who’ve completed quizzes, add users to a specific group, and more.', 'zoho-flow'),
        'icon_file' => 'learndash.png',
        'class_test' => 'Sfwd_Lms',
        'app_documentation_link' => '',
        'embed_link' => 'learndash_lms',
        'version' => 'v1',
        'rest_apis' => array (
            array (
                'type' => 'list',
                'path' => '/courses',
                'method' => 'get_courses',
                'capability' => 'manage_options',
                'schema_method' => 'get_course_schema',
            ),
            array (
                'type' => 'list',
                'path' => '/groups',
                'method' => 'get_groups',
                'capability' => 'manage_options',
                'schema_method' => 'get_group_schema',
            ),
            array (
                'type' => 'create',
                'path' => '/course/(?\'course_id\'[\\d]+)/enroll',
                'method' => 'enroll_user_to_course',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'create',
                'path' => '/group/(?\'group_id\'[\\d]+)/add_users',
                'method' => 'add_users_to_group',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'update',
                'path' => '/group/(?\'group_id\'[\\d]+)/remove_users',
                'method' => 'remove_users_from_group',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'update',
                'path' => '/course/(?\'course_id\'[\\d]+)/remove_user',
                'method' => 'remove_users_from_course',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'list',
                'path' => '/user/(?\'user_id\'[\\d]+)/courses',
                'method' => 'user_courses',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'list',
                'path' => '/course/(?\'course_id\'[\\d]+)/quizzes',
                'method' => 'get_quizzes',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'list',
                'path' => '/group/(?\'group_id\'[\\d]+)/users',
                'method' => 'group_users',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'list',
                'path' => '/users',
                'method' => 'get_users',
                'capability' => 'read',
                'schema_method' => 'get_user_schema',
            ),
            array (
                'type' => 'list',
                'path' => '/course/(?\'course_id\'[\\d]+)',
                'method' => 'get_courses',
                'capability' => 'read',
            ),
            array (
                'type' => 'list',
                'path' => '/course/(?\'course_id\'[\\d]+)/lessons',
                'method' => 'get_lessons',
                'capability' => 'read',
            ),
            array (
                'type' => 'list',
                'path' => '/lesson/(?\'lesson_id\'[\\d]+)/topics',
                'method' => 'get_topics',
                'capability' => 'read',
            ),
            array (
                'type' => 'list',
                'path' => '/post_types',
                'method' => 'list_post_types',
                'capability' => 'read',
            ),
            array (
                'type' => 'list',
                'path' => '/questions',
                'method' => 'get_ldquestions',
                'capability' => 'read',
            ),
            array (
                'type' => 'list',
                'path' => '/essay_submissions',
                'method' => 'get_essay_submissions',
                'capability' => 'read',
            ),
            array (
                'type' => 'create',
                'path' => '/(?\'action\'.+)/(?\'form_id\'[\\d]+)/webhook',
                'method' => 'create_webhook',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'delete',
                'path' => '/webhook/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'list',
                'path' => '/(?\'action\'.+)/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'get_webhooks',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'list',
                'path' => '/webhooks',
                'method' => 'get_all_webhooks',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array (
            array (
                'action' => 'learndash_course_completed',
                'method' => 'process_course_completed',
                'args_count' => 1,
            ),
            array(
                'action' => 'learndash_topic_completed',
                'method' => 'process_topic_completed',
                'args_count' => 1
            ),
            array(
                'action' => 'learndash_lesson_completed',
                'method' => 'process_lesson_completed',
                'args_count' => 1
            ),
            array(
                'action' => 'learndash_quiz_completed',
                'method' => 'process_quiz_completed',
                "args_count" => 2
            ),
            array(
                'action' => 'learndash_new_essay_submitted',
                'method' => 'process_essay_submitted',
                "args_count" => 2
            ),
            array(
                'action' => 'learndash_update_course_access',
                'method' => 'process_enrolled_into_course',
                "args_count" => 4

            ),
            array(
                'action' => 'ld_added_group_access',
                'method' => 'process_group_enrolled',
                'args_count'=> 2
            )
        ),
    ),
    array (
        'name' => esc_html__('Everest Forms'),
        'api_path' => 'everest-forms',
        'class_name' => 'Zoho_Flow_Everest_Forms',
        'gallery_app_link' => 'everest-forms',
        'description' => esc_html__('Everest Forms is a drag-and-drop form builder plugin that’s lightweight, fast, and mobile responsive. Automatically create calendar events from new form submissions, add subscribers to your mailing list, create tickets for complaints received, and more, using Zoho Flow.', 'zoho-flow'),
        'icon_file' => 'everest-forms.png',
        'class_test' => 'EverestForms',
        'app_documentation_link' => 'everest-forms',
        'embed_link' => 'everest_forms',
        'version' => 'v1',
        'rest_apis' => array (
            array (
                'type' => 'list',
                'path' => '/forms',
                'method' => 'get_forms',
                'capability' => 'manage_everest_forms',
                'schema_method' => 'get_form_schema',
            ),
            array (
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'get_fields',
                'capability' => 'manage_everest_forms',
                'schema_method' => 'get_field_schema',
            ),
            array (
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'get_webhooks',
                'capability' => 'manage_everest_forms',
                'schema_method' => 'get_form_webhook_schema',
            ),
            array (
                'type' => 'create',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_everest_forms',
            ),
            array (
                'type' => 'delete',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'manage_everest_forms',
            ),
        ),
        'hooks' => array (
            array (
                'action' => 'everest_forms_process_complete',
                'method' => 'process_form_submission',
                'args_count' => 4,
            ),
        ),
    ),
    array (
        'name' => esc_html__("Hustle"),
        'api_path' => 'hustle',
        'class_name' => 'Zoho_Flow_Hustle',
        'gallery_app_link' => 'hustle',
        'description' => esc_html__('Use Hustle to create mailing lists and marketing templates for pop-ups, banners, slide-ins, and more on your WordPress site. By integrating Hustle with your favorite applications, you will be able to create automated emails for your mailing lists.', 'zoho-flow'),
        'icon_file' => 'hustle.png',
        'class_test' => 'Hustle_Module_Collection',
        'app_documentation_link' => '',
        'embed_link' => 'hustle',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/modules',
                'method' => 'list_modules',
                'capability' => 'hustle_edit_module',
            ),
            array(
                'type' => 'list',
                'path' => '/modules/(?\'module_id\'[\\d]+)/fields',
                'method' => 'list_fields',
                'capability' => 'hustle_edit_module',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'hustle_access_emails',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'hustle_form_submit_before_set_fields',
                'method' => 'payload_entry_created',
                'args_count' => 3,
            )
        ),
    ),
    array (
        'name' => esc_html__("GiveWP"),
        'api_path' => 'givewp',
        'class_name' => 'Zoho_Flow_GiveWP',
        'gallery_app_link' => 'givewp',
        'description' => esc_html__('GiveWP is an online donation and fundraising platform for your WordPress website. Build GiveWP integrations on Zoho Flow to instantly notify your team of new donations, generate automated thank-you notes, or even update donor information in your CRM.', 'zoho-flow'),
        'icon_file' => 'givewp.png',
        'class_test' => 'Give_Donate_Form',
        'app_documentation_link' => '',
        'embed_link' => 'givewp',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'get_forms',
                'capability' => 'read_private_give_forms',
            ),
            array(
                'type' => 'list',
                'path' => '/donor',
                'method' => 'get_donor',
                'capability' => 'read_private_give_forms',
            ),
            array(
                'type' => 'create',
                'path' => '/donors/(?\'donor_id\'[\\d]+)/notes',
                'method' => 'add_donor_note',
                'capability' => 'edit_give_forms',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'read_private_give_forms',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'give_donor_post_create',
                'method' => 'payload_donar_added',
                'args_count' => 2,
            ),
            array (
                'action' => 'give_complete_form_donation',
                'method' => 'payload_donation_form_complete',
                'args_count' => 3,
            ),
        ),
    ),
    array (
        'name' => esc_html__("Download Manager"),
        'api_path' => 'download-manager',
        'class_name' => 'Zoho_Flow_Download_Manager',
        'gallery_app_link' => 'download-manager',
        'description' => esc_html__('Use Download Manager to manage file downloads from your WordPres site. By integrating Download Manager with your favorite applications, you can get notified when someone downloads a file from your WordPress site.', 'zoho-flow'),
        'icon_file' => 'download-manager.png',
        'class_test' => 'WPDM\__\DownloadStats',
        'app_documentation_link' => '',
        'embed_link' => 'download_manager',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'wpdm_onstart_download',
                'method' => 'payload_download_started',
                'args_count' => 1,
            )
        )
    ),
    array (
        'name' => esc_html__("Paid Memberships Pro"),
        'api_path' => 'paid-memberships-pro',
        'class_name' => 'Zoho_Flow_Paid_Memberships_Pro',
        'gallery_app_link' => 'paid-memberships-pro',
        'description' => esc_html__('Elevate your membership site with Paid Memberships Pro, offering levels, subscription packages, and more. Connect it with Zoho Flow, to easily handle member renewals, send out reminder emails before expiration, or even promote upgrades based on user activities.', 'zoho-flow'),
        'icon_file' => 'paid-memberships-pro.png',
        'class_test' => 'MemberOrder',
        'app_documentation_link' => '',
        'embed_link' => 'paid_memberships_pro',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/memberfields',
                'method' => 'get_fields',
                'capability' => 'pmpro_userfields',
            ),
            array(
                'type' => 'list',
                'path' => '/membershiplevels',
                'method' => 'get_levels',
                'capability' => 'pmpro_membershiplevels',
            ),
            array(
                'type' => 'list',
                'path' => '/member',
                'method' => 'get_user',
                'capability' => 'pmpro_memberslist',
            ),
            array(
                'type' => 'create',
                'path' => '/membershiplevelchange',
                'method' => 'change_user_membership_level',
                'capability' => 'pmpro_membershiplevels',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'pmpro_memberslist',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'pmpro_added_order',
                'method' => 'payload_order_added',
                'args_count' => 1,
            ),
            array (
                'action' => 'pmpro_updated_order',
                'method' => 'payload_order_updated',
                'args_count' => 1,
            ),
            array (
                'action' => 'pmpro_after_change_membership_level',
                'method' => 'payload_membership_level_changed',
                'args_count' => 3,
            ),
        )
    ),
    array(
        'name' => esc_html__("Event Tickets"),
        'api_path' => 'event-tickets',
        'class_name' => 'Zoho_Flow_Event_Tickets',
        'gallery_app_link' => 'event-tickets',
        'description' => esc_html__('Use Event Tickets to collect registrations, sell tickets, manage attendees, and more from your WordPress site. By integrating Event Tickets with messaging applications, you can automatically notify attendees of any event or appointment related updates.', 'zoho-flow'),
        'icon_file' => 'event-tickets.png',
        'class_test' => 'TEC\Tickets\Commerce\Attendee',
        'app_documentation_link' => '',
        'embed_link' => 'event_tickets',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/rsvp-tickets',
                'method' => 'list_rsvp_tickets',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/tc-tickets',
                'method' => 'list_commerce_tickets',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/attendee/(?\'attendee_id\'[\\d]+)',
                'method' => 'fetch_attendee',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/tc-order/(?\'order_id\'[\\d]+)',
                'method' => 'fetch_order',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'event_tickets_rsvp_attendee_created',
                'method' => 'payload_rsvp_attendee_added',
                'args_count' => 4,
            ),
            array (
                'action' => 'event_tickets_rsvp_attendee_created',
                'method' => 'payload_attendee_added_rsvp',
                'args_count' => 4,
            ),
            array (
                'action' => 'tec_tickets_commerce_attendee_after_create',
                'method' => 'payload_tc_attendee_added',
                'args_count' => 4,
            ),
            array (
                'action' => 'tec_tickets_commerce_attendee_after_create',
                'method' => 'payload_attendee_added_tc',
                'args_count' => 4,
            ),
            array (
                'action' => 'tec_tickets_commerce_order_status_completed',
                'method' => 'payload_tc_order_completed',
                'args_count' => 3,
            ),
            array (
                'action' => 'rsvp_checkin',
                'method' => 'payload_rsvp_checkin',
                'args_count' => 2,
            ),
            array (
                'action' => 'event_tickets_checkin',
                'method' => 'payload_tc_checkin',
                'args_count' => 3,
            ),
            array (
                'action' => 'rsvp_checkin',
                'method' => 'payload_checkin',
                'args_count' => 2,
            ),
            array (
                'action' => 'event_tickets_checkin',
                'method' => 'payload_checkin',
                'args_count' => 2,
            ),
            array (
                'action' => 'rsvp_uncheckin',
                'method' => 'payload_uncheckin',
                'args_count' => 1,
            ),
            array (
                'action' => 'event_tickets_uncheckin',
                'method' => 'payload_uncheckin',
                'args_count' => 1,
            ),
        )
    ),
    array(
        'name' => esc_html__("Events Manager"),
        'api_path' => 'events-manager',
        'class_name' => 'Zoho_Flow_Events_Manager',
        'gallery_app_link' => 'events-manager',
        'description' => esc_html__('Use Events Manager to create events, enable registrations, display booking calendars, and more on your Wordpress site. By integrating Events Manager with spreadsheets, you will be able to move registrant information to spreadsheets automatically, offering you valuable insights from that data.', 'zoho-flow'),
        'icon_file' => 'events-manager.png',
        'class_test' => 'EM_Booking',
        'app_documentation_link' => '',
        'embed_link' => 'events_manager',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/events',
                'method' => 'list_events',
                'capability' => 'read_private_events',
            ),
            array(
                'type' => 'list',
                'path' => '/events/(?\'event_id\'[\\d]+)/tickets',
                'method' => 'list_tickets',
                'capability' => 'read_private_events',
            ),
            array(
                'type' => 'list',
                'path' => '/booking-statuses',
                'method' => 'list_booking_status',
                'capability' => 'manage_bookings',
            ),
            array(
                'type' => 'list',
                'path' => '/booking/(?\'booking_id\'[\\d]+)',
                'method' => 'fetch_booking',
                'capability' => 'manage_bookings',
            ),
            array(
                'type' => 'create',
                'path' => '/booking/(?\'booking_id\'[\\d]+)/status/(?\'status\'[\\d]+)',
                'method' => 'update_booking_status',
                'capability' => 'manage_bookings',
            ),
            array(
                'type' => 'create',
                'path' => '/booking/(?\'booking_id\'[\\d]+)/note',
                'method' => 'add_booking_note',
                'capability' => 'manage_bookings',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'em_booking_status_changed',
                'method' => 'payload_booking_status_updated',
                'args_count' => 2,
            ),
            array (
                'action' => 'em_booking_rsvp_status_changed',
                'method' => 'payload_booking_rsvp_status_updated',
                'args_count' => 3,
            ),
            array (
                'action' => 'em_bookings_added',
                'method' => 'payload_booking_added',
                'args_count' => 1,
            )
        )
    ),
    array (
        'name' => esc_html__("Tutor LMS"),
        'api_path' => 'tutor-lms',
        'class_name' => 'Zoho_Flow_Tutor_LMS',
        'gallery_app_link' => 'tutor-lms',
        'description' => esc_html__('Use Tutor LMS to create and sell online courses on your WordPress site. By integrating Tutor LMS with your favourite applications, you will be track the progress of courses more efficiently.', 'zoho-flow'),
        'icon_file' => 'tutor-lms.png',
        'class_test' => 'TUTOR\Course',
        'app_documentation_link' => '',
        'embed_link' => 'tutor_lms',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/courses',
                'method' => 'list_courses',
                'capability' => 'read_tutor_course',
            ),
            array(
                'type' => 'list',
                'path' => '/courses/(?\'course_id\'[\\d]+)/topics',
                'method' => 'list_course_topics',
                'capability' => 'read_tutor_course',
            ),
            array(
                'type' => 'list',
                'path' => '/topics/(?\'topic_id\'[\\d]+)/lessons',
                'method' => 'list_topic_lessons',
                'capability' => 'read_tutor_lesson',
            ),
            array(
                'type' => 'list',
                'path' => '/topics/(?\'topic_id\'[\\d]+)/quizzes',
                'method' => 'list_topic_quizzes',
                'capability' => 'read_tutor_quiz',
            ),
            array(
                'type' => 'list',
                'path' => '/quizzes/(?\'quiz_id\'[\\d]+)/questions',
                'method' => 'list_quiz_questions',
                'capability' => 'read_tutor_quiz',
            ),
            array(
                'type' => 'create',
                'path' => '/courses/(?\'course_id\'[\\d]+)/enroll',
                'method' => 'enroll_user_to_course',
                'capability' => 'manage_tutor',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_tutor',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'tutor_after_enrolled',
                'method' => 'payload_user_enrolled_to_course',
                'args_count' => 3,
            ),
            array (
                'action' => 'tutor_mark_lesson_complete_after',
                'method' => 'payload_user_completed_lesson',
                'args_count' => 2,
            ),
            array (
                'action' => 'tutor_course_complete_after',
                'method' => 'payload_user_completed_course',
                'args_count' => 2,
            ),
            array (
                'action' => 'tutor_quiz/start/after',
                'method' => 'payload_user_started_quiz',
                'args_count' => 3,
            ),
            array (
                'action' => 'tutor_quiz/attempt_ended',
                'method' => 'payload_user_completed_quiz',
                'args_count' => 3,
            ),
            array (
                'action' => 'tutor_after_student_signup',
                'method' => 'payload_student_signup',
                'args_count' => 1,
            )
        )
    ),
    array (
        'name' => esc_html__("User Registration"),
        'api_path' => 'user-registration',
        'class_name' => 'Zoho_Flow_User_Registration',
        'gallery_app_link' => 'user-registration',
        'description' => esc_html__('Simplify user signups on your WordPress site with the User Registration plugin. When connected to Zoho Flow, automate onboarding processes by sending welcome emails, adding users to specific groups, or initiating a new member journey in your marketing app.', 'zoho-flow'),
        'icon_file' => 'user-registration.png',
        'class_test' => 'UR_Form_Handler',
        'app_documentation_link' => '',
        'embed_link' => 'user_registration',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'get_forms',
                'capability' => 'manage_user_registration',
            ),
            array(
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'get_form_fields',
                'capability' => 'manage_user_registration',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_user_registration',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'user_registration_after_register_user_action',
                'method' => 'payload_after_register_user_action',
                'args_count' => 3,
            )
        )
    ),
    array(
        'name' => esc_html__("Ninja Tables"),
        'api_path' => 'ninja-tables',
        'class_name' => 'Zoho_Flow_NinjaTables',
        'gallery_app_link' => 'ninja-tables',
        'description' => esc_html__('Ninja Tables a table builder plugin you can use to create and manage tables and view data in multiple formats. Integrate Ninja Tables with your favorite applications to ensure an instant and seamless flow of data transfer to your tables.', 'zoho-flow'),
        'icon_file' => 'ninja-tables.png',
        'class_test' => 'NinjaTables\App\Models\NinjaTableItem',
        'app_documentation_link' => '',
        'embed_link' => 'ninja_tables',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/tables',
                'method' => 'list_tables',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/tables/(?\'table_id\'[\\d]+)',
                'method' => 'get_table_details',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/tables/(?\'table_id\'[\\d]+)/row/(?\'row_id\'[\\d]+)',
                'method' => 'fetch_table_row',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/tables/(?\'table_id\'[\\d]+)/row',
                'method' => 'add_table_row',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'update',
                'path' => '/tables/(?\'table_id\'[\\d]+)/row/(?\'row_id\'[\\d]+)',
                'method' => 'update_table_row',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_private_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'delete_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'ninja_table_after_add_item',
                'method' => 'payload_added_item',
                'args_count' => 3,
            ),
            array (
                'action' => 'ninja_table_after_update_item',
                'method' => 'payload_updated_item',
                'args_count' => 3,
            ),
        )
    ),
    array (
        'name' => esc_html__("Amelia"),
        'api_path' => 'amelia',
        'class_name' => 'Zoho_Flow_Amelia',
        'gallery_app_link' => 'amelia',
        'description' => esc_html__('Use Amelia to manage appointment scheduling on your WordPress site. By integrating Amelia with your favorite applications, you will be able to send reminder notifications to attendees whenever there is an event.', 'zoho-flow'),
        'icon_file' => 'amelia.png',
        'class_test' => 'AmeliaBooking\Plugin',
        'app_documentation_link' => '',
        'embed_link' => 'amelia',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/events',
                'method' => 'list_events',
                'capability' => 'amelia_read_events',
            ),
            array(
                'type' => 'list',
                'path' => '/services',
                'method' => 'list_services',
                'capability' => 'amelia_read_services',
            ),
            array(
                'type' => 'list',
                'path' => '/categories',
                'method' => 'list_categories',
                'capability' => 'amelia_read_services',
            ),
            array(
                'type' => 'list',
                'path' => '/categories/(?\'category_id\'[\\d]+)/services',
                'method' => 'list_services_by_category',
                'capability' => 'amelia_read_services',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'amelia_after_booking_added',
                'method' => 'payload_event_booking_added',
                'args_count' => 1,
            ),
            array (
                'action' => 'amelia_after_booking_added',
                'method' => 'payload_service_booking_added',
                'args_count' => 1,
            ),
            array (
                'action' => 'amelia_after_customer_added',
                'method' => 'payload_customer_added',
                'args_count' => 1,
            )
        ),
    ),
    array (
        'name' => esc_html__("Bookly"),
        'api_path' => 'bookly',
        'class_name' => 'Zoho_Flow_Bookly',
        'gallery_app_link' => 'bookly',
        'description' => esc_html__('Use Bookly to accept online bookings for events, automate reservations, and more on your WordPress site. Integrate Bookly with your favourite applications to send email confirmations or notifications when someone registers for an event.', 'zoho-flow'),
        'icon_file' => 'bookly.png',
        'class_test' => 'Bookly\Lib\Entities\Appointment',
        'app_documentation_link' => '',
        'embed_link' => 'bookly',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/services',
                'method' => 'list_services',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/customers',
                'method' => 'list_customers',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/appointments',
                'method' => 'list_appointments',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
        ),
    ),
    array (
        'name' => esc_html__("JetFormBuilder"),
        'api_path' => 'jetformbuilder',
        'class_name' => 'Zoho_Flow_JetFormBuilder',
        'gallery_app_link' => 'jetformbuilder',
        'description' => esc_html__('Use JetFormBuilder to create new forms and update the style and format of the existing forms on your WordPress site. Integrate JetFormBuilder to move form entries from WordPress to your favorite applications.', 'zoho-flow'),
        'icon_file' => 'jetformbuilder.png',
        'class_test' => 'JFB_Modules\Form_Record\Query_Views\Record_Fields_View',
        'app_documentation_link' => '',
        'embed_link' => 'jetformbuilder',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'list_forms',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'list_form_fields',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'jet-form-builder/form-handler/after-send',
                'method' => 'payload_record_added',
                'args_count' => 2,
            )
        ),
    ),
    array (
        'name' => esc_html__("WP-Members"),
        'api_path' => 'wp-members',
        'class_name' => 'Zoho_Flow_WP_Members',
        'gallery_app_link' => 'wp-members',
        'description' => esc_html__('Use WP-Members to manage content restrictions for registered users on your WordPress site. By integrating WP-Members with your favorite applications, you\'ll be able to manage your members\' membership levels more efficiently.', 'zoho-flow'),
        'icon_file' => 'wp-members.png',
        'class_test' => 'WP_Members',
        'function_test' => 'wpmem_init',
        'app_documentation_link' => '',
        'embed_link' => 'wp_members',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/fields',
                'method' => 'get_all_fields',
                'capability' => 'edit_users',
            ),
            array(
                'type' => 'list',
                'path' => '/user',
                'method' => 'fetch_user',
                'capability' => 'edit_users',
            ),
            array(
                'type' => 'update',
                'path' => '/user/(?\'user_id\'[\\d]+)/activate',
                'method' => 'activate_user',
                'capability' => 'edit_users',
            ),
            array(
                'type' => 'update',
                'path' => '/user/(?\'user_id\'[\\d]+)/deactivate',
                'method' => 'deactivate_user',
                'capability' => 'edit_users',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'wpmem_post_register_data',
                'method' => 'payload_user_registered',
                'args_count' => 2,
            ),
            array (
                'action' => 'wpmem_post_update_data',
                'method' => 'payload_user_profile_updated',
                'args_count' => 3,
            ),
            array (
                'action' => 'wpmem_user_activated',
                'method' => 'payload_user_activated',
                'args_count' => 1,
            ),
            array (
                'action' => 'wpmem_user_deactivated',
                'method' => 'payload_user_deactivated',
                'args_count' => 1,
            )
        ),
    ),
    array (
        'name' => esc_html__("WP-Polls"),
        'api_path' => 'wp-polls',
        'class_name' => 'Zoho_Flow_WP_Polls',
        'gallery_app_link' => 'wp-polls',
        'description' => esc_html__('Use WP-Polls to create polls on your WordPress site. By integrating WP-Polls with your favorite applications, you can review and analyze your poll results and derive insights from them.', 'zoho-flow'),
        'icon_file' => 'wp-polls.png',
        'class_test' => 'WP_Widget_Polls',
        'app_documentation_link' => '',
        'embed_link' => 'wp_polls',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/polls',
                'method' => 'list_polls',
                'capability' => 'manage_polls',
            ),
            array(
                'type' => 'list',
                'path' => '/polls/(?\'poll_id\'[\\d]+)',
                'method' => 'get_poll',
                'capability' => 'manage_polls',
            ),
            array(
                'type' => 'list',
                'path' => '/polls/(?\'poll_id\'[\\d]+)/options',
                'method' => 'list_poll_options',
                'capability' => 'manage_polls',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_polls',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'wp_polls_vote_poll_success',
                'method' => 'payload_poll_submitted',
                'args_count' => 0,
            )
        )
    ),
    array (
        'name' => esc_html__("WP Booking Calendar"),
        'api_path' => 'wp-booking-calendar',
        'class_name' => 'Zoho_Flow_WP_Booking_Calendar',
        'gallery_app_link' => 'wp-booking-calendar',
        'description' => esc_html__('Use WP Booking Calendar to manage your event bookings more efficiently on your WordPress site. You can integrate WP Booking Calendar with other applications to offer a seamless and hassle-free event experience for your attendees.', 'zoho-flow'),
        'icon_file' => 'wp-booking-calendar.png',
        'class_test' => 'WPBC_Settings_API_General',
        'app_documentation_link' => '',
        'embed_link' => 'wp_booking_calendar',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/fields',
                'method' => 'list_fields',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'wpbc_track_new_booking',
                'method' => 'payload_booking_added',
                'args_count' => 1,
            ),
            array (
                'action' => 'wpbc_set_booking_approved',
                'method' => 'payload_booking_approved',
                'args_count' => 2,
            ),
            array (
                'action' => 'wpbc_set_booking_pending',
                'method' => 'payload_booking_pending',
                'args_count' => 2,
            ),
            array (
                'action' => 'wpbc_move_booking_to_trash',
                'method' => 'payload_booking_moved_to_trash',
                'args_count' => 2,
            )
        ),
    ),
    array (
        'name' => esc_html__("Easy Digital Downloads"),
        'api_path' => 'easy-digital-downloads',
        'class_name' => 'Zoho_Flow_Easy_Digital_Downloads',
        'gallery_app_link' => 'easy-digital-downloads',
        'description' => esc_html__('Use Easy Digital Downloads to build and manage an online store on your WordPress site. By integrating Easy Digital Downloads with your favourite applications, you will be able to derive insights from customer data more efficiently.', 'zoho-flow'),
        'icon_file' => 'edd.png',
        'class_test' => 'EDD_Customer',
        'app_documentation_link' => '',
        'embed_link' => 'easy_digital_downloads',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/customers',
                'method' => 'get_customers',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/order',
                'method' => 'fetch_order',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/customer',
                'method' => 'fetch_customer',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/download',
                'method' => 'fetch_download',
                'capability' => 'read',
            ),
            array(
                'type' => 'create',
                'path' => '/customer/(?\'customer_id\'[\\d]+)/note',
                'method' => 'add_customer_note',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/order/(?\'order_id\'[\\d]+)/note',
                'method' => 'add_order_note',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/order/(?\'order_id\'[\\d]+)/sendreceipt',
                'method' => 'send_receipt',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'edd_complete_purchase',
                'method' => 'payload_order_created',
                'args_count' => 3,
            ),
           array (
                'action' => 'edd_complete_download_purchase',
                'method' => 'payload_download_purchased',
                'args_count' => 5,
            ),
            array (
                'action' => 'edd_update_payment_status',
                'method' => 'payload_payment_status_changed',
                'args_count' => 3,
            ),
            array (
                'action' => 'edd_refund_order',
                'method' => 'payload_payment_refund',
                'args_count' => 3,
            ),
            array (
                'action' => 'edd_customer_post_update',
                'method' => 'payload_customer_updated',
                'args_count' => 3,
            ),
            array (
                'action' => 'edd_post_add_customer_email',
                'method' => 'payload_customer_email_added',
                'args_count' => 2,
            )
        )
    ),
    array (
        'name' => esc_html__('Simple Membership'),
        'api_path' => 'simple-membership',
        'class_name' => 'Zoho_Flow_Simple_Membership',
        'gallery_app_link' => 'simple-membership',
        'description' => esc_html__('With Simple Membership, protect your WordPress posts and pages, restricting access only to members. Integrate Simple Membership with the other apps you use to automatically update membership levels in your CRM, notify members about expiration through various platforms, or even send out personalized member-only offers.', 'zoho-flow'),
        'icon_file' => 'simple-membership.png',
        'class_test' => 'SimpleWpMembership',
        'app_documentation_link' => '',
        'embed_link' => 'simple_membership',
        'version' => 'v1',
        'rest_apis' => array (
            array(
                'type' => 'create',
                'path' => '/create_membership_level', //Deprecated
                'method' => 'create_membership',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'update',
                'path' => '/update_membership_level', //Deprecated
                'method' => 'update_membership',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'create',
                'path' => '/createmember', //Deprecated
                'method' => 'create_member',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'update',
                'path' => '/updatemember', //Deprecated
                'method' => 'update_member',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'list',
                'path' => '/getmember/(?\'member_id\'[\\d]+)', //Deprecated
                'method' => 'get_member',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/getmember/(?P<login>\S+)', //Deprecated
                'method' => 'get_member',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/(?\'type\'[a-zA-Z_]+)/webhooks', //Deprecated
                'method' => 'get_webhooks',
                'capability' => 'read',
            ),
            array(
                'type' => 'create',
                'path' => '/(?\'type\'[a-zA-Z_]+)/webhooks', //Deprecated
                'method' => 'create_webhook_deprecated',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'update',
                'path' => '/updatemembershiplevel',
                'method' => 'update_membership_level_of_member', //Deprecated
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'list',
                'path' => '/members',
                'method' => 'list_members',
                'capability' => 'edit_posts',
            ),
            array (
                'type' => 'list',
                'path' => '/member',
                'method' => 'fetch_member',
                'capability' => 'edit_posts',
            ),
            array (
                'type' => 'list',
                'path' => '/member',
                'method' => 'fetch_member',
                'capability' => 'edit_posts',
            ),
            array (
                'type' => 'list',
                'path' => '/membershiplevels',
                'method' => 'list_membership_levels',
                'capability' => 'edit_posts',
            ),
            array (
                'type' => 'list',
                'path' => '/membershiplevel',
                'method' => 'fetch_membership_level',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'update',
                'path' => '/member/(?\'member_id\'[\\d]+)/membershiplevel/(?\'membership_level_id\'[\\d]+)',
                'method' => 'update_membership_level',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array (
            array(
                'action' => 'swpm_admin_end_registration_complete_user_data',
                'method' => 'process_swpm_registration_user_data', //Deprecated
                'args_count' => 1,
            ),
            array(
                'action' => 'swpm_admin_end_edit_complete_user_data',
                'method' => 'process_swpm_registration_user_data', //Deprecated
                'args_count' => 1,
            ),
            array(
                'action' => 'swpm_front_end_registration_complete_user_data',
                'method' => 'process_swpm_registration_user_data', //Deprecated
                'args_count' => 1,
            ),
            array(
                'action' => 'swpm_front_end_profile_edited',
                'method' => 'process_swpm_registration_user_data', //Deprecated
                'args_count' => 1,
            ),
            array(
                'action' => 'swpm_admin_end_registration_complete_user_data',
                'method' => 'payload_member_added_admin_end',
                'args_count' => 1,
            ),
            array(
                'action' => 'swpm_admin_end_edit_complete_user_data',
                'method' => 'payload_member_updated_admin_end',
                'args_count' => 1,
            ),
            array(
                'action' => 'swpm_front_end_registration_complete_user_data',
                'method' => 'payload_member_added_front_end',
                'args_count' => 1,
            ),
            array(
                'action' => 'swpm_front_end_profile_edited',
                'method' => 'payload_member_updated_front_end',
                'args_count' => 1,
            ),
            array(
                'action' => 'swpm_membership_level_changed',
                'method' => 'payload_member_level_updated',
                'args_count' => 1,
            )
        )
    ),
    array (
        'name' => esc_html__('Profile Builder'),
        'api_path' => 'profile-builder',
        'class_name' => 'Zoho_Flow_Profile_Builder',
        'gallery_app_link' => 'profile-builder',
        'description' => esc_html__('Use Profile Builder to create and manage user registrations, profiles, and more on your WordPress site. By integrating Profile Builder with other applications, you can ensure all the user profile details are up to date.', 'zoho-flow'),
        'icon_file' => 'profile-builder.png',
        'class_test' => 'wppb_login_widget',
        'app_documentation_link' => '',
        'embed_link' => 'profile_builder',
        'version' => 'v1',
        'rest_apis' => array (
            array(
                'type' => 'list',
                'path' => '/fields',
                'method' => 'get_all_fields',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/user',
                'method' => 'fetch_user',
                'capability' => 'edit_users',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array (
            array(
                'action' => 'wppb_register_success',
                'method' => 'payload_user_registered',
                'args_count' => 3,
            ),
            array(
                'action' => 'wppb_edit_profile_success',
                'method' => 'payload_user_profile_updated',
                'args_count' => 3,
            )
        )
    ),
    array(
        'name' => esc_html__("BuddyBoss"),
        'api_path' => 'buddyboss',
        'class_name' => 'Zoho_Flow_BuddyBoss',
        'gallery_app_link' => 'buddyboss',
        'description' => esc_html__('BuddyBoss is a WordPress community platform that enables users to create online communities, online forums, private groups, and more. Integrate BuddyBoss with other applications to create a centralized marketing hub.', 'zoho-flow'),
        'icon_file' => 'buddyboss.png',
        'class_test' => 'BP_Activity_Activity',
        'app_documentation_link' => '',
        'embed_link' => 'buddyboss',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/activities',
                'method' => 'get_activities',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/creategroup',
                'method' => 'create_group',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/activity_post',
                'method' => 'activity_post_update',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/invite_member',
                'method' => 'invite_member_to_group',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/follow_unfollow_member',
                'method' => 'follow_request',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/friendship',
                'method' => 'create_friendship',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/sendinvite',
                'method' => 'send_invite',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/topic',
                'method' => 'create_topic',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/forums',
                'method' => 'get_forums',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/groups',
                'method' => 'get_groups',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/members',
                'method' => 'get_members',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/(?\'type\'[a-zA-Z_]+)/webhooks',
                'method' => 'get_webhooks',
                'capability' => 'read',
            ),
            array(
                'type' => 'create',
                'path' => '/(?\'type\'[a-zA-Z_]+)/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'delete_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array(
                'action' => 'bp_member_invite_submit',//in rest
                'method' => 'trigger_new_invite',
                'args_count' => 2,
            ),
            array(
                'action' => 'bp_notification_after_save',
                'method' => 'trigger_new_notification',
                'args_count' => 1,
            ),
            array(
                'action' => 'bp_activity_after_save',
                'method' => 'trigger_new_activity',
                'args_count' => 1,
            ),
            array(
                'action' => 'bp_core_signup_user',
                'method' => 'trigger_new_member',
                'args_count' => 5,
            ),
            array(
                'action' => 'bbp_publicized_forum',
                'method' => 'trigger_new_forum',
                'args_count' => 1,
            ),
        ),
    ),
    array(
        'name' => esc_html__("Simply Schedule Appointments"),
        'api_path' => 'simply-schedule-appointments',
        'class_name' => 'Zoho_Flow_Simply_Schedule_Appointments',
        'gallery_app_link' => 'simply-schedule-appointments',
        'description' => esc_html__('Use Simply Schedule Appointments to manage scheduling availability and appointment bookings on your WordPress side. Integrate Simply Schedule Appointments with your favourite applications to notify registrants of cancellations, delays, or any other updates on your appointments.', 'zoho-flow'),
        'icon_file' => 'simply-schedule-appointments.png',
        'class_test' => 'SSA_Appointment_Object',
        'app_documentation_link' => '',
        'embed_link' => 'simply_schedule_appointments',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/appointment-types',
                'method' => 'list_appointment_types',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/appointment-type/(?\'appointment_type_id\'[\\d]+)',
                'method' => 'fetch_appointment_type',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/appointment/(?\'appointment_id\'[\\d]+)',
                'method' => 'fetch_appointment',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'ssa/appointment/booked',
                'method' => 'payload_appointment_booked',
                'args_count' => 4,
            ),
            array (
                'action' => 'ssa/appointment/pending',
                'method' => 'payload_appointment_pending',
                'args_count' => 4,
            ),
            array (
                'action' => 'ssa/appointment/edited',
                'method' => 'payload_appointment_edited',
                'args_count' => 4,
            ),
            array (
                'action' => 'ssa/appointment/rescheduled',
                'method' => 'payload_appointment_rescheduled',
                'args_count' => 4,
            ),
            array (
                'action' => 'ssa/appointment/canceled',
                'method' => 'payload_appointment_canceled',
                'args_count' => 4,
            ),
            array (
                'action' => 'ssa/appointment/abandoned',
                'method' => 'payload_appointment_abandoned',
                'args_count' => 4,
            ),
            array (
                'action' => 'ssa/appointment/customer_information_edited',
                'method' => 'payload_appointment_customer_information_edited',
                'args_count' => 4,
            ),
        )
    ),
    array (
        'name' => esc_html__('Login/Signup Popup'),
        'api_path' => 'login-signup-popup',
        'class_name' => 'Zoho_Flow_Login_Signup_Popup',
        'gallery_app_link' => 'login-signup-popup',
        'description' => esc_html__('Login/Signup Popup is a lightweight WordPress plugin that can make registration, login, password reset, and other login-related actions easier.', 'zoho-flow'),
        'icon_file' => 'login-signup-popup.png',
        'class_test' => 'Xoo_Aff',
        'app_documentation_link' => '',
        'embed_link' => 'login_signup_popup',
        'version' => 'v1',
        'rest_apis' => array (
            array (
                'type' => 'list',
                'path' => '/fields',
                'method' => 'get_user_meta_keys',
                'capability' => 'list_users',
            ),
            array (
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'list_users',
            ),
            array (
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array (
            array (
                'action' => 'xoo_el_login_success',
                'method' => 'payload_login_success',
                'args_count' => 1,
            ),
            array (
                'action' => 'xoo_el_registration_success',
                'method' => 'payload_registration_success',
                'args_count' => 1,
            ),
            array (
                'action' => 'xoo_el_created_customer',
                'method' => 'payload_customer_created',
                'args_count' => 2,
            ),
            array (
                'action' => 'xoo_el_reset_password_success',
                'method' => 'payload_password_reset_success',
                'args_count' => 1,
            ),
        ),
    ),
    array(
        'name' => esc_html__("FluentCRM"),
        'api_path' => 'fluentcrm',
        'class_name' => 'Zoho_Flow_FluentCRM',
        'gallery_app_link' => 'fluentcrm',
        'description' => esc_html__('FluentCRM is an email marketing automation plugin where you can manage your email campaigns and other email marketing activities. By integrating FluentCRM with other applications using Zoho Flow, you\'ll be able to automate your email campaigns.', 'zoho-flow'),
        'icon_file' => 'fluentcrm.png',
        'class_test' => 'FluentCrm\App\Models\Subscriber',
        'app_documentation_link' => '',
        'embed_link' => 'fluentcrm',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'get_forms',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/allcontacts',
                'method' => 'get_contacts',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/fetchcontact/(?\'id\'[\\d]+)',
                'method' => 'fetch_contact',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/tags',
                'method' => 'fetch_tags',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/lists',
                'method' => 'fetch_lists',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/createcontact',
                'method' => 'create_contact',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'update',
                'path' => '/updatecontact',
                'method' => 'update_contact',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/tags/create',
                'method' => 'create_tags',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/lists/create',
                'method' => 'create_lists',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/(?\'type\'[a-zA-Z_]+)/webhooks',
                'method' => 'get_webhooks',
                'capability' => 'read',
            ),
            array(
                'type' => 'create',
                'path' => '/(?\'type\'[a-zA-Z_]+)/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'delete_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array(
                'action' => 'fluent_crm/contact_created',
                'method' => 'process_form_submission',
                'args_count' => 1,
            ),
            array(
                'action' => 'fluent_crm/contact_updated',
                'method' => 'process_contact_updated',
                'args_count' => 1,
            )
        ),
    ),
    array (
        'name' => esc_html__("Quiz And Survey Master"),
        'api_path' => 'quiz-and-survey-master',
        'class_name' => 'Zoho_Flow_Quiz_And_Survey_Master',
        'gallery_app_link' => 'quiz-and-survey-master',
        'description' => esc_html__('Use Quiz And Survey Master to create polls on your WordPress site. By integrating Quiz and Survey Master with other applications, you can review and analyze the quiz results more efficiently and derive insights from them.', 'zoho-flow'),
        'icon_file' => 'quiz-and-survey-master.png',
        'class_test' => 'QMNQuizManager',
        'app_documentation_link' => '',
        'embed_link' => 'quiz_and_survey_master',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/quizzes',
                'method' => 'list_quizzes',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/quizzes/(?\'quiz_id\'[\\d]+)/questions',
                'method' => 'list_quiz_questions',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/quizzes/(?\'quiz_id\'[\\d]+)/fields',
                'method' => 'list_quiz_contact_fields',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'qsm_quiz_submitted',
                'method' => 'payload_quiz_submitted',
                'args_count' => 4,
            )
        )
    ),
    array (
        'name' => esc_html__("Happyforms"),
        'api_path' => 'happyforms',
        'class_name' => 'Zoho_Flow_Happyforms',
        'gallery_app_link' => 'happyforms',
        'description' => esc_html__('Use Happyforms to create different types of online forms on your WordPress site. You can integrate Happy Forms with your favorite applications to collect form entries and get insights from them.', 'zoho-flow'),
        'icon_file' => 'happyforms.png',
        'class_test' => 'HappyForms_Form_Controller',
        'app_documentation_link' => '',
        'embed_link' => 'happyforms',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'list_forms',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'list_form_fields',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'happyforms_submission_success',
                'method' => 'payload_submission_added',
                'args_count' => 3,
            )
        ),
    ),
    array (
        'name' => esc_html__('The Newsletter Plugin'),
        'api_path' => 'the-newsletter-plugin',
        'class_name' => 'Zoho_Flow_The_Newsletter_Plugin',
        'gallery_app_link' => 'the-newsletter-plugin',
        'description' => esc_html__('Use Newsletter Plugin to create newsletter emails, manage subscriptions, and more on your WordPress site. Integrate The Newsletter Plugin with your favorite applications to automatically add subscribers as contacts to your email marketing campaigns.', 'zoho-flow'),
        'icon_file' => 'the-newsletter-plugin.png',
        'class_test' => 'TNP_User',
        'app_documentation_link' => '',
        'embed_link' => 'the_newsletter_plugin',
        'version' => 'v1',
        'rest_apis' => array (
            array (
                'type' => 'list',
                'path' => '/customfields',
                'method' => 'get_all_custom_fields',
                'capability' => 'read_private_posts',
            ),
            array (
                'type' => 'list',
                'path' => '/lists',
                'method' => 'get_all_lists',
                'capability' => 'read_private_posts',
            ),
            array (
                'type' => 'list',
                'path' => '/subscribers',
                'method' => 'list_subscribers',
                'capability' => 'read_private_posts',
            ),
            array (
                'type' => 'list',
                'path' => '/subscriber',
                'method' => 'get_subscriber',
                'capability' => 'read_private_posts',
            ),
            array (
                'type' => 'create',
                'path' => '/subscriber',
                'method' => 'add_subscriber',
                'capability' => 'edit_private_posts',
            ),
            array (
                'type' => 'create',
                'path' => '/subscriber/unsubscribe',
                'method' => 'unsubscribe_subscriber',
                'capability' => 'edit_private_posts',
            ),
            array (
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_private_posts',
            ),
            array (
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array (
            array (
                'action' => 'newsletter_user_post_subscribe',
                'method' => 'payload_subscriber_added',
                'args_count' => 1,
            ),
            array (
                'action' => 'newsletter_user_confirmed',
                'method' => 'payload_subscriber_confirmed',
                'args_count' => 1,
            ),
            array (
                'action' => 'newsletter_user_unsubscribed',
                'method' => 'payload_subscriber_unsubscribed',
                'args_count' => 1,
            ),
            array (
                'action' => 'newsletter_user_reactivated',
                'method' => 'payload_subscriber_resubscribed',
                'args_count' => 1,
            )
        ),
    ),
    array (
        'name' => esc_html__('AffiliateWP'),
        'api_path' => 'affiliatewp',
        'class_name' => 'Zoho_Flow_AffiliateWP',
        'gallery_app_link' => 'affiliatewp',
        'description' => esc_html__('Use AffiliateWP to create affiliate programs to promote your products and services on your WordPress site. You can integrate AffiliateWP with your favorite applications to generate leads for your business through affiliate programs.', 'zoho-flow'),
        'icon_file' => 'affiliatewp.png',
        'class_test' => 'Affiliate_WP',
        'app_documentation_link' => '',
        'embed_link' => 'affiliatewp',
        'version' => 'v1',
        'rest_apis' => array (
            array (
                'type' => 'list',
                'path' => '/affiliates',
                'method' => 'get_all_affiliates',
                'capability' => 'manage_affiliates',
            ),
            array (
                'type' => 'list',
                'path' => '/affiliate',
                'method' => 'get_affiliate',
                'capability' => 'manage_affiliates',
            ),
            array (
                'type' => 'create',
                'path' => '/affiliate',
                'method' => 'add_affiliate',
                'capability' => 'manage_affiliates',
            ),
            array (
                'type' => 'update',
                'path' => '/affiliate/(?\'affiliate_id\'[\\d]+)/status/(?\'status\'[a-zA-Z_-]+)',
                'method' => 'updated_affiliate_status',
                'capability' => 'manage_affiliates',
            ),
            array (
                'type' => 'list',
                'path' => '/referrals',
                'method' => 'get_all_referrals',
                'capability' => 'manage_referrals',
            ),
            array (
                'type' => 'list',
                'path' => '/referral',
                'method' => 'get_referral',
                'capability' => 'manage_referrals',
            ),
            array (
                'type' => 'create',
                'path' => '/referral',
                'method' => 'add_referral',
                'capability' => 'manage_referrals',
            ),
            array (
                'type' => 'update',
                'path' => '/referral/(?\'referral_id\'[\\d]+)/status/(?\'status\'[a-zA-Z_-]+)',
                'method' => 'updated_referral_status',
                'capability' => 'manage_referrals',
            ),
            array (
                'type' => 'list',
                'path' => '/payouts',
                'method' => 'get_all_payouts',
                'capability' => 'manage_payouts',
            ),
            array (
                'type' => 'list',
                'path' => '/payout',
                'method' => 'get_payout',
                'capability' => 'manage_payouts',
            ),
            array (
                'type' => 'list',
                'path' => '/creatives',
                'method' => 'get_all_creatives',
                'capability' => 'manage_creatives',
            ),
            array (
                'type' => 'list',
                'path' => '/creative',
                'method' => 'get_creative',
                'capability' => 'manage_creatives',
            ),
            array (
                'type' => 'create',
                'path' => '/creative',
                'method' => 'add_creative',
                'capability' => 'manage_creatives',
            ),
            array (
                'type' => 'update',
                'path' => '/creative/(?\'creative_id\'[\\d]+)',
                'method' => 'update_creative',
                'capability' => 'manage_creatives',
            ),
            array (
                'type' => 'list',
                'path' => '/visits',
                'method' => 'get_all_visits',
                'capability' => 'manage_visits',
            ),
            array (
                'type' => 'list',
                'path' => '/visit',
                'method' => 'get_visit',
                'capability' => 'manage_visits',
            ),
            array (
                'type' => 'list',
                'path' => '/groups',
                'method' => 'get_all_groups',
                'capability' => 'manage_affiliate_options',
            ),
            array (
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_affiliate_options',
            ),
            array (
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array (
            array (
                'action' => 'affwp_insert_referral',
                'method' => 'payload_referral_added',
                'args_count' => 1,
            ),
            array (
                'action' => 'affwp_updated_referral',
                'method' => 'payload_referral_updated',
                'args_count' => 3,
            ),
            array (
                'action' => 'affwp_set_referral_status',
                'method' => 'payload_referral_status_updated',
                'args_count' => 3,
            ),
            array (
                'action' => 'affwp_insert_affiliate',
                'method' => 'payload_affiliate_added',
                'args_count' => 2,
            ),
            array (
                'action' => 'affwp_updated_affiliate',
                'method' => 'payload_affiliate_updated',
                'args_count' => 2,
            ),
            array (
                'action' => 'affwp_set_affiliate_status',
                'method' => 'payload_affiliate_status_updated',
                'args_count' => 3,
            ),
            array (
                'action' => 'affwp_insert_payout',
                'method' => 'payload_payout_added',
                'args_count' => 1,
            ),
            array (
                'action' => 'affwp_insert_creative',
                'method' => 'payload_creative_added',
                'args_count' => 2,
            ),
            array (
                'action' => 'affwp_set_creative_status',
                'method' => 'payload_creative_status_updated',
                'args_count' => 3,
            ),
        ),
    ),
    array (
        'name' => esc_html__("Jetpack CRM"),
        'api_path' => 'jetpack-crm',
        'class_name' => 'Zoho_Flow_Jetpack_CRM',
        'gallery_app_link' => 'jetpack-crm',
        'description' => esc_html__('Use Jetpack CRM to manage leads, customers, email marketing, invoices, billings, and more. You can integrate Jetpack CRM with your favorite applications to collect lead information and create marketing campaigns for them.', 'zoho-flow'),
        'icon_file' => 'jetpack-crm.png',
        'class_test' => 'ZeroBSCRM',
        'app_documentation_link' => '',
        'embed_link' => 'jetpack_crm',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/contacts',
                'method' => 'list_contacts',
                'capability' => 'admin_zerobs_view_customers',
            ),
            array(
                'type' => 'list',
                'path' => '/contact',
                'method' => 'fetch_contact',
                'capability' => 'admin_zerobs_view_customers',
            ),
            array(
                'type' => 'create',
                'path' => '/contact',
                'method' => 'add_or_update_contact',
                'capability' => 'admin_zerobs_customers',
            ),
            array(
                'type' => 'list',
                'path' => '/contact/statuses',
                'method' => 'list_contact_statuses',
                'capability' => 'admin_zerobs_view_customers',
            ),
            array(
                'type' => 'list',
                'path' => '/quote',
                'method' => 'fetch_quote',
                'capability' => 'admin_zerobs_view_quotes',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'jpcrm_contact_created',
                'method' => 'payload_contact_created',
                'args_count' => 1,
            ),
            array (
                'action' => 'jpcrm_contact_updated',
                'method' => 'payload_contact_updated',
                'args_count' => 2,
            ),
            array (
                'action' => 'jpcrm_contact_status_updated',
                'method' => 'payload_contact_status_updated',
                'args_count' => 2,
            ),
            array (
                'action' => 'zbs_new_company',
                'method' => 'payload_company_created',
                'args_count' => 1,
            ),
            array (
                'action' => 'zbs_new_quote',
                'method' => 'payload_quote_created',
                'args_count' => 1,
            ),
            array (
                'action' => 'jpcrm_quote_accepted',
                'method' => 'payload_quote_accepted',
                'args_count' => 1,
            ),
            array (
                'action' => 'jpcrm_invoice_created',
                'method' => 'payload_invoice_created',
                'args_count' => 1,
            ),
            array (
                'action' => 'jpcrm_invoice_updated',
                'method' => 'payload_invoice_updated',
                'args_count' => 2,
            ),
            array (
                'action' => 'jpcrm_transaction_created',
                'method' => 'payload_transaction_created',
                'args_count' => 1,
            )
        )
    ),
    array (
        'name' => esc_html__('Super Socializer'),
        'api_path' => 'super-socializer',
        'class_name' => 'Zoho_Flow_Super_Socializer',
        'gallery_app_link' => 'super-socializer',
        'description' => esc_html__('Use Super Socializer to login to your WordPress site through your social media handles, share posts and comment on social media platforms directly, and more. By integrating Super Socializer with your favourite applications, you can get notified every time a registration is made through social media on your WordPress site.', 'zoho-flow'),
        'icon_file' => 'super-socializer.png',
        'class_test' => 'TheChampLoginWidget',
        'app_documentation_link' => '',
        'embed_link' => 'super_socializer',
        'version' => 'v1',
        'rest_apis' => array (
            array (
                'type' => 'list',
                'path' => '/users/me',
                'method' => 'get_curren_user',
                'capability' => 'list_users',
            ),
            array (
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_users',
            ),
            array (
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array (
            array (
                'action' => 'the_champ_login_user',
                'method' => 'payload_user_login',
                'args_count' => 4,
            ),
            array (
                'action' => 'the_champ_user_successfully_created',
                'method' => 'payload_user_created',
                'args_count' => 3,
            ),
        ),
     ),
    array (
        'name' => esc_html__("Mailster"),
        'api_path' => 'mailster',
        'class_name' => 'Zoho_Flow_Mailster',
        'gallery_app_link' => 'mailster',
        'description' => esc_html__('Mailster is a comprehensive email newsletter plugin for WordPress. With Mailster integrations, you can automate list management by adding new subscribers from different sources, sending customized follow-up emails based on user behavior, or even updating subscriber info from other platforms.', 'zoho-flow'),
        'icon_file' => 'mailster.png',
        'class_test' => 'MailsterCampaigns',
        'app_documentation_link' => '',
        'embed_link' => 'mailster',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/campaigns',
                'method' => 'get_campaigns',
                'capability' => 'mailster_dashboard',
            ),
            array(
                'type' => 'list',
                'path' => '/fields',
                'method' => 'get_custom_fields',
                'capability' => 'mailster_dashboard',
            ),
            array(
                'type' => 'list',
                'path' => '/lists',
                'method' => 'get_lists',
                'capability' => 'mailster_dashboard',
            ),
            array(
                'type' => 'list',
                'path' => '/statuses',
                'method' => 'get_statuses',
                'capability' => 'mailster_dashboard',
            ),
            array(
                'type' => 'list',
                'path' => '/subscriber',
                'method' => 'get_subscriber',
                'capability' => 'mailster_manage_subscribers',
            ),
            array(
                'type' => 'create',
                'path' => '/subscriber',
                'method' => 'add_subscriber',
                'capability' => 'mailster_add_subscribers',
            ),
            array(
                'type' => 'update',
                'path' => '/subscriber/(?\'subscriber_id\'[\\d]+)',
                'method' => 'update_subscriber',
                'capability' => 'mailster_edit_subscribers',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'mailster_manage_subscribers',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'mailster_manage_subscribers',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array (
            array (
                'action' => 'mailster_add_subscriber',
                'method' => 'payload_add_subscriber',
                'args_count' => 1,
            ),
            array (
                'action' => 'mailster_update_subscriber',
                'method' => 'payload_update_subscriber',
                'args_count' => 1,
            ),
            array (
                'action' => 'mailster_tag_added',
                'method' => 'payload_subscriber_tag',
                'args_count' => 3,
            ),
            array (
                'action' => 'mailster_list_added',
                'method' => 'payload_add_subscriber_to_list',
                'args_count' => 3,
            ),

        ),
    ),
    array (
        'name' => esc_html__("weForms"),
        'api_path' => 'weforms',
        'class_name' => 'Zoho_Flow_WeForms',
        'gallery_app_link' => 'weforms',
        'description' => esc_html__('Use WeForms to create online contact forms on your WordPress site. Integrate WeForms with other applications to collect form entry data from your WordPress site.', 'zoho-flow'),
        'icon_file' => 'weforms.png',
        'class_test' => 'WeForms_Form',
        'app_documentation_link' => '',
        'embed_link' => 'weforms',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'list_forms',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'list_form_fields',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'weforms_entry_submission',
                'method' => 'payload_submission_added',
                'args_count' => 4,
            )
        ),
    ),
    array (
        'name' => esc_html__("Kali Forms"),
        'api_path' => 'kali-forms',
        'class_name' => 'Zoho_Flow_Kali_Forms',
        'gallery_app_link' => 'kali-forms',
        'description' => esc_html__('Use Kali Forms to create different types of online forms on your WordPress site. By integrating Kali Forms with your favorite applications, you can get notified whenever a form entry is made.', 'zoho-flow'),
        'icon_file' => 'kali-forms.png',
        'class_test' => 'KaliForms\Inc\Frontend\Form_Processor',
        'app_documentation_link' => '',
        'embed_link' => 'kali_forms',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'list_forms',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'list_form_fields',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'kaliforms_after_form_process_action',
                'method' => 'payload_form_entry_submitted',
                'args_count' => 1,
            )
        )
    ),
    array(
        'name' => esc_html__("UsersWP"),
        'api_path' => 'userswp',
        'class_name' => 'Zoho_Flow_UsersWP',
        'gallery_app_link' => 'userswp',
        'description' => esc_html__('UsersWP is a user registration WordPress plugin that enables users to create and manage secure logins and registrations. Integrate UsersWP with other applications to ensure a safe and secure login.', 'zoho-flow'),
        'icon_file' => 'userswp.png',
        'class_test' => 'UsersWP_Forms',
        'app_documentation_link' => '',
        'embed_link' => 'userswp',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'list_users',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'uwp_after_process_login',
                'method' => 'payload_login_success',
                'args_count' => 1,
            ),
            array (
                'action' => 'wp_login_failed',
                'method' => 'payload_login_failed',
                'args_count' => 1,
            ),
            array (
                'action' => 'uwp_after_process_register',
                'method' => 'payload_register_success',
                'args_count' => 2,
            ),
            array (
                'action' => 'uwp_after_process_forgot',
                'method' => 'payload_forgot_password',
                'args_count' => 1,
            ),
        )
    ),
    array(
        'name' => esc_html__("Appointment Hour Booking"),
        'api_path' => 'appointment-hour-booking',
        'class_name' => 'Zoho_Flow_Appointment_Hour_Booking',
        'gallery_app_link' => 'appointment-hour-booking',
        'description' => esc_html__('Use Appointment Hour Booking to create booking forms for appointments on your WordPress site. By integrating Appointment Hour Booking with messaging applications, you can notify attendees of any appointment related updates.', 'zoho-flow'),
        'icon_file' => 'appointment-hour-booking.png',
        'class_test' => 'CP_AppBookingPlugin',
        'app_documentation_link' => '',
        'embed_link' => 'appointment_hour_booking',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/calendar-forms',
                'method' => 'list_calendar_forms',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/calendar-form/(?\'calendar_form_id\'[\\d]+)',
                'method' => 'fetch_calendar_form',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/statuses',
                'method' => 'list_statuses',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/appointment/(?\'appointment_id\'[\\d]+)',
                'method' => 'fetch_appointment',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'update',
                'path' => '/appointment/(?\'appointment_id\'[\\d]+)/status',
                'method' => 'update_appointment_status',
                'capability' => 'edit_private_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'cpappb_update_status',
                'method' => 'payload_appointment_status_updated',
                'args_count' => 2,
            ),
            array (
                'action' => 'cpappb_process_data',
                'method' => 'payload_appointment_booked',
                'args_count' => 1,
            )
        )
    ),
    array(
        'name' => esc_html__("WP Simple Booking Calendar"),
        'api_path' => 'wp-simple-booking-calendar',
        'class_name' => 'Zoho_Flow_WP_Simple_Booking_Calendar',
        'gallery_app_link' => 'wp-simple-booking-calendar',
        'description' => esc_html__('Use WP Simple Booking Calendar to display availability of properties on your WordPress site. By integrating WP Simple Booking Calendar with your favourite applications you can automatically update the availability details of your properties on your calendar.', 'zoho-flow'),
        'icon_file' => 'wp-simple-booking-calendar.png',
        'class_test' => 'WPSBC_Base_Object',
        'app_documentation_link' => '',
        'embed_link' => 'wp_simple_booking_calendar',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/calendars',
                'method' => 'list_calendars',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/calendar/(?\'calendar_id\'[\\d]+)/event',
                'method' => 'fetch_event',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/calendar/(?\'calendar_id\'[\\d]+)/event',
                'method' => 'addd_or_update_event',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/calendar/(?\'calendar_id\'[\\d]+)/legend-items',
                'method' => 'list_legend_items',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'wpsbc_insert_event',
                'method' => 'payload_event_added',
                'args_count' => 2,
            ),
            array (
                'action' => 'wpsbc_update_event',
                'method' => 'payload_event_updated',
                'args_count' => 2,
            ),
            array (
                'action' => 'wpsbc_insert_event',
                'method' => 'payload_event_added_or_updated',
                'args_count' => 2,
            ),
            array (
                'action' => 'wpsbc_update_event',
                'method' => 'payload_event_added_or_updated',
                'args_count' => 2,
            )
        )
    ),
    array(
        'name' => esc_html__("Restrict User Access"),
        'api_path' => 'restrict-user-access',
        'class_name' => 'Zoho_Flow_Restrict_User_Access',
        'gallery_app_link' => 'restrict-user-access',
        'description' => esc_html__('Use Restrict User Access to manage content restrictions for registered users on your WordPress site. By integrating Restrict User Access with other applications, you can easily control your members\' membership levels.', 'zoho-flow'),
        'icon_file' => 'restrict-user-access.png',
        'class_test' => 'RUA_User',
        'app_documentation_link' => '',
        'embed_link' => 'restrict_user_access',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/levels',
                'method' => 'list_levels',
                'capability' => 'edit_users',
            ),
            array(
                'type' => 'list',
                'path' => '/user',
                'method' => 'fetch_user',
                'capability' => 'edit_users',
            ),
            array(
                'type' => 'update',
                'path' => '/user/(?\'user_id\'[\\d]+)/level/(?\'level_id\'[\\d]+)/add',
                'method' => 'add_user_level',
                'capability' => 'edit_users',
            ),
            array(
                'type' => 'update',
                'path' => '/user/(?\'user_id\'[\\d]+)/level/(?\'level_id\'[\\d]+)/remove',
                'method' => 'remove_user_level',
                'capability' => 'edit_users',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'rua/user_level/added',
                'method' => 'payload_user_level_added',
                'args_count' => 2,
            ),
            array (
                'action' => 'rua/user_level/removed',
                'method' => 'payload_user_level_removed',
                'args_count' => 2,
            ),
            array (
                'action' => 'rua/user_level/extended',
                'method' => 'payload_user_level_extended',
                'args_count' => 2,
            ),
        )
    ),
    array(
        'name' => esc_html__("New User Approve"),
        'api_path' => 'new-user-approve',
        'class_name' => 'Zoho_Flow_New_User_Approve',
        'gallery_app_link' => 'new-user-approve',
        'description' => esc_html__('Use New User Approve to manage user registrations and approvals on your WordPress site. By integrating New User Approve with your favorite applications, you can automatically send approval confirmations to your users.', 'zoho-flow'),
        'icon_file' => 'new-user-approve.png',
        'class_test' => 'pw_new_user_approve',
        'app_documentation_link' => '',
        'embed_link' => 'new_user_approve',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_users',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'new_user_approve_user_approved',
                'method' => 'payload_user_approved',
                'args_count' => 1,
            ),
            array (
                'action' => 'new_user_approve_user_denied',
                'method' => 'payload_user_denied',
                'args_count' => 1,
            ),
            array (
                'action' => 'nua_invited_user',
                'method' => 'payload_user_invited',
                'args_count' => 2,
            )
        )
    ),
    array(
        'name' => esc_html__("Quiz Maker"),
        'api_path' => 'quiz-maker',
        'class_name' => 'Zoho_Flow_QuizMaker',
        'gallery_app_link' => 'quiz-maker',
        'description' => esc_html__('Use Quiz Maker to create various types of quizzes on your WordPress site. Integrate Quiz Maker with your favorite applications to move your quiz results to other apps to analyze and derive results out of them.', 'zoho-flow'),
        'icon_file' => 'quiz-maker.png',
        'class_test' => 'Quiz_Maker',
        'app_documentation_link' => '',
        'embed_link' => 'quiz_maker',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/quizzes',
                'method' => 'get_quizzes',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/questions',
                'method' => 'get_questions',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/quiz/(?\'quiz_id\'[\\d]+)',
                'method' => 'get_quiz',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/question/(?\'question_id\'[\\d]+)',
                'method' => 'get_question',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/(?\'modulename\'[a-zA-Z_]+)/categories',
                'method' => 'get_categories',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/reports',
                'method' => 'get_reports',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/quiz/add',
                'method' => 'add_or_edit_quiz',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'update',
                'path' => '/quiz/edit',
                'method' => 'edit_quiz',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/question/add',
                'method' => 'add_or_edit_question',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'update',
                'path' => '/question/edit',
                'method' => 'edit_question',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/question_types',
                'method' => 'get_question_types',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        )
    ),
    array(
        'name' => esc_html__("WP Booking System"),
        'api_path' => 'wp-booking-system',
        'class_name' => 'Zoho_Flow_WP_Booking_System',
        'gallery_app_link' => 'wp-booking-system',
        'description' => esc_html__('Use WP Booking System to create booking calendars, booking forms, manage bookings and more on your WordPress site. By integrating WP Booking System with other applications, you can send email confirmations when someone registers for an event or appointment.', 'zoho-flow'),
        'icon_file' => 'wp-booking-system.png',
        'class_test' => 'WPBS_Object_DB',
        'app_documentation_link' => '',
        'embed_link' => 'wp_booking_system',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/calendars',
                'method' => 'list_calendars',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'list_forms',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/calendar/(?\'calendar_id\'[\\d]+)/legend-items',
                'method' => 'list_legend_items',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/calendar/(?\'calendar_id\'[\\d]+)/event',
                'method' => 'fetch_event',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/form/(?\'form_id\'[\\d]+)',
                'method' => 'fetch_form',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/form/(?\'form_id\'[\\d]+)/booking/(?\'booking_id\'[\\d]+)',
                'method' => 'fetch_booking',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/calendar/(?\'calendar_id\'[\\d]+)/event',
                'method' => 'addd_or_update_event',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'wpbs_insert_event',
                'method' => 'payload_event_added',
                'args_count' => 2,
            ),
            array (
                'action' => 'wpbs_update_event',
                'method' => 'payload_event_updated',
                'args_count' => 2,
            ),
            array (
                'action' => 'wpbs_insert_event',
                'method' => 'payload_event_added_or_updated',
                'args_count' => 2,
            ),
            array (
                'action' => 'wpbs_update_event',
                'method' => 'payload_event_added_or_updated',
                'args_count' => 2,
            ),
            array (
                'action' => 'wpbs_insert_booking',
                'method' => 'payload_booking_added',
                'args_count' => 2,
            ),
            array (
                'action' => 'wpbs_update_booking',
                'method' => 'payload_booking_updated',
                'args_count' => 2,
            ),
            array (
                'action' => 'wpbs_insert_booking',
                'method' => 'payload_booking_added_or_updated',
                'args_count' => 2,
            ),
            array (
                'action' => 'wpbs_update_booking',
                'method' => 'payload_booking_added_or_updated',
                'args_count' => 2,
            )
        )
    ),
    array(
        'name' => esc_html__("LifterLMS"),
        'api_path' => 'lifter-lms',
        'class_name' => 'Zoho_Flow_Lifter_LMS',
        'gallery_app_link' => 'lifterlms',
        'description' => esc_html__('Use Lifter LMS to create and manage online courses, paid memberships and more on your WordPress site. By integrating Lifter LMS with your favourite applications you will be able to manage membership or course payments easily.', 'zoho-flow'),
        'icon_file' => 'lifter-lms.png',
        'class_test' => 'LifterLMS',
        'app_documentation_link' => '',
        'embed_link' => 'lifterlms',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/courses',
                'method' => 'get_courses',
                'capability' => 'manage_lifterlms',
            ),
            array(
                'type' => 'list',
                'path' => '/lessons',
                'method' => 'get_lessons',
                'capability' => 'manage_lifterlms',
            ),
            array(
                'type' => 'list',
                'path' => '/sections',
                'method' => 'get_sections',
                'capability' => 'manage_lifterlms',
            ),
            array(
                'type' => 'list',
                'path' => '/quizzes',
                'method' => 'get_quizzes',
                'capability' => 'manage_lifterlms',
            ),
            array(
                'type' => 'list',
                'path' => '/questions',
                'method' => 'get_questions',
                'capability' => 'manage_lifterlms',
            ),
            array(
                'type' => 'list',
                'path' => '/accessplans',
                'method' => 'get_access_plans',
                'capability' => 'manage_lifterlms',
            ),
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'get_forms',
                'capability' => 'manage_lifterlms',
            ),
            array(
                'type' => 'list',
                'path' => '/form/(?\'location\'[a-zA-Z_]+)',
                'method' => 'get_form',
                'capability' => 'manage_lifterlms',
            ),
            array(
                'type' => 'list',
                'path' => '/forms/(?\'location\'[a-zA-Z_]+)/fields',
                'method' => 'get_form_fields',
                'capability' => 'manage_lifterlms',
            ),
            array(
                'type' => 'list',
                'path' => '/memberships',
                'method' => 'get_memberships',
                'capability' => 'manage_lifterlms',
            ),
            array(
                'type' => 'list',
                'path' => '/users',
                'method' => 'get_users',
                'capability' => 'read',
            ),
            array (
                'type' => 'list',
                'path' => '/(?\'action\'.+)/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'get_webhooks',
                'capability' => 'manage_lifterlms',
            ),
            array (
                'type' => 'list',
                'path' => '/(?\'action\'.+)/(?\'form_id\'[a-zA-Z_]+)/webhooks',
                'method' => 'get_webhooks',
                'capability' => 'manage_lifterlms',
            ),
            array (
                'type' => 'create',
                'path' => '/(?\'action\'.+)/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_lifterlms',
            ),
            array (
                'type' => 'create',
                'path' => '/(?\'action\'.+)/(?\'form_id\'[a-zA-Z_]+)/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_lifterlms',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'delete_posts',
            ),
            array (
                'type' => 'list',
                'path' => '/webhooks',
                'method' => 'get_all_webhooks',
                'capability' => 'manage_lifterlms',
            ),
            array (
                'type' => 'list',
                'path' => '/(?\'type\'[a-zA-Z_]+)/(?\'post_id\'[\\d]+)/enrolled_students',
                'method' => 'get_enrolled_users',
                'capability' => 'manage_lifterlms',
            ),
            array(
                'type' => 'update',
                'path' => '/(?\'type\'[a-zA-Z_]+)/(?\'post_id\'[\\d]+)/enroll',
                'method' => 'enroll_users_to_course_or_membership',
                'capability' => 'enroll',
            ),
            array(
                'type' => 'update',
                'path' => '/(?\'type\'[a-zA-Z_]+)/(?\'post_id\'[\\d]+)/remove',
                'method' => 'remove_users_from_course_or_membership',
                'capability' => 'unenroll',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/course/(?\'form_id\'[\\d]+)',
                'method' => 'get_courses',
                'capability' => 'manage_lifterlms',
            ),
            array(
                'type' => 'list',
                'path' => '/quiz/(?\'form_id\'[\\d]+)',
                'method' => 'get_quizzes',
                'capability' => 'manage_lifterlms',
            ),
            array(
                'type' => 'list',
                'path' => '/(?\'basemodule\'[a-zA-Z_]+)/(?\'data_id\'[\\d]+)/(?\'submodule\'[a-zA-Z_]+)',
                'method' => 'get_data_by_module',
                'capability' => 'manage_lifterlms',
            ),
        ),
        'hooks' => array(
            array(
                'action' => 'lifterlms_user_registered',
                'method' => 'process_form_submission',
                'args_count' => 3,
            ),
            array(
                'action' => 'lifterlms_user_updated',
                'method' => 'process_form_submission',
                'args_count' => 3,
            ),
            array(
                'action' => 'llms_user_enrolled_in_course',
                'method' => 'process_llms_user_enrolled_in_course',
                'args_count' => 2,
            ),
            array(
                'action' => 'llms_user_removed_from_course',
                'method' => 'process_llms_user_removed_from_course',
                'args_count' => 2,
            ),
            array(
                'action' => 'llms_user_added_to_membership_level',
                'method' => 'process_llms_user_added_to_membership_level',
                'args_count' => 2,
            ),
            array(
                'action' => 'llms_user_removed_from_membership',
                'method' => 'process_llms_user_removed_from_membership_level',
                'args_count' => 4,
            ),
            array(
                'action' => 'lifterlms_lesson_completed',
                'method' => 'process_lifterlms_lesson_completed',
                'args_count' => 2,
            ),
            array(
                'action' => 'lifterlms_quiz_completed',
                'method' => 'process_lifterlms_quiz_completed',
                'args_count' => 3,
            ),
            array(
                'action' => 'after_llms_mark_complete',
                'method' => 'process_lifterlms_course_completed',
                'args_count' => 4
            ),
        ),
    ),
    array(
        'name' => esc_html__("BookingPress"),
        'api_path' => 'bookingpress',
        'class_name' => 'Zoho_Flow_BookingPress',
        'gallery_app_link' => 'bookingpress',
        'description' => esc_html__('Use BookingPress to schedule events, appointments, and calls from your WordPress site. By integrating BookingPress with messaging applications, you can automatically notify attendees on any event or appointment related updates.', 'zoho-flow'),
        'icon_file' => 'bookingpress.png',
        'class_test' => 'bookingpress_calendar',
        'app_documentation_link' => '',
        'embed_link' => 'bookingpress',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/categories',
                'method' => 'list_categories',
                'capability' => 'bookingpress_services',
            ),
            array(
                'type' => 'list',
                'path' => '/services',
                'method' => 'list_services',
                'capability' => 'bookingpress_services',
            ),
            array(
                'type' => 'list',
                'path' => '/fields',
                'method' => 'list_form_fields',
                'capability' => 'bookingpress_appointments',
            ),
            array(
                'type' => 'list',
                'path' => '/customer',
                'method' => 'fetch_customer',
                'capability' => 'bookingpress_customers',
            ),
            array(
                'type' => 'list',
                'path' => '/appointment',
                'method' => 'fetch_appointment',
                'capability' => 'bookingpress_appointments',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'bookingpress_appointments',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'bookingpress_after_insert_appointment',
                'method' => 'payload_appointment_created',
                'args_count' => 1,
            ),
            array (
                'action' => 'bookingpress_after_update_appointment',
                'method' => 'payload_appointment_updated',
                'args_count' => 1,
            ),
            array (
                'action' => 'bookingpress_after_change_appointment_status',
                'method' => 'payload_appointment_status_changed',
                'args_count' => 1,
            ),
            array (
                'action' => 'bookingpress_after_update_customer',
                'method' => 'payload_customer_updated',
                'args_count' => 1,
            ),
            array (
                'action' => 'bookingpress_after_create_customer',
                'method' => 'payload_customer_created',
                'args_count' => 1,
            )
        )
    ),
    array(
        'name' => esc_html__("Booking Package"),
        'api_path' => 'booking-package',
        'class_name' => 'Zoho_Flow_Booking_Package',
        'gallery_app_link' => 'booking-package',
        'description' => esc_html__('Use Booking Package to create and manage room rental bookings, event bookings, service bookings, and more on your WordPress site. By integrating Booking Package with your favourite applications, you can update your appointment bookings on your calendar.', 'zoho-flow'),
        'icon_file' => 'booking-package.png',
        'class_test' => 'BOOKING_PACKAGE',
        'app_documentation_link' => '',
        'embed_link' => 'booking_package',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/calendar-accounts',
                'method' => 'list_calendar_account_list',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/calendar-account/(?\'calendar_id\'[\\d]+)/fields',
                'method' => 'list_form_fields',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'booking_package_booking_completed',
                'method' => 'payload_booking_completed',
                'args_count' => 1,
            ),
            array (
                'action' => 'booking_package_changed_status',
                'method' => 'payload_booking_status_changed',
                'args_count' => 1,
            )
        )
    ),
    array(
        'name' => esc_html__("Paid Member Subscriptions"),
        'api_path' => 'paid-member-subscriptions',
        'class_name' => 'Zoho_Flow_Paid_Member_Subscriptions',
        'gallery_app_link' => 'paid-member-subscriptions',
        'description' => esc_html__('Use Paid Membership Subscriptions to set up and manage member subscriptions on your WordPress site. Integrate Paid Membership Subscriptions with your favorite applications and automatically share invoices when payments are made.', 'zoho-flow'),
        'icon_file' => 'paid-member-subscriptions.png',
        'class_test' => 'PMS_Member',
        'app_documentation_link' => '',
        'embed_link' => 'paid_member_subscriptions',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/member-subscriptions',
                'method' => 'list_member_subscriptions',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'pms_member_subscription_insert',
                'method' => 'payload_member_subscription_added',
                'args_count' => 2,
            ),
            array (
                'action' => 'pms_member_subscription_update',
                'method' => 'payload_member_subscription_updated',
                'args_count' => 3,
            ),
            array (
                'action' => 'pms_payment_insert',
                'method' => 'payload_payment_added',
                'args_count' => 2,
            ),
            array (
                'action' => 'pms_payment_update',
                'method' => 'payload_payment_updated',
                'args_count' => 3,
            )
        )
    ),
    array(
        'name' => esc_html__("RegistrationMagic"),
        'api_path' => 'registrationmagic',
        'class_name' => 'Zoho_Flow_RegistrationMagic',
        'gallery_app_link' => 'registrationmagic',
        'description' => esc_html__('Use RegsitrationMagic to build and manage registration forms, signup/login pages, and more on your WordPress site. By integrating RegistrationMagic with other applications,  you can efficiently collect and store form entries.', 'zoho-flow'),
        'icon_file' => 'registrationmagic.png',
        'class_test' => 'RM_Public',
        'app_documentation_link' => '',
        'embed_link' => 'registrationmagic',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'list_forms',
                'capability' => 'rm_form_managemanage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/form/(?\'form_id\'[\\d]+)/fields',
                'method' => 'list_form_fields',
                'capability' => 'rm_form_managemanage_options',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'rm_new_user_registered',
                'method' => 'payload_user_registered',
                'args_count' => 1,
            ),
            array (
                'action' => 'rm_user_activated',
                'method' => 'payload_user_activated',
                'args_count' => 1,
            ),
            array (
                'action' => 'rm_user_deactivated',
                'method' => 'payload_user_deactivated',
                'args_count' => 1,
            ),
            array (
                'action' => 'rm_user_signon',
                'method' => 'payload_user_signon',
                'args_count' => 1,
            ),
            array (
                'action' => 'rm_submission_completed',
                'method' => 'payload_submission_completed',
                'args_count' => 3,
            ),
            array (
                'action' => 'rm_subscribe_newsletter',
                'method' => 'payload_newsletter_subscribed',
                'args_count' => 2,
            )
        )
    ),
    array(
        'name' => esc_html__("WP User Manager"),
        'api_path' => 'wp-user-manager',
        'class_name' => 'Zoho_Flow_WP_User_Manager',
        'gallery_app_link' => 'wp-user-manager',
        'description' => esc_html__('Use WP User Manager to manage logins, registrations, profile customizations, and more on your WordPress site. By integrating WP User Manager with your favorite apps, you can easily collect user information.', 'zoho-flow'),
        'icon_file' => 'wp-user-manager.png',
        'class_test' => 'WP_User_Manager',
        'app_documentation_link' => '',
        'embed_link' => 'wp_user_manager',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/registration-forms',
                'method' => 'list_registration_forms',
                'capability' => 'edit_users',
            ),
            array(
                'type' => 'list',
                'path' => '/fields',
                'method' => 'list_all_fields',
                'capability' => 'edit_users',
            ),
            array(
                'type' => 'list',
                'path' => '/registration-form/(?\'form_id\'[\\d]+)/fields',
                'method' => 'list_registration_form_fields',
                'capability' => 'edit_users',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'wpum_after_registration',
                'method' => 'payload_user_registered',
                'args_count' => 3,
            ),
            array (
                'action' => 'wpum_after_user_update',
                'method' => 'payload_user_profile_updated',
                'args_count' => 3,
            )
        )
    ),
    array (
        'name' => esc_html__("PlanSo Forms"),
        'api_path' => 'planso-forms',
        'class_name' => 'Zoho_Flow_Planso_Forms',
        'gallery_app_link' => 'planso-forms',
        'description' => esc_html__('PlanSo Forms’s intuitive and user-friendly interface, added with features like auto-responder emails and integrated spam protection, makes it easy to build amazing forms for your WordPress site. Manage form submissions and analyze data efficiently by automatically moving data between your apps using Zoho Flow.', 'zoho-flow'),
        'icon_file' => 'planso-forms.png',
        'class_test' => 'Recursive_ArrayAccess',
        'app_documentation_link' => '',
        'embed_link' => 'planso_forms',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'get_forms',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'get_fields',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'get_webhooks',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'create',
                'path' => '/forms/(?\'form_id\'[\\d]+)/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_options',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'delete_posts',
            ),
        ),
        'hooks' => array(
            array(
                'action' => 'psfb_submit_after_error_check_success',
                'method' => 'process_form_submission',
                'args_count' => 1,
            ),
        ),
    ),
    array (
        'name' => esc_html__("WP Travel Engine"),
        'api_path' => 'wp-travel-engine',
        'class_name' => 'Zoho_Flow_WP_Travel_Engine',
        'gallery_app_link' => 'wp-travel-engine',
        'description' => esc_html__('WP Travel Engine is a travel booking plugin that can help you build SEO-friendly travel booking websites. By integrating WP Travel Engine with your favorite applications, you\'ll be able to automate making bookings and payments, and sending invoices.', 'zoho-flow'),
        'icon_file' => 'wp-travel-engine.png',
        'class_test' => 'Wp_Travel_Engine_Admin',
        'app_documentation_link' => '',
        'embed_link' => 'wp_travel_engine',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/trips',
                'method' => 'get_trips',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_private_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'delete_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'wp_travel_engine_after_enquiry_sent',
                'method' => 'payload_enquiry_created',
                'args_count' => 1,
            ),
            array (
                'action' => 'wp_travel_engine_after_booking_process_completed',
                'method' => 'payload_booking_created',
                'args_count' => 1,
            )
        )
    ),
    array (
        'name' => esc_html__('DigiMember'),
        'api_path' => 'digi-member',
        'class_name' => 'Zoho_Flow_Digi_Member',
        'gallery_app_link' => 'digimember',
        'description' => esc_html__('This easy-to-use membership plugin for WordPress lets you build your own automated membership site. Let Zoho Flow automatically add new orders to your spreadsheet, notify you by chat when a new order is made, create new orders from emails received, and more.', 'zoho-flow'),
        'icon_file' => 'digi-member.png',
        'class_test' => 'ncore_Class',
        'app_documentation_link' => '',
        'embed_link' => 'digimember',
        'version' => 'v1',
        'rest_apis' => array (
            array (
                'type' => 'list',
                'path' => '/products',
                'method' => 'get_all_products',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'list',
                'path' => '/users/(?\'user_id\'[\\d]+)/products',
                'method' => 'get_products_of_user',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'list',
                'path' => '/orders/(?\'user_id\'[\\d]+)',
                'method' => 'get_user_orders',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'create',
                'path' => '/orders',
                'method' => 'create_orders',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'list',
                'path' => '/(?\'post_type\'[a-zA-Z_]+)/webhooks',
                'method' => 'get_webhook_for_order',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'create',
                'path' => '/(?\'post_type\'[a-zA-Z_]+)/webhooks',
                'method' => 'create_webhook_for_order',
                'capability' => 'manage_options',
            ),
            array (
                'type' => 'delete',
                'path' => '/(?\'post_type\'[a-zA-Z_]+)/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'manage_options',
            ),
        ),
        'hooks' => array (
            array (
                'action' => 'digimember_purchase',
                'method' => 'digi_purchase',
                'args_count' => 4,
            ),
        ),
    ),
    array (
        'name' => esc_html__("WS Form"),
        'api_path' => 'ws-form',
        'class_name' => 'Zoho_Flow_WS_Form',
        'gallery_app_link' => 'ws-form',
        'description' => esc_html__('Use WS Forms to create professional, dynamic, mobile-friendly, and accessible forms on your WordPress site. By integrating WS Form with your favorite applications, you will get notified every time a form entry is made.', 'zoho-flow'),
        'icon_file' => 'ws-form.png',
        'class_test' => 'WS_Form_Form',
        'app_documentation_link' => '',
        'embed_link' => 'ws_form',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'list_forms',
                'capability' => 'manage_options_wsform',
            ),
            array(
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'list_form_fields',
                'capability' => 'manage_options_wsform',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'manage_options_wsform',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'wsf_submit_create',
                'method' => 'payload_submission_added',
                'args_count' => 1,
            ),
            array (
                'action' => 'wsf_submit_status',
                'method' => 'payload_submission_status_changed',
                'args_count' => 2,
            )
        ),
    ),
    array(
        'name' => esc_html__("Bookit"),
        'api_path' => 'bookit',
        'class_name' => 'Zoho_Flow_Bookit',
        'gallery_app_link' => 'bookit',
        'description' => esc_html__('Use Bookit to create and manage appointment and event bookings on your WordPress site. By integrating Bookit with finance applications, you will be able to send payment invoices when a booking is completed.', 'zoho-flow'),
        'icon_file' => 'bookit.png',
        'class_test' => 'Bookit\Plugin',
        'app_documentation_link' => '',
        'embed_link' => 'bookit',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/services',
                'method' => 'list_services',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'bookit_appointment_created',
                'method' => 'payload_appointment_created',
                'args_count' => 1,
            ),
            array (
                'action' => 'bookit_appointment_updated',
                'method' => 'payload_appointment_updated',
                'args_count' => 1,
            ),
            array (
                'action' => 'bookit_appointment_status_changed',
                'method' => 'payload_appointment_status_changed',
                'args_count' => 1,
            ),
            array (
                'action' => 'bookit_customer_saved',
                'method' => 'payload_customer_created_or_updated',
                'args_count' => 1,
            ),
        )
    ),
    array(
        'name' => esc_html__("ARMember"),
        'api_path' => 'armember',
        'class_name' => 'Zoho_Flow_ARMember',
        'gallery_app_link' => 'armember',
        'description' => esc_html__('Use ARMember to manage subscription plans, restrict content access, handle member signups, and more on your WordPress site. Integrate ARMember with other applications to collect membership payments and share payment invoices.', 'zoho-flow'),
        'icon_file' => 'armember.png',
        'class_test' => 'ARMwidgetForm',
        'app_documentation_link' => '',
        'embed_link' => 'armember',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/fields',
                'method' => 'get_all_fields',
                'capability' => 'arm_manage_forms',
            ),
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'get_all_forms',
                'capability' => 'arm_manage_forms',
            ),
            array(
                'type' => 'list',
                'path' => '/form/(?\'form_id\'[\\d]+)/fields',
                'method' => 'get_form_fields',
                'capability' => 'arm_manage_forms',
            ),
            array(
                'type' => 'list',
                'path' => '/subscription-plans',
                'method' => 'get_all_plans',
                'capability' => 'arm_manage_plans',
            ),
            array(
                'type' => 'list',
                'path' => '/members',
                'method' => 'list_all_members',
                'capability' => 'arm_manage_members',
            ),
            array(
                'type' => 'create',
                'path' => '/member/(?\'user_id\'[\\d]+)/status',
                'method' => 'update_member_status',
                'capability' => 'arm_manage_members',
            ),
            array(
                'type' => 'create',
                'path' => '/member/(?\'user_id\'[\\d]+)/plan/(?\'plan_id\'[\\d]+)',
                'method' => 'update_member_subscription_plan',
                'capability' => 'arm_manage_members',
            ),
            array(
                'type' => 'list',
                'path' => '/member',
                'method' => 'fetch_member',
                'capability' => 'arm_manage_members',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'arm_manage_members',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'arm_member_update_meta',
                'method' => 'payload_member_added_or_updated',
                'args_count' => 3,
            ),
            array (
                'action' => 'arm_after_add_new_user',
                'method' => 'payload_member_added',
                'args_count' => 2,
            ),
            array (
                'action' => 'arm_after_update_user_profile',
                'method' => 'payload_member_updated',
                'args_count' => 2,
            ),
            array (
                'action' => 'arm_after_form_validate_action',
                'method' => 'payload_form_entry_submitted',
                'args_count' => 2,
            ),
            array (
                'action' => 'arm_after_add_transaction',
                'method' => 'payload_transaction_added',
                'args_count' => 1,
            ),
            array (
                'action' => 'arm_cancel_subscription',
                'method' => 'payload_subscription_cancelled',
                'args_count' => 2,
            )
        )
    ),
    array(
        'name' => esc_html__("Fluent Support"),
        'api_path' => 'fluent-support',
        'class_name' => 'Zoho_Flow_Fluent_Support',
        'gallery_app_link' => 'fluent-support',
        'description' => esc_html__('Fluent Support is a support ticket management system to manage support agents, customers, and tickets. Integrate Fluent Support with other applications using Zoho Flow to escalate or assign tickets to a support agent or get notified every time a new ticket is initiated.', 'zoho-flow'),
        'icon_file' => 'fluent-support.png',
        'class_test' => 'FluentSupport\App\Api\Classes\Tickets',
        'app_documentation_link' => '',
        'embed_link' => 'fluent_support',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/products',
                'method' => 'get_all_products',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/customers',
                'method' => 'get_all_customers',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/tickets',
                'method' => 'get_all_tickets',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/mailbox',
                'method' => 'get_all_mailbox',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/agents',
                'method' => 'get_all_agents',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/getcustomers',
                'method' => 'get_customer',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/getagents',
                'method' => 'get_agent',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/gettickets',
                'method' => 'get_ticket',
                'capability' => 'read_private_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/customers',
                'method' => 'customer_create',
                'capability' => 'edit_private_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/tickets',
                'method' => 'ticket_create',
                'capability' => 'edit_private_posts',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_private_posts',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'delete_private_posts',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'fluent_support/ticket_created',
                'method' => 'payload_ticket_created',
                'args_count' => 2,
            ),
            array (
                'action' => 'fluent_support/ticket_agent_change',
                'method' => 'payload_ticket_agent_changed',
                'args_count' => 2,
            ),
            array (
                'action' => 'fluent_support/tickets_moved',
                'method' => 'payload_tickets_moved',
                'args_count' => 3,
            ),
            array (
                'action' => 'fluent_support/ticket_closed',
                'method' => 'payload_ticket_closed',
                'args_count' => 2,
            ),
            array (
                'action' => 'fluent_support/ticket_reopen',
                'method' => 'payload_ticket_reopened',
                'args_count' => 2,
            ),
        )
    ),
    array (
        'name' => esc_html__("VikBooking"),
        'api_path' => 'vikbooking',
        'class_name' => 'Zoho_Flow_VikBooking',
        'gallery_app_link' => 'vikbooking',
        'description' => esc_html__('Use VikBooking to manage a reservation system for hotels, villas, apartments, hostels and more on your WordPress site. By integrating VikBooking with other applications, you can send notification messages to your registrants when they make a booking through your website.', 'zoho-flow'),
        'icon_file' => 'vikbooking.png',
        'class_test' => 'JControllerVikBooking',
        'app_documentation_link' => '',
        'embed_link' => 'vikbooking',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/custom-fields',
                'method' => 'list_custom_fields',
                'capability' => 'com_vikbooking_vbo_bookings',
            ),
            array(
                'type' => 'list',
                'path' => '/customers',
                'method' => 'list_customers',
                'capability' => 'com_vikbooking_vbo_bookings',
            ),
            array(
                'type' => 'list',
                'path' => '/orders',
                'method' => 'list_orders',
                'capability' => 'com_vikbooking_vbo_bookings',
            ),
            array(
                'type' => 'list',
                'path' => '/customer',
                'method' => 'fetch_customer',
                'capability' => 'com_vikbooking_vbo_bookings',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
        ),
    ),
    array (
        'name' => esc_html__("Quill Forms"),
        'api_path' => 'quill-forms',
        'class_name' => 'Zoho_Flow_Quill_Forms',
        'gallery_app_link' => 'ws-form',
        'description' => esc_html__('Use Quill Forms to build different types of forms on your WordPress site. By integrating Quill Forms with other applications, you can collect and manage form information more efficiently.', 'zoho-flow'),
        'icon_file' => 'quill-forms.png',
        'class_test' => 'QuillForms\Form_Submission',
        'app_documentation_link' => '',
        'embed_link' => 'quill_forms',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/forms',
                'method' => 'list_forms',
                'capability' => 'read_quillform',
            ),
            array(
                'type' => 'list',
                'path' => '/forms/(?\'form_id\'[\\d]+)/fields',
                'method' => 'list_form_fields',
                'capability' => 'read_quillform',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'read_quillform_entry',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'quillforms_after_entry_processed',
                'method' => 'payload_form_entry_submitted',
                'args_count' => 2,
            )
        ),
    ),
    array(
        'name' => esc_html__("Fluent Booking"),
        'api_path' => 'fluent-booking',
        'class_name' => 'Zoho_Flow_Fluent_Booking',
        'gallery_app_link' => 'fluent-booking',
        'description' => esc_html__('Use FluentBooking to manage appointment scheduling on your WordPress site. You can integrate FluentBooking with your favourite apps to get reminders on your calls, events, meetings, webinars, and more.', 'zoho-flow'),
        'icon_file' => 'fluent-booking.png',
        'class_test' => 'FluentBooking\App\Models\Booking',
        'app_documentation_link' => '',
        'embed_link' => 'fluent_booking',
        'version' => 'v1',
        'rest_apis' => array(
            array(
                'type' => 'list',
                'path' => '/calendars',
                'method' => 'list_calendars',
                'capability' => 'read_private_pages',
            ),
            array(
                'type' => 'list',
                'path' => '/calendars/(?\'calendar_id\'[\\d]+)/events',
                'method' => 'list_calendar_events',
                'capability' => 'read_private_pages',
            ),
            array(
                'type' => 'list',
                'path' => '/events/(?\'event_id\'[\\d]+)/fields',
                'method' => 'list_event_fields',
                'capability' => 'read_private_pages',
            ),
            array(
                'type' => 'list',
                'path' => '/events/(?\'event_id\'[\\d]+)/bookings/(?\'booking_id\'[\\d]+)',
                'method' => 'fetch_booking',
                'capability' => 'read_private_pages',
            ),
            array(
                'type' => 'create',
                'path' => '/webhooks',
                'method' => 'create_webhook',
                'capability' => 'edit_private_pages',
            ),
            array(
                'type' => 'delete',
                'path' => '/webhooks/(?\'webhook_id\'[\\d]+)',
                'method' => 'delete_webhook',
                'capability' => 'read',
            ),
            array(
                'type' => 'list',
                'path' => '/systeminfo',
                'method' => 'get_system_info',
                'capability' => 'read',
            )
        ),
        'hooks' => array(
            array (
                'action' => 'fluent_booking/after_booking_scheduled',
                'method' => 'payload_booking_created',
                'args_count' => 2,
            ),
            array (
                'action' => 'fluent_booking/booking_schedule_cancelled',
                'method' => 'payload_booking_cancelled',
                'args_count' => 2,
            ),
            array (
                'action' => 'fluent_booking/after_booking_rescheduled',
                'method' => 'payload_booking_rescheduled',
                'args_count' => 2,
            ),
            array (
                'action' => 'fluent_booking/booking_schedule_rejected',
                'method' => 'payload_booking_rejected',
                'args_count' => 2,
            ),
            array (
                'action' => 'fluent_booking/after_booking_completed',
                'method' => 'payload_booking_completed',
                'args_count' => 2,
            ),
            array (
                'action' => 'fluent_booking/after_booking_pending',
                'method' => 'payload_booking_pending',
                'args_count' => 2,
            ),
            array (
                'action' => 'fluent_booking/after_booking_no_show',
                'method' => 'payload_booking_no_show',
                'args_count' => 2,
            ),
            array (
                'action' => 'fluent_booking/payment/update_payment_status_paid',
                'method' => 'payload_payment_paid',
                'args_count' => 1,
            )
        )
    )
);
