<div id="pbm">
    <div id="pbm-header">
        <?php if( $pbm_active_key ){ ?>
            <div class="pbm-wrapper">
                <div id="pbm-header-right">	
                    <form action="" method="post">
                        <input type="Submit" id="pbmLogOut" class="type-submit" name="clearkey" value="Log Out" />
                    </form>
                    <span id="pbm-username">
                        <span id="pbmUserLogo">
                            <?php echo get_avatar($pbm_server_settings['ownerEmail'], 25 ); ?>
                        </span>
                        <?php
                            echo $pbm_server_settings['ownerEmail'];
                        ?>
                    </span>
                </div>
                <img src="<?php echo PBM_URL; ?>layout/images/pbm-red-logo.png" />
                <?php if( $pbm_active_key ) { ?>
                    <div id="pbm-site-name"><?php echo( $pbm_server_settings['name'] ); ?></div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
    <?php if ( !empty( $first_time ) ) { ?>
        <div class="updated pbm-wrapper" id="pbm-first-time-setup">
            <div id="pbmNoticeText">
                <h3>Welcome to Passbeemedia Web Push, the plugin is up and running!</h3>
                <h4>Your site visitors can now opt-in to receive notifications from you. ( Safari / Mavericks only right now )</h4>
            </div>
            <div id="pbmNoticeTarget">
                <a href="#" id="pbmNoticeCTA" ><span id="pbmNoticeCTAHighlight"></span>Dismiss</a>
            </div>
		</div>
    <?php } ?>
    <?php if ( isset( $status ) && empty( $first_time ) ){ ?>
        <div id="pbm-status"><span id="pbm-status-text"><?php echo($status); ?></span><span id="pbm-status-close">Dismiss</span></div>
    <?php } ?>
        <!--BEGIN ADMIN TABS-->
        <?php if( $pbm_active_key ) { ?>

            <div id="pbm-tabs" class="pbm-wrapper">
                <ul>
                    <li class="active">Dashboard</li>
                    <li>Send a notification</li>
                    <li>Settings</li>
                </ul>
            </div>
        <?php } ?>
        <!--END ADMIN TABS-->
        <div id="pbm-pre-wrap" class="<?php echo( !empty( $pbm_active_key ) ? 'pbm-white':''); ?>">
            <div id="pbm-main-wrapper">
                    <!--BEGIN USER LOGIN SECTION-->
                    <?php if( !$pbm_active_key ) { ?>
                        <form action="" method="post">
                            <div id="pbm-login-wrapper">
                                <?php if( empty( $pbm_sites ) ){ ?>
                                    <div id="pbm-signup-wrapper">
                                        <div id="pbm-signup-inner">
                                            <img src="<?php echo PBM_URL; ?>layout/images/pbm_logo.png" alt="Pbm Logo" />
                                            <h2>Create a free account</h2>
                                            <p>
                                                Welcome! Creating an account only takes a few seconds and will give you access 
                                                to additional features like our analytics dashboard at passbeemedia.com
                                            </p>
                                            <a href="<?php echo( Pbm::registration_url() ); ?>" id="pbm-create-account" class="pbm-signin-link"><img src="<?php echo PBM_URL; ?>layout/images/pbm-arrow-white.png" />Create an account</a>
                                            <div id="pbm-bottom-right">Already have an account? <span class="pbm-signup">Sign in</span></div>
                                        </div>
                                    </div>
                                <?php } ?>
								<div class="pbm-push-ads">
									<h2>Start Engaging Your Customers With Web Push Notifications Today. Its Free...</h2>
									<h5>Contact us at <a href="mailto:support@passbeemedia.com">support@passbeemedia.com</a></h5>
								</div>
                                <div id="pbm-signin-wrapper" class="pbm-login-account">
                                    <div id="pbm-primary-logo">
                                        <img src="<?php echo PBM_URL; ?>layout/images/pbm_logo.png" alt="" />
                                    </div>
                                    <div class="pbm-primary-heading">
                                        <span class="pbm-primary-cta">Welcome! Log in to your Passbeemedia account.</span>
                                        <span class="pbm-secondary-cta">If you don’t have a account <a href="<?php echo( Pbm::registration_url() ); ?>" class="pbm-signin-link">sign up for free!</a></span>
                                    </div>
                                    <div class="pbm-section-content">
                                        <!--USER NAME-->
                                        <div class="pbm-login-input">
                                            <span class="pbm-label">Email:</span>
                                            <input name="pbmuserlogin" type="text" class="type-text pbm-control-login" value="<?php echo isset($_POST['pbmuserlogin']) ? $_POST['pbmuserlogin'] : '' ?>" size="50" tabindex="1" />
                                        </div>
                                        <div class="pbm-login-input">
                                            <!--PASSWORD-->
                                            <span class="pbm-label">Password:</span>
                                            <input name="pbmpasslogin" type="password" class="type-text pbm-control-login" value="<?php echo isset($_POST['pbmpasslogin']) ? $_POST['pbmpasslogin'] : '' ?>" size="50" tabindex="2" />
                                        </div>
                                        <?php if( isset( $pbm_sites ) ) { ?>
                                            <!--CONFIGS-->
                                            <div class="pbm-login-input">

                                                <span class="pbm-label">Choose a configurations to use:</span>

                                                <select id="pbmsites" name="pbmsites" class="pbm-site-select">
                                                    <option value="none" selected="selected">-- Choose Site --</option>
                                                    <?php  
                                                        for($i = 0; $i < count( $pbm_sites ); $i++ ) {
                                                    ?>
                                                        <option value="<?php echo $pbm_sites[$i]['key'] . '|' . $pbm_sites[$i]['secret']; ?>"><?php echo $pbm_sites[$i]['name']; ?></option>
                                                    <?php 
                                                        }
                                                    ?>
                                                </select>
                                                <span class="pbmDisclaimer">
                                                    To switch configurations after you log in, you will need to log out and choose a different configuration.
                                                </span>
                                            </div>
                                        <?php } ?>				
                                    </div>
                                    <div class="pbm-primary-footer">
                                        <input type="hidden" id="pbm-timezone-offset" name="pbm-timezone-offset" value="" />
                                        <input type="Submit" class="type-submit" id="pbm-middle-save" name="<?php echo isset($pbm_sites) ? 'pbmconfigselect' : 'pbmlogin' ?>" value="<?php echo isset( $pbm_sites ) ? 'Choose Site' : 'Login' ?>" tabindex="3" />
                                        <?php submit_button( 'Cancel', 'delete', 'cancel', false, array( 'tabindex' => '4' ) ); ?>
                                        <span class="left-link"><a href="https://go.goroost.com/login?forgot=true" target="_blank">forget password?</a></span>
                                    </div>
                                    <div id="pbm-sso">
                                        <div id="pbm-sso-text">
                                            Or sign in with
                                        </div>
                                        <div class="pbm-sso-option">
                                            <a href="<?php echo( Pbm::login_url( 'FACEBOOK' ) ); ?>" class="pbm-sso-link">
                                                <span id="pbm-sso-facebook" class="pbm-plugin-image">Facebook</span>
                                            </a>
                                        </div>
                                        <div class="pbm-sso-option">  
                                            <a href="<?php echo( Pbm::login_url( 'TWITTER' ) ); ?>" class="pbm-sso-link"><span id="pbm-sso-twitter" class="pbm-plugin-image">Twitter</span></a>
                                        </div>
                                        <div class="pbm-sso-option">
                                            <a href="<?php echo( Pbm::login_url( 'GOOGLE' ) ); ?>" class="pbm-sso-link"><span id="pbm-sso-google" class="pbm-plugin-image">Google</span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php } ?>
                    <!--END USER LOGIN SECTION-->

                    <!--BEGIN ALL TIME STATS SECTION-->
                    <?php if( $pbm_active_key ) { ?>
                        <div id="pbm-activity" class="pbm-admin-section">
                            <div id="pbm-all-stats">
                                <div class="pbm-no-collapse">
                                    <div class="pbmStats">
                                        <div class="pbm-stats-metric">
                                            <span class="pbm-stat"><?php echo(number_format($pbm_stats['registrations'])); ?></span>
                                            <hr />
                                            <span class="pbm-stat-label">Total subscribers on <?php echo( $pbm_server_settings['name'] ); ?></span>
                                        </div>
                                        <div class="pbm-stats-metric">
                                            <span class="pbm-stat"><?php echo(number_format($pbm_stats['messages'])); ?></span>
                                            <hr />
                                            <span class="pbm-stat-label">Total notifications sent to your subscribers</span>
                                        </div>
                                        <div class="pbm-stats-metric">
                                            <span class="pbm-stat"><?php echo(number_format($pbm_stats['read'])); ?></span>
                                            <hr />
                                            <span class="pbm-stat-label">Total notifications read by your subscribers</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php } ?>
                    <!--END ALL TIME STATS SECTION-->

                    <!--BEGIN RECENT ACTIVITY SECTION-->
                    <?php if( $pbm_active_key ) { ?>
                            <div class="pbm-section-wrapper">
                                <div class="pbm-section-heading" id="pbm-chart-heading">
                                    Recent Activity
                                    <div id="pbm-time-period">
                                        <span id="test-id" class="chart-range-toggle chart-reload active"><span class="load-chart" data-type="APP" data-range="DAY">Day</span></span>
                                        <span class="chart-range-toggle chart-reload"><span class="load-chart" data-type="APP" data-range="WEEK">Week</span></span>
<!--
                                        <span class="chart-range-toggle chart-reload"><span class="load-chart" data-type="APP" data-range="MONTH">Month</span></span>
-->
                                    </div>
                                    <div id="pbm-metric-options">
                                        <ul>
                                            <li class="chart-metric-toggle chart-reload active"><span class="chart-value" data-value="s">Subscribes</span></li>
                                            <li class="chart-metric-toggle chart-reload"><span class="chart-value" data-value="n">Notifications</span></li>
                                            <li class="chart-metric-toggle chart-reload"><span class="chart-value" data-value="r">Reads</span></li>
                                            <li class="chart-metric-toggle chart-reload"><span class="chart-value" data-value="p">Page Views</li></li>
                                            <li class="chart-metric-toggle chart-reload"><span class="chart-value" data-value="u">Unsubscribes</span></li>
<!--
                                            <li class="chart-metric-toggle chart-reload"><span class="chart-value" data-value="m">Messages</span></li>
-->
<!--
                                            <li class="chart-metric-toggle chart-reload"><span class="chart-value" data-value="pr">Prompts</span></li>
-->
                                        </ul>
                                    </div>
                                </div>
                                <div class="pbm-section-content pbm-section-secondary" id="pbm-recent-activity">
                                    <div class="pbm-no-collapse">
                                        <div id="pbm-curtain">
                                            <div id="pbm-curtain-notice">Graphs will appear once you have some subscribers.</div>
                                        </div>
                                        <div id="pbmchart_dynamic" class="pbmStats">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <!--END RECENT ACTIVITY SECTION-->

                    <!--BEGIN MANUAL PUSH SECTION-->
                    <?php if( $pbm_active_key ) { ?>
                        <form action="" method="post" id="manual-push-form">
                            <div id="pbm-manual-push" class="pbm-admin-section">
                                <div class="pbm-section-wrapper">
                                    <span class="pbm-section-heading">Send a manual push notification</span>
                                    <div class="pbm-section-content pbm-section-secondary" id="pbm-manual-send-section">
                                        <div class="pbm-no-collapse">
                                            <div id="pbm-manual-send-wrapper">
                                                <div class="pbm-send-type pbm-send-active" id="pbm-send-with-link" data-related="1">	
                                                    <div class="pbm-input-text">
                                                        <div class="pbm-label">Notification text:</div>
                                                        <div class="pbm-input-wrapper">
                                                            <span id="pbmManualNoteCount"><span id="pbmManualNoteCountInt">0</span> / 70 (reccommended)</span>
                                                            <input name="manualtext" type="text" class="type-text pbm-control-secondary" id="pbmManualNote" value="" size="50" />
                                                            <span class="pbm-input-caption">Enter the text for the notification you would like to send your subscribers.</span>
                                                        </div>
                                                    </div>
                                                    <div class="pbm-input-text">
                                                        <div class="pbm-label">Notification link:</div>
                                                        <div class="pbm-input-wrapper">
                                                            <input name="manuallink" type="text" class="type-text pbm-control-secondary" value="" size="50" />
                                                            <span class="pbm-input-caption">Enter a website link (URL) that your subscribers will be sent to upon clicking the notification.</span>
                                                        </div>
                                                    </div>
                                                    <input type="Submit" class="type-submit pbm-control-secondary" name="manualpush" id="manualpush" value="Send notification" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php } ?>
                    <!--END MANUAL PUSH SECTION-->	

                    <!--BEGIN SETTINGS SECTION-->
                    <?php if( $pbm_active_key ) { ?>
                        <form action="" method="post">
                            <div id="pbm-settings" class="pbm-admin-section">
                                <div class="pbm-section-wrapper">
                                    <span class="pbm-section-heading">Settings</span>
                                    <div class="pbm-section-content pbm-section-secondary">
                                        <div class="pbm-no-collapse">	
                                            <div id="pbm-block">
                                                <div class="pbm-setting-wrapper">

                                                    <span class="pbm-label">Auto Push:</span>
                                                    <input type="checkbox" name="autoPush" class="pbm-control-secondary" value="1" <?php if(!empty($pbm_server_settings['autoPush']) && $pbm_server_settings['autoPush'] == 1){ echo('checked');} ?> />
                                                    <span class="pbm-setting-caption">Automatically send a push notification to your subscribers every time you publish a new post.</span>
                                                </div>
                                                <div class="pbm-setting-wrapper">
                                                    <span class="pbm-label">Activate all Passbeemedia features:</span>
                                                    <input type="checkbox" name="autoUpdate" class="pbm-control-secondary" value="1" <?php if( true == $pbm_server_settings['autoUpdate'] ){ echo( 'checked' ); } ?> />
                                                    <span class="pbm-setting-caption">This will automatically activate current and future features as they are added to the plugin.</span>

                                                </div>
                                                <div class="pbm-setting-wrapper">
                                                    <span class="pbm-label">bbPress Push Notifications:</span>
                                                    <input type="checkbox" name="bbPress" class="pbm-control-secondary" value="1" <?php if( true == $pbm_settings['bbPress'] ){ echo( 'checked' ); } ?> <?php echo( !empty( $bbPress_active['present'] ) ? '':'disabled' ); ?> />
                                                    <span class="pbm-setting-caption">Extends subscriptions for bbPress forums, topics, and replies to allow subscribing via push notifications if site is viewed in a push capable browser.</span>

                                                </div>
                                                <input type="Submit" class="type-submit pbm-control-secondary" id="pbm-middle-save" name="savesettings" value="Save Settings" />                              
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php } ?>
                    <!--END SETTINGS SECTION-->
                <div id="pbmSupportTag">Have Questions, Comments, or Need a Hand? Hit us up at <a href="mailto:support@passbeemedia.com" target="_blank">support@passbeemedia.com</a> We're Here to Help.</div>
            </div>
        </div>
	<script>
        (function($){
            $('#pbm-status-close').click(function() {
                $('#pbm-status').css('display', 'none');
            });
            $('#pbmNoticeCTA').click(function(e) {
                e.preventDefault();
                $('#pbm-first-time-setup').css('display', 'none');
            });
            var timeZoneOffset = new Date().getTimezoneOffset();
            $('#pbm-timezone-offset').val(timeZoneOffset);
            
            <?php if( $pbm_active_key ) { ?>
                $('.chart-range-toggle, .chart-metric-toggle').on('click', function(){
                    $(this).parent().find('.active').removeClass('active');
                    $(this).addClass('active');
                });
            <?php if( 0 !== $pbm_stats['registrations'] ) { ?>
                    $('#pbm-curtain').hide();
                    var chart;
                    var data = {
                        type: $('.chart-range-toggle.active span').data('type'),
                        range: $('.chart-range-toggle.active span').data('range'),
                        value: $('.chart-metric-toggle.active span').data('value'),
                        offset: new Date().getTimezoneOffset(),
                        action: 'graph_reload',
                    };

                    $('.chart-reload').on('click', function(e){
                        e.preventDefault();
                        $("#pbmchart_dynamic").html("");

                        data = {
                            type: $('.chart-range-toggle.active span').data('type'),
                            range: $('.chart-range-toggle.active span').data('range'),
                            value: $('.chart-metric-toggle.active span').data('value'),
                            offset: new Date().getTimezoneOffset(),
                            action: 'graph_reload',
                        };

                        graphDataRequest(data);
                    });

                    function graphDataRequest(data) {
                        $.post(ajaxurl, data, function(response) {
                            var data = $.parseJSON( response );
                            loadGraph(data);
                        });
                    }

                    function loadGraph(data) {
                        $('#pbmchart_dynamic').html('');

                        chart = new Morris.Bar({
                            element: 'pbmchart_dynamic',
                            data: data,
                            barColors: ["#e25351"],
                            xkey: 'label',
                            ykeys: ['value'],
                            labels: ['Value'],
                            hideHover: 'auto',
                            barRatio: 0.4,
                            xLabelAngle: 20
                        });
                    }

                    $(window).resize(function() {
                        chart.redraw();
                    });
                    graphDataRequest(data);
                <?php } ?>
            
            <?php } ?>
        })(jQuery);
        <?php if( isset( $pbm_sites ) ){ ?>
			jQuery(".pbm-control-login").attr("disabled", "disabled");
		<?php } ?>		
		<?php if( $pbm_active_key ) { ?>
            (function($){
                function confirmMessage() {
                    if ( !confirm( "Are you sure you would like to send a notification?" ) ) {
                        return false;
                    } else {
                        return true;
                    }
                }
                $('#manualpush').on('click', function(e) {
                    e.preventDefault();
                    var subscribers = <?php echo $pbm_stats['registrations']; ?>;
                    if( 0 === subscribers ) {
                        var resub;
                        $.post(ajaxurl, { action: 'subs_check' }, function(response) {
                            var response = $.parseJSON( response );
                            resub = response;
                            if( 0 == resub ) {
                                alert('You must have one visitor subscribed to your site to send notifications');
                               return;
                            } else {
                                if( true === confirmMessage() ) {
                                     $('#manualpush').unbind('click').trigger('click');
                                }
                            }
                        });
                    } else {
                        if( true === confirmMessage() ) {
                             $('#manualpush').unbind('click').trigger('click');
                        }
                    }
                });
            })(jQuery);
		<?php } ?>
        <?php if( empty( $pbm_sites ) ){ ?>
            (function($){
                if( $('#pbm-login-wrapper').length ) {
                    var signup = $('#pbm-signup-wrapper');
                    var signin = $('#pbm-signin-wrapper');

                    signin.hide();

                    $('.pbm-signup').on('click', function() {
                        signup.toggle();
                        signin.toggle();
                    });
                }
            })(jQuery);
        <?php } ?>        
	</script>
</div>
