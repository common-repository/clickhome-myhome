<?php

/**
 * ClickHome.MyHome WordPress Plugin
 *
 * ClickHome is a leading solution for residential construction companies. It covers the full range of residential construction building company processes, from the first point of contact with the client, to managing the work processes with trades, suppliers and subcontractors, right through to ongoing care and maintenance. The WordPress module enables existing ClickHome clients to easily develop an engaging website for their clients.
 *
 * @package    MyHome
 * @subpackage Main
 * @license    GPL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

/**
 * Plugin Name: ClickHome.MyHome
 * Plugin URI: http://www.clickhome.com.au/
 * Description: ClickHome is a leading solution for residential construction companies. It covers the full range of residential construction building company processes, from the first point of contact with the client, to managing the work processes with trades, suppliers and subcontractors, right through to ongoing care and maintenance. The WordPress module enables existing ClickHome clients to easily develop an engaging website for their clients.
 * Version: 1.6.3
 * Author: ClickHome
 * URI: http://www.clickhome.com.au/
 * License: GPL2
 */

// Exit if the script is accessed directly
if(!defined('ABSPATH'))
  die;

// Register the autoload function
spl_autoload_register('myHomeAutoload');

// Register the activation and plugin init hooks
register_activation_hook(__FILE__, 'myHomeOnRegister');
register_deactivation_hook(__FILE__, 'myHomeOnDeregister');
add_action('init','myHomeOnInit',0); // Allow MyHomeWidgets to use the widget_init hook

//register_activation_hook(__DIR__ . '/classes/MyHomeApi.php', array('MyHomeAdmin', 'setupHealthCheck'));
//register_deactivation_hook(__DIR__ . '/classes/MyHomeApi.php', 'removeHealthCheck');

/**
 * Whether a debug message should be displayed on the website when an error is handled
 *
 * <p>It is strongly advised to disable this in production environments</p>
 * <p>It is advised to set this constant to the same value as WP_DEBUG</p>
 *
 * @see MyHome::handleError()
 */
define('MH_DEBUG',false);

/**
 * The plugin version
 */
define('MH_VERSION','1.6.3');

/**
 * Plugin root directory path (eg "/var/www/website/wp-content/plugins/clickhome-myhome")
 */
define('MH_PATH_HOME',__DIR__);

/**
 * Classes root directory path (eg "/var/www/website/wp-content/plugins/clickhome-myhome/classes")
 */
define('MH_PATH_CLASSES',MH_PATH_HOME.'/classes');

/**
 * Controller classes root directory path (eg "/var/www/website/wp-content/plugins/clickhome-myhome/controllers")
 */
define('MH_PATH_CONTROLLERS',MH_PATH_HOME.'/controllers');

/**
 * Widget classes root directory path (eg "/var/www/website/wp-content/plugins/clickhome-myhome/widgets")
 */
define('MH_PATH_WIDGETS',MH_PATH_HOME.'/widgets');

/**
 * Views root directory path (eg "/var/www/website/wp-content/plugins/clickhome-myhome/views")
 */
define('MH_PATH_VIEWS',MH_PATH_HOME.'/views');

/**
 * Database root directory path (eg "/var/www/website/wp-content/plugins/clickhome-myhome/database")
 *
 * @since 1.3
 */
define('MH_PATH_DATABASE',MH_PATH_HOME.'/database');

/**
 * Models root directory path (eg "/var/www/website/wp-content/plugins/clickhome-myhome/models")
 *
 * @since 1.3
 */
define('MH_PATH_MODELS',MH_PATH_HOME.'/models');

/**
 * Vendor root directory path (eg "/var/www/website/wp-content/plugins/clickhome-myhome/vendor")
 *
 * @since 1.4
 */
define('MH_PATH_VENDOR',MH_PATH_HOME.'/vendor');

/**
 * Plugin base URL (eg "http://website.com.au/wp-content/plugins/clickhome-myhome")
 */
define('MH_URL_HOME',plugins_url('',__FILE__));

/**
 * CSS directory URL (eg "http://website.com.au/wp-content/plugins/clickhome-myhome/css")
 */
define('MH_URL_STYLES',MH_URL_HOME.'/css');

/**
 * CSS directory URL (eg "http://website.com.au/wp-content/plugins/clickhome-myhome/js")
 */
