<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class used for APIs and Webhooks
 *
 * @since zohoflow      2.5.0
 * @since jetpack_crm   6.4.2
 */
class Zoho_Flow_Jetpack_CRM extends Zoho_Flow_Service{
    
    /**
     * 
     * @var array Webhook events supported.
     */
    public static $supported_events = array( "contact_created", "contact_updated", "contact_status_updated", "company_created", "quote_created", "quote_accepted", "invoice_created", "invoice_updated", "transaction_created" );
    
    /**
     * List forms
     * 
     * @param WP_REST_Request $request WP_REST_Request object.
     * 
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of contact details | WP_Error object with error details.
     */
    public function list_contacts( $request ){
        global $zbs;
        return rest_ensure_response( $zbs->DAL->contacts->getContacts( array( 'perPage' => 20, 'sortByField' => 'ID', 'sortOrder' => 'DESC' ) ) );
    }
    
    /**
     * Fetch contact
     * 
     * @param WP_REST_Request $request WP_REST_Request object.
     * 
     * request params  Optional. Arguments for querying forms.
     * @type string  $fetch_field    Values: id, email.
     * @type string  $fetch_value    Value to fetch.
     * 
     * @return WP_REST_Response|WP_Error    WP_REST_Response contact details | WP_Error object with error details..
     */
    public function fetch_contact( $request ){
        if( !isset( $request[ 'fetch_field' ] ) || !isset( $request[ 'fetch_value' ]) ){
            return new WP_Error( 'rest_bad_request', 'Invalid arguments', array( 'status' => 404 ) );
        }
        $fetch_field = $request[ 'fetch_field' ];
        $fetch_value = $request[ 'fetch_value' ];
        if( 'email' === $fetch_field ){
            $contact_id = zeroBS_getCustomerIDWithEmail( $fetch_value );
            if( $contact_id ){
                return rest_ensure_response( $this->get_contact_details( $contact_id ) );
            }
            return new WP_Error( 'rest_bad_request', 'Contact does not exist!', array( 'status' => 404 ) );
        }
        elseif( 'id' === $fetch_field ){
            if( $this->is_valid_contact( $fetch_value ) ){
                return rest_ensure_response( $this->get_contact_details( $fetch_value ) );
            }
            return new WP_Error( 'rest_bad_request', 'Contact does not exist!', array( 'status' => 404 ) );
        }
        
    }
    
    /**
     * Add or update contact
     * 
     * @param WP_REST_Request $request WP_REST_Request object.
     * 
     * @return WP_REST_Response|WP_Error    WP_REST_Response contact details | WP_Error object with error details.
     */
    public function add_or_update_contact( $request ){
        $contact_fields_array = $request->get_params();
        global $zbs;
        $contact_id = $zbs->DAL->contacts->addUpdateContact( $contact_fields_array );
        if( $contact_id ){
            return rest_ensure_response( $this->get_contact_details( $contact_id ) );
        }
        else{
            return new WP_Error( 'rest_bad_request', 'Contact not saved', array( 'status' => 404 ) );
        }
    }
    
    /**
     * List contact statuses
     * 
     * @param WP_REST_Request $request WP_REST_Request object.
     * 
     * @return WP_REST_Response|WP_Error    WP_REST_Response array of contact statuses  | WP_Error object with error details.
     */
    public function list_contact_statuses( $request ){
        return rest_ensure_response( zeroBSCRM_getCustomerStatuses( true ) );
    }
    
    /**
     * Fetch quote
     *
     * @param WP_REST_Request $request WP_REST_Request object.
     *
     * request params  Optional. Arguments for querying forms.
     * @type string  $fetch_field    Values: id.
     * @type string  $fetch_value    Value to fetch
     *
     * @return WP_REST_Response|WP_Error    WP_REST_Response quote details | WP_Error object with error details..
     */
    public function fetch_quote( $request ){
        if( !isset( $request[ 'fetch_field' ] ) || !isset( $request[ 'fetch_value' ]) ){
            return new WP_Error( 'rest_bad_request', 'Invalid arguments', array( 'status' => 404 ) );
        }
        $fetch_field = $request[ 'fetch_field' ];
        $fetch_value = $request[ 'fetch_value' ];
        if( 'id' === $fetch_field ){
            if( $this->is_valid_quote( $fetch_value ) ){
                return rest_ensure_response( $this->get_quote_details( $fetch_value ) );
            }
            return new WP_Error( 'rest_bad_request', 'Quote does not exist!', array( 'status' => 404 ) );
        }
        
    }
    
    /**
     * Check whether the Contact ID is valid or not.
     * 
     * @param int $contact_id
     * 
     * @return boolean  true if the contact exists | false for others.
     */
    private function is_valid_contact ( $contact_id ){
        if( isset( $contact_id ) ){
            if( zeroBS_getCustomer( $contact_id ) ){
                return true;
            }
            return false;
        }
        else{
            return false;
        }
    }
    
