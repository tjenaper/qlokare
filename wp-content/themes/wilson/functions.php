<?php

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}

// Theme setup
add_action( 'after_setup_theme', 'wilson_setup' );

function wilson_setup() {
	
	// Automatic feed
	add_theme_support( 'automatic-feed-links' );
	
	// Post thumbnails
	add_theme_support( 'post-thumbnails' ); 
	add_image_size( 'post-image', 788, 9999 );
	
	// Post formats
	add_theme_support( 'post-formats', array( 'video', 'aside', 'quote' ) );
	
	// Title tag function
	add_theme_support('title-tag');

	// Add nav menu
	register_nav_menu( 'primary', 'Primary Menu' );
	
	// Make the theme translation ready
	load_theme_textdomain('wilson', get_template_directory() . '/languages');
	
	$locale = get_locale();
	$locale_file = get_template_directory() . "/languages/$locale.php";
	if ( is_readable($locale_file) )
	  require_once($locale_file);
	
}

// Enqueue Javascript files
function wilson_load_javascript_files() {

	if ( !is_admin() ) {
		wp_enqueue_script( 'jquery' );	
		wp_enqueue_script( 'wilson_global', get_template_directory_uri().'/js/global.js', array('jquery'), '', true );
		if ( is_singular() ) wp_enqueue_script( "comment-reply" );
	}
}

add_action( 'wp_enqueue_scripts', 'wilson_load_javascript_files' );


// Enqueue styles
function wilson_load_style() {
	if ( !is_admin() ) {
	    wp_enqueue_style( 'wilson_googleFonts', '//fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic|Raleway:400,700' );
	    wp_enqueue_style( 'wilson_style', get_stylesheet_uri() );
	}
}

add_action('wp_enqueue_scripts', 'wilson_load_style');


// Add editor styles
function wilson_add_editor_styles() {
    add_editor_style( 'wilson-editor-styles.css' );
    $font_url = '//fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic|Raleway:400,700';
    add_editor_style( str_replace( ',', '%2C', $font_url ) );
}
add_action( 'init', 'wilson_add_editor_styles' );


// Add footer widget areas
add_action( 'widgets_init', 'wilson_sidebar_reg' ); 

function wilson_sidebar_reg() {
	register_sidebar(array(
		'name' => __( 'Sidebar', 'wilson' ),
		'id' => 'sidebar',
		'description' => __( 'Widgets in this area will be displayed in the sidebar.', 'wilson' ),
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
		'before_widget' => '<div class="widget %2$s"><div class="widget-content">',
		'after_widget' => '</div><div class="clear"></div></div>'
	));
	register_sidebar(array(
		'name' => __( 'Footer A', 'wilson' ),
		'id' => 'footer-a',
		'description' => __( 'Widgets in this area will be displayed in the left column in the footer.', 'wilson' ),
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
		'before_widget' => '<div class="widget %2$s"><div class="widget-content">',
		'after_widget' => '</div><div class="clear"></div></div>'
	));	
	register_sidebar(array(
		'name' => __( 'Footer B', 'wilson' ),
		'id' => 'footer-b',
		'description' => __( 'Widgets in this area will be displayed in the right column in the footer.', 'wilson' ),
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
		'before_widget' => '<div class="widget %2$s"><div class="widget-content">',
		'after_widget' => '</div><div class="clear"></div></div>'
	));
}
	
// Add theme widgets
require_once (get_template_directory() . "/widgets/dribbble-widget.php");  
require_once (get_template_directory() . "/widgets/flickr-widget.php");  
require_once (get_template_directory() . "/widgets/video-widget.php");


// Set content-width
if ( ! isset( $content_width ) ) $content_width = 788;


// Add classes to next_posts_link and previous_posts_link
add_filter('next_posts_link_attributes', 'wilson_posts_link_attributes_1');
add_filter('previous_posts_link_attributes', 'wilson_posts_link_attributes_2');

function wilson_posts_link_attributes_1() {
    return 'class="post-nav-older"';
}
function wilson_posts_link_attributes_2() {
    return 'class="post-nav-newer"';
}


// Menu walker adding "has-children" class to menu li's with children menu items
class wilson_nav_walker extends Walker_Nav_Menu {
    function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {
        $id_field = $this->db_fields['id'];
        if ( !empty( $children_elements[ $element->$id_field ] ) ) {
            $element->classes[] = 'has-children';
        }
        Walker_Nav_Menu::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }
}


// Add class to body if the post/page has a featured image
add_action('body_class', 'wilson_if_featured_image_class' );

function wilson_if_featured_image_class($classes) {
	if(current_theme_supports('post-thumbnails'))
		if(has_post_thumbnail())
			$classes[] = "has-featured-image";
	
	return $classes;
}	add_filter('post_class', "wilson_if_featured_image_class");


// Custom more-link text
add_filter( 'the_content_more_link', 'wilson_custom_more_link', 10, 2 );

