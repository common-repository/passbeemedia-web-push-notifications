<?php

class Pbm {

    public function __construct() {
        $this->add_actions();
    }
    
    public static function init() {
		self::install();
	}	

    public static $pbm_version = '1.0.0';
    
    protected static $database_version = 1000;
    
    public static function site_url() {
        return get_option( 'siteurl' );
    }
    
    public static function registration_url() {
		$tld = PBM_PS_URL . '?returnURL=';
        $admin_path = admin_url('admin.php?page=pbm-safari-push');
        $url = $tld . urlencode( $admin_path . '&source=wpplugin' );
        return $url;
    }

    public static function login_url( $sso ) {
		$tld = PBM_PS_URL . 'login?returnURL=';
        $admin_path = admin_url('admin.php?page=pbm-safari-push');
        $url = $tld . urlencode( $admin_path );
        $url = $url . '&oauth=' . $sso;
        return $url;
    }
    
    public static function pbm_settings() {
        return get_option('pbm_settings');   
    }
    
	public static function install() {
		$pbm_settings = self::pbm_settings();
        
		if ( empty( $pbm_settings ) ) {
			$pbm_settings = array(
				'appKey' => '',
				'appSecret' => '',
				'version' => self::$pbm_version,
				'autoPush' => 0,
                'bbPress' => 1
            );			
			add_option('pbm_settings', $pbm_settings);
		}
        if( self::$pbm_version !== $pbm_settings['version'] ) {
            self::update( $pbm_settings );
        }
        self::pbm_activated();
	}

    public static function update( $pbm_settings ) {
        $pbm_settings['version'] = self::$pbm_version;
        update_option('pbm_settings', $pbm_settings);        
        if( empty( $pbm_settings['database_version'] ) || $pbm_settings['database_version'] < self::$database_version ) {
            self::update_database( $pbm_settings );
        }
    }
    
    protected static function update_database( $pbm_settings ) {
        if( empty( $pbm_settings['database_version'] ) ) {
            $pbm_settings['database_version'] = 1407;
            if( empty( $pbm_settings['bbPress'] ) ) {
                $pbm_settings['bbPress'] = 1;
            }
        }
        update_option('pbm_settings', $pbm_settings);
    }
    
    public static function pbm_activated() {
        add_option('pbm_do_redirect', true);
    }

    public function activate_redirect() {
        if ( get_option('pbm_do_redirect', false) ) {
            delete_option('pbm_do_redirect');
            if( !isset( $_GET['activate-multi'] ) ){
                wp_redirect( admin_url( 'admin.php?page=pbm-safari-push' ) );
                exit;
            }
        }
    }
    
	public static function uninstall(){
        delete_option('pbm_settings');
        delete_post_meta_by_key( 'pbmOverride' );
        delete_post_meta_by_key( 'pbmForce' );
        delete_post_meta_by_key( 'pbm_bbp_subscription' );
	}

