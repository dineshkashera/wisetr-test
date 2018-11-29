<?php
/*
  Plugin Name: Wisetr test
  Description: wisetr test answer questions  
  Author: dineshkashera
  Author URI: https://stackoverflow.com/users/6410722/dineshkashera
  Plugin URI: https://stackoverflow.com/users/6410722/dineshkashera
  Text Domain: wisetr-test
  Version: 1.0.0
  Requires at least: 3.0.0
  Tested up to: 5.0.0
  WC requires at least: 3.0
  WC tested up to: 3.5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
{
    define('PLUG_DIR_URI', plugin_dir_url(__FILE__));
    define('PLUG_DIR_PATH',plugin_dir_path(__FILE__));
    define('PLUG_DOMAIN','wisetr-test');
    
    class Wisetr_Test{
        
        private static $_instance;
        
        public static function getInstance() {
            self::$_instance = new self;
            if( !self::$_instance instanceof self )
                self::$_instance = new self;
                
                return self::$_instance;
                
        }
        
        public function __construct() {
            
            //call our assign_useremail_on_guestcheckout() function on the thank you page
            add_action( 'woocommerce_thankyou',array($this,'assign_useremail_on_guestcheckout'), 10, 1 );
            //add script templates in footer
            add_action( 'wp_footer',array($this, 'add_script_custom_templates'));
            //add cdn file in admin side
            add_action('admin_head',array($this,'call_to_add_cdn'));
            //enqueue frontend script
            add_action( 'wp_enqueue_scripts',array($this, 'add_custom_script'));
            //admin enqueue scripts for exports
            add_action( 'admin_enqueue_scripts',array($this, 'add_admin_scripts'));
            
            //add shortcode for form html 
            add_shortcode( 'form-repeat-block', array($this,'form_repeat_block_shortcode'));
            //add custom sub menu in products menu
            add_action('admin_menu', array($this,'register_export_submenu_page'),99);
            
            //product csv exports
            add_action('init', array($this,'create_product_csv_download'),10);
            
            //start my session 
            add_action('init',array($this,'start_session_custom'));
            
            //end my session
            add_action('wp_logout',array($this,'end_session_custom'));
            add_action('wp_login',array($this,'end_session_custom'));
            
            //add custom fields on checkout under billing section
            add_filter(  'woocommerce_billing_fields', array($this,'custom_billing_fields_for_google_place_api'), 20, 1);
            //add custom fields after cart table
            add_action( 'woocommerce_cart_collaterals', array($this,'action_woocommerce_after_cart_contents'), 10, 0 ); //woocommerce_after_cart_contents
            //add custom value in cart 
            add_action('init',array($this,'add_custom_fee_in_cart'),99,0);
            
            //calculates fee on cart updates
            add_action( 'woocommerce_cart_calculate_fees',array($this,'custom_fee_based_on_cart_total_wisetr'), 10, 1 );
            
        }
        
        
        /**
         * Assign user email on guest checkout
         * @param $order_id
         */
        
        public function assign_useremail_on_guestcheckout($order_id){
            
            // get all the order data
            $order = new WC_Order($order_id);
            
            //get the user email from the order
            $order_email = $order->billing_email;
            
            // perform guest user actions here
            if( !is_user_logged_in() ){
                
                $getuser = get_user_by( 'email', $order_email );
                $user_id = $getuser->ID;
                
                if(isset($user_id)){
                    update_post_meta($order_id, '_customer_user', $user_id);
                }
               
            }
        }
        
        
        /**
         * Add Script templates
         */
        public function add_script_custom_templates(){
            include_once PLUG_DIR_PATH.'templates/repeat-single-block.php';
        }
        
        /**
         * add select2 cdn
         */
        public function call_to_add_cdn(){?>
            <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
            <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
        <?php }
        
        /**
         * Add custom scripts for frontend
         */
        public function add_custom_script(){
            
            if ( !wp_script_is( 'google-maps', 'registered' ) ) {
                wp_register_script( 'google-maps', ( is_ssl() ? 'https' : 'http' ) . '://maps.googleapis.com/maps/api/js?libraries=places&sensor=false&key=AIzaSyCkldtSiTJ6TQ5sJsa2E_b6l5rGDfe3DMQ', array( 'jquery' ), false );
            }
            
            //enqueue google maps api if not already enqueued
            if ( !wp_script_is( 'google-maps', 'enqueued' ) ) {
                wp_enqueue_script( 'google-maps' );
            }   
            
            wp_enqueue_script( 'my-custom-js', PLUG_DIR_URI.'assets/js/wisetr-frontend.js',array('jquery','wp-util'),'1.0',true);
            wp_enqueue_style( 'my-custom-css', PLUG_DIR_URI.'assets/css/custom-style.css');
        }
        
        /**
         * Add custom scripts for admin side
         */
        public function add_admin_scripts(){
            wp_enqueue_script( 'export-product-js', PLUG_DIR_URI.'assets/js/export-product.js',array('jquery'),'1.0',true);
            wp_enqueue_style( 'my-custom-admin-css', PLUG_DIR_URI.'assets/css/admin-style.css');
            $globalData = array(
                'siteurl'  => site_url(),
                'ajaxurl' => admin_url( 'admin-ajax.php' )
            );
            
            wp_localize_script('export-product-js', 'CALL', $globalData);
        }
        
        /**
         * Add custom shortcode [form-repeat-block]
         */
        public function form_repeat_block_shortcode(){
            
            
            ob_start();
            ?>
        	<div class="template_block">
        		<form method="post" action="">
        			<div class="all_fields_block">
        				<div class="add_command"><a href="javascript:void(0)" Title="Add" id="add_elements">Add</a></div>
        				<div class="single_block">
                            <div class="inner_block">
                                <div class="fields">
                                    <select name="course_fields[]" class="course_select">
                                        <option value="">Select Course</option>
                                        <option value="MCA">MCA</option>
                                        <option value="BCA">BCA</option>
                                        <option value="BTECH">BTECT</option> 
                                    </select>
                                </div>
                                <div class="fields">
                                    <input type="text" name="stu_name[]" value="" Placeholder="Student Name" class="stu_name">
                                </div>
                            </div>
						</div>  
        			</div>
        			<div class="submit_fields">
        				<input type="submit" name="records_submit" class="records_submit">
        			</div>
        		</form>
        	</div>
        	<?php 
        	
        	if(isset($_POST['records_submit'])){
        	    $data = $_POST['course_fields'];
        	    $course = $_POST['stu_name'];
        	    foreach($data as $key => $value){
        	        echo 'Student name : '. $course[$key] .', Course : '.$value. '</br>';
        	    }
            }
            ?>
        	<?php 
            $output_string = ob_get_contents();
            ob_end_clean();
            return $output_string;
        }
        
        /**
         * Add sub menu page
         */
        public function register_export_submenu_page(){
            add_submenu_page( 'edit.php?post_type=product',
                               __( 'Wisetr Export', PLUG_DOMAIN ), 
                               __('Wisetr Export', PLUG_DOMAIN ), 
                               'manage_options', 
                               'wisetr-pro-exports',
                                array($this,'call_wisetr_exports_pro')
                            );
        }
        
        /**
         * Add settings for exports
         */
        
        public function call_wisetr_exports_pro(){
            include_once PLUG_DIR_PATH.'includes/wisetr-exports-pro.php';
        }
        
        /*
         * Get all meta key for post type product and product_variation
         */
        public static function get_all_post_meta_key(){
         
            $meta_fields    =   array();
            $the_query      =   new WP_Query( 
                                    array(
                                        'post_type'     => array('product'),
                                        'post_status'   => array('publish'),
                                    ) 
                                );
            $exclude_fields  = array('_wc_review_count','_wc_rating_count','_wc_average_rating','_edit_last','_edit_lock','_thumbnail_id','_sale_price_dates_from','_sale_price_dates_to','_purchase_note','_default_attributes','_virtual','_downloadable');
            
            if ( $the_query->have_posts() ) {
                while ( $the_query->have_posts() ) {
                    $the_query->the_post();
                    
                    $meta_array = get_post_meta( get_the_ID() );
                    foreach( $meta_array as $key => $meta) {
                        if(!in_array($key,$exclude_fields))
                            $meta_fields[] = $key;
                    }
                }
                
                $meta_fields[] = 'ID';
                $meta_fields[] = 'title';
                
                wp_reset_postdata();
                return array_unique($meta_fields);
            }
        }
        
        public function create_product_csv_download(){
           
            if(isset($_POST['wisetr_export']) && !empty($_POST['woo_product_export'])){
                
                $filename = "product_exports.csv";
                $meta_key = $_POST['woo_product_export'];
                
                // Get products
                $args = array(
                            'status' => 'publish',
                            'type'   => array( 'simple','variable'),
                        );
                
                $products               =   wc_get_products( $args );
                $csv_all_list           =   array();
                
                foreach($products as $key => $single_product){
                    
                    $product_id             =   $single_product->id;
                    $product_type           =   '';
                    $product_meta_values    =   array();
                    
                    //simple product loop
                    if($single_product->is_type('simple')){
                       
                        foreach($meta_key as $index => $index_key ){
                            
                            if($index_key == 'ID'){
                                $product_meta_values[] = (int)$product_id;
                            }
                            elseif($index_key == 'title'){
                                $product_meta_values[] = get_the_title($product_id);
                            }else{
                                $product_meta_values[] = get_post_meta($product_id,$index_key,true);
                            }
                        }
                        $csv_all_list[] = $product_meta_values;
                    }
                    
                    //variable product loop
                    if($single_product->is_type('variable')){
                        
                        $children_product       =   $single_product->get_children();
                        
                        foreach($children_product as $indx => $child_id){
                            $product_meta_values    =   array();
                            foreach($meta_key as $index => $index_key ){
                                if($index_key == 'ID'){
                                    $product_meta_values[] = (int)$child_id;
                                }
                                elseif($index_key == 'title'){
                                    $product_meta_values[] = get_the_title($product_id);
                                }else{
                                    $product_meta_values[] = get_post_meta($child_id,$index_key,true);
                                }
                            }
                            $csv_all_list[] = $product_meta_values;
                        }
                    }
                }
                
                header('Content-type: text/csv; charset=utf-8');// application/excel
                header('Content-Disposition: attachment; filename='.$filename);
                
                // do not cache the file
                header('Pragma: no-cache');
                header('Expires: 0');
                
                
                // output headers so that the file is downloaded rather than displayed
                $output = fopen('php://output', 'w');
                // output the column headings
                fputcsv($output, $meta_key);
                
                
                foreach ($csv_all_list as $fields) {
                    // loop over the rows, outputting them
                    fputcsv($output,$fields,',');
                }
                
                fclose($output); 
                exit; 
            }
        }
        
        /**
         * Start session
         */
        public function start_session_custom(){
            if(!session_id()){
                session_start();
            }
        }
        
        /**
         * End session
         */
        public function end_session_custom() {
            session_destroy();
        }
        
        /**
         * register custom fields on billing section on checkout page 
         * @param unknown $fields
         * @return unknown|string[]
         */      
        
        public function custom_billing_fields_for_google_place_api( $fields ) {
            
            $fields['billing_google_address']['label'] = 'Google Autocomplete address';
            $fields['billing_google_address']['placeholder'] = 'Google Autocomplete address';
            $fields['billing_google_address']['required'] = true;
            $fields['billing_google_address']['class'] = array('g_autocomlete');
            $fields['billing_google_address']['priority'] = 55;
            
            return $fields;
        }
        
        /**
         * Add custom fields on cart page
         */
        public function action_woocommerce_after_cart_contents(){
            $checked =  $_SESSION['checked'];
        ?>
        	<form method="post" action="<?php echo site_url('cart');?>">
				<div class="custom_fields">
            		<div class="fields">
            			<input type="checkbox" name="addition_cart_cost" value="1"  <?php if($checked == 'yes'){echo 'checked="checked"';}?>>Add additional cost to cart
            				<input type="submit" name="form_submit" value="<?php if($checked == 'yes'){echo 'Remove';}else{echo 'Add';}?>">
            			</div>
            	</div>
             </form>
        <?php 
        
        }
        
        /**
         * Add custom fee in cart
         */
        
        public function add_custom_fee_in_cart(){
           
            if ( is_admin() && ! defined( 'DOING_AJAX' ) )
                return;
               
                $checked = '';
                if(isset($_POST['form_submit'])){
                    if($_POST['addition_cart_cost'] == 1 || $_SESSION['checked'] == 'yes'){
                        $_SESSION['checked'] = 'yes';
                        $checked = $_SESSION['checked'];
                    }
                    
                    if(!isset($_POST['addition_cart_cost'])){
                        $_SESSION['checked'] = 'no';
                        $checked = $_SESSION['checked'];
                    }
                }
                
                if($checked == 'yes'){
                    add_action( 'woocommerce_cart_calculate_fees',array($this,'custom_fee_based_on_cart_total_wisetr'), 10, 1 );
                }
        }
        
        /**
         *Add custom fee on cart total 
         */
        public function custom_fee_based_on_cart_total_wisetr($cart_object){
            
            if ( !WC()->cart->is_empty() ){ 
                global $woocommerce;
                
                if($_SESSION['checked'] == 'yes'){
                    $subtotal   =   $woocommerce->cart->subtotal;
                    $excost     =   (($subtotal * 2)/100) + 0.3;
                    $woocommerce->cart->add_fee( 'Additional Fee', $excost, $taxable = false,'');
                }
            }
            
        }
    }//end of class Wisetr_Test
    
    
    new Wisetr_Test();
    
    
}else{
    
  function send_plugin_error_notice(){?>
       <div class="error notice is-dismissible">
       		<p><?php _e( 'Woocommerce is not activated, please activate woocommerce first to install and use wisetr test', PLUG_DOMAIN ); ?></p>
       </div>
   <?php
  }
  add_action( 'admin_init', 'plugin_deactivate_call' );
  
  function plugin_deactivate_call(){
      deactivate_plugins( plugin_basename(__FILE__ ) );
      add_action( 'admin_notices', 'send_plugin_error_notice' );
  }
}