define('MH_URL_SCRIPTS',MH_URL_HOME.'/js');

/**
 * Images directory URL (eg "http://website.com.au/wp-content/plugins/clickhome-myhome/images")
 */
define('MH_URL_IMAGES',MH_URL_HOME.'/images');

/**
 * Vendor code URL (eg "http://website.com.au/wp-content/plugins/clickhome-myhome/vendor")
 */
define('MH_URL_VENDOR',MH_URL_HOME.'/vendor');

/**
 * Returns the MyHome instance
 *
 * @return MyHome the MyHome instance
 */
function myHome(){
  return MyHome::getInstance();
}

/**
 * Autoload callback for spl_autoload_register()
 *
 * @since 1.3
 * @param string $class the class name to look for
 */
function myHomeAutoload($class){
  $directories= array(MH_PATH_CLASSES,
    MH_PATH_CONTROLLERS,
    MH_PATH_CONTROLLERS.'/MyHomeAdmin',
    MH_PATH_CONTROLLERS.'/MyHomeShortcodes',
    MH_PATH_WIDGETS,
    MH_PATH_DATABASE,
    MH_PATH_MODELS);

  foreach($directories as $directory){
    $path=sprintf('%s/%s.php',$directory,$class);

    if(is_readable($path)){
      /** @noinspection PhpIncludeInspection */
      require_once $path;
      break;
    }
  }
}

/**
 * Initialises the plugin
 *
 * Creates the MyHome singleton instance and upgrades the plugin, if required
 */