function wilson_custom_more_link( $more_link, $more_link_text ) {
	return str_replace( $more_link_text, __('Continue reading', 'wilson'), $more_link );
}

function wilson_meta() { ?>
	
	<div class="post-meta">
	
		<span class="post-date"><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_time(get_option('date_format')); ?></a></span>
		
		<span class="date-sep"> / </span>
			
		<span class="post-author"><?php the_author_posts_link(); ?></span>
		
		<?php if ( comments_open() ) : ?>
		
			<span class="date-sep"> / </span>
			
			<?php comments_popup_link( '<span class="comment">' . __( '0 Comments', 'wilson' ) . '</span>', __( '1 Comment', 'wilson' ), __( '% Comments', 'wilson' ) ); ?>
		
		<?php endif; ?>
		
		<?php if( is_sticky() && !has_post_thumbnail() ) { ?> 
		
			<span class="date-sep"> / </span>
		
			<?php _e('Sticky', 'wilson'); ?>
		
		<?php } ?>
		
		<?php if ( current_user_can( 'manage_options' ) ) { ?>
		
			<span class="date-sep"> / </span>
						
			<?php edit_post_link(__('Edit', 'wilson')); ?>
		
		<?php } ?>
								
	</div> <!-- /post-meta -->

<?php 
}


// Style the admin area
function wilson_custom_colors() {
   echo '<style type="text/css">
   
#postimagediv #set-post-thumbnail img {
	max-width: 100%;
	height: auto;
}

         </style>';
}

add_action('admin_head', 'wilson_custom_colors');


// Wilson comment function
if ( ! function_exists( 'wilson_comment' ) ) :
function wilson_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
	?>
	
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
	
		<?php __( 'Pingback:', 'wilson' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'wilson' ), '<span class="edit-link">', '</span>' ); ?>
		
	</li>
	<?php
			break;
		default :
		global $post;
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
	
		<div id="comment-<?php comment_ID(); ?>" class="comment">
		
			<div class="comment-meta comment-author vcard">
							
				<?php echo get_avatar( $comment, 120 ); ?>

				<div class="comment-meta-content">
											
					<?php printf( '<cite class="fn">%1$s %2$s</cite>',
						get_comment_author_link(),
						( $comment->user_id === $post->post_author ) ? '<span class="post-author"> ' . __( '(Post author)', 'wilson' ) . '</span>' : ''
					); ?>
					
					<p><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ) ?>"><?php echo get_comment_date() . ' at ' . get_comment_time() ?></a></p>
					
				</div> <!-- /comment-meta-content -->
				
			</div> <!-- /comment-meta -->

			<div class="comment-content post-content">
			
				<?php if ( '0' == $comment->comment_approved ) : ?>
				
					<p class="comment-awaiting-moderation"><?php _e( 'Awaiting moderation', 'wilson' ); ?></p>
					
				<?php endif; ?>
			
				<?php comment_text(); ?>
				
				<div class="comment-actions">
				
					<?php edit_comment_link( __( 'Edit', 'wilson' ), '', '' ); ?>
					
					<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'wilson' ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
					
					<div class="clear"></div>
				
				</div> <!-- /comment-actions -->
				
			</div><!-- /comment-content -->

		</div><!-- /comment-## -->
	<?php
		break;
	endswitch;
}
endif;



// Wilson theme options

class Wilson_Customize {

