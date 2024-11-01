<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Zoho_Flow_Service_Suggestion{
  private $id;
  private $name;
  private $icon_file;
  private $gallery_app_link;
  private $plugin_api_page_link;
  private $has_api_key;
  private $is_plugin_integration;

  /**
   * admin notice blocked services:
   * WPForms,Formidable Forms,everest-forms, Mailster, Bitform, Ninja Tables, Akismet,WP Mail, SMTP, Fluent SMTP, The Newsletter Plugin, UserFeedback, Jetpack CRM, Fluent Booking, BookingPress, Easy Digital Downloads, Simply Schedule Appointments, Quill Forms, Paid Member Subscriptions, ARMember
   */
  public function __construct() {
      global $pagenow;
      $current_page = $pagenow;
      $_is_admin_page  = 'admin.php' === $current_page ? true : false;
      $_is_edit_page  = 'edit.php' === $current_page ? true : false;
      $_page = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : false;
      $_post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : false;
      if( $_is_admin_page && $_page ){
          $service_id = $this->admin_page_plugin_finder( $_page );
          if ( isset( $service_id ) ){
              $this->set_service_meta( $service_id );
          }
      }
      elseif( $_is_edit_page && $_post_type ){
          $service_id = $this->edit_post_type_plugin_finder( $_post_type );
          if ( isset( $service_id ) ){
              $this->set_service_meta( $service_id );
          }
      }
      if( !isset( $this->id ) ){
          $this->set_service_meta( 'wordpress-org' );
      }

      if( $this->is_plugin_integration ){
          $this->has_api_key = $this->has_api_keys();
      }
	}

	private function admin_page_plugin_finder( $page ){
	    $page_service_map = array(
	        'wpcf7' => 'contact-form-7',
	        'wpcf7-integration' => 'contact-form-7',
	        'nf-submissions' => 'ninja-forms',
	        'nf-settings' => 'ninja-forms',
	        'nf-system-status' => 'ninja-forms',
	        'nf-import-export' => 'ninja-forms',
	        'formidable' => 'formidable-forms',
	        'formidable-smtp' => 'formidable-forms',
	        'formidable-addons' => 'formidable-forms',
	        'formidable-import' => 'formidable-forms',
	        'formidable-entries' => 'formidable-forms',
	        'ultimatemember' => 'ultimate-member',
	        'um_roles' => 'ultimate-member',
	        'ultimatemember-extensions' => 'ultimate-member',
	        'um_options' => 'ultimate-member',
	        'digimember' => 'digi-member',
	        'digimember_orders' => 'digi-member',
	        'learndash_lms_settings' => 'learndash',
	        'learndash-lms-reports' => 'learndash',
	        'ps-form-builder' => 'planso-forms',
	        'simple_wp_membership' => 'simple-membership',
	        'simple_wp_membership_addons' => 'simple-membership',
	        'forminator' => 'forminator',
	        'forminator-integrations' => 'forminator',
	        'forminator-addons' => 'forminator',
	        'forminator-entries' => 'forminator',
	        'give-forms' => 'givewp',
	        'user-registration' => 'user-registration',
	        'user-registration-addons' => 'user-registration',
	        'pmpro-dashboard' => 'paid-memberships-pro',
	        'pmpro-memberslist' => 'paid-memberships-pro',
	        'pmpro-orders' => 'paid-memberships-pro',
	        'pmpro-addons' => 'paid-memberships-pro',
	        'wc-addons' => 'woocommerce',
	        'wc-admin' => 'woocommerce',
	        'wc-orders' => 'woocommerce',
	        'wc-reports' => 'woocommerce',
	        'wc-settings' => 'woocommerce',
	        'wc-status' => 'woocommerce',
	        'gf_edit_forms' => 'gravity-forms',
	        'gf_addons' => 'gravity-forms',
	        'gf_entries' => 'gravity-forms',
	        'gf_export' => 'gravity-forms',
	        'mailpoet-homepage' => 'mailpoet',
	        'mailpoet-forms' => 'mailpoet',
	        'mailpoet-subscribers' => 'mailpoet',
	        'mailpoet-lists' => 'mailpoet',
	        'mailpoet-help' => 'mailpoet',
	        'mailpoet-upgrade' => 'mailpoet',
	        'wptravelengine-admin-page' => 'wp-travel-engine',
	        'fluent_forms_smtp' => 'fluent-forms',
	        'fluent_forms' => 'fluent-forms',
	        'fluentcrm-admin' => 'fluentcrm',
	        'fluent-support' => 'fluent-support',
	        'tablepress' => 'tablepress',
	        'tablepress_add' => 'tablepress',
	        'tablepress_import' => 'tablepress',
	        'tablepress_export' => 'tablepress',
	        'ninja_tables' => 'ninja-tables',
	        'akismet-key-config' => 'akismet',
	        'postman' => 'post-smtp',
	        'postman_email_log' => 'post-smtp',
	        'userswp' => 'userswp',
	        'uwp_form_builder' => 'userswp',
	        'uwp_status' => 'userswp',
	        'uwp-addons' => 'userswp',
	        'bp-components' => 'buddyboss',
	        'bp-integrations' => 'buddyboss',
	        'bp-profile-setup' => 'buddyboss',
	        'bp-activity' => 'buddyboss',
	        'bp-tools' => 'buddyboss',
	        'bp-help' => 'buddyboss',
	        'bp-settings' => 'buddyboss',
	        'bp-pages' => 'buddyboss',
	        'easy-login-woocommerce-settings' => 'login-signup-popup',
	        'xoo-el-fields' => 'login-signup-popup',
	        'affiliate-wp' => 'affiliatewp',
	        'affiliate-wp-affiliates' => 'affiliatewp',
	        'affiliate-wp-referrals' => 'affiliatewp',
	        'affiliate-wp-payouts' => 'affiliatewp',
	        'affiliate-wp-tools' => 'affiliatewp',
	        'affiliate-wp-settings' => 'affiliatewp',
	        'affiliate-wp-add-ons' => 'affiliatewp',
	        'affiliate-wp-about' => 'affiliatewp',
	        'quiz-maker' => 'quiz-maker',
	        'quiz-maker-questions' => 'quiz-maker',
	        'quiz-maker-integrations' => 'quiz-maker',
	        'quiz-maker-results' => 'quiz-maker',
	        'quiz-maker-quiz-attributes' => 'quiz-maker',
	        'quiz-maker-quiz-features' => 'quiz-maker',
	        'hustle' => 'hustle',
	        'hustle_popup_listing' => 'hustle',
	        'hustle_slidein_listing' => 'hustle',
	        'hustle_embedded_listing' => 'hustle',
	        'hustle_integrations' => 'hustle',
	        'hustle_entries' => 'hustle',
	        'hustle_settings' => 'hustle',
	        'hustle_pro' => 'hustle',
	        'ws-form' => 'ws-form',
	        'ws-form-add' => 'ws-form',
	        'ws-form-edit' => 'ws-form',
	        'ws-form-submit' => 'ws-form',
	        'ws-form-settings' => 'ws-form',
	        'ws-form-upgrade' => 'ws-form',
	        'ws-form-add-ons' => 'ws-form',
	        'happyforms-import' => 'happyforms',
	        'happyforms-export' => 'happyforms',
	        'wpamelia-dashboard' => 'amelia',
	        'wpamelia-calendar' => 'amelia',
	        'wpamelia-appointments' => 'amelia',
	        'wpamelia-events' => 'amelia',
	        'wpamelia-services' => 'amelia',
	        'wpamelia-customers' => 'amelia',
	        'wpamelia-notifications' => 'amelia',
	        'wpamelia-settings' => 'amelia',
	        'wpamelia-lite-vs-premium' => 'amelia',
	        'metform_get_help' => 'metform',
	        'wpbc' => 'wp-booking-calendar',
	        'wpbc-new' => 'wp-booking-calendar',
	        'wpbc-availability' => 'wp-booking-calendar',
	        'wpbc-resources' => 'wp-booking-calendar',
	        'wpbc-settings' => 'wp-booking-calendar',
	        'wpbc-go-pro' => 'wp-booking-calendar',
	        'wp-polls/polls-manager.php' => 'wp-polls',
	        'wp-polls/polls-add.php' => 'wp-polls',
	        'wp-polls/polls-options.php' => 'wp-polls',
	        'wp-polls/polls-templates.php' => 'wp-polls',
	        'qsm_dashboard' => 'quiz-and-survey-master',
	        'qsm-answer-label' => 'quiz-and-survey-master',
	        'mlw_quiz_results' => 'quiz-and-survey-master',
	        'qmn_global_settings' => 'quiz-and-survey-master',
	        'qsm_quiz_tools' => 'quiz-and-survey-master',
	        'qmn_stats' => 'quiz-and-survey-master',
	        'qmn_addons' => 'quiz-and-survey-master',
	        'qsm-free-addon' => 'quiz-and-survey-master',
	        'tutor' => 'tutor-lms',
	        'tutor-students' => 'tutor-lms',
	        'question_answer' => 'tutor-lms',
	        'tutor_quiz_attempts' => 'tutor-lms',
	        'tutor-addons' => 'tutor-lms',
	        'llms-dashboard' => 'lifter-lms',
	        'llms-settings' => 'lifter-lms',
	        'llms-reporting' => 'lifter-lms',
	        'llms-import' => 'lifter-lms',
	        'llms-status' => 'lifter-lms',
	        'llms-resources' => 'lifter-lms',
	        'llms-add-ons' => 'lifter-lms',
	        'heateor-ss-general-options' => 'super-socializer',
	        'heateor-social-commenting' => 'super-socializer',
	        'heateor-social-login' => 'super-socializer',
	        'heateor-social-sharing' => 'super-socializer',
	        'heateor-like-buttons' => 'super-socializer',
	        'tec-tickets' => 'event-tickets',
	        'tec-tickets-attendees' => 'event-tickets',
	        'tec-tickets-settings' => 'event-tickets',
	        'tec-tickets-help' => 'event-tickets',
	        'bookly-dashboard' => 'bookly',
	        'bookly-calendar' => 'bookly',
	        'bookly-appointments' => 'bookly',
	        'bookly-staff' => 'bookly',
	        'bookly-services' => 'bookly',
	        'bookly-customers' => 'bookly',
	        'bookly-settings' => 'bookly',
	        'bookly-shop' => 'bookly',
	        'bookly-news' => 'bookly',
	        'bookly-notifications' => 'bookly',
	        'bookly-cloud-products' => 'bookly',
					'cp_apphourbooking' => 'appointment-hour-booking',
					'cp_apphourbooking_settings' => 'appointment-hour-booking',
					'cp_apphourbooking_addons' => 'appointment-hour-booking',
					'cp_apphourbooking_support' => 'appointment-hour-booking',
					'wpsbc-calendars' => 'wp-simple-booking-calendar',
					'wpsbc-settings' => 'wp-simple-booking-calendar',
					'wpbs-calendars' => 'wp-booking-system',
					'wpbs-forms' => 'wp-booking-system',
					'wpbs-settings' => 'wp-booking-system',
					'vikbooking' => 'vikbooking',
					'bookit' => 'bookit',
					'bookit-appointments' => 'bookit',
					'bookit-services' => 'bookit',
					'bookit-staff' => 'bookit',
					'bookit-customers' => 'bookit',
					'bookit-settings' => 'bookit',
					'bookit-account' => 'bookit',
					'bookit-addons-integrations' => 'bookit',
					'booking-package/index.php' => 'booking-package',
					'booking-package_schedule_page' => 'booking-package',
					'manage-fields' => 'profile-builder',
					'profile-builder-add-ons' => 'profile-builder',
					'profile-builder-general-settings' => 'profile-builder',
					'profile-builder-basic-info' => 'profile-builder',
					'profile-builder-dashboard' => 'profile-builder',
					'wprua' => 'restrict-user-access',
					'wprua-level' => 'restrict-user-access',
					'wprua-settings' => 'restrict-user-access',
					'wprua-addons' => 'restrict-user-access',
					'rm_form_manage' => 'registrationmagic',
					'rm_dashboard_widget_dashboard' => 'registrationmagic',
					'rm_submission_manage' => 'registrationmagic',
					'rm_attachment_manage' => 'registrationmagic',
					'rm_analytics_show_form' => 'registrationmagic',
					'rm_form_manage_cstatus' => 'registrationmagic',
					'rm_ex_chronos_manage_tasks' => 'registrationmagic',
					'rm_invitations_manage' => 'registrationmagic',
					'rm_user_manage' => 'registrationmagic',
					'rm_user_role_manage' => 'registrationmagic',
					'rm_paypal_field_manage' => 'registrationmagic',
					'rm_payments_manage' => 'registrationmagic',
					'rm_options_manage' => 'registrationmagic',
					'rm_support_forum' => 'registrationmagic',
					'rm_support_premium_page' => 'registrationmagic',
					'new-user-approve-admin' => 'new-user-approve',
					'nua-invitation-code' => 'new-user-approve'

	    );
	    if( isset( $page ) ){
	        if( array_key_exists( $page, $page_service_map ) ){
	            return $page_service_map[$page];
	        }
	    }
	}

	private function edit_post_type_plugin_finder( $post_type ){
	    $post_type_service_map = array(
	        'um_form' => 'ultimate-member',
	        'um_directory' => 'ultimate-member',
	        'um_form' => 'ultimate-member',
	        'sfwd-courses' => 'learndash',
	        'sfwd-essays' => 'learndash',
	        'sfwd-question' => 'learndash',
	        'acf-field-group' => 'advanced-custom-fields',
	        'give_forms' => 'givewp',
	        'wptravelengine-admin-page' => 'wp-travel-engine',
	        'booking' => 'wp-travel-engine',
	        'enquiry' => 'wp-travel-engine',
	        'happyform' => 'happyforms',
	        'jet-form-builder' => 'jetformbuilder',
	        'metform-form' => 'metform',
	        'kadence_form' => 'kadence-blocks',
	        'kaliforms_forms' => 'kali-forms',
	        'qsm_quiz' => 'quiz-and-survey-master',
	        'wpdmpro' => 'download-manager',
	        'llms_form' => 'lifter-lms',
	        'popup' => 'popup-maker',
					'tribe_events' => 'the-events-calendar',
					'tribe_venue' => 'the-events-calendar',
					'tribe_organizer' => 'the-events-calendar',
	        'tec_tc_order' => 'event-tickets',
					'event' => 'events-manager',
					'location' => 'events-manager',
					'event-recurring' => 'events-manager',
					'invitation_code' => 'new-user-approve'
	    );
	    if( isset( $post_type ) ){
	        if( array_key_exists( $post_type, $post_type_service_map ) ){
	            return $post_type_service_map[$post_type];
	        }
	    }
	}

  private function set_service_meta( $service_id ){
      if( 'wordpress-org' === $service_id ){
          $this->id = 'wordpress-org';
          $this->name = 'WordPress.org';
          $this->icon_file = 'wordpress.png';
          $this->gallery_app_link = 'wordpress-org';
          $this->plugin_api_page_link = 'wordpress-org';
          $this->is_plugin_integration = true;
      }
      elseif( 'contact-form-7' === $service_id ){
          $this->id = 'contact-form-7';
          $this->name = 'Contact Form 7';
          $this->icon_file = 'contact-form-7.png';
          $this->gallery_app_link = 'contact-form-7';
          $this->plugin_api_page_link = 'contact-form-7';
          $this->is_plugin_integration = true;
      }
      elseif( 'ninja-forms' === $service_id ){
          $this->id = 'ninja-forms';
          $this->name = 'Ninja Forms';
          $this->icon_file = 'ninja-forms.png';
          $this->gallery_app_link = 'ninja-forms';
          $this->plugin_api_page_link = 'ninja-forms';
          $this->is_plugin_integration = true;
      }
      elseif( 'formidable-forms' === $service_id ){
          $this->id = 'formidable-forms';
          $this->name = 'Formidable Forms';
          $this->icon_file = 'formidable-forms.png';
          $this->gallery_app_link = 'formidable-forms';
          $this->plugin_api_page_link = 'formidable-forms';
          $this->is_plugin_integration = true;
      }
      elseif( 'ultimate-member' === $service_id ){
          $this->id = 'ultimate-member';
          $this->name = 'Ultimate Member';
          $this->icon_file = 'ultimate-member.png';
          $this->gallery_app_link = 'ultimate-member';
          $this->plugin_api_page_link = 'ultimate-member';
          $this->is_plugin_integration = true;
      }
      elseif( 'digi-member' === $service_id ){
          $this->id = 'digi-member';
          $this->name = 'DigiMember';
          $this->icon_file = 'digi-member.png';
          $this->gallery_app_link = 'digimember';
          $this->plugin_api_page_link = 'digi-member';
          $this->is_plugin_integration = true;
      }
      elseif( 'learndash' === $service_id ){
          $this->id = 'learndash';
          $this->name = 'LearnDash';
          $this->icon_file = 'learndash.png';
          $this->gallery_app_link = 'learndash';
          $this->plugin_api_page_link = 'learndash';
          $this->is_plugin_integration = true;
      }
      elseif( 'planso-forms' === $service_id ){
          $this->id = 'planso-forms';
          $this->name = 'PlanSo Forms';
          $this->icon_file = 'planso-forms.png';
          $this->gallery_app_link = 'planso-forms';
          $this->plugin_api_page_link = 'planso-forms';
          $this->is_plugin_integration = true;
      }
      elseif( 'simple-membership' === $service_id ){
          $this->id = 'simple-membership';
          $this->name = 'Simple Membership';
          $this->icon_file = 'simple-membership.png';
          $this->gallery_app_link = 'simple-membership';
          $this->plugin_api_page_link = 'simple-membership';
          $this->is_plugin_integration = true;
      }
      elseif( 'advanced-custom-fields' === $service_id ){
          $this->id = 'advanced-custom-fields';
          $this->name = 'Advanced Custom Fields';
          $this->icon_file = 'acf.png';
          $this->gallery_app_link = 'advanced-custom-fields';
          $this->plugin_api_page_link = 'advanced-custom-fields';
          $this->is_plugin_integration = true;
      }
      elseif( 'forminator' === $service_id ){
          $this->id = 'forminator';
          $this->name = 'Forminator';
          $this->icon_file = 'forminator.png';
          $this->gallery_app_link = 'forminator';
          $this->plugin_api_page_link = 'forminator';
          $this->is_plugin_integration = true;
      }
      elseif( 'givewp' === $service_id ){
          $this->id = 'givewp';
          $this->name = 'GiveWP';
          $this->icon_file = 'givewp.png';
          $this->gallery_app_link = 'givewp';
          $this->plugin_api_page_link = 'givewp';
          $this->is_plugin_integration = true;
      }
      elseif( 'user-registration' === $service_id ){
          $this->id = 'user-registration';
          $this->name = 'User Registration';
          $this->icon_file = 'user-registration.png';
          $this->gallery_app_link = 'user-registration';
          $this->plugin_api_page_link = 'user-registration';
          $this->is_plugin_integration = true;
      }
      elseif( 'paid-memberships-pro' === $service_id ){
          $this->id = 'paid-memberships-pro';
          $this->name = 'Paid Memberships Pro';
          $this->icon_file = 'paid-memberships-pro.png';
          $this->gallery_app_link = 'paid-memberships-pro';
          $this->plugin_api_page_link = 'paid-memberships-pro';
          $this->is_plugin_integration = true;
      }
      elseif( 'wp-travel-engine' === $service_id ){
          $this->id = 'wp-travel-engine';
          $this->name = 'WP Travel Engine';
          $this->icon_file = 'wp-travel-engine.png';
          $this->gallery_app_link = 'wp-travel-engine';
          $this->plugin_api_page_link = 'wp-travel-engine';
          $this->is_plugin_integration = true;
      }
      elseif( 'mailpoet' === $service_id ){
          $this->id = 'mailpoet';
          $this->name = 'MailPoet';
          $this->icon_file = 'mailpoet.png';
          $this->gallery_app_link = 'mailpoet';
          $this->plugin_api_page_link = 'mailpoet';
          $this->is_plugin_integration = true;
      }
      elseif( 'fluent-forms' === $service_id ){
          $this->id = 'fluent-forms';
          $this->name = 'Fluent Forms';
          $this->icon_file = 'fluent-forms.png';
          $this->gallery_app_link = 'fluent-forms';
          $this->plugin_api_page_link = 'fluent-forms';
          $this->is_plugin_integration = true;
      }
      elseif( 'fluentcrm' === $service_id ){
          $this->id = 'fluentcrm';
          $this->name = 'FluentCRM';
          $this->icon_file = 'fluentcrm.png';
          $this->gallery_app_link = 'fluentcrm';
          $this->plugin_api_page_link = 'fluentcrm';
          $this->is_plugin_integration = true;
      }
      elseif( 'fluent-support' === $service_id ){
          $this->id = 'fluent-support';
          $this->name = 'Fluent Support';
          $this->icon_file = 'fluent-support.png';
          $this->gallery_app_link = 'fluent-support';
          $this->plugin_api_page_link = 'fluent-support';
          $this->is_plugin_integration = true;
      }
      elseif( 'tablepress' === $service_id ){
          $this->id = 'tablepress';
          $this->name = 'TablePress';
          $this->icon_file = 'tablepress.png';
          $this->gallery_app_link = 'tablepress';
          $this->plugin_api_page_link = 'tablepress';
          $this->is_plugin_integration = true;
      }
      elseif( 'ninja-tables' === $service_id ){
          $this->id = 'ninja-tables';
          $this->name = 'Ninja Tables';
          $this->icon_file = 'ninja-tables.png';
          $this->gallery_app_link = 'ninja-tables';
          $this->plugin_api_page_link = 'ninja-tables';
          $this->is_plugin_integration = true;
      }
      elseif( 'post-smtp' === $service_id ){
          $this->id = 'post-smtp';
          $this->name = 'Post SMTP';
          $this->icon_file = 'post-smtp.png';
          $this->gallery_app_link = 'post-smtp';
          $this->plugin_api_page_link = 'post-smtp';
          $this->is_plugin_integration = true;
      }
      elseif( 'akismet' === $service_id ){
          $this->id = 'akismet';
          $this->name = 'Akismet';
          $this->icon_file = 'akismet.png';
          $this->gallery_app_link = 'akismet';
          $this->plugin_api_page_link = 'akismet';
          $this->is_plugin_integration = true;
      }
      elseif( 'userswp' === $service_id ){
          $this->id = 'userswp';
          $this->name = 'UsersWP';
          $this->icon_file = 'userswp.png';
          $this->gallery_app_link = 'userswp';
          $this->plugin_api_page_link = 'userswp';
          $this->is_plugin_integration = true;
      }
      elseif( 'buddyboss' === $service_id ){
          $this->id = 'buddyboss';
          $this->name = 'BuddyBoss';
          $this->icon_file = 'buddyboss.png';
          $this->gallery_app_link = 'buddyboss';
          $this->plugin_api_page_link = 'buddyboss';
          $this->is_plugin_integration = true;
      }
      elseif( 'login-signup-popup' === $service_id ){
          $this->id = 'login-signup-popup';
          $this->name = 'Login/Signup Popup';
          $this->icon_file = 'login-signup-popup.png';
          $this->gallery_app_link = 'login-signup-popup';
          $this->plugin_api_page_link = 'login-signup-popup';
          $this->is_plugin_integration = true;
      }
      elseif( 'affiliatewp' === $service_id ){
          $this->id = 'affiliatewp';
          $this->name = 'AffiliateWP';
          $this->icon_file = 'affiliatewp.png';
          $this->gallery_app_link = 'affiliatewp';
          $this->plugin_api_page_link = 'affiliatewp';
          $this->is_plugin_integration = true;
      }
      elseif( 'quiz-maker' === $service_id ){
          $this->id = 'quiz-maker';
          $this->name = 'Quiz Maker';
          $this->icon_file = 'quiz-maker.png';
          $this->gallery_app_link = 'quiz-maker';
          $this->plugin_api_page_link = 'quiz-maker';
          $this->is_plugin_integration = true;
      }
      elseif( 'hustle' === $service_id ){
          $this->id = 'hustle';
          $this->name = 'Hustle';
          $this->icon_file = 'hustle.png';
          $this->gallery_app_link = 'hustle';
          $this->plugin_api_page_link = 'hustle';
          $this->is_plugin_integration = true;
      }
      elseif( 'ws-form' === $service_id ){
          $this->id = 'ws-form';
          $this->name = 'WS Form';
          $this->icon_file = 'ws-form.png';
          $this->gallery_app_link = 'ws-form';
          $this->plugin_api_page_link = 'ws-form';
          $this->is_plugin_integration = true;
      }
      elseif( 'happyforms' === $service_id ){
          $this->id = 'happyforms';
          $this->name = 'Happyforms';
          $this->icon_file = 'happyforms.png';
          $this->gallery_app_link = 'happyforms';
          $this->plugin_api_page_link = 'happyforms';
          $this->is_plugin_integration = true;
      }
      elseif( 'weforms' === $service_id ){
          $this->id = 'weforms';
          $this->name = 'weForms';
          $this->icon_file = 'weforms.png';
          $this->gallery_app_link = 'weforms';
          $this->plugin_api_page_link = 'weforms';
          $this->is_plugin_integration = true;
      }
      elseif( 'jetformbuilder' === $service_id ){
          $this->id = 'jetformbuilder';
          $this->name = 'JetFormBuilder';
          $this->icon_file = 'jetformbuilder.png';
          $this->gallery_app_link = 'jetformbuilder';
          $this->plugin_api_page_link = 'jetformbuilder';
          $this->is_plugin_integration = true;
      }
      elseif( 'amelia' === $service_id ){
          $this->id = 'amelia';
          $this->name = 'Amelia';
          $this->icon_file = 'amelia.png';
          $this->gallery_app_link = 'amelia';
          $this->plugin_api_page_link = 'amelia';
          $this->is_plugin_integration = true;
      }
      elseif( 'metform' === $service_id ){
          $this->id = 'metform';
          $this->name = 'MetForm';
          $this->icon_file = 'metform.png';
          $this->gallery_app_link = 'metform';
          $this->plugin_api_page_link = 'metform';
          $this->is_plugin_integration = true;
      }
      elseif( 'wp-booking-calendar' === $service_id ){
          $this->id = 'wp-booking-calendar';
          $this->name = 'WP Booking Calendar';
          $this->icon_file = 'wp-booking-calendar.png';
          $this->gallery_app_link = 'wp-booking-calendar';
          $this->plugin_api_page_link = 'wp-booking-calendar';
          $this->is_plugin_integration = true;
      }
      elseif( 'kadence-blocks' === $service_id ){
          $this->id = 'kadence-blocks';
          $this->name = 'Kadence Blocks';
          $this->icon_file = 'kadence-blocks.png';
          $this->gallery_app_link = 'kadence-blocks';
          $this->plugin_api_page_link = 'kadence-blocks';
          $this->is_plugin_integration = true;
      }
      elseif( 'kali-forms' === $service_id ){
          $this->id = 'kali-forms';
          $this->name = 'Kali Forms';
          $this->icon_file = 'kali-forms.png';
          $this->gallery_app_link = 'kali-forms';
          $this->plugin_api_page_link = 'kali-forms';
          $this->is_plugin_integration = true;
      }
      elseif( 'wp-polls' === $service_id ){
          $this->id = 'wp-polls';
          $this->name = 'WP-Polls';
          $this->icon_file = 'wp-polls.png';
          $this->gallery_app_link = 'wp-polls';
          $this->plugin_api_page_link = 'wp-polls';
          $this->is_plugin_integration = true;
      }
      elseif( 'quiz-and-survey-master' === $service_id ){
          $this->id = 'quiz-and-survey-master';
          $this->name = 'Quiz And Survey Master';
          $this->icon_file = 'quiz-and-survey-master.png';
          $this->gallery_app_link = 'quiz-and-survey-master';
          $this->plugin_api_page_link = 'quiz-and-survey-master';
          $this->is_plugin_integration = true;
      }
      elseif( 'download-manager' === $service_id ){
          $this->id = 'download-manager';
          $this->name = 'Download Manager';
          $this->icon_file = 'download-manager.png';
          $this->gallery_app_link = 'download-manager';
          $this->plugin_api_page_link = 'download-manager';
          $this->is_plugin_integration = true;
      }
      elseif( 'tutor-lms' === $service_id ){
          $this->id = 'tutor-lms';
          $this->name = 'Tutor LMS';
          $this->icon_file = 'tutor-lms.png';
          $this->gallery_app_link = 'tutor-lms';
          $this->plugin_api_page_link = 'tutor-lms';
          $this->is_plugin_integration = true;
      }
      elseif( 'lifter-lms' === $service_id ){
          $this->id = 'lifter-lms';
          $this->name = 'LifterLMS';
          $this->icon_file = 'lifter-lms.png';
          $this->gallery_app_link = 'lifterlms';
          $this->plugin_api_page_link = 'lifter-lms';
          $this->is_plugin_integration = true;
      }
      elseif( 'popup-maker' === $service_id ){
          $this->id = 'popup-maker';
          $this->name = 'Popup Maker';
          $this->icon_file = 'popup-maker.png';
          $this->gallery_app_link = 'popup-maker';
          $this->plugin_api_page_link = 'popup-maker';
          $this->is_plugin_integration = true;
      }
      elseif( 'super-socializer' === $service_id ){
          $this->id = 'super-socializer';
          $this->name = 'Super Socializer';
          $this->icon_file = 'super-socializer.png';
          $this->gallery_app_link = 'super-socializer';
          $this->plugin_api_page_link = 'super-socializer';
          $this->is_plugin_integration = true;
      }
      elseif( 'the-events-calendar' === $service_id ){
          $this->id = 'the-events-calendar';
          $this->name = 'The Events Calendar';
          $this->icon_file = 'the-events-calendar.png';
          $this->gallery_app_link = 'the-events-calendar';
          $this->plugin_api_page_link = 'the-events-calendar';
          $this->is_plugin_integration = true;
      }
      elseif( 'event-tickets' === $service_id ){
          $this->id = 'event-tickets';
          $this->name = 'Event Tickets';
          $this->icon_file = 'event-tickets.png';
          $this->gallery_app_link = 'event-tickets';
          $this->plugin_api_page_link = 'event-tickets';
          $this->is_plugin_integration = true;
      }
      elseif( 'bookly' === $service_id ){
          $this->id = 'bookly';
          $this->name = 'Bookly';
          $this->icon_file = 'bookly.png';
          $this->gallery_app_link = 'bookly';
          $this->plugin_api_page_link = 'bookly';
          $this->is_plugin_integration = true;
      }
			elseif( 'appointment-hour-booking' === $service_id ){
          $this->id = 'appointment-hour-booking';
          $this->name = 'Appointment Hour Booking';
          $this->icon_file = 'appointment-hour-booking.png';
          $this->gallery_app_link = 'appointment-hour-booking';
          $this->plugin_api_page_link = 'appointment-hour-booking';
          $this->is_plugin_integration = true;
      }
			elseif( 'events-manager' === $service_id ){
          $this->id = 'events-manager';
          $this->name = 'Events Manager';
          $this->icon_file = 'events-manager.png';
          $this->gallery_app_link = 'events-manager';
          $this->plugin_api_page_link = 'events-manager';
          $this->is_plugin_integration = true;
      }
			elseif( 'wp-simple-booking-calendar' === $service_id ){
          $this->id = 'wp-simple-booking-calendar';
          $this->name = 'WP Simple Booking Calendar';
          $this->icon_file = 'wp-simple-booking-calendar.png';
          $this->gallery_app_link = 'wp-simple-booking-calendar';
          $this->plugin_api_page_link = 'wp-simple-booking-calendar';
          $this->is_plugin_integration = true;
      }
			elseif( 'wp-booking-system' === $service_id ){
          $this->id = 'wp-booking-system';
          $this->name = 'WP Booking System';
          $this->icon_file = 'wp-booking-system.png';
          $this->gallery_app_link = 'wp-booking-system';
          $this->plugin_api_page_link = 'wp-booking-system';
          $this->is_plugin_integration = true;
      }
			elseif( 'vikbooking' === $service_id ){
          $this->id = 'vikbooking';
          $this->name = 'VikBooking';
          $this->icon_file = 'vikbooking.png';
          $this->gallery_app_link = 'vikbooking';
          $this->plugin_api_page_link = 'vikbooking';
          $this->is_plugin_integration = true;
      }
			elseif( 'bookit' === $service_id ){
          $this->id = 'bookit';
          $this->name = 'Bookit';
          $this->icon_file = 'bookit.png';
          $this->gallery_app_link = 'bookit';
          $this->plugin_api_page_link = 'bookit';
          $this->is_plugin_integration = true;
      }
			elseif( 'booking-package' === $service_id ){
          $this->id = 'booking-package';
          $this->name = 'Booking Package';
          $this->icon_file = 'booking-package.png';
          $this->gallery_app_link = 'booking-package';
          $this->plugin_api_page_link = 'booking-package';
          $this->is_plugin_integration = true;
      }
			elseif( 'profile-builder' === $service_id ){
          $this->id = 'profile-builder';
          $this->name = 'Profile Builder';
          $this->icon_file = 'profile-builder.png';
          $this->gallery_app_link = 'profile-builder';
          $this->plugin_api_page_link = 'profile-builder';
          $this->is_plugin_integration = true;
      }
			elseif( 'restrict-user-access' === $service_id ){
          $this->id = 'restrict-user-access';
          $this->name = 'Restrict User Access';
          $this->icon_file = 'restrict-user-access.png';
          $this->gallery_app_link = 'restrict-user-access';
          $this->plugin_api_page_link = 'restrict-user-access';
          $this->is_plugin_integration = true;
      }
			elseif( 'registrationmagic' === $service_id ){
          $this->id = 'registrationmagic';
          $this->name = 'RegistrationMagic';
          $this->icon_file = 'registrationmagic.png';
          $this->gallery_app_link = 'registrationmagic';
          $this->plugin_api_page_link = 'registrationmagic';
          $this->is_plugin_integration = true;
      }
			elseif( 'new-user-approve' === $service_id ){
          $this->id = 'new-user-approve';
          $this->name = 'New User Approve';
          $this->icon_file = 'new-user-approve.png';
          $this->gallery_app_link = 'new-user-approve';
          $this->plugin_api_page_link = 'new-user-approve';
          $this->is_plugin_integration = true;
      }
      elseif( 'woocommerce' === $service_id ){
          $this->id = 'woocommerce';
          $this->name = 'WooCommerce';
          $this->icon_file = 'woocommerce.png';
          $this->gallery_app_link = 'woocommerce';
          $this->plugin_api_page_link = '';
          $this->is_plugin_integration = false;
      }
      elseif( 'gravity-forms' === $service_id ){
          $this->id = 'gravity-forms';
          $this->name = 'Gravity Forms';
          $this->icon_file = 'gravity-forms.png';
          $this->gallery_app_link = 'gravity-forms';
          $this->plugin_api_page_link = '';
          $this->is_plugin_integration = false;
      }
  }

  public function permission_to_show(){
      if( ( $this->is_plugin_integration ) && ( !$this->has_api_key ) ){
          return true;
      }
      else if( !( $this->is_plugin_integration ) ){
          return true;
      }
      return false;
  }

  private function has_api_keys(){
    $args = array(
							'post_type' => WP_ZOHO_FLOW_API_KEY_POST_TYPE,
							'posts_per_page' => -1,
							'author' => get_current_user_id(),
							'fields' => 'ids',
			        'meta_query' => array(
			              	'relation' => 'AND',
			              	array(
			        					'key' => 'user_id',
			        					'value' => get_current_user_id(),
			        					'compare' => '='
			        				),
			        				array(
			        					'key' => 'plugin_service',
			        					'value' => $this->id,
			        					'compare' => '='
			        				)
			  			)
						);
		$api_keys = get_posts( $args );
		if( isset( $api_keys ) && ( sizeof( $api_keys ) > 0 ) )	{
			return true;
		}
		return false;
  }

  public function get_option_slug(){
    return "zoho_flow_next_suggestion_date_".$this->id."_".get_current_user_id();
  }

  public function display(){
    if( !empty( $this->id ) ){
      ?>
      <div id= "flow-suggestion-notice" style="border: 5px solid transparent;border-bottom: 0;border-left: 0;border-right: 0;padding: 10px;border-image: url('<?php echo plugins_url('../assets/images/zoho-colors.gif', __FILE__); ?>') 100% 1 stretch;" class="notice notice-info is-dismissible">
    		<div style="display:flex;">
    			<div style="margin-top: auto;margin-bottom: auto;padding: 15px;display: inline-block;">
            <p hidden id="flow_service_id"><?php echo $this->id; ?></p>
    				<div style="display:inline-flex;">
    					<img style="max-height: 40px;max-width: 40px;object-fit: contain;padding: 10px;border: solid;border-color: #b8afaf;border-width: thin;border-radius: 10px;-webkit-box-sizing: content-box;" src="<?php echo plugins_url('../assets/images/flow-256.png', __FILE__); ?>"/>
    					<div style="margin-top: auto;margin-bottom: auto;font-size: 25px;font-weight: 400;">&#8644;</div>
              <img style="max-height: 40px;max-width: 40px;object-fit: contain;padding: 10px;border: solid;border-color: #b8afaf;border-width: thin;border-radius: 10px;-webkit-box-sizing: content-box;" src="<?php echo plugins_url('../assets/images/logos/' . $this->icon_file, __FILE__); ?>"/>
    				</div>
    			</div>
    			<div>
    				<div style="font-size: 15px;padding: 10px;padding-top: 5px;text-align: center;margin-left: auto;margin-right: auto;max-width: 90%;">
    					<?php
    					echo sprintf(
    						esc_html__('Unlock unlimited possibilities with %2$s! Seamlessly integrate your favorite services, including %1$s, with various business applications and experience the true potential of automation.'), sprintf('<strong>'.$this->name.'</strong>'),sprintf('<strong>Zoho Flow</strong>')
    					);
    					?>
    				</div>
    				<div style="text-align:center;">
              <?php
              if( !empty( $this->plugin_api_page_link ) ){
                $flow_plugin_api_page_link = add_query_arg(
                  array(
                    'service' => $this->plugin_api_page_link
                  ),
                  menu_page_url( 'zoho_flow', false )
                );
                ?>
                  <a id="suggestion-notice-review-botton" class="button button-primary" style="margin:5px;" href="<?php echo $flow_plugin_api_page_link ?>" target="_blank"><?php echo 'Try now' ?></a>
                  <a id="suggestion-notice-gallery-botton" class="button button-secondary" style="margin:5px;" href="https://www.zohoflow.com/apps/<?php echo $this->gallery_app_link ?>/integrations/?utm_source=wordpress&utm_medium=link&utm_campaign=zoho_flow_integration_suggestion_<?php echo $this->id ?>" target="_blank"><?php echo 'Check how' ?></a>
                <?php
              }
              else{
                ?>
                  <a id="suggestion-notice-gallery-botton" class="button button-primary" style="margin:5px;" href="https://www.zohoflow.com/apps/<?php echo $this->gallery_app_link ?>/integrations/?utm_source=wordpress&utm_medium=link&utm_campaign=zoho_flow_plugin_suggestion" target="_blank"><?php echo 'Check how' ?></a>
                <?php
              }
               ?>
    					<a id="suggestion-notice-later-botton" class="button button-secondary" style="margin:5px;"><?php echo 'Remind me later' ?></a>
    					<a id="suggestion-notice-donot-botton" class="button button-secondary" style="margin:5px;"><?php echo 'Do not show again' ?></a>
    				</div>
    			</div>
    		</div>
  	   </div>
    <?php
    }
  }

}