function myHomeOnInit(){
	new MyHome;

	$myHomeVersion=get_option('myhome_version');
	if(!$myHomeVersion || $myHomeVersion != MH_VERSION)	 //version_compare($myHomeVersion,MH_VERSION,'<'))
		myHomeUpgradePlugin();

	/* Shortcode Styles */
	if (!is_admin()) {
		/* Included Scripts */
		//echo(MH_PATH_VENDOR. '/colorbox/jquery.colorbox-min.js');
		wp_enqueue_style( 'bootstrap', MH_URL_VENDOR . '/bootstrap/bootstrap.min.css');
		wp_enqueue_style( 'font-awesome', MH_URL_VENDOR . '/font-awesome/css/font-awesome.min.css');
		wp_enqueue_style( 'jquery-ui', MH_URL_VENDOR . '/jquery.ui/jquery-ui.css');
		wp_enqueue_style( 'colorbox', MH_URL_VENDOR . '/jquery.colorbox/colorbox.css');
		wp_enqueue_style( 'slick', MH_URL_VENDOR . '/jquery.slick/slick/slick.css');
		wp_enqueue_style( 'slick-theme', MH_URL_VENDOR . '/jquery.slick/slick/slick-theme.css');
		wp_enqueue_style( 'toastr', MH_URL_VENDOR . '/toastr/toastr.min.css');

		wp_enqueue_script( 'underscore', MH_URL_VENDOR . '/underscore/underscore.min.js', array( 'jquery' ), null );
		wp_enqueue_script( 'base64', MH_URL_VENDOR . '/base64/base64.min.js' );
		wp_enqueue_script( 'validate', MH_URL_VENDOR . '/jquery.validate/jquery.validate.min.js', array( 'jquery' ), null );
		wp_enqueue_script( 'jquery-ui', MH_URL_VENDOR . '/jquery.ui/jquery-ui.js', array('jquery')); // datepicker and slider
		wp_enqueue_script( 'colorbox', MH_URL_VENDOR . '/jquery.colorbox/jquery.colorbox-min.js', array( 'jquery' ), null );
		wp_enqueue_script( 'slick', MH_URL_VENDOR . '/jquery.slick/slick/slick.min.js', array('jquery'), null );
		wp_enqueue_script( 'flexslider', MH_URL_VENDOR . '/jquery.flexslider/jquery.flexslider-min.js', array( 'jquery' ), null );
		wp_enqueue_script( 'fitvids', MH_URL_VENDOR . '/jquery.fitvids/jquery.fitvids.js', array('jquery'), null );
		wp_enqueue_script( 'signature_pad', MH_URL_VENDOR . '/signature_pad/signature_pad.min.js', array('jquery'), null );
		wp_enqueue_script( 'toastr', MH_URL_VENDOR . '/toastr/toastr.min.js', array( 'jquery' ), null );

	  //wp_enqueue_style('myhome', MH_URL_STYLES . '/myhome.css');
	  //wp_enqueue_style('myhome-theme-' . get_template() . '-support', MH_URL_STYLES . '/theme-support/' . get_template() . '.css');
	}

  /* Update home_url when logged in */
	/*if(!myHome()->session->guest()) {
    $main_page_url = get_page_link(myHome()->options->getMainPage());
    $my_home_url = function () use ($main_page_url) {
      return $main_page_url;
    };
    add_filter( 'home_url', $my_home_url);
  }*/
	
	/* Body Classes */
	add_filter( 'body_class', function( $c = array() ) {
		global $post;

		/* Logged in/out body class */
		if(!myHome()->session->guest()) {
      $c[] = 'mh-logged-in';
      try { 
        $handoverdate = myHome()->session->getJobDetails()->handoverdate;
        $daysSinceHandover = (int) date_diff(myHome()->wpDateTime($handoverdate)->setTime(0,0), myHome()->wpDateTime()->setTime(0,0))->format("%r%a"); // $daysSinceHandover = myHome()->wpDateTime($handoverdate)->setTime(0,0) -> diff(myHome()->wpDateTime()->setTime(0,0)) -> days; // echo $daysSinceHandover;
        // myHome()->log->info($handoverdate . ' | days since handover: ' . $daysSinceHandover);
        if($daysSinceHandover > 0) $c[] = 'mh-show-maintenance';
      } catch(Exception $e) { throw new MyHomeException('Wrong handover date: ' . $handoverdate); }
		} else {
			$c[] = 'mh-logged-out';
		}
		
		/* Page specific body classes */
		if( (isset($post->post_content) && (has_shortcode($post->post_content, 'MyHome.Login') || has_shortcode($post->post_content, 'MyHome.ResetPassword')))) {
			$c[] = 'mh-header-infinity mh-login';
		} else if(is_front_page()) {
			$c[] = 'mh-header-infinity mh-home';
		}

		/* Remove entry-header from selected pages */
    //var_dump($post);
		if( isset($post->post_content) && 
			(
				has_shortcode( $post->post_content, 'MyHome.TenderOverview' ) ||
				has_shortcode( $post->post_content, 'MyHome.TenderPackages' ) ||
				has_shortcode( $post->post_content, 'MyHome.TenderSelections' ) ||
				has_shortcode( $post->post_content, 'MyHome.TenderSelectionsEdit' ) ||
				has_shortcode( $post->post_content, 'MyHome.TenderVariations' )
			)
		) {
			$c[] = 'mh-hide-entry-header';
		}

		$c[] = 'theme-' . rawurlencode( get_template() );

    /* Body-class dependant hooks */
    if(!myHome()->helpers->like_in_array('mh-header-infinity', $c)) { // if(!like_in_array('mh-header-infinity', $c)) {
      if (rawurlencode( get_template() ) == 'astrid') {
	      //add_action( 'loop_start', 'myhome_header_credits' );
	      add_action( 'astrid_before_content', 'myhome_header_credits' );
      }
    }
    if (rawurlencode( get_template() ) == 'astrid') {
	    add_action( 'astrid_footer', 'myhome_footer_credits' );
    }

		return $c;
	});

	/* Set background style */
	add_action( 'wp_head', function() {
		$bgImage=myHome()->options->getBgImage();
		if(!$bgImage) $bgImage = get_stylesheet_directory_uri() . '/images/bg1.jpg'; // . '/wp-content/themes/astrid-myhome/images/bg1.jpg'; // = get_site_url() . '/wp-content/themes/astrid-myhome/images/bg1.jpg';
    if(substr($bgImage, 0, 4) != 'http' && substr($bgImage, 0, 1) != '/') $bgImage = get_site_url() . $bgImage;
    
		//background-image: url('" . get_site_url() . "{$bgImage}');
		$custom_css = "<style type=\"text/css\">
							      body.theme-astrid {
									      background-image: url('{$bgImage}');
							      }
						      </style>";
		echo $custom_css;
		//wp_add_inline_style( 'mh-bg-image', $custom_css );
	});

  /* Page Slug Body Class */
  function add_slug_body_class( $classes ) {
    global $post;
    if ( isset( $post ) ) {
      $classes[] = $post->post_type . '-' . $post->post_name;
    }
    return $classes;
  }
  add_filter( 'body_class', 'add_slug_body_class' );


  /* Shortcode in page-title */
  function override_post_title($title){
    $title['title'] = do_shortcode($title['title']); 
    return $title; 
  }
  //remove_theme_support( 'title-tag' );
  add_filter( 'document_title_parts', 'override_post_title', 10);
  add_filter( 'the_title', 'do_shortcode' ); //add_filter( 'wp_title', 'do_shortcode');//, 10, 2 );
  
  /* Shortcode in Text-Widget */
  //add_filter( 'widget_text', 'shortcode_unautop'); // Not working
  remove_filter('widget_text', 'wpautop'); 
  add_filter( 'widget_text', 'do_shortcode');

  /* Remove empty <p></p> tags */
  remove_filter('the_content', 'wpautop'); 
  remove_filter('the_excerpt', 'wpautop');
  add_filter('the_content', 'modify_content');
  function modify_content($content) {
    /* Auto add baseDir within post content */
    $content = str_replace('(/wp-content', '(' . site_url() . '/wp-content', $content);
    $content = str_replace("'/wp-content", "'" . site_url() . '/wp-content', $content);
    $content = str_replace('"/wp-content', '"' . site_url() . '/wp-content', $content);

    /* Remove empty <p></p> tags */
    //$content = str_replace('<p>', '', $content);
    //$content = str_replace('</p>', '', $content); // Wordpress bug leaves extra closing p tags everywhere
    return $content;
  }

  /* Show Hooks
  $debug_tags = array();
  add_action( 'all', function ( $tag ) {
      global $debug_tags;
      if ( in_array( $tag, $debug_tags ) ) {
          return;
      }
      echo "<pre>" . $tag . "</pre>";
      $debug_tags[] = $tag;
  } ); */
}