    public function add_actions() {
        add_action( 'transition_post_status', array( $this, 'build_note' ), 10, 3 );
        add_action( 'post_submitbox_misc_actions', array( $this, 'note_override' ) );
        add_action( 'wp_head', array( $this, 'byline' ), 1 );
        add_action( 'wp_footer', array( $this, 'pbmJS' ) );
        add_action( 'save_post', array( $this, 'save_post_meta_pbm' ) );
        add_filter( 'clean_url', array( $this, 'add_async' ), 2, 1 );
        add_action( 'wp_ajax_graph_reload', array( $this, 'graph_reload' ) );
        add_action( 'wp_ajax_nopriv_graph_reload', array( $this, 'graph_reload' ) );
        add_action( 'wp_ajax_subs_check', array( $this, 'subs_check' ) );
        add_action( 'wp_ajax_nopriv_subs_check', array( $this, 'subs_check' ) );
        
        if ( is_admin() ) {
            add_filter( 'plugin_action_links_pbm-safari-push/pbm.php', array( $this, 'add_action_links' ) );
            add_action( 'admin_init', array( $this, 'activate_redirect' ) );
            add_action( 'admin_init', array( $this, 'pbm_logout' ) );
            add_action( 'admin_init', array( $this, 'manual_send' ) );
            add_action( 'admin_notices', array( $this, 'setup_notice' ) );
            add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );
            add_action( 'admin_menu', array( $this, 'admin_menu_add' ) );
        }
    }

    public function add_action_links ( $links ) {
        $rlink = array(
            '<a href="' . admin_url( 'admin.php?page=pbm-safari-push' ) . '">Go to Plugin</a>',
        );
        return array_merge( $rlink, $links );
    }

    public function add_async( $url ) {
        if ( false === strpos( $url, '#async' ) ) {
            return $url;
        } else if ( is_admin() ) {
            return str_replace( '#async', '', $url );
        } else {
            return str_replace( '#async', '', $url )."' async data-pbm='true";
        }
    }

    public function byline() {
        $byline = "<!-- Push notifications for this website enabled by Passbeemedia. Support for Safari, Firefox, and Chrome Browser Push. (v ". self::$pbm_version .") - http://passbeemedia.com/ -->";
        echo "\n${byline}\n";
    }

    public function pbmJS() {
        $pbm_settings = self::pbm_settings();
        $app_key = $pbm_settings['appKey'];
    ?>
        <script>var _apikey = "<?php echo $app_key; ?>";</script><script src="//webpush.passbeemedia.com/assets/js/pbm_push.js" async></script>
    <?php
    }
    
    public static function setup_notice() {
        global $hook_suffix;
        $pbm_page = 'toplevel_page_pbm-safari-push';
        
        $pbm_settings = self::pbm_settings();
        $app_key = $pbm_settings['appKey'];

        if ( !$app_key && ( $hook_suffix !== $pbm_page ) ) {
    ?>
		<div class="updated" id="pbmSetupNotice">
            <div id="pbmNoticeLogo">
                <img src="<?php echo( PBM_URL . 'layout/images/pbm_logo.png' ) ?>" />
            </div>
            <div id="pbmNoticeText">
                <p>
                    Thanks for installing the Passbeemedia plugin! You’re almost finished with<br />setup, all you need to do is create an account and login.
                </p>
            </div>
            <div id="pbmNoticeTarget">
                <a href="<?php echo( admin_url( 'admin.php?page=pbm-safari-push' ) ); ?>" id="pbmNoticeCTA" >
                    <span id="pbmNoticeCTAHighlight"></span>
                    Finish Setup
                </a>
            </div>
		</div>    
    <?php
        } else if ( !$app_key && ( $hook_suffix === $pbm_page ) ) {
            $api_check = Pbm_API::api_check();
            if ( is_wp_error( $api_check ) ) {
    ?>
        <div class="error" id="pbm-api-error">There was a problem accessing the <strong>Passbeemedia API</strong>. You may not be able to log in. Contact Passbeemedia support at <a href="mailto:support@passbeemedia.com" target="_blank">support@passbeemedia.com</a> for more information.</div>
    <?php
            }
        }
    }

	public function admin_menu_add(){
	    add_menu_page(
        	'Pbm Safari Push',
	        'Pbm Safari Push',
	        'manage_options',
            'pbm-safari-push',
        	array( __CLASS__, 'admin_menu_page' ),
	        PBM_URL . 'layout/images/pbm_thumb.png'
	    );
	}    

	public static function admin_scripts() {
        wp_enqueue_style( 'pbmstyle', PBM_URL . 'layout/css/pbmstyle.css', '', self::$pbm_version );
        wp_enqueue_script( 'pbmGoogleFont', PBM_URL . 'layout/js/pbmGoogleFont.js', '', self::$pbm_version, false );
        $pbm_settings = self::pbm_settings();
        $app_key = $pbm_settings['appKey'];
        if ( !empty( $app_key ) ) {
            wp_enqueue_style( 'morrisstyle', PBM_URL . 'layout/css/morris-0.4.3.min.css', '', self::$pbm_version );
            wp_enqueue_script( 'morrisscript', PBM_URL . 'layout/js/morris-0.4.3.min.js', array('jquery', 'raphael'), self::$pbm_version );
            wp_enqueue_script( 'raphael', PBM_URL . 'layout/js/raphael-min-2.1.0.js', array('jquery'), self::$pbm_version );
            wp_enqueue_script( 'pbmscript', PBM_URL . 'layout/js/pbmscript.js', array('jquery'), self::$pbm_version, true );
        }
    }
	
	public static function update_keys( $form_keys ){
		$pbm_settings = self::pbm_settings();
		$pbm_settings['appKey'] = $form_keys['appKey'];
		$pbm_settings['appSecret'] = $form_keys['appSecret'];
		update_option('pbm_settings', $pbm_settings);
	}
    
	public static function update_settings($form_data){	
		$pbm_settings = self::pbm_settings();
		$pbm_settings['autoPush'] = $form_data['autoPush'];
		$pbm_settings['bbPress'] = $form_data['bbPress'];
		update_option('pbm_settings', $pbm_settings);
	}

    public function save_post_meta_pbm( $post_id ) {
        if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || empty( $_POST['hiddenPbm'] ) ) {
            return false;
        } else {
            $no_note = get_post_meta( $post_id, 'bmOverride', true );
            $send_note = get_post_meta( $post_id, 'pbmForce', true );
            if ( isset( $_POST['pbmOverride'] ) && !$no_note ) {
                $override_setting = $_POST['pbmOverride'];
                add_post_meta($post_id, 'pbmOverride', $override_setting, true);
            } elseif ( !isset( $_POST['pbmOverride'] ) && $no_note ) {
                delete_post_meta( $post_id, 'pbmOverride' );
            }
            if ( isset( $_POST['pbmForce'] ) && !$send_note ) {
                $override_setting = $_POST['pbmForce'];
                add_post_meta( $post_id, 'pbmForce', $override_setting, true );
            } elseif ( !isset( $_POST['pbmForce'] ) && $send_note ) {
                delete_post_meta( $post_id, 'pbmForce' );
            }
        }
    }

    public static function filter_string( $string ) {
        $string = str_replace( '&#8220;', '&quot;', $string );
        $string = str_replace( '&#8221;', '&quot;', $string );
        $string = str_replace( '&#8216;', '&#39;', $string );
        $string = str_replace( '&#8217;', '&#39;', $string );
        $string = str_replace( '&#8211;', '-', $string );
        $string = str_replace( '&#8212;', '-', $string );
        $string = str_replace( '&#8242;', '&#39;', $string );
        $string = str_replace( '&#8230;', '...', $string );
        $string = str_replace( '&prime;', '&#39;', $string );
        return html_entity_decode( $string, ENT_QUOTES );
    }

    public function build_note( $new_status, $old_status, $post ) {
		if ( $new_status != $old_status && !empty( $post ) ) {
		    $post_type = get_post_type( $post );
		    if ( 'post' === $post_type && 'publish' === $new_status ) {
				$post_id = $post->ID;
				$pbm_settings = self::pbm_settings();
				$app_key = $pbm_settings['appKey'];
				$app_secret = $pbm_settings['appSecret'];
				$auto_push = $pbm_settings['autoPush'];
                
				if ( !empty( $app_key ) ) {	
					if ( ( 'publish' === $new_status && 'future' === $old_status ) || empty( $_POST['hiddenPbm'] ) ) {
						$override = get_post_meta( $post_id, 'pbmOverride', true );
                        $send_note = get_post_meta( $post_id, 'pbmForce', true );
					} else {
                        if ( isset( $_POST['pbmOverride'] ) ) {
                            $override = $_POST['pbmOverride'];
                        }
                        if( isset( $_POST['pbmForce'] ) ) {
                            $send_note = $_POST['pbmForce'];
                        }
					}
                }
                if ( ( 1 == $auto_push || !empty( $send_note ) ) && !empty( $app_key ) ) {
					if ( empty( $override ) ) {
						$alert = get_the_title( $post_id );
						$url = wp_get_shortlink( $post_id );
						if ( has_post_thumbnail($post_id)) {
						    $raw_image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id));
						    $image_url = $raw_image[0];
						} else {
						    $image_url = null;
						}
						Pbm_API::send_notification($alert, $url, $image_url, $app_key, $app_secret, null );
					}
				}
			}
        }
    }

	public function note_override(){
        global $post;
        if ( 'post' === $post->post_type ) {
            if ( 'publish' == $post->post_status ) {
                $check_hidden = true;   
            }          
            $pbm_settings = self::pbm_settings();
            $app_key = $pbm_settings['appKey'];
            $auto_push = $pbm_settings['autoPush'];
            if ( !empty( $app_key ) ) {
                printf('<div class="misc-pub-section misc-pub-section-last" id="pbm-post-checkboxes" %s >', ( isset( $check_hidden ) ) ? "style='display:none;'":"" );
                $pid = get_the_ID();
                if( 1 == $auto_push ) {
                    $checked = get_post_meta($pid, 'pbmOverride', true);
                    printf('<label><input type="checkbox" value="1" id="pbm-override-checkbox" name="pbmOverride" %s />', ( !empty( $checked ) ) ? "checked":"" );
                    echo '<strong>Do NOT</strong> send notification with <strong>Passbeemedia</strong></label>';
                } else {
                    $checked = get_post_meta($pid, 'pbmForce', true);
                    printf('<label><input type="checkbox" value="1" id="pbm-forced-checkbox" name="pbmForce" %s />', ( !empty( $checked ) ) ? "checked":"" );
                    echo '<strong>Send</strong> notification with <strong>Passbeemedia</strong></label>';
                }
                echo '<input type="hidden" name="hiddenPbm" value="true" />';
                echo '</div>';
            }
        }
	}
	
    public static function complete_login( $logged_in, $site ) {
        if ( !empty( $logged_in ) ) {
            if ($logged_in['success'] ) {
                if ( count( $logged_in['apps'] ) > 1 ){
                    $pbm_sites = $logged_in['apps'];
                    return $pbm_sites;
                } else {
                    $form_keys = array(
                        'appKey' => $logged_in['apps'][0]['key'],
                        'appSecret' => $logged_in['apps'][0]['secret'],
                    );
                }
            }
        } elseif ( !empty( $site ) ) {
            $site_key = $site[0];
            $site_secret = $site[1];
            $form_keys = array(
                'appKey' => $site_key,
                'appSecret' => $site_secret,
            );
        }
    
        $response = array();

        if ( !empty( $form_keys ) ) {
            self::update_keys( $form_keys );
            $response['status'] = true;
            $response['firstTime'] = true;
            $response['server_settings'] = Pbm_API::get_server_settings( $form_keys['appKey'], $form_keys['appSecret'] );	
            $response['stats'] = Pbm_API::get_stats( $form_keys['appKey'], $form_keys['appSecret'] );
            self::admin_scripts();
        } else {
            $response['status'] = 'Please check your Email or Username and Password.';
            $response['stats'] = null;
            $response['server_settings'] = null;
        }
        return $response;
    }

    public function graph_reload() {
        $pbm_settings = self::pbm_settings();
        $app_key = $pbm_settings['appKey'];
        $app_secret = $pbm_settings['appSecret'];
        $type = $_POST['type'];
        $range = $_POST['range'];
        $value = $_POST['value'];
        $time_offset = $_POST['offset'];
        $pbm_graph_data = Pbm_API::get_graph_data( $app_key, $app_secret, $type, $range, $value, $time_offset );
        $pbm_graph_data = json_encode( $pbm_graph_data );
        echo $pbm_graph_data;
        die();                
    }
    
    public function subs_check() {
        $pbm_settings = self::pbm_settings();
        $app_key = $pbm_settings['appKey'];
        $app_secret = $pbm_settings['appSecret'];
        $pbm_stats = Pbm_API::get_stats( $app_key, $app_secret );
        $pbm_subs = json_encode( $pbm_stats['registrations'] );
        echo $pbm_subs;
        die();
    }

    public function pbm_logout() {
        if ( isset( $_POST['clearkey'] ) ) {
            $form_keys = array(
                'appKey' => '',
                'appSecret' => '',
            );
            self::update_keys( $form_keys );
            wp_dequeue_script( 'pbmscript' );
            $status = 'Passbeemedia has been disconnected.';
            $status = urlencode( $status );
            wp_redirect( admin_url( 'admin.php?page=pbm-safari-push' ) . '&status=' . $status );
            exit;
        }
    }
    
    public function manual_send() {
        if ( isset( $_POST['manualtext'] ) ) {
            $manual_text = $_POST['manualtext'];
	        $manual_link = $_POST['manuallink'];
            $manual_text = stripslashes( $manual_text );
            if ( '' == $manual_text || '' == $manual_link ) {
                $status = 'Your message or link can not be blank.';
            } else {
                $pbm_settings = self::pbm_settings();
                $app_key = $pbm_settings['appKey'];
                $app_secret = $pbm_settings['appSecret'];
                if ( false === strpos( $manual_link, 'http' ) ) {
                    $manual_link = 'http://' . $manual_link;
                }
                $msg_status = Pbm_API::send_notification( $manual_text, $manual_link, null, $app_key, $app_secret, null );
                if ( true === $msg_status['success'] ) {
                    $status = 'Message Sent.';
                } else {
                    $status = 'Message failed. Please make sure you have a valid URL.';
                }
			}
            $status = urlencode( $status );
            wp_redirect( admin_url( 'admin.php?page=pbm-safari-push' ) . '&status=' . $status );
            exit;
        }        
    }

    public static function admin_menu_page() {
        $pbm_settings = self::pbm_settings();
                
        if ( empty( $pbm_settings ) ) {
            self::install();
        } else {
            $app_key = $pbm_settings['appKey'];
            $app_secret = $pbm_settings['appSecret'];
        }
        
        if ( !empty( $app_key ) ) {
            $pbm_active_key = true;
            $bbPress_active = Pbm_bbPress::bbPress_active();
        } else {
            $pbm_active_key = false;
        }
        
        if ( !empty( $app_key ) && empty( $pbm_server_settings ) ) {
            $pbm_server_settings = Pbm_API::get_server_settings( $app_key, $app_secret );	
            $pbm_stats = Pbm_API::get_stats( $app_key, $app_secret );
        }

        if ( empty( $app_key ) && isset( $_GET['pbm_token'] ) ) {
            $pbm_token = $_GET['pbm_token'];
            $pbm_token = urldecode($pbm_token);
            $logged_in = Pbm_API::login( null, null, $pbm_token );
            $response = self::complete_login( $logged_in, null );
            $first_time = $response['firstTime'];
            $pbm_server_settings = $response['server_settings'];	
            $pbm_stats = $response['stats'];
            $pbm_active_key = true;
        }
        
	    if ( isset( $_POST['pbmlogin'] ) ) {
            $pbm_user = $_POST['pbmuserlogin'];
            $pbm_pass = $_POST['pbmpasslogin'];
            $logged_in = Pbm_API::login( $pbm_user, $pbm_pass, null );
            $response = self::complete_login( $logged_in, null );
            if( empty( $response['status'] ) ) {
                $pbm_sites = $response;
            } else {
                if( !empty( $response['firstTime'] ) ) {
                    $first_time = $response['firstTime'];
                    $pbm_server_settings = $response['server_settings'];	
                    $pbm_stats = $response['stats'];
                    $pbm_active_key = true;
                } else {
                    $status = $response['status'];
                }
            }
		}

	    if ( isset( $_POST['pbmconfigselect'] ) ) {
            $selected_site = $_POST['pbmsites'];
            $site = explode( '|', $selected_site );
            $response = self::complete_login( null, $site );
            $first_time = $response['firstTime'];
            $pbm_server_settings = $response['server_settings'];	
            $pbm_stats = $response['stats'];
            $pbm_active_key = true;
		}

        if ( isset( $_GET['status'] ) ) {
            $status = urldecode( $_GET['status'] );
        }
        
	    if ( isset( $_POST['savesettings'] ) ) {	
            $autoPush = false;
            $bbPress = false;
            
            if ( isset( $_POST['autoPush'] ) ) {
                $autoPush = true;
            }
            if ( isset( $_POST['bbPress'] ) ) {
                $bbPress = true;
            }
            
            $form_data = array(
                'autoPush' => $autoPush,
                'bbPress' => $bbPress,
            );
            self::update_settings( $form_data );

            Pbm_API::save_remote_settings( $app_key, $app_secret, $pbm_server_settings, $_POST );
            $pbm_server_settings = Pbm_API::get_server_settings( $app_key, $app_secret );	
            $pbm_stats = Pbm_API::get_stats( $app_key, $app_secret );
	        $status = 'Settings Saved.';
	    }
		
	    require_once( dirname( plugin_dir_path( __FILE__ ) ) . '/layout/admin.php');		
	}
}
