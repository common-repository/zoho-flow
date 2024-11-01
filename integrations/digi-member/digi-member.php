<?php
class Zoho_Flow_Digi_Member extends Zoho_Flow_Service
{
    public function get_all_products() {
        return rest_ensure_response( digimember_listProducts() );
    }
    
    public function get_products_of_user( $request ){
        $user_id = $request['user_id'];
        return rest_ensure_response(digimember_listAccessableProducts($user_id));
    }
    
    public function create_orders( $request ){
        
        $product_id = $request['product_id'];
        $email = $request['email'];
        $first_name=$request['first_name'];
        $last_name= $request['last_name'];
        $order_id=$request['order_id'];
        
        digimember_createOrder($product_id, $email, $first_name, $last_name, $order_id, false);
        $order = digimember_getOrder($order_id);
        
        $args = array(
            'post_type'=> 'orders'
        );
        
        $webhooks = $this->get_webhook_posts($args);
        foreach ( $webhooks as $webhook ) {
            $url = $webhook->url;
            zoho_flow_execute_webhook($url, $order, array());
        }
        return rest_ensure_response($order);
    }

    public function get_user_orders($request){
        
        $user_id = $request['user_id'];        
        $orders = digimember_listOrders($user_id);
        
        return rest_ensure_response($orders);
    }

    public function get_webhook_for_order( $request ){
        
        $data = array();
        $args = array(
            'post_type' => $request['post_type']
        );

        $webhooks = $this->get_webhook_posts($args);
        foreach ( $webhooks as $webhook ) {
            $webhook = array(
                'plugin_service' => $this->get_service_name(),
                'id' => $webhook->ID,
                'post_type' => $request['post_type'],
                'url' => $webhook->url
            );
            array_push($data, $webhook);
        }
        
        return rest_ensure_response( $data );
    }
    
    public function create_webhook_for_order( $request ) {
        $post_title = $request['post_type'];
        $url = esc_url_raw($request['url']);
        $post_id = $this->create_webhook_post($post_title, array(
            'post_type' => $request['post_type'],
            'url' => $url
        ));
        
        return rest_ensure_response( array(
            'plugin_service' => $this->get_service_name(),
            'id' => $post_id,
            'post_type' =>$request['post_type'],
            'url' => $url
        ) );
    }
    
    public function delete_webhook($request){
        $webhook_id = $request['webhook_id'];
        if(!ctype_digit($webhook_id)){
            return new WP_Error( 'rest_bad_request', esc_html__( 'The webhook ID is invalid.', 'zoho-flow' ), array( 'status' => 400 ) );
        }
        $result = $this->delete_webhook_post($webhook_id);
        if(is_wp_error($result)){
            return $result;
        }
        return rest_ensure_response(array(
            'plugin_service' => $this->get_service_name(),
            'id' => $result->ID
        ));
        return rest_ensure_response($result);
    }
    
    public function digi_purchase($user_id, $product_id, $order_id, $reason ) {
        if($reason === 'order_paid'){
            $data = array();
            
            $data = digimember_getOrder($order_id);
            
            $args = array(
                'post_type'=> 'orders'
            );
            
            $webhooks = $this->get_webhook_posts($args);
            foreach ( $webhooks as $webhook ) {
                $url = $webhook->url;
                zoho_flow_execute_webhook($url, $data, array());
            }
            return rest_ensure_response($data);
        }
    }
}