function myHomeStyles() {
	if (!is_admin()) {
		wp_enqueue_style('myhome', MH_URL_STYLES . '/myhome.css');
		wp_enqueue_style('myhome-print', MH_URL_STYLES . '/myhome-print.css', null, null, 'print');
		wp_enqueue_style('myhome-theme-' . get_template() . '-support', MH_URL_STYLES . '/theme-support/' . get_template() . '.css');

    wp_enqueue_script('myhome', MH_URL_SCRIPTS . '/myhome.js', array('jquery'), null );
    wp_add_inline_script('myhome', call_user_func(function() {
      return '
        jQuery(function ($) {
          mh.urls = {
            api: "' .  myHome()->options->getEndpoint() . '", 
            images: "' . MH_URL_IMAGES . '"
          };
          mh.auth = {
            ContractNumber: "' . myHome()->session->getAuthentication()['contractNumber'] . '",
            Authorization: "' . myHome()->session->getAuthentication()['authorization'] . '"
          };
        });
      ';
    }));
  }
}
add_action('wp_enqueue_scripts', 'myHomeStyles', 9); // Allow MyHomeWidgets to use the widget_init hook

/* Theme support */
if ( get_template() == 'astrid' ) {
  /* Update logo */
  /*add_action( 'wp_head', function() {
    $mainLogo = myHome()->options->getMainLogo();
    var_dump($mainLogo);
    if ( $mainLogo ) {
	    set_theme_mod('site_logo', $mainLogo);
    }
  }); 
  });*/

  /* Update home_url when logged in */
  if ( ! function_exists( 'astrid_branding' )) {
	  //if ( function_exists( 'the_custom_logo' ) && !has_custom_logo() ) { // Favour Astrid
		  //the_custom_logo();
    function astrid_branding() {
      // Set logged out/in home url
      global $mh_site_url;
      $mh_site_url = !myHome()->session->guest()?get_page_link(myHome()->options->getMainPage()):home_url( '/' );

      // Detect if plugin/theme has a custom logo set
	    if ( myHome()->options->getMainLogo() ) {
         $site_logo = '<img class="site-logo" src="' . esc_url(myHome()->options->getMainLogo()) . '" alt="' . esc_attr(get_bloginfo('name')) . '" />'; 
	    } else if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
		    //the_custom_logo();
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        $site_logo = wp_get_attachment_image( $custom_logo_id, 'full', false, array(
				  'class'    => 'custom-logo',
				  'itemprop' => 'logo',
			  ) );
      }

	    if ( isset($site_logo) ) {
        echo '<a href="' . esc_url( $mh_site_url ) . '" title="' . esc_attr(get_bloginfo('name')) . '">' . $site_logo . '</a>'; 
	    } else {
		    echo '<h1 class="site-title"><a href="' . esc_url( $mh_site_url ) . '" rel="home">' . esc_html(get_bloginfo('name')) . '</a></h1>';
		    echo '<p class="site-description">' . esc_html(get_bloginfo( 'description' )) . '</p>';
	    }
	  }
  }

  if ( ! function_exists( 'astrid_footer_branding' ) ) {
    function astrid_footer_branding() {
	    //$footer_logo = get_theme_mod('site_logo'); 
      $footer_logo = get_theme_mod('footer_logo');
	    echo '<div class="footer-branding">';
	    if ( $footer_logo ) :
		    echo '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr(get_bloginfo('name')) . '" style="background-image: url(' . esc_url($footer_logo) . ');"></a>'; 
	    else :
		    echo '<h2 class="site-title-footer"><a href="' . esc_url( home_url( '/' ) ) . '" rel="home">' . esc_html(get_bloginfo('name')) . '</a></h2>';
	    endif;
	    echo '</div>';
    }
  }

	/* Set pages to full-width template */
	add_filter( 'template_include', function ( $template ) {
		if(is_page()) {
			$template_path = get_template_directory() . '/page-templates/page_fullwidth.php';
		} else {
			$template_path = $template;
			if(!$template_path) $template_path = get_template_directory() . '/single.php';
		}
		
		if(isset($template_path) && file_exists($template_path)){
			include($template_path);
			exit;
		}
	}, 99 );
}