    /**
     * Get contact details
     * 
     * @param int $contact_id
     * 
     * @return array contact details
     */
    private function get_contact_details( $contact_id ){
        $contact_details = zeroBS_getCustomer( $contact_id, true, true, true );
        $contact_details['tags'] = zeroBSCRM_getCustomerTagsByID( $contact_id );
        $contact_details['aliases'] = zeroBS_getCustomerAliases( $contact_id ) ? zeroBS_getCustomerAliases( $contact_id ) : array();
        $contact_details['company_id'] = zeroBS_getCustomerCompanyID( $contact_id ) ? zeroBS_getCustomerCompanyID( $contact_id ) : '';
        $contact_details['files'] = zeroBSCRM_getCustomerFiles( $contact_id ) ? zeroBSCRM_getCustomerFiles( $contact_id ): array();
        return $contact_details;
    }
    
    /**
     * Get company details
     *
     * @param int $company_id
     *
     * @return array company details
     */
    private function get_company_details( $company_id ){
        $company_details = zeroBS_getCompany( $company_id, true );
        $company_details['tags'] = zeroBSCRM_getCompanyTagsByID( $company_id );
        $company_details['files'] = zeroBSCRM_files_getFiles( 'company', $company_id ) ? zeroBSCRM_files_getFiles( 'company', $company_id ) : array();
        return $company_details;
    }
    
    /**
     * Check whether the Quote ID is valid or not.
     *
     * @param int $quote_id
     *
     * @return boolean  true if the quote exists | false for others.
     */
    private function is_valid_quote( $quote_id ){
        if( isset( $quote_id ) ){
            if( zeroBS_getQuote( $quote_id ) ){
                return true;
            }
            return false;
        }
        else{
            return false;
        }
    }
    
    /**
     * Get quote details
     *
     * @param int $quote_id
     *
     * @return array quote details
     */
    private function get_quote_details( $quote_id ){
        $quote_details = zeroBS_getQuote( $quote_id, true );
        $quote_details['files'] = zeroBSCRM_files_getFiles( 'quotes', $quote_id ) ? zeroBSCRM_files_getFiles( 'quotes', $quote_id ) : array();
        return $quote_details;
    }
    
    /**
     * Get invoice details
     *
     * @param int $invoice_id
     *
     * @return array invoice details
     */
    private function get_invoice_details( $invoice_id ){
        $invoice = New zbsDAL_invoices();
        $invoice_details = $invoice->getInvoice( $invoice_id );
        $invoice_details['files'] = zeroBSCRM_files_getFiles( 'invoice', $invoice_id ) ? zeroBSCRM_files_getFiles( 'invoice', $invoice_id ) : array();
        return $invoice_details;
    }
    
    /**
     * Get transaction details
     *
     * @param int $transaction_id
     *
     * @return array transaction details
     */
    private function get_transaction_details( $transaction_id ){
        $transaction_details = zeroBS_getTransaction( $transaction_id, true );
        $transaction_details['tags'] = zeroBSCRM_getTransactionTagsByID( $transaction_id );
        $transaction_details['files'] = zeroBSCRM_files_getFiles( 'transaction', $transaction_id ) ? zeroBSCRM_files_getFiles( 'transaction', $transaction_id ) : array();
        return $transaction_details;
    }
    
