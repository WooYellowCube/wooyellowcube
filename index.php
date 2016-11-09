<?php
/**
* Plugin Name: WooYellowCube
* Plugin URI: http://www.wooyellowcube.com
* Description: WooCommerce synchronization with YellowCube
* Version: 2.3V3
*/

// YellowCube API namespaces
use YellowCube\ART\Article;
use YellowCube\ART\ChangeFlag;
use YellowCube\ART\UnitsOfMeasure\ISO;
use YellowCube\ART\UnitsOfMeasure\EANType;
use YellowCube\WAB\AdditionalService\AdditionalShippingServices;
use YellowCube\WAB\AdditionalService\BasicShippingServices;
use YellowCube\WAB\AdditionalService\CODAccountNo;
use YellowCube\WAB\AdditionalService\CODAmount;
use YellowCube\WAB\AdditionalService\CODRefNo;
use YellowCube\WAB\AdditionalService\DeliveryDate;
use YellowCube\WAB\AdditionalService\DeliveryInstructions;
use YellowCube\WAB\AdditionalService\DeliveryLocation;
use YellowCube\WAB\AdditionalService\DeliveryPeriodeCode;
use YellowCube\WAB\AdditionalService\DeliveryTimeFrom;
use YellowCube\WAB\AdditionalService\DeliveryTimeJIT;
use YellowCube\WAB\AdditionalService\DeliveryTimeTo;
use YellowCube\WAB\AdditionalService\FloorNo;
use YellowCube\WAB\AdditionalService\FrightShippingFlag;
use YellowCube\WAB\AdditionalService\NotificationServiceCode;
use YellowCube\WAB\AdditionalService\NotificationType;
use YellowCube\WAB\Order;
use YellowCube\WAB\OrderHeader;
use YellowCube\WAB\Partner;
use YellowCube\WAB\Doc;
use YellowCube\Config;
use YellowCube\WAB\Position;

class WooYellowCube
{

  public $yellowcube;
  public $defaultLanguage = 'en';
  public $defaultWSDL = 'https://service-test.swisspost.ch/apache/yellowcube-test/?wsdl';

  public function __construct() {

    require_once('vendor/autoload.php');

    $this->actions();
    $this->columns();

    if($this->areSettingsReady()){
      $this->crons_responses();
      $this->crons_daily();
      $this->crons_hourly();
    }

    $this->languages();

  }

  public function areSettingsReady() {

    if(!get_option('wooyellowcube_setter')) {
      return false;
    }

    if(!get_option('wooyellowcube_operatingMode')) {
      return false;
    }

    return true;
  }

  public function languages() {
    add_action( 'plugins_loaded', array(&$this, 'languages_textdomain') );
  }

  public function yellowcube(){

    switch ((int)get_option('wooyellowcube_yellowcubeSOAPUrl')) {
      case 1:
        $this->defaultWSDL = 'https://service-test.swisspost.ch/apache/yellowcube-test/?wsdl';
        break;
      case 2:
        $this->defaultWSDL = 'https://service-test.swisspost.ch/apache/yellowcube-int/?wsdl';
        break;
      case 3:
        $this->defaultWSDL = 'https://service.swisspost.ch/apache/yellowcube/?wsdl';
        break;
    }

    // YellowCube SOAP configuration
    $soap_config = new YellowCube\Config(get_option('wooyellowcube_setter'), $this->defaultWSDL, null, get_option('wooyellowcube_operatingMode'));

    // YellowCube SOAP signature
    if(get_option('wooyellowcube_authentification')) $soap_config->setCertificateFilePath(__DIR__ .'/'.get_option('wooyellowcube_authentificationFile'));

    // YellowCube API instanciation
    try {
      $this->yellowcube = new YellowCube\Service($soap_config);

      return true;
    } catch(Exception $e){

      // log
      //$this->log_create(0, 'INIT-ERROR', null, null, __('SOAP WSDL not reachable', 'wooyellowcube'));

      return false;
    }

  }

