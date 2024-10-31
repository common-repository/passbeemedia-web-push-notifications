<?php

class Pbm_bbPress {

    public static function bbPress_active() {
        $pbm_settings = Pbm::pbm_settings();
        $bbPress = array(
            'present' => false,
            'enabled' => $pbm_settings['bbPress'],
        );
        if( class_exists( 'bbPress' ) ) {
            $bbPress['present'] = true;
        }
        return $bbPress;
    }
    
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'load_pbm_bbp_scripts' ), 1 );

        add_action( 'wp_ajax_pbm_bbp_subscribe', array( $this, 'pbm_bbp_ajax_subscription' ) );
        add_action( 'wp_ajax_nopriv_pbm_bbp_subscribe', array( $this, 'pbm_bbp_ajax_subscription' ) );
        add_action( 'wp_ajax_pbm_bbp_unsubscribe', array( $this, 'pbm_bbp_ajax_subscription' ) );
        add_action( 'wp_ajax_nopriv_pbm_bbp_unsubscribe', array( $this, 'pbm_bbp_ajax_subscription' ) );

        add_action( 'bbp_theme_before_reply_form_submit_wrapper', array( $this, 'pbm_bbp_reply_subscription' ) );
        add_action( 'bbp_theme_before_topic_form_submit_wrapper', array( $this, 'pbm_bbp_reply_subscription' ) );
        add_action( 'bbp_template_before_single_topic', array( $this, 'pbm_bbp_topic_subscription' ) );        
        add_action( 'bbp_template_before_single_forum', array( $this, 'pbm_bbp_forum_subscription' ) );
        add_action( 'bbp_get_request', array( $this, 'pbm_bbp_subscriptions_handler' ), 1 );
        add_action( 'bbp_new_reply', array( $this, 'pbm_bbp_subscriptions_handler' ), 1 );
        add_action( 'bbp_edit_reply', array( $this, 'pbm_bbp_subscriptions_handler' ), 1 );
        add_action( 'bbp_new_topic', array( $this, 'pbm_bbp_subscriptions_handler' ), 1 );
        add_action( 'bbp_edit_topic', array( $this, 'pbm_bbp_subscriptions_handler' ), 1 );
        
        add_action( 'bbp_new_reply', array( $this, 'pbm_bbp_notify_subscribers' ), 11, 5 );
        add_action( 'bbp_new_topic', array( $this, 'pbm_bbp_notify_subscribers' ), 11, 5 );

        add_action( 'bbp_delete_reply', array( $this, 'pbm_bbp_remove_all_subscriptions' ) );
        add_action( 'bbp_delete_topic', array( $this, 'pbm_bbp_remove_all_subscriptions' ) );
        add_action( 'bbp_delete_forum', array( $this, 'pbm_bbp_remove_all_subscriptions' ) );
    }

    public function load_pbm_bbp_scripts() {
        wp_enqueue_script( 'pbmbbp', pbm_URL . 'layout/js/pbmbbp.js', array('jquery'), Pbm::$pbm_version, false );
	}
    
    public function pbm_bbp_reply_subscription( $post ) {
        global $post;
        $pbm_bbp_subscriptions = get_post_meta( $post->ID, 'pbm_bbp_subscription', true );
    ?>
        <div class="pbm-bbp-reply-subscription-wrap" style="display:none;">
            <input type="checkbox" value='1' name="pbm-bbp-subscription" class="pbm-bbp-reply-subscription" />
            <label for="pbm-bbp-subscription">Notify me of follow-up replies via <strong>desktop push notifications.</strong></label>
            <input type="hidden" name="pbm-bbp-device-token" id="pbm-bbp-device-token" value="" />
        </div>
        <script>
            jQuery(document).ready(function($) {
                setTimeout(function(){
                    if(window.pbmEnabled){
                        jQuery('.pbm-bbp-reply-subscription-wrap').show();
                        jQuery('#pbm-bbp-device-token').val(window.pbmToken);                        
                        <?php
                            if ( !empty( $pbm_bbp_subscriptions ) ) {
                                $reply_bbp_subscriptions = json_encode( $pbm_bbp_subscriptions );
                        ?>
                                var registrations = <?php echo( $reply_bbp_subscriptions ); ?>;
                                if (typeof registrations[window.pbmToken] !== 'undefined') {
                                    if(registrations[window.pbmToken] === true) {
                                        jQuery('.pbm-bbp-reply-subscription').prop('checked', true);
                                    }
                                }
                        <?php
                            }
                        ?>
                    }
                }, 1500);
            });
        </script>
    <?php
    }

    public function pbm_bbp_topic_subscription( $post ) {
        global $post;
        $post_id = $post->ID;

        $url = bbp_get_topic_permalink( $post_id );

        $pbm_bbp_subscriptions = get_post_meta( $post_id, 'pbm_bbp_subscription', true );
        
        echo( sprintf( "<span id='pbm-subscribe-%d' style='display:none;'><a href='%s' data-post='%d' class='pbm-topic-subscribe-link'></a></span>", $post_id, $url, $post_id ) );
        ?>
        <script>
            jQuery(document).ready(function($) {
                var subscribeLink = $('.pbm-topic-subscribe-link');
                var subscribeWrap = $('#pbm-subscribe-<?php echo( $post_id ); ?>');
                <?php
                    if ( !empty( $pbm_bbp_subscriptions ) ) {
                        $reply_bbp_subscriptions = json_encode( $pbm_bbp_subscriptions );
                ?>
                        var registrations = <?php echo( $reply_bbp_subscriptions ); ?>;
                <?php        
                    } else {
                ?>
                        var registrations = [];
                <?php
                    }
                ?>
                
                setTimeout(function(){
                    if(window.pbmEnabled){
                        if (typeof registrations[window.pbmToken] !== 'undefined') {
                            if(registrations[window.pbmToken] === true) {
                                subscribeLink.text('Unsubscribe from Push Notifications');
                                subscribeLink.data('action', 'pbm_bbp_unsubscribe');
                            }
                        } else {
                            subscribeLink.text('Subscribe with Push Notifications');
                            subscribeLink.data('action', 'pbm_bbp_subscribe');
                        }
                        <?php
                            if( true === bbp_is_subscriptions_active() && true === is_user_logged_in() ) {
                        ?>
                            subscribeWrap.detach().appendTo('#subscription-toggle').show();
                            subscribeWrap.prepend(' | ');
                        <?php
                            } else if( true === bbp_is_favorites_active() && true === is_user_logged_in() ) {
                        ?>
                            subscribeWrap.detach();                        
                            subscribeWrap.appendTo('.bbp-header .bbp-reply-content').append(' | ').wrap("<div id='subscription-toggle'></div>").show();
                        <?php
                            } else {
                        ?>
                            subscribeWrap.detach();
                            subscribeWrap.appendTo('.bbp-header .bbp-reply-content').wrap("<div id='subscription-toggle'></div>").show();
                        <?php
                            }
                        ?>
                    }
                }, 1500);

                subscribeLink.on('click', function(e){
                    e.preventDefault();
                    var data = {
                        link: subscribeLink.attr('href'),
                        action: subscribeLink.data('action'),
                        pbmToken: window.pbmToken,
                        postID: subscribeLink.data('post'),
                    };                    
                    if(subscribeLink.data('action') === 'pbm_bbp_subscribe'){
                        subscribeLink.text('Unsubscribe from Push Notifications');
                        subscribeLink.data('action', 'pbm_bbp_unsubscribe');
                    } else {
                        subscribeLink.text('Subscribe with Push Notifications');
                        subscribeLink.data('action', 'pbm_bbp_subscribe');
                    }

                    $.post(ajaxurl, data, function(response) {

                    });
                });
            });
        </script>
        <?php
    }
    
    function pbm_bbp_ajax_subscription() {
        $post_id = $_POST['postID'];
        self::pbm_bbp_subscriptions_handler( $post_id );
        die();
    }
    
    public function pbm_bbp_forum_subscription( $post ) {
        global $post;
        $post_id = $post->ID;

        $url = bbp_get_forum_permalink( $post_id );

        $pbm_bbp_subscriptions = get_post_meta( $post_id, 'pbm_bbp_subscription', true );
        
        echo( sprintf( "<span id='pbm-subscribe-%d' style='display:none;'><a href='%s' data-post='%d' class='pbm-forum-subscribe-link' class='subscription-toggle'></a></span>", $post_id, $url, $post_id ) );
        ?>
        <script>
            jQuery(document).ready(function($) {
                var subscribeLink = $('.pbm-forum-subscribe-link');
                var subscribeWrap = $('#pbm-subscribe-<?php echo( $post_id ); ?>');
                <?php
                    if ( !empty( $pbm_bbp_subscriptions ) ) {
                        $reply_bbp_subscriptions = json_encode( $pbm_bbp_subscriptions );
                ?>
                        var registrations = <?php echo( $reply_bbp_subscriptions ); ?>;
                <?php        
                    } else {
                ?>
                        var registrations = [];
                <?php
                    }
                ?>
                
                setTimeout(function(){
                    if(window.pbmEnabled){
                        if (typeof registrations[window.pbmToken] !== 'undefined') {
                            if(registrations[window.pbmToken] === true) {
                                subscribeLink.text('Unsubscribe from Push Notifications');
                                subscribeLink.data('action', 'pbm_bbp_unsubscribe');
                            }
                        } else {
                            subscribeLink.text('Subscribe with Push Notifications');
                            subscribeLink.data('action', 'pbm_bbp_subscribe');
                        }
                        <?php
                            if( true === bbp_is_subscriptions_active() && true === is_user_logged_in() ) {
                        ?>
                            subscribeWrap.detach().appendTo('#subscription-toggle').show();
                            subscribeWrap.prepend(' | ');
                        <?php
                            } else {
                        ?>
                            subscribeWrap.wrap("<div id='subscription-toggle'></div>").show();
                        <?php
                            }
                        ?>
                    }
                }, 1500);

                subscribeLink.on('click', function(e){
                    e.preventDefault();
                    var data = {
                        link: subscribeLink.attr('href'),
                        action: subscribeLink.data('action'),
                        pbmToken: window.pbmToken,
                        postID: subscribeLink.data('post'),
                    };                    
                    if(subscribeLink.data('action') === 'pbm_bbp_subscribe'){
                        subscribeLink.text('Unsubscribe from Push Notifications');
                        subscribeLink.data('action', 'pbm_bbp_unsubscribe');
                    } else {
                        subscribeLink.text('Subscribe with Push Notifications');
                        subscribeLink.data('action', 'pbm_bbp_subscribe');
                    }

                    $.post(ajaxurl, data, function(response) {

                    });
                });
            });
        </script>
        <?php
    }
    
    public function pbm_bbp_subscriptions_handler( $post_id ) {
        if( isset( $_POST['pbmToken'] ) ) {
            $action = $_POST['action'];
            $pbm_device_token = $_POST['pbmToken'];
        } elseif( isset( $_POST['pbm-bbp-device-token'] ) ) {
            $action = false;
            $pbm_device_token = $_POST['pbm-bbp-device-token'];
        } else {
            return;
        }

        if( ( !empty( $pbm_device_token ) ) && ( false === $action ) ) {
            if( isset( $_POST['pbm-bbp-subscription'] ) ) {
                $pbm_bbp_subscription = true;
            } else {
                $pbm_bbp_subscription = false;
            }   

            $subscriptions = get_post_meta( $post_id, 'pbm_bbp_subscription', true );
            if( !empty( $subscriptions )  ) {
                if ( isset( $subscriptions[$pbm_device_token] ) ) {
                    if( true === $pbm_bbp_subscription ) {
                        return;
                    } else {
                        unset( $subscriptions[$pbm_device_token] );
                    }
                } else {
                    if( true === $pbm_bbp_subscription ) {
                        $subscriptions[$pbm_device_token] = $pbm_bbp_subscription;
                    }
                }
            } else {
                $subscriptions = array(
                    $pbm_device_token => $pbm_bbp_subscription,
                );
            }
            update_post_meta( $post_id, 'pbm_bbp_subscription', $subscriptions );
        }

        if( ( !empty( $pbm_device_token ) ) && ( 'pbm_bbp_subscribe' === $action ) ) {
            $subscriptions = get_post_meta( $post_id, 'pbm_bbp_subscription', true );
            if( !empty( $subscriptions )  ) {
                $subscriptions[$pbm_device_token] = true;
            } else {
                $subscriptions = array(
                    $pbm_device_token => true,
                );
            }
            update_post_meta( $post_id, 'pbm_bbp_subscription', $subscriptions );
        }

        if( ( !empty( $pbm_device_token ) ) && ( 'pbm_bbp_unsubscribe' === $action ) ) {
            $subscriptions = get_post_meta( $post_id, 'pbm_bbp_subscription', true );
            unset( $subscriptions[$pbm_device_token] );
            update_post_meta( $post_id, 'pbm_bbp_subscription', $subscriptions );
        }        
    }
    
    public function pbm_bbp_remove_all_subscriptions( $action ) {
        delete_post_meta( $action, 'pbm_bbp_subscription' );
    }
    
    public function pbm_bbp_notify_subscribers( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author = 0 ) {
        $topic_id = bbp_get_topic_id( $topic_id );
        $forum_id = bbp_get_forum_id( $forum_id );

        if ( ! bbp_is_topic_published( $topic_id ) ) {
            return false;
        }
        
        $topic_title   = bbp_get_topic_title( $topic_id );
        $topic_url     = get_permalink( $topic_id );
        if( isset( $_POST['bbp_reply_to'] ) ) {
            $reply_to_id = $_POST['bbp_reply_to'];
        }
        
        $pbm_settings = Pbm::pbm_settings();
        $app_key = $pbm_settings['appKey'];
        $app_secret = $pbm_settings['appSecret'];
        
        $message = 'New Post in ' . $topic_title;
        
        if( !empty( $reply_to_id ) ) {
            $reply_subscriptions = get_post_meta( $reply_to_id, 'pbm_bbp_subscription', true );
        }
        
        $topic_subscriptions = get_post_meta( $topic_id, 'pbm_bbp_subscription', true );
        $forum_subscriptions = get_post_meta( $forum_id, 'pbm_bbp_subscription', true );

        if( !empty( $reply_subscriptions ) ) {
            $device_tokens = array();
            foreach( $reply_subscriptions as $token => $active ) {
                $device_tokens[] = $token;
            }
        }
        
        if( !empty( $topic_subscriptions ) ) {
            if( empty( $device_tokens ) ) {
                $device_tokens = array();
            }
            foreach( $topic_subscriptions as $token => $active ) {
                $device_tokens[] = $token;
            }
        }

        if( !empty( $forum_subscriptions ) ) {
            if( empty( $device_tokens ) ) {
                $device_tokens = array();
            }
            foreach( $forum_subscriptions as $token => $active ) {
                $device_tokens[] = $token;
            }
        }
        
        if( empty( $device_tokens) ) {
            return;
        }
        pbm_API::send_notification( $message, $topic_url, null, $app_key, $app_secret, $device_tokens );
    }    
}