    /**
     * Creates a webhook entry
     * The events available in $supported_events array only accepted
     *
     * @param WP_REST_Request   $request  WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response array with Webhook ID | WP_Error object with error details.
     */
    public function create_webhook( $request ){
        $entry = json_decode( $request->get_body() );
        if( ( isset( $entry->name ) ) && ( isset( $entry->url ) ) && ( isset( $entry->event ) ) && ( in_array( $entry->event, self::$supported_events ) ) && ( preg_match( "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $entry->url ) ) ){
            $args = array(
                'name' => $entry->name,
                'url' => $entry->url,
                'event' => $entry->event
            );
            $post_name = "Jetpack CRM ";
            $post_id = $this->create_webhook_post( $post_name, $args );
            if( is_wp_error( $post_id ) ){
                $errors = $post_id->get_error_messages();
                return new WP_Error( 'rest_bad_request', $errors, array( 'status' => 400 ) );
            }
            return rest_ensure_response(
                array(
                    'webhook_id' => $post_id
                ) );
        }
        else{
            return new WP_Error( 'rest_bad_request', 'Data validation failed', array( 'status' => 400 ) );
        }
    }
    
    /**
     * Deletes a webhook entry
     * Webhook ID returned from webhook create event should be used. Use minimum user scope.
     *
     * @param WP_REST_Request   $request    WP_REST_Request object.
     * @return WP_REST_Response|WP_Error    WP_REST_Response array with success message | WP_Error object with error details.
     */
    public function delete_webhook( $request ){
        $webhook_id = $request['webhook_id'];
        if( is_numeric( $webhook_id ) ){
            $webhook_post = $this->get_webhook_post( $webhook_id );
            if( !empty( $webhook_post[0]->ID ) ){
                $delete_webhook = $this->delete_webhook_post( $webhook_id );
                if( is_wp_error( $delete_webhook ) ){
                    $errors = $delete_webhook->get_error_messages();
                    return new WP_Error( 'rest_bad_request', $errors, array( 'status' => 400 ) );
                }
                else{
                    return rest_ensure_response( array( 'message' => 'Success' ) );
                }
            }
            else{
                return new WP_Error( 'rest_bad_request', 'Invalid webhook ID', array( 'status' => 400 ) );
            }
        }
        else{
            return new WP_Error( 'rest_bad_request', 'Invalid webhook ID', array( 'status' => 400 ) );
        }
    }
    
    /**
     * Fires once the contact is saved
     * 
     * @param Automattic\Jetpack\CRM\Entities\Contact $contact
     */
    public function payload_contact_created( $contact ){
        $args = array(
            'event' => 'contact_created'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'contact_created',
                'data' => $this->get_contact_details( $contact->id )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires once the contact is updated
     *
     * @param Automattic\Jetpack\CRM\Entities\Contact $contact
     * @param Automattic\Jetpack\CRM\Entities\Contact $previous_contact
     */
    public function payload_contact_updated( $contact, $previous_contact ){
        $contact_array = json_decode(json_encode($contact), true);
        $previous_contact_array = json_decode(json_encode($previous_contact), true);
        if( $contact_array != $previous_contact_array ){
            $args = array(
                'event' => 'contact_updated'
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'contact_updated',
                    'data' => $this->get_contact_details( $contact->id )
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once the contact status is updated
     *
     * @param Automattic\Jetpack\CRM\Entities\Contact $contact
     * @param Automattic\Jetpack\CRM\Entities\Contact $previous_contact
     */
    public function payload_contact_status_updated( $contact, $previous_contact ){
        $args = array(
            'event' => 'contact_status_updated'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'contact_status_updated',
                'data' => $this->get_contact_details( $contact->id )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires once company is created
     * 
     * @param int $company_id
     */
    public function payload_company_created( $company_id ){
        $args = array(
            'event' => 'company_created'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'company_created',
                'data' => $this->get_company_details( $company_id )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires once quote is created
     *
     * @param int $quote_id
     */
    public function payload_quote_created( $quote_id ){
        $args = array(
            'event' => 'quote_created'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'quote_created',
                'data' => $this->get_quote_details( $quote_id )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires once quote is accepted
     *
     * @param int $quote_id
     */
    public function payload_quote_accepted( $quote_id ){
        $args = array(
            'event' => 'quote_accepted'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'quote_accepted',
                'data' => $this->get_quote_details( $quote_id )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires once invoice is created
     *
     * @param Automattic\Jetpack\CRM\Entities\Invoice $invoice
     */
    public function payload_invoice_created( $invoice ){
        $args = array(
            'event' => 'invoice_created'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'invoice_created',
                'data' => $this->get_invoice_details( $invoice->id )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /**
     * Fires once invoice is updated
     *
     * @param Automattic\Jetpack\CRM\Entities\Invoice $invoice
     * @param Automattic\Jetpack\CRM\Entities\Invoice $previous_invoice
     */
    public function payload_invoice_updated( $invoice, $previous_invoice ){
        $invoice_array = json_decode(json_encode($invoice), true);
        $previous_invoice_array = json_decode(json_encode($previous_invoice), true);
        if( $invoice_array != $previous_invoice_array ){
            $args = array(
                'event' => 'invoice_updated'
            );
            $webhooks = $this->get_webhook_posts( $args );
            if( !empty( $webhooks ) ){
                $event_data = array(
                    'event' => 'invoice_updated',
                    'data' => $this->get_invoice_details( $invoice->id )
                );
                foreach( $webhooks as $webhook ){
                    $url = $webhook->url;
                    zoho_flow_execute_webhook( $url, $event_data, array() );
                }
            }
        }
    }
    
    /**
     * Fires once transaction is created
     *
     * @param Automattic\Jetpack\CRM\Entities\Transaction $transaction
     */
    public function payload_transaction_created( $transaction ){
        $args = array(
            'event' => 'transaction_created'
        );
        $webhooks = $this->get_webhook_posts( $args );
        if( !empty( $webhooks ) ){
            $event_data = array(
                'event' => 'transaction_created',
                'data' => $this->get_transaction_details( $transaction->id )
            );
            foreach( $webhooks as $webhook ){
                $url = $webhook->url;
                zoho_flow_execute_webhook( $url, $event_data, array() );
            }
        }
    }
    
    /*
     * Get user and system info.
     * Default API
     *
     * @return WP_REST_Response|WP_Error  WP_REST_Response system and logged in user details | WP_Error object with error details.
     */
    public function get_system_info(){
        $system_info = parent::get_system_info();
        if( ! function_exists( 'get_plugin_data' ) ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $plugin_dir = ABSPATH . 'wp-content/plugins/zero-bs-crm/ZeroBSCRM.php';
        if(file_exists( $plugin_dir ) ){
            $plugin_data = get_plugin_data( $plugin_dir );
            $system_info['jetpack_crm'] = $plugin_data['Version'];
        }
        return rest_ensure_response( $system_info );
    }
    
}