  function languages_textdomain() {
    load_plugin_textdomain('wooyellowcube', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
  }

  public function retrieveWAR(){
    global $wpdb;

    if($this->yellowcube()){

      $days = 10 * 60 * 60 * 24; // 10 days in ms

      // Get all orders that don't have status to 1 and dated more than 10 days
      $orders = $wpdb->get_results('SELECT * FROM wooyellowcube_orders WHERE status != 2 AND created_at > '.(time() - $days));

      if(count($orders) > 0){

        $temp = array();

        // Loop each orders
        foreach($orders as $order){

          $order_id = $order->id_order;
          $order_object = new WC_Order((int)$order_id);
          $order_number = $order_object->get_order_number();
          $order_final = (trim($order_number) == '') ? $order_id : $order_number;

          $replies = $this->yellowcube->getYCCustomerOrderTestReply($order_object->get_order_number());

          foreach($replies as $reply){

            $header = $reply->getCustomerOrderHeader();
            $track = $header->getPostalShipmentNo();

            $wpdb->update(
              'wooyellowcube_orders',
              array(
                'status' => 2,
                'yc_shipping' => $track
              ),
              array(
                'id_order' => $order_id
              )
            );

            $this->log_create(1, 'WAR-SHIPMENT DELIVERED', $order_final, $order_final, 'Track & Trace received for order '.$order_id.' : '.$track);
            $order_object->update_status('completed', __('Your order has been shipped', 'wooyellowcube'), false);

          }
        }
      }
    }
  }

  /** Actions */
  private function actions(){

    // Add all the meta boxes in backend
    add_action('add_meta_boxes', array(&$this, 'meta_boxes'));

    // Ajax head
    add_action('admin_head', array(&$this, 'ajax_head'));

    // Execute ajax actions
    $this->ajax();

    // Add administration scripts
    add_action('admin_enqueue_scripts', array(&$this, 'scripts'));

    // Add administration styles
    add_action('admin_enqueue_scripts', array(&$this, 'styles'));

    // Add menus management
    add_action('admin_menu', array(&$this, 'menus'));

    // A new order is completed
    add_action('woocommerce_order_status_processing', array(&$this, 'order'), 10, 1);

    // Add the track & trace in order details
    add_action('woocommerce_order_details_after_order_table', array(&$this, 'meta_tracktrace'));

    add_action( 'woocommerce_email_before_order_table', array(&$this, 'email_completed'), 20 );

  }

  public function email_completed($order, $sent_to_admin) {
    global $wpdb;

    $order_id = $order->id;

    $getOrderFromYellowCube = $wpdb->get_row('SELECT * FROM wooyellowcube_orders WHERE id_order='.$order_id);

    if($getOrderFromYellowCube->yc_shipping){
      echo '<p>Track & Trace : http://www.post.ch/swisspost-tracking?p_language=en&formattedParcelCodes='.$getOrderFromYellowCube->yc_shipping.'</p>';
    }

  }

  /** Scripts */
  public function scripts(){
    // Add WooYellowCube JS file
    wp_enqueue_script('wooyellowcube-js', plugin_dir_url(__FILE__).'assets/js/wooyellowcube.js');
  }

  /** Styles */
  public function styles(){
    // Add WooYellowCube CSS file
    wp_register_style('wooyellowcube-css', plugin_dir_url(__FILE__).'assets/css/wooyellowcube.css', false, '1.0.0');
    wp_enqueue_style('wooyellowcube-css');
    wp_register_style('wooyellowcube-datatable', 'https://cdn.datatables.net/u/dt/dt-1.10.12/datatables.min.css', false, null);
    wp_enqueue_style('wooyellowcube-datatable');
  }

  /** Meta boxes management */
  public function meta_boxes(){
    // Order meta box - Product meta box
    add_meta_box('wooyellowcube-order', 'WooYellowCube', array(&$this, 'meta_boxes_order'), 'shop_order');
    add_meta_box('wooyellowcube-product', 'WooYellowCube', array($this, 'meta_boxes_product'), 'product');
  }

  /** Metabox : Order */
  public function meta_boxes_order($post){
		global $wpdb;
    require_once('views/metabox-order.php');
  }

  /** Metabox : Product */
  public function meta_boxes_product($post){
    global $wpdb;
    require_once('views/metabox-product.php');
  }

  /** Metabox : Track & Trace */
  public function meta_tracktrace($order){
    global $wpdb;

    // Get shipping postal no
    $shipping = $wpdb->get_row('SELECT * FROM wooyellowcube_orders WHERE id_order=\''.$order->id.'\'');

    // If we have it, display the track & trace
    if($shipping && trim($shipping->yc_shipping) != ''){
      echo '<p><strong>'.__('Order track & trace', 'wooyellowcube').'</strong> : <a href="http://www.post.ch/swisspost-tracking?p_language=en&formattedParcelCodes='.$shipping->yc_shipping.'" target="_blank">'.$shipping->yc_shipping.'</a></p>';
    }
  }

  /** Menu pages */
  public function menus(){
    add_menu_page('WooYellowCube', 'WooYellowCube', 'manage_options', 'wooyellowcube', array(&$this, 'menu_settings'), plugins_url('/assets/images/icon.png', __FILE__)); // Settings
    add_submenu_page('wooyellowcube', __('Shipping', 'wooyellowcube'), __('Shipping', 'wooyellowcube'), 'manage_options', 'wooyellowcube-shipping', array(&$this, 'menu_shipping')); // Stock
    add_submenu_page('wooyellowcube', __('Activities logs', 'wooyellowcube'), __('Activities logs', 'wooyellowcube'), 'manage_options', 'wooyellowcube-logs', array(&$this, 'menu_logs')); // Activities
    add_submenu_page('wooyellowcube', __('Stock', 'wooyellowcube'), __('Stock', 'wooyellowcube'), 'manage_options', 'wooyellowcube-stock', array(&$this, 'menu_stock')); // Stock
    add_options_page( __('Stock details', 'wooyellowcube'), __('Stock details', 'wooyellowcube'), 'manage_options', 'wooyellowcube-stock-view', array(&$this, 'menu_stock_view') );
    add_submenu_page('wooyellowcube', __('Need help ?', 'wooyellowcube'), __('Need help ?', 'wooyellowcube'), 'manage_options', 'wooyellowcube-help', array(&$this, 'menu_help')); // Help
  }

  /** Menu : Settings */
  public function menu_settings(){
    global $wpdb;

    // Update form has been submitted
    if(isset($_POST['wooyellowcube-settings'])){

      // Update all WordPress options
      update_option('wooyellowcube_setter', htmlspecialchars($_POST['setter']));
      update_option('wooyellowcube_receiver', htmlspecialchars($_POST['receiver']));
      update_option('wooyellowcube_depositorNo', htmlspecialchars($_POST['depositorNo']));
      update_option('wooyellowcube_partnerNo', htmlspecialchars($_POST['partnerNo']));
      update_option('wooyellowcube_plant', htmlspecialchars($_POST['plant']));
      update_option('wooyellowcube_operatingMode', htmlspecialchars($_POST['operatingMode']));
      update_option('wooyellowcube_authentification', htmlspecialchars($_POST['authentification']));
      update_option('wooyellowcube_authentificationFile', htmlspecialchars($_POST['authentificationFile']));
      update_option('wooyellowcube_cronDelay', htmlspecialchars($_POST['cronDelay']));
      update_option('wooyellowcube_yellowcubeSOAPUrl', htmlspecialchars($_POST['yellowcubeSOAPUrl']));
      update_option('wooyellowcube_email', htmlspecialchars($_POST['email']));
      update_option('wooyellowcube_language', htmlspecialchars($_POST['language']));
      update_option('wooyellowcube_activation', htmlspecialchars($_POST['activation']));
      update_option('wooyellowcube_lotmanagement', htmlspecialchars($_POST['lotmanagement']));
      update_option('wooyellowcube_logs', htmlspecialchars($_POST['logs_delete']));

    }

    require_once('views/menu-settings.php');
  }

  /** Menu : Shipping */
  public function menu_shipping(){
    global $wpdb;

    // Get the form
    if(isset($_POST['submit_shipping'])){
      $shipping_methods = array();
      $shipping_additionals = array();

      // Get all the shipping methods
      foreach($_POST['yellowcube_shipping_id'] as $key => $method_id){
        $shipping_methods[$method_id] = $_POST['yellowcube_shipping'][$key];
        $shipping_additionals[$method_id] = $_POST['yellowcube_additionals'][$key];
      }

      // Serialize array
      update_option('wooyellowcube_shipping', serialize($shipping_methods));
      update_option('wooyellowcube_shipping_additional', serialize($shipping_additionals));

      echo '<p class="alert alert-success">'.__('Shipping informations has been updated', 'wooyellowcube').'</p>';
    }

    require_once('views/menu-shipping.php');
  }

  /** Menu : Stock */
  public function menu_stock(){
    global $wpdb;

    $status = false;

    if(isset($_POST['bulking_execute'])){
      $option = htmlspecialchars($_POST['bulking_actions']);

      if($option == 3){
        $this->update_stock();
      }


      if(isset($_POST['products'])){
        foreach($_POST['products'] as $product_id){
          if($option == 1){
            $this->YellowCube_ART($product_id, 'update');
            $status = 1;
          }

          if($option == 2){

            // Get the stock row
            $stock_row = $wpdb->get_results('SELECT * FROM wooyellowcube_stock WHERE product_id=\'' . $product_id . '\'');
            $quantity = 0;
            $product_id = 0;

            if(count($stock_row) > 0){

              foreach($stock_row as $row){
                $quantity = $quantity + $row->yellowcube_stock;
                $product_id = $row->product_id;
              }

            }else{
              $quantity = $stock_row->yellowcube_stock;
              $product_id = $stock_row->product_id;
            }

            wc_update_product_stock($product_id, $quantity);
          }
        }

        if($option == 2){
          $this->update_stock();
          $status = 2;
        }
      }
    }

    require_once('views/menu-stock.php');
  }

  public function menu_stock_view(){
    require_once('views/menu-stock-view.php');
  }

  /** Menu : Need help */
  public function menu_help(){
    global $wpdb;
    require_once('views/menu-help.php');
  }

  /** Menu : Logs */
  public function menu_logs(){
    global $wpdb;
    require_once('views/menu-logs.php');
  }

  /** Order from WooCommerce */
  public function order($order_id){
      $this->YellowCube_WAB($order_id);
  }

  /** Columns management */
  public function columns(){
    add_filter('manage_edit-product_columns', array(&$this, 'columns_products')); // Products column
    add_filter('manage_edit-shop_order_columns', array(&$this, 'columns_orders')); // Orders column
    add_action('manage_posts_custom_column', array(&$this, 'columns_content')); // Columns posts
  }

  /** Columns : Products */
  public function columns_products($columns){
    // Add YellowCube column to Products list
    $columns['yellowcube_products'] = 'YellowCube';

    return $columns;
  }

  /** Columns : Orders */
  public function columns_orders($columns){
    // Add YellowCube column to Products list
    $columns['yellowcube_orders'] = 'YellowCube';

    return $columns;
  }

  /** Columns : Content */
  public function columns_content($column_name){
    global $post, $wpdb;

    switch($column_name){

        /** Products status */
        case 'yellowcube_products':
          $product = $this->get_product_status($post->ID);

          // YellowCube entry has been found
          if($product){

              // Display status
              switch($product->yc_response){
                  case 0: echo '<img src="'.plugin_dir_url(__FILE__).'assets/images/yc-error.png" alt="'.__('Error', 'wooyellowcube').'" /> '.__('Error', 'wooyellowcube'); break;
                  case 1: echo '<img src="'.plugin_dir_url(__FILE__).'assets/images/yc-pending.png" alt="'.__('Pending', 'wooyellowcube').'" /> '.__('Pending', 'wooyellowcube'); break;
                  case 2: echo '<img src="'.plugin_dir_url(__FILE__).'assets/images/yc-success.png" alt="'.__('Success', 'wooyellowcube').'" /> '.__('Success', 'wooyellowcube'); break;
              }

          }else{
            echo '<img src="'.plugin_dir_url(__FILE__).'assets/images/yc-unlink.png" alt="'.__('Unlink', 'wooyellowcube').'" /> '.__('Unlink', 'wooyellowcube');
          }

        break;

        /** Orders status */
        case 'yellowcube_orders':

          $order = $this->get_order_status($post->ID);

          // YellowCube entry has been found
          if($order){

              // Display status
              switch($order->yc_response){
                  case 0: echo '<img src="'.plugin_dir_url(__FILE__).'assets/images/yc-error.png" alt="'.__('Error', 'wooyellowcube').'" /> '.__('Error', 'wooyellowcube'); break;
                  case 1: echo '<img src="'.plugin_dir_url(__FILE__).'assets/images/yc-pending.png" alt="'.__('Pending', 'wooyellowcube').'" /> '.__('Pending', 'wooyellowcube'); break;
                  case 2: echo '<img src="'.plugin_dir_url(__FILE__).'assets/images/yc-success.png" alt="'.__('Success', 'wooyellowcube').'" /> '.__('Success', 'wooyellowcube'); break;
              }

          }else{
            echo '<img src="'.plugin_dir_url(__FILE__).'assets/images/yc-unlink.png" alt="'.__('Unlink', 'wooyellowcube').'" /> '.__('Unlink', 'wooyellowcube');
          }

        break;

    }

  }

  /** Ajax calls */
  public function ajax(){
    add_action('wp_ajax_product_send', array(&$this, 'ajax_product_send')); // Product - send
    add_action('wp_ajax_product_update', array(&$this, 'ajax_product_update')); // Product - update
    add_action('wp_ajax_product_remove', array(&$this, 'ajax_product_desactivate')); // Product - remove
    add_action('wp_ajax_order_send', array(&$this, 'ajax_order_send')); // Order - send
    add_action('wp_ajax_order_again', array(&$this, 'ajax_order_again')); // Order - again
  }

  /** Add ajax URL to header */
  public function ajax_head(){
    echo '<script type="text/javascript">var wooyellowcube_ajax = "'.admin_url('admin-ajax.php').'"</script>';
  }

  /** Ajax - Product - Send */
  public function ajax_product_send(){
    $post_id = htmlspecialchars($_POST['post_id']); // Get post ID
    $lotmanagement = htmlspecialchars($_POST['lotmanagement']); // Get lot management

    $this->YellowCube_ART($post_id, 'insert', null, $lotmanagement); // Insert the product in YellowCube

    exit();
  }

  /** Ajax - Product - Update */
  public function ajax_product_update(){
    $post_id = htmlspecialchars($_POST['post_id']); // Get post ID
    $lotmanagement = htmlspecialchars($_POST['lotmanagement']); // Get lot management
    $this->YellowCube_ART($post_id, 'update', null, $lotmanagement); // Update the product in YellowCube
    exit();
  }


  /** Ajax - Product - Remove */
  public function ajax_product_desactivate(){

    // Get post ID
    $post_id = htmlspecialchars($_POST['post_id']);

    // Delete the product in YellowCube
    $this->YellowCube_ART($post_id, 'desactivate');

    exit();
  }

  /** Ajax - Product - Refresh */
  public function ajax_product_refresh(){

    // Get post ID
    $post_id = htmlspecialchars($_POST['post_id']);

    echo 'ajax_product_refresh';

    exit();
  }

  /** Ajax - Order - Send */
  public function ajax_order_send(){

    // Get post ID
    $post_id = htmlspecialchars($_POST['post_id']);

    // Insert the order in YellowCube
    $this->YellowCube_WAB($post_id);

    exit();
  }

  /** Ajax - Order - Again */
  public function ajax_order_again(){

    // Get post ID
    $post_id = htmlspecialchars($_POST['post_id']);

    echo 'ajax_order_again';

    exit();
  }

  /** Ajax - Order - Refresh */
  public function ajax_order_refresh(){

    // Get post ID
    $post_id = htmlspecialchars($_POST['post_id']);

    echo 'ajax_order_refresh';

    exit();
  }

  /** Notice : Update the plugin */
  public function notice_update(){

    // require view
    require_once('views/notice-update.php');

  }

  /**
  * ART request to YellowCube
  *
  * Note : $variation_id is optional
  *
  */
  public function YellowCube_ART($product_id, $type, $variation_id = false, $lotmanagement = 0){
    global $wpdb;

    // YellowCube connexion
    if($this->yellowcube()){

      // Product object
      $wc_product = new WC_Product((int)$product_id);
      if(!$wc_product) return false;

      if(wp_get_post_parent_id($product_id) == 0){
        $wc_product_parent = new WC_Product((int)$product_id);
      }else{
        $wc_product_parent = new WC_Product((int)wp_get_post_parent_id($product_id));
      }

      $attributes = str_replace(' ', '', $wc_product_parent->get_attribute('EAN'));

      $product_ean = '';

      if(strpos($attributes, '=') !== false){
        $attributes_first_level = explode(',', $attributes);
        $temp_attributes = array();

        foreach($attributes_first_level as $level){
          $level = explode('=', $level);
          $product_identification = $level[0];
          $product_ean = $level[1];
          $temp_attributes[$product_identification] = $product_ean;
        }

        $product_ean = $temp_attributes[$product_id];

      }else{
        $product_ean = $attributes;
      }

      // YellowCube\Article
      $article = new Article;

      // Validate type
      switch($type){
        case 'insert': $type = 'INSERT'; $article->setChangeFlag(ChangeFlag::INSERT); break;
        case 'update': $type = 'UPDATE'; $article->setChangeFlag(ChangeFlag::UPDATE); break;
        case 'desactivate': $type = 'DESACTIVATE'; $article->setChangeFlag(ChangeFlag::DEACTIVATE); break;
        default : $type = 'INSERT'; $article->setChangeFlag(ChangeFlag::INSERT); break;
      }

      //
      $article
      ->setPlantID(get_option('wooyellowcube_plant'))
      ->setDepositorNo(get_option('wooyellowcube_depositorNo'))
      ->setArticleNo($wc_product->get_sku())
      ->setBaseUOM(ISO::PCE)
      ->setNetWeight(round(wc_get_weight($wc_product->get_weight(), 'kg'), 3), ISO::KGM)
      ->setGrossWeight(round(wc_get_weight($wc_product->get_weight(), 'kg'), 3), ISO::KGM)
      ->setAlternateUnitISO(ISO::PCE)
      ->setBatchMngtReq($lotmanagement)
      ->addArticleDescription(substr($wc_product->get_title(), 0, 39), 'de')
      ->addArticleDescription(substr($wc_product->get_title(), 0, 39), 'fr');

      if(strlen($product_ean) == 8){
        $article->setEAN($product_ean, EANType::UC);
      }else{
        $article->setEAN($product_ean, EANType::HE);
      }

      $volume = 1;

      // Set Length
      if($wc_product->length){
        $article->setLength(round(wc_get_dimension($wc_product->length, 'cm'), 3), ISO::CMT);
        $volume = $volume * wc_get_dimension($wc_product->length, 'cm');
      }

      // Set Width
      if($wc_product->width){
          $article->setWidth(round(wc_get_dimension($wc_product->width, 'cm'), 3), ISO::CMT);
          $volume = $volume * wc_get_dimension($wc_product->width, 'cm');
      }

      // Set Height
      if($wc_product->height){
        $article->setHeight(round(wc_get_dimension($wc_product->height, 'cm'), 3), ISO::CMT);
        $volume = $volume * wc_get_dimension($wc_product->height, 'cm');
      }

      // Set Volume
      $article->setVolume(round($volume, 3), ISO::CMQ);

      $response_status_code = 0;
      $response_status_text = '';
      $response_reference = 0;

      try{
        $response = $this->yellowcube->insertArticleMasterData($article);
        $response_status = 1;

        $response_status_code = $response->getStatusCode();
        $response_status_text = $response->getStatusText();
        $response_reference = $response->getReference();

        $this->log_create(1, 'ART-'.$type, $response_reference, $product_id, $response_status_text);

      } catch(Exception $e){

        $response_status = 0;
        $response_status_code = 0;
        $response_status_text = $e->getMessage();

        $this->log_create(0, 'ART-'.$type, '', $product_id, $e->getMessage());

      }

      // Insert a product to YellowCube
      if($type == 'INSERT'){

       $wpdb->insert(
          'wooyellowcube_products',
          array(
            'id_product' => $product_id,
            'created_at' => time(),
            'lotmanagement' => $lotmanagement,
            'yc_response' => $response_status,
            'yc_status_code' => $response_status_code,
            'yc_status_text' => $response_status_text,
            'yc_reference' => $response_reference
          )
        );

      }

      // Update a product to YellowCube
      if($type == 'UPDATE'){

        $wpdb->update(
          'wooyellowcube_products',
          array(
	        'lotmanagement' => $lotmanagement,
            'yc_response' => $response_status,
            'yc_status_code' => $response_status_code,
            'yc_status_text' => $response_status_text,
            'yc_reference' => $response_reference
          ),
          array(
            'id_product' => $product_id
          )
        );

      }

      // Desactivate a product to YellowCube
      if($type == 'DESACTIVATE'){

        // Be sure that they is no error before deleting
        if($response_status != 0){
          $wpdb->delete(
            'wooyellowcube_products',
            array(
              'id_product' => $product_id
            )
          );
        }

      }
    }

  }

  /** WAB Request to YellowCube */
  public function YellowCube_WAB($order_id){

    global $wpdb, $woocommerce;

	 /** Check if the WAB has already been sent with success */
	 $wpdb->get_var('SELECT COUNT(id) FROM wooyellowcube_orders WHERE id_order='.$order_id);

	 $user_count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->users WHERE yc_response=2" );
	 if($user_count != 0) return false;

    // YellowCube connexion
    if($this->yellowcube()){

  		// Get the order by ID
  		$wc_order = new WC_Order($order_id);

  		// YellowCube\Order
  		$order = new Order();
  		$order->setOrderHeader(new OrderHeader(
  		    get_option('wooyellowcube_depositorNo'),
  		    $wc_order->get_order_number(),
  		    date('Ymd')
  		));

  		$locale = get_locale();
  		$locale = explode('_', $locale);
  		$locale = $locale[0];

  		if($locale == ''){
  			$locale = 'en';
  		}

  		// YellowCube\Partner
  		$partner = new Partner();
  		$partner
  		    ->setPartnerType('WE')
  		    ->setPartnerNo(get_option('wooyellowcube_partnerNo'))
  		    ->setPartnerReference(substr($wc_order->shipping_first_name, 0, 1).substr($wc_order->shipping_last_name, 0, 1).$wc_order->shipping_postcode)
  		    ->setName1($wc_order->shipping_first_name.' '.$wc_order->shipping_last_name)
  		    ->setName2($wc_order->shipping_company)
  		    ->setName3($wc_order->shipping_address_2)
  		    ->setStreet($wc_order->shipping_address_1)
  		    ->setCountryCode($wc_order->shipping_country)
  		    ->setZIPCode($wc_order->shipping_postcode)
  		    ->setCity($wc_order->shipping_city)
  		    ->setPhoneNo($wc_order->billing_phone)
  		    ->setEmail($wc_order->billing_email)
  		    ->setLanguageCode($locale);

      $shipping_methods = $wc_order->get_shipping_methods();
      $shipping_name = '';

      foreach($shipping_methods as $method){
        $shipping_name = $this->woocommerce_reverse_meta($method['item_meta_array']);
        $shipping_name = $shipping_name['method_id'];
      }

      $shipping_saved_methods = unserialize(get_option('wooyellowcube_shipping'));
      $shipping_constant = $shipping_saved_methods[$shipping_name];

      switch($shipping_constant){
        case 'ECO': $order->addValueAddedService(new BasicShippingServices(BasicShippingServices::ECO)); break;
        case 'PRI': $order->addValueAddedService(new BasicShippingServices(BasicShippingServices::PRI)); break;
        case 'PICKUP': $order->addValueAddedService(new BasicShippingServices(BasicShippingServices::PICKUP)); break;
        default: $order->addValueAddedService(new BasicShippingServices(BasicShippingServices::ECO)); break;
      }

      /** AdditionalService */
    	$shipping_additional_saved_methods = unserialize(get_option('wooyellowcube_shipping_additional'));
    	$shipping_additional_constant = $shipping_additional_saved_methods[$shipping_name];

    	if(!empty($shipping_additional_constant)){
    		$order->addValueAddedService(new AdditionalShippingServices($shipping_additional_constant));
    	}


  		$order->setPartnerAddress($partner);

  		// Temporary products list (needed to generate the PDF)
  		$temp_products = array();

  		// Order items
  		$order_products = $wc_order->get_items();
  		$product_position = 0;

  		// Each items from the order
  		foreach($order_products as $product){

        // Get product ID
        $product = $this->woocommerce_reverse_meta($product['item_meta_array']);
        $product_id = $product['_product_id'];
        $product_search = ($product['_variation_id'] == 0) ? $product_id : $product['_variation_id'];

        // Check if the product is in YellowCube and is not an error
        $yellowcube_product = $wpdb->get_row('SELECT * FROM wooyellowcube_products WHERE id_product=\''.$product_search.'\' AND yc_response=\'2\'');

        if($yellowcube_product){

          $product_position++;

    			// Product linked to YellowCube & activated
    			$product_object = new WC_Product((int)$product_search);

    			// YellowCube\Position
    			$position = new Position();
    			$position
    			    ->setPosNo($product_position)
    			    ->setArticleNo($product_object->get_sku())
    			    ->setPlant(get_option('wooyellowcube_plant'))
    			    ->setQuantity($product['_qty'])
    			    ->setQuantityISO('PCE')
    			    ->setShortDescription($product['name']);

    			// Add the product to the temporary products list (needed to generate the PDF)
  	  			array_push($temp_products, array('name' => $product['name'], 'sku' => $product_object->get_sku(), 'quantity' => $product['qty']));

    				$order->addOrderPosition($position);

    			}

  		}

  		// Check if the order has already been sent to YellowCube
  		$order_exist = $wpdb->get_row('SELECT * FROM wooyellowcube_orders WHERE id_order=\''.$order_id.'\'');

  		// Send WAB to YellowCube
      try{

        $yellowcube_order = $this->yellowcube->createYCCustomerOrder($order);

        // YellowCube success
        if(!$order_exist){

          $wpdb->insert(
            'wooyellowcube_orders',
            array(
                'id_order' => $order_id,
                'created_at' => time(),
                'yc_response' => 1,
                'yc_status_code' => $yellowcube_order->getStatusCode(),
                'yc_status_text' => $yellowcube_order->getStatusText(),
                'yc_reference' => $yellowcube_order->getReference()
            )
          );

          $order_id = $wpdb->insert_id;

        }else{

          $wpdb->update(
            'wooyellowcube_orders',
            array(
                'created_at' => time(),
                'yc_response' => 1,
                'yc_status_code' => $yellowcube_order->getStatusCode(),
                'yc_status_text' => $yellowcube_order->getStatusText(),
                'yc_reference' => $yellowcube_order->getReference()
            ),
            array(
              'id_order' => $order_id
            )
          );

        }

        // Save log
        $this->log_create(1, 'WAB-DELIVERY ORDER', $yellowcube_order->getReference(), $wc_order->get_order_number(), $yellowcube_order->getStatusText());

      } catch(Exception $e){

  		$order = new WC_Order((int)$order_id);
  		$order->update_status('failed', __('Please see YellowCube instructions', 'wooyellowcube'));


        if(!$order_exist){

          // YellowCube error
          $wpdb->insert(
            'wooyellowcube_orders',
            array(
                'id_order' => $order_id,
                'created_at' => time(),
                'yc_response' => 0,
                'yc_status_code' => 0,
                'yc_status_text' => $e->getMessage(),
                'yc_reference' => 0
            )
          );

        }else{

          $wpdb->update(

            'wooyellowcube_orders',
            array(
              'created_at' => time(),
              'yc_response' => 0,
              'yc_status_code' => 0,
              'yc_status_text' => $e->getMessage(),
              'yc_reference' => 0
            ),
            array(
              'id_order' => $order_id
            )
          );

        }

        // Save log
        $this->log_create(0, 'WAB-ERROR', '', $wc_order->get_order_number(), $e->getMessage());

      }
    }
  }

  /** Get product status */
  public function get_product_status($product_id){
    global $wpdb;

    return $wpdb->get_row('SELECT yc_response, yc_status_code FROM wooyellowcube_products WHERE id_product=\''.$product_id.'\' ');

  }

  /** Get order status */
  public function get_order_status($order_id){
    global $wpdb;

    return $wpdb->get_row('SELECT yc_response, yc_status_code FROM wooyellowcube_orders WHERE id_order=\''.$order_id.'\' ');

  }

  /** Get product object by SKU */
  public function get_product_by_sku($sku){
		global $wpdb;

		$product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku));

