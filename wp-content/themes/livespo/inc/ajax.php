<?php

add_action( 'wp_ajax_policysubmit', 'ajax_policysubmit' );
//for none logged-in users
add_action('wp_ajax_nopriv_policysubmit', 'ajax_policysubmit');

function ajax_policysubmit(){
  $result= json_encode(['status' => 0]);
  $mobile = isset($_POST['mobile'])?$_POST['mobile']:"";

  if(!empty($mobile)){
    $args = array(
      'post_type' => 'policysubmit',
      's'         => $mobile
    );
    $the_query = new WP_Query( $args );
    if ( $the_query->have_posts() ) {
      $request_id='';
      while ( $the_query->have_posts() ) {
          $the_query->the_post();
          $request_id = tr_posts_field('request_id',$post->ID);
      }
      $result= json_encode(['status' => 1,'request_id'=>$request_id]);
    }
    else{
      //Save DB
      $data = array(
        'post_title'    => $mobile,
        'post_status'   => 'draft',
        'post_type'   => 'policysubmit',
      );
      // Insert the post into the database
      $post_id = wp_insert_post( $data );
      $request_id= uniqid();
      add_post_meta( $post_id, 'request_id', $request_id );
      $result=  json_encode(['status' => 1,'request_id'=>$request_id]);
    }


  }
  echo $result;
  exit();
}

add_action( 'wp_ajax_policyupdate', 'ajax_policyupdate' );
//for none logged-in users
add_action('wp_ajax_nopriv_policyupdate', 'ajax_policyupdate');

function ajax_policyupdate(){
  $result= json_encode(['status' => 0]);
  $request_id = isset($_POST['request_id'])?$_POST['request_id']:"";

  if(!empty($request_id)){
    $args = array(
      'post_type' => 'policysubmit',
      'posts_per_page'   => 1,
      'post_status' => 'draft',
      'meta_query' => array(
           array(
               'key' => 'request_id',
               'value' => $request_id,
               'compare' => '=',
           )
       )
    );
    $posts = get_posts( $args );
    $result= json_encode(['status' => 0,'posts'=>$posts]);
    if(!empty($posts )){
      $update_post = array(
          'ID' => $posts[0]->ID,
          'post_status' => 'publish'
      );
      wp_update_post($update_post);
      $result=  json_encode(['status' => 1]);
    }
  }
  echo $result;
  exit();
}

add_action( 'wp_ajax_newsletter', 'ajax_newsletter' );
//for none logged-in users
add_action('wp_ajax_nopriv_newsletter', 'ajax_newsletter');

function ajax_newsletter(){
  $result= json_encode(['status' => 0]);
  $newsletter_email = isset($_POST['newsletter_email'])?$_POST['newsletter_email']:"";
 
  if(!empty($newsletter_email)){
    $args = array(
      'post_type' => 'newsletter',
      's'         => $newsletter_email
    );
    $the_query = new WP_Query( $args );
    if ( $the_query->have_posts() ) {
      $result= json_encode(['status' => 2]);
      echo $result; die();
    }

    //Save DB
    $newsletter_data = array(
      'post_title'    => $newsletter_email,
      'post_status'   => 'publish',
      'post_type'   => 'newsletter',
    );
    // Insert the post into the database
    $post_id = wp_insert_post( $newsletter_data );
    $result=  json_encode(['status' => 1]);
  }
  echo $result;
  exit();
}

add_action( 'wp_ajax_contactform', 'ajax_contactform' );
//for none logged-in users
add_action('wp_ajax_nopriv_contactform', 'ajax_contactform');

function ajax_contactform(){
  $result= json_encode(['status' => 0]);
  $contact_name = isset($_POST['fullname'])?$_POST['fullname']:"";
  $contact_mobile = isset($_POST['mobile'])?$_POST['mobile']:"";
  $contact_email = isset($_POST['email'])?$_POST['email']:"";
  $contact_address = isset($_POST['address'])?$_POST['address']:"";
  $contact_interest = isset($_POST['interest'])?$_POST['interest']:"";
  
  $content = isset($_POST['content'])?$_POST['content']:"";

  /*$args = array(
    'post_type' => 'register',
    'meta_query' => array(
      'relation' => 'OR',
      array(
        'key' => 'mobile',
        'value' => $contact_mobile
      ),
      array(
        'key' => 'email',
        'value' => $contact_email
      ),
    )
  );
   
  $the_query = new WP_Query( $args );
  if ( $the_query->have_posts() ) {
    $result= json_encode(['status' => 2]);
    echo $result; die();
  }*/

  $body  ="Xin chào,<br>";
  $body .="<p>Bạn nhận được thông tin đăng ký của khách hàng.</p>";
  $body .="<strong>Họ và tên:</strong> ".$contact_name."<br>";
  $body .="<strong>Điện thoại di động:</strong> ".$contact_mobile."<br>";
  $body .="<strong>Email:</strong> ".$contact_email."<br>";
  //$body .="<strong>Địa chỉ:</strong> ".$contact_address."<br>";
  $body .="<strong>Sản phẩm quan tâm:</strong> ".$contact_interest."<br>";
  $body .="<strong>Nội dung:</strong> ".$content."<br>";

  $headers = array('Content-Type: text/html; charset=UTF-8');

   //Save DB
  $register_data = array(
    'post_title'    => wp_strip_all_tags( $contact_name ),
    'post_status'   => 'publish',
    'post_type'   => 'register',
  );
  // Insert the post into the database
  $post_id = wp_insert_post( $register_data );
  add_post_meta( $post_id, 'mobile', $contact_mobile );
  add_post_meta( $post_id, 'email', $contact_email );
  //add_post_meta( $post_id, 'address', $contact_address );
  add_post_meta( $post_id, 'interest', $contact_interest );
  add_post_meta( $post_id, 'content', $content );

  $device ="Desktop";
  if(isMobileDevice()){
      $device ="Mobile";
  }
  $user_agent =  $_SERVER['HTTP_USER_AGENT'];
  $source = isset($_POST['source'])?urldecode($_POST['source']):""; 

  add_post_meta( $post_id, 'device', $device );
  add_post_meta( $post_id, 'source', $source );
  add_post_meta( $post_id, 'user_agent', $user_agent );


  $check = wp_mail( tr_options_field('tr_theme_options.receive_email'), "Thông tin đăng ký Từ website TBS Group", $body , $headers );

  //Mail To Customer

  if($check || 1==1){
    $result=  json_encode(['status' => 1]);
  }

  echo $result;
  die();
}