   public static function register ( $wp_customize ) {
   
      //1. Define a new section (if desired) to the Theme Customizer
      $wp_customize->add_section( 'wilson_options', 
         array(
            'title' => __( 'Wilson Options', 'wilson' ), //Visible title of section
            'priority' => 35, //Determines what order this appears in
            'capability' => 'edit_theme_options', //Capability needed to tweak
            'description' => __('Allows you to customize theme options for Wilson.', 'wilson'),
         ) 
      );
      
	  $wp_customize->add_section( 'wilson_logo_section' , array(
		    'title'       => __( 'Logo', 'wilson' ),
		    'priority'    => 40,
		    'description' => __('Upload a logo to replace the default site name and description in the sidebar','wilson'),
	  ) );
      
      //2. Register new settings to the WP database...
      $wp_customize->add_setting( 'accent_color', //No need to use a SERIALIZED name, as `theme_mod` settings already live under one db record
         array(
            'default' => '#FF706C', //Default setting/value to save
            'type' => 'theme_mod', //Is this an 'option' or a 'theme_mod'?
            'capability' => 'edit_theme_options', //Optional. Special permissions for accessing this setting.
            'transport' => 'postMessage', //What triggers a refresh of the setting? 'refresh' or 'postMessage' (instant)?
            'sanitize_callback' => 'sanitize_hex_color'            
         ) 
      );
      
      $wp_customize->add_setting( 'wilson_logo', 
      	array( 
      		'sanitize_callback' => 'esc_url_raw'
      	) 
      );
            
      //3. Finally, we define the control itself (which links a setting to a section and renders the HTML controls)...
      $wp_customize->add_control( new WP_Customize_Color_Control( //Instantiate the color control class
         $wp_customize, //Pass the $wp_customize object (required)
         'wilson_accent_color', //Set a unique ID for the control
         array(
            'label' => __( 'Accent Color', 'wilson' ), //Admin-visible name of the control
            'section' => 'colors', //ID of the section this control should render in (can be one of yours, or a WordPress default section)
            'settings' => 'accent_color', //Which setting to load and manipulate (serialized is okay)
            'priority' => 10, //Determines the order this control appears in for the specified section
         ) 
      ) );
      
      $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'wilson_logo', array(
		    'label'    => __( 'Logo', 'wilson' ),
		    'section'  => 'wilson_logo_section',
		    'settings' => 'wilson_logo',
	  ) ) );
      
      //4. We can also change built-in settings by modifying properties. For instance, let's make some stuff use live preview JS...
      $wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
      $wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
      $wp_customize->get_setting( 'accent_color' )->transport = 'postMessage';
      $wp_customize->get_setting( 'background_color' )->transport = 'postMessage';
   }

   public static function header_output() {
      ?>
      
	      <!--Customizer CSS--> 
	      
	      <style type="text/css">
	           <?php self::generate_css('.blog-title a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.blog-menu a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.blog-menu .current-menu-item > a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.featured-media .sticky-post', 'background-color', 'accent_color'); ?>
	           <?php self::generate_css('.post-title a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.post-meta a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.post-content a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.post-content a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.blog .format-quote blockquote cite a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.post-content a.more-link:hover', 'background-color', 'accent_color'); ?>
	           <?php self::generate_css('.post-content fieldset legend', 'background-color', 'accent_color'); ?>
	           <?php self::generate_css('.post-content input[type="submit"]:hover', 'background-color', 'accent_color'); ?>
	           <?php self::generate_css('.post-content input[type="reset"]:hover', 'background-color', 'accent_color'); ?>
	           <?php self::generate_css('.post-content input[type="button"]:hover', 'background-color', 'accent_color'); ?>
	           <?php self::generate_css('.content .button:hover', 'background-color', 'accent_color'); ?>
	           <?php self::generate_css('.post-cat-tags a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.post-cat-tags a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.archive-nav a:hover', 'background-color', 'accent_color'); ?>
	           <?php self::generate_css('.logged-in-as a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.logged-in-as a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.content #respond input[type="submit"]:hover', 'background-color', 'accent_color'); ?>
	           <?php self::generate_css('.comment-meta-content cite a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.comment-meta-content p a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.comment-actions a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('#cancel-comment-reply-link', 'color', 'accent_color'); ?>
	           <?php self::generate_css('#cancel-comment-reply-link:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.comment-nav-below a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget-title a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget-title a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_text a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_text a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_rss a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_rss a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_archive a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_archive a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_links a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_links a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_recent_comments a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_recent_comments a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_recent_entries a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_recent_entries a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_categories a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_categories a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_meta a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_meta a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_recent_comments a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_pages a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widget_pages a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('#wp-calendar a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('#wp-calendar a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('#wp-calendar tfoot a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.dribbble-shot:hover', 'background', 'accent_color'); ?>
	           <?php self::generate_css('.widgetmore a', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.widgetmore a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.flickr_badge_image a:hover img', 'background', 'accent_color'); ?>
	           <?php self::generate_css('.tagcloud a:hover', 'background', 'accent_color'); ?>
	           <?php self::generate_css('.credits a:hover', 'color', 'accent_color'); ?>
	           <?php self::generate_css('.mobile-menu a:hover', 'background', 'accent_color'); ?>
	      </style> 
	      
	      <!--/Customizer CSS-->
	      
      <?php
   }
   
   public static function live_preview() {
      wp_enqueue_script( 
           'wilson-themecustomizer', // Give the script a unique ID
           get_template_directory_uri() . '/js/theme-customizer.js', // Define the path to the JS file
           array(  'jquery', 'customize-preview' ), // Define dependencies
           '', // Define a version (optional) 
           true // Specify whether to put in footer (leave this true)
      );
   }

   public static function generate_css( $selector, $style, $mod_name, $prefix='', $postfix='', $echo=true ) {
      $return = '';
      $mod = get_theme_mod($mod_name);
      if ( ! empty( $mod ) ) {
         $return = sprintf('%s { %s:%s; }',
            $selector,
            $style,
            $prefix.$mod.$postfix
         );
         if ( $echo ) {
            echo $return;
         }
      }
      return $return;
    }
}

// Setup the Theme Customizer settings and controls...
add_action( 'customize_register' , array( 'Wilson_Customize' , 'register' ) );

// Output custom CSS to live site
add_action( 'wp_head' , array( 'Wilson_Customize' , 'header_output' ) );

// Enqueue live preview javascript in Theme Customizer admin screen
add_action( 'customize_preview_init' , array( 'Wilson_Customize' , 'live_preview' ) );

?>