		if($product_id) return new WC_Product($product_id);

		return false;

	}

  /** CRON management : Response */
  public function crons_responses(){
    global $wpdb;


    $cron_response = get_option('wooyellowcube_cron_response');
    $cron_response_limit = 60; // 60 seconds

    if(((time() - $cron_response) > $cron_response_limit) || isset($_GET['cron_response']) != ''){

      // Get results from previous requests on products
      $products_execution = $wpdb->get_results('SELECT * FROM wooyellowcube_products WHERE yc_response = 1');

      // Get results from previous requests on orders
      $orders_execution = $wpdb->get_results('SELECT * FROM wooyellowcube_orders WHERE yc_response = 1');

      // Connect to YellowCube if we have some requests entries
      $load_yellowcube = false;
      $load_yellowcube = (is_array($products_execution)) ? true : false;
      $load_yellowcube = (is_array($orders_execution)) ? true : false;

      if($load_yellowcube){
          $this->yellowcube();
      }

      // Products execution
      if($products_execution){

        foreach($products_execution as $execution){

            try{
              $response = $this->yellowcube->getInsertArticleMasterDataStatus($execution->yc_reference);

              if($response->getStatusCode() == 100){

                // Update the record
                $wpdb->update(
                  'wooyellowcube_products',
                  array(
                    'yc_response' => 2,
                    'yc_status_text' => $response->getStatusText()
                  ),
                  array(
                    'id_product' => $execution->id_product
                  )
                );

                $this->log_create(1, 'ART-ACCEPTED', $response->getReference(), $execution->id_product, $response->getStatusText());

              }


            } catch(Exception $e){

              $wpdb->update(
                'wooyellowcube_products',
                array(
                  'yc_response' => 0,
                  'yc_status_text' => $e->getMessage()
                ),
                array(
                  'id_product' => $execution->id_product
                )
              );

              $this->log_create(0, 'ART-REFUSED', $execution->yc_reference, $execution->id_product, $e->getMessage());

            }
        }
      }

		// Orders execution
		if($orders_execution){

			foreach($orders_execution as $execution){

				try{
					$response = $this->yellowcube->getYCCustomerOrderStatus($execution->yc_reference);

					// Update the order only when we got 101 StatusCode
					if($response->getStatusCode() == 100){

						// Update the record
						$wpdb->update(
							'wooyellowcube_orders',
							array(
								'yc_response' => 2,
								'yc_status_text' => $response->getStatusText()
							),
							array(
								'id_order' => $execution->id_order
							)
						);

            $this->log_create(1, 'WAB-ACCEPTED', $execution->id_order, $execution->id_order, $response->getStatusText());

					}
				} catch(Exception $e){

					$wpdb->update(
						'wooyellowcube_orders',
						array(
							'yc_response' => 0,
							'yc_status_text' => $e->getMessage()
						),
						array(
							'id_order' => $execution->id_order
						)
					);

          $this->log_create(0, 'WAB-REFUSED', $execution->id_order, $execution->id_order, $response->getStatusText());

				}
			}
		}

		// Update last execution date
		update_option('wooyellowcube_cron_response', time());

	}

  }

  /** CRON management : Daily */
  public function crons_daily(){
    global $wpdb;

    $cron_daily = get_option('wooyellowcube_cron_daily');
    $current_day = date('Ymd');

    // Execute CRON
    if($current_day != $cron_daily){
      $this->update_stock();

      // Update last execution date
      update_option('wooyellowcube_cron_daily', date('Ymd'));
    }

    if(isset($_GET['cron_daily'])){
  		$this->update_stock();
  	}

    if(get_option('wooyellowcube_logs') > 1){
      $date_gap = get_option('wooyellowcube_logs') * 60 * 60 * 24;
      $wpdb->query("DELETE FROM wooyellowcube_logs WHERE created_at < ".(time() - $date_gap));
    }

  }

  public function update_stock(){
	  global $wpdb;

    if($this->yellowcube()){

      try{

        // Get Yellowcube inventory
        $articles = $this->yellowcube->getInventory();

        // Remove all the current entries
        $wpdb->query('DELETE FROM wooyellowcube_stock');
         $wpdb->query('DELETE FROM wooyellowcube_stock_lots');


        foreach($articles as $article){

          $article_SKU = $article->getArticleNo();
          $article_object = $this->get_product_by_sku($article_SKU);


          $article_quantity = (method_exists($article_object, 'get_stock_quantity')) ? $article_object->get_stock_quantity() : 0;

			 $object_id = (isset($article_object)) ? $article_object->id : 0;

			 if($article->getStorageLocation() == 'YAFS'){

	          // Insert product stock in database
	          $wpdb->insert(
	              'wooyellowcube_stock',
	              array(
	                'product_id' => $object_id,
	                'product_name' => $article->getArticleDescription(),
	                'woocommerce_stock' => $article_quantity,
	                'yellowcube_stock' => $article->getQuantityUOM(),
	                'yellowcube_date' => time(),
	                'yellowcube_articleno' => $article->getArticleNo(),
	                'yellowcube_lot' => $article->getLot(),
	                'yellowcube_bestbeforedate' => $article->getBestBeforeDate()
	              )
	          );

	          $wpdb->insert(
		         'wooyellowcube_stock_lots',
		         array(
			         'id_product' => $object_id,
			         'product_lot' => $article->getLot(),
			         'product_quantity' => $article->getQuantityUOM(),
			         'product_expiration' => $article->getBestBeforeDate()
		         )
	          );

          }

        }

        $this->log_create(1, 'BAR', 0, 0, null);

      }catch(Exception $e){
        error_log($e->getMessage());
      }
    }
  }



  /** CRON - Hourly */

  public function crons_hourly(){
    global $wpdb;

    // Cron hourly eecution
    $cron_hourly = get_option('wooyellowcube_cron_hourly');
    $current_time = time();
    $cron_limit_time = 60 * 60;

    // Need to execute the cron
    if((time() - $cron_hourly) > $cron_limit_time){

      // Get YellowCube
      if($this->yellowcube()){
        $this->retrieveWAR();
      }

    }

    if(isset($_GET['cron_hourly'])){

  		$this->retrieveWAR();
  	}

    // Update cron last update
    update_option('wooyellowcube_cron_hourly', time());


  }

  /**
  * Add a log
  */
  public function log_create($response, $type, $reference, $object, $message){
    global $wpdb;

    /*
    * $response
    * 0 = Error
    * 1 = Sent
    */

    // Insert the log in database
    $wpdb->insert(
      'wooyellowcube_logs',
      array(
        'id' => '',
        'created_at' => time(),
        'type' => $type,
        'response' => $response,
        'reference' => $reference,
        'object' => $object,
        'message' => $message
      )
    );

    return true;
  }

  public function woocommerce_reverse_meta($array){

    if(!is_array($array)) return false; // Check if param is an array
    $out_temp = array(); // Out temporary

    foreach($array as $id => $object){
      $out_temp[$object->key] = $object->value;
    }

    // Return correct informations
    return $out_temp;

  }

  static public function getEmailSubject(){
    return (get_option('wooyellowcube_email_subject') != '') ? get_option('wooyellowcube_email_subject') : self::$email_subject;
  }

  static public function getEmailReply(){
    return (get_option('wooyellowcube_email_reply') != '') ? get_option('wooyellowcube_email_reply') : self::$email_reply;
  }

  static public function getEmailContent(){
    return (get_option('wooyellowcube_email_content') != '') ? get_option('wooyellowcube_email_content') : self::$email_content;
  }

}

if(!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php");
}

if(!function_exists('is_user_logged_in')):
/**
 * Checks if the current visitor is a logged in user.
 *
 * @since 2.0.0
 *
 * @return bool True if user is logged in, false if not logged in.
 */

 function is_user_logged_in() {
     $user = wp_get_current_user();

     return $user->exists();
 }

endif;

/**
 * Plugin initialization from init action
 *
 * @since 2.3.4
 */
 
function wooyellowcube_init(){

  // instanciate WooYellowCube class
  $wooyellowcube = new WooYellowCube();
}

add_action('init', 'wooyellowcube_init');
?>