add_action( 'wp_ajax_nopriv_pagination_data', 'ajax_pagination_data' );
add_action( 'wp_ajax_pagination_data', 'ajax_pagination_data' );
function ajax_pagination_data() {
    $paged = !empty($_POST['page'])?$_POST['page']:1;
    $query_vars = [];
    $query_vars['post_type'] = "post";
    $query_vars['post_status'] = "publish"; 
    $query_vars['paged'] = $paged;
    $query_vars['cat'] = $_POST['cat'];
    $query_vars['posts_per_page'] = !empty($_POST['posts_per_page'])?$_POST['posts_per_page']:4;

    $posts = new WP_Query( $query_vars );
    $GLOBALS['wp_query'] = $posts;
 
 
    if( $posts->have_posts() ) { 
        echo '<div class="items post-list row gutter-0 animated fadeInUpShort delay-500">';
        while ( $posts->have_posts() ) { 
            $posts->the_post();
            echo '<div class="col-12 col-sm-6 col-lg-3">';
            get_template_part( "partials/content", "news");
            echo '</div>';
        }
        echo '</div>';
        if (function_exists("custom_ajax_pagination")) {
          custom_ajax_pagination($posts->max_num_pages,"",$paged);
        }
    } 
 
    die();      
 
}


add_action( 'wp_ajax_nopriv_checkpopup', 'ajax_checkpopup' );
add_action( 'wp_ajax_checkpopup', 'ajax_checkpopup' );
function ajax_checkpopup() {
    $time = time();
    if(isset($_SESSION['visit_time'])) {
        if(($time - $_SESSION['visit_time']) >= 89){
          $_SESSION['visit_time'] = strtotime('tomorrow');
          echo  json_encode(['status' => 1]); die();
        }
    } else {
        $_SESSION['visit_time'] = $time;
    } 
    echo  json_encode(['status' => 0]); die();  
 
}


add_action( 'wp_ajax_get_news', 'ajax_get_news' );
//for none logged-in users
add_action('wp_ajax_nopriv_get_news', 'ajax_get_news');

function ajax_get_news(){

    $id = isset($_POST['id'])?$_POST['id']:"";
    $post= wp_get_single_post($id);

    if(!empty($post)){
      echo json_encode(['status' => 1,
        'post'=> [
          "id"=>$post->ID,
          //"href"=>get_permalink($post->ID),
          "title"=>$post->post_title,
          "date"=>date('d/m/Y', strtotime($post->post_date)),
          "content"=> apply_filters('the_content', $post->post_content)
        ]
      ]); 
    }
    else{
      echo json_encode(['status' => 0]);
    }
    die();
}

add_action('wp_ajax_updatepost', 'ajax_updatepost');

function ajax_updatepost(){
    $post_id = isset($_POST['id'])?$_POST['id']:"";
    $meta_key = isset($_POST['field'])?$_POST['field']:"";
    $meta_value = isset($_POST['value'])?$_POST['value']:"";
    try {
      update_post_meta( $post_id, $meta_key, $meta_value );
      echo json_encode(['status' => 1]);
    } catch (\Exception $e) {
      echo json_encode(['status' => 0]);
    }


    //Don't forget to always exit in the ajax function.
    exit();

}


add_action( 'wp_ajax_get_prod', 'ajax_get_prod' );
//for none logged-in users
add_action('wp_ajax_nopriv_get_prod', 'ajax_get_prod');

function ajax_get_prod(){

    $id = isset($_POST['id'])?$_POST['id']:"";
    $post= wp_get_single_post($id);

    if(!empty($post)){
      $thap = isset($_POST['thap'])?$_POST['thap']:"";
      $tang = isset($_POST['tang'])?$_POST['tang']:"";

      $content = nmc_get_template_part( 'partials/modal-product-detail', [ 'return' => true,'post'=>$post,'thap'=>$thap,'tang'=>$tang] );

      echo json_encode(['status' => 1,'content'=> $content]);
    }
    else{
      echo json_encode(['status' => 0]);
    }
    die();
}