/* Header Hook */
function myhome_header_credits() {
	if(myHome()->session->guest()) return;

	echo '<div class="container">';
	echo do_shortcode( '[MyHome.ContractHeader]' );
	echo '</div>';
}
/* Footer Hook */
function myhome_footer_credits() {
	echo '<br />';
	echo ' MyHome Client Service Portal powered by <a href="http://clickhome.com.au" target="_blank">ClickHome Software</a>.';
	echo '<br />';
}

/* Permalink shortcode */
function permalink_shortcode($atts) {
	extract(shortcode_atts(array(
		'id' => 1,
		'text' => ""  // default value if none supplied
    ), $atts));
  
  if ($text) {
    $url = get_permalink($id);
    return "<a href='$url'>$text</a>";
  } else {
    return get_permalink($id);
	}
}
add_shortcode('permalink', 'permalink_shortcode');


/**
 * Register hook
 *
 * <p>It aborts the plugin activation if the PHP version is below the required version or if attempted to activate
 * network-wide</p>
 * <p>It also tries to upgrade the plugin to its latest version</p>
 *
 * @since 1.3 this method no longer throws exceptions
 * @uses  myHomeUpgradePlugin()
 */
function myHomeOnRegister(){
  if(version_compare(PHP_VERSION,'5.3.0','<'))
    die('This plugin requires PHP version 5.3.0 or greater');

  // Each site must have its own database
  if(is_network_admin())
    die('This plugin must not be activated network-wide');

  // Instantiate the MyHome class
  new MyHome; //myHome()->log->info('myHomeOnRegister()');
  
  // Not fully integrated yet
  //myHome()->admin->setupHealthCheck();

  myHomeUpgradePlugin();
}
function myHomeOnDeregister(){
  myHome()->log->info('myHomeOnDeregister()');
  //myHome()->admin->removeHealthCheck();
}

/**
 * Upgrades the database structure to its latest version and sets the MyHome version
 *
 * @since 1.3
 * @uses  MyHomeDatabase::createTables()
 */
function myHomeUpgradePlugin(){ myHome()->log->info('myHomeUpgradePlugin()' . get_option('myhome_guid'));
  myHome()->database->createTables(true);

  update_option('myhome_version', MH_VERSION);
}
