<?php
/*
  Plugin Name: WP Tweet Plus
  Description: WP Tweet Plus allows you to add tweet button to your Wordpress site.
  Author: <a href="http://crudlab.com/">CRUDLab</a>
  Version: 1.0.0
 */
require_once( ABSPATH . "wp-includes/pluggable.php" );
add_action('admin_menu', 'wptb_plugin_setup_menu');
//register_uninstall_hook( __FILE__, 'uninstall_hook');
register_deactivation_hook(__FILE__, 'wptb_uninstall_hook');

function wptb_shortcode($atts, $content = null) {
    return '<span class="wptb_caption"></span>';
}

add_shortcode('wptweet', 'wp_tweet_button');

function wptb_uninstall_hook() {
    global $wpdb;
    $thetable = $wpdb->prefix . "wptb";
    //Delete any options that's stored also?
    $wpdb->query("DROP TABLE IF EXISTS $thetable");
}

function wptb_plugin_setup_menu() {
    global $wpdb;
    $table = $wpdb->prefix . 'wptb';
    $myrows = $wpdb->get_results("SELECT * FROM $table WHERE id = 1");
    if ($myrows[0]->status == 0) {
        add_menu_page('WP Tweet Plus', 'Tweet  <span id="wptb_circ" class="update-plugins count-1" style="background:#F00"><span class="plugin-count">&nbsp&nbsp</span></span>', 'manage_options', 'wp-tweet-button', 'wptb_init', plugins_url("/images/ico.png", __FILE__));
    } else {
        add_menu_page('WP Tweet Plus', 'Tweet  <span id="wptb_circ" class="update-plugins count-1" style="background:#0F0"><span class="plugin-count">&nbsp&nbsp</span></span>', 'manage_options', 'wp-tweet-button', 'wptb_init', plugins_url("/images/ico.png", __FILE__));
    }
}

function utf8_character_issue_fix($string) {
    $value = $string;
    if (mb_check_encoding($string, 'ISO-8859-1') && mb_check_encoding($string, 'UTF-8')) {
        $value = $string;
    } else if (mb_check_encoding($string, 'ISO-8859-1'))
        $value = iconv('ISO-8859-1', 'UTF-8', $string);
    return $value;
}

//add_filter('wp_head', 'wptb_header');
add_filter('the_content', 'wp_tweet_button');

function wp_tweet_button($content = NULL) {
    $post_id = get_the_ID();
    global $wpdb;
    $table = $wpdb->prefix . 'wptb';
    $myrows = $wpdb->get_results("SELECT * FROM $table WHERE id = 1");
    $beforeafter = $myrows[0]->beforeafter;
    $display_as = $myrows[0]->display_as;
    $status = $myrows[0]->status;
    $position = $myrows[0]->position;
    $share_opt = $myrows[0]->share_opt;
    $share_url = $myrows[0]->url;
    $display = $myrows[0]->display;
    $except_ids = $myrows[0]->except_ids;
    $language = $myrows[0]->language;
    $tweet_opt = $myrows[0]->tweet_opt;
    $tweet_text = $myrows[0]->tweet_text;
    $via = $myrows[0]->via;
    $recommend = $myrows[0]->recommend;
    $hashtag = $myrows[0]->hashtag;
    $large_btn = $myrows[0]->large_btn;
    $opt_out = $myrows[0]->opt_out;
    $str = $content;
    ($large_btn == 0) ? $large_btn = 'small' : $large_btn = 'large';
    ($opt_out == 0) ? $opt_out = 'false' : $opt_out = 'true';
    if ($display_as == 'vertical') {
        $large_btn = 'small';
    }
    if ($tweet_opt == 0) {
        $tweet_text = get_the_title();
    }
    if ($share_opt == 0) {
        $share_url = get_post_permalink();
    }
    if ($share_opt == 2) {
        $share_url = get_site_url();
    }
    $fb = '<div style="width:100%; text-align:' . $position . '"> <a href="' . $share_url . '" '
            . 'data-lang="' . $language . '" '
            . 'data-hashtags="' . $hashtag . '" '
            . 'class="twitter-share-button" '
            . 'data-size="' . $large_btn . '" '
            . 'data-count="' . $display_as . '" '
            . 'data-via="' . $via . '" '
            . 'data-text="' . $tweet_text . '" '
            . 'data-url="' . $share_url . '" '
            . 'data-related="' . $recommend . '" 
                data-dnt="' . $opt_out . '"
                >Tweet</a></div><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\'://platform.twitter.com/widgets.js\';fjs.parentNode.insertBefore(js,fjs);}}(document, \'script\', \'twitter-wjs\');</script>
       <br>';
    $width = $myrows[0]->width . 'px';
    if ($status == 0) {
        $str = $content;
    } else {
        ?>
        <?php
        if ($display == 3) {
            if ($beforeafter == 'before') {
                $str = $fb . $content;
            } else {
                $str = $content . $fb;
            }
        }
        if ($display == 2) {
            if (is_home() || is_front_page()) {
                if ($beforeafter == 'before') {
                    $str = $fb . $content;
                } else {
                    $str = $content . $fb;
                }
            }
        }
        if ($display == 1) {
            if (!is_home() && !is_front_page()) {
                if ($beforeafter == 'before') {
                    $str = $fb . $content;
                } else {
                    $str = $content . $fb;
                }
            }
        }
        if ($display == 4) {
            if ($beforeafter == 'before') {
                $str = $fb . $content;
            } else {
                $str = $content . $fb;
            }
        }
        if ($display == 5 && $content == NULL) {
                $str = $fb;
        }
    }
    $except_check = true;
    if ($display == 4) {
        @$expect_ids_arrays = split(',', $except_ids);
        foreach ($expect_ids_arrays as $id) {
            if (trim($id) == $post_id) {
                $except_check = false;
            }
        }
    }
    if ($except_check) {
        return $str;
    } else {
        return $content;
    }
}

function wptb_contains($needle, $haystack) {
    return strpos($needle, $haystack) !== false;
}

if (isset($_REQUEST['magic_data'])) {
    $data = '';
    $args = array(
        'post_type' => 'any',
        'post_status' => 'publish'
    );
    $posts = get_posts($args);
    foreach ($posts as $post) {
        $data[] = array('id'=>$post->ID ,'name'=>$post->post_title);
    }

    echo json_encode($data);
    exit();
}

if (isset($_REQUEST['update_wptb'])) {
    //die;
    $except_ids = $_REQUEST['except_ids'];
    
    $except_ids = implode(', ',$except_ids);
//    echo $except_ids;
//    print_r($_REQUEST);
//    exit();
    global $wpdb;
    $type = '';
    $display = @mysql_real_escape_string($_REQUEST['display']);
    $beforeafter = @mysql_real_escape_string($_REQUEST['beforeafter']);
    $display_as = @mysql_real_escape_string($_REQUEST['display_as']);
    $position = @mysql_real_escape_string($_REQUEST['position']);

    $share_opt = @mysql_real_escape_string($_REQUEST['share_opt']);
    $share_url = @mysql_real_escape_string($_REQUEST['url']);
    $tweet_opt = @mysql_real_escape_string($_REQUEST['tweet_opt']);
    $tweet_text = @mysql_real_escape_string($_REQUEST['tweet_text']);
    $via = @mysql_real_escape_string($_REQUEST['via']);
    $recommend = @mysql_real_escape_string($_REQUEST['recommend']);
    $hashtag = @mysql_real_escape_string($_REQUEST['hashtag']);
    $large_btn = @mysql_real_escape_string($_REQUEST['large_btn']);
    $opt_out = @mysql_real_escape_string($_REQUEST['opt_out']);
    $language = @mysql_real_escape_string($_REQUEST['language']);
    $edit_id = @mysql_real_escape_string($_REQUEST['edit']);

    ($edit_id == 0 || $edit_id == '') ? $edit_id = 1 : '';
    $ul = '0';
    global $current_user;
    get_currentuserinfo();
    if (isset($current_user)) {
        $ul = $current_user->ID;
    }

    $table = $wpdb->prefix . 'wptb';
    $data1 = array(
        'display' => $display,
        'display_as' => $display_as,
        'position' => $position,
        'beforeafter' => $beforeafter,
        'except_ids' => $except_ids,
        'share_opt' => $share_opt,
        'url' => $share_url,
        'tweet_opt' => $tweet_opt,
        'tweet_text' => $tweet_text,
        'via' => $via,
        'recommend' => $recommend,
        'hashtag' => $hashtag,
        'large_btn' => $large_btn,
        'opt_out' => $opt_out,
        'language' => $language,
        'last_modified' => current_time('mysql')
    );
    $v = $wpdb->update($table, $data1, array('id' => $edit_id));
    header('Location:' . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
}

if (isset($_REQUEST['wptb_switchonoff'])) {
    global $wpdb;
    $val = $_REQUEST['wptb_switchonoff'];
    $data = array(
        'status' => $val
    );
    $table = $wpdb->prefix . 'wptb';
    if ($wpdb->update($table, $data, array('id' => 1))) {
        echo $val;
    } else {
        echo 'error';
    };
    die;
}

function wptb_init() {
    if (!isset($_REQUEST['edit'])) {
        echo '<script>location = location+"&edit=1"</script>';
    }
    global $wpdb;
    add_filter('admin_head', 'wptb_ShowTinyMCE');
    $check = array();
    $setting = array('media_buttons' => false);
    $table = $wpdb->prefix . 'wptb';
    if (!isset($_REQUEST['edit'])) {
        header('Location:' . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] . '&edit=1');
    }
    if (!(isset($_REQUEST['new']) || isset($_REQUEST['edit']))) {
        $myrows = $wpdb->get_results("SELECT * FROM $table WHERE id = 1");
    } else if (isset($_REQUEST['edit'])) {
        $edit_id = $_REQUEST['edit'];
        $str = "SELECT * FROM $table WHERE id = 1";
        $myrows = $wpdb->get_results($str);
    }
    $data = '';
    $data_array = array();

    $display_as[$myrows[0]->display_as] = 'checked';
    $beforeafter[$myrows[0]->beforeafter] = 'checked';
    $position[$myrows[0]->position] = 'checked';
    $show_count[$myrows[0]->show_count] = 'checked';
    $large_btn[$myrows[0]->large_btn] = 'checked';
    $opt_out[$myrows[0]->opt_out] = 'checked';
    $language[$myrows[0]->language] = 'checked';
    $share_opt[$myrows[0]->share_opt] = 'checked';
    $tweet_opt[$myrows[0]->tweet_opt] = 'checked';
    $display[$myrows[0]->display] = ' selected="selected"';
    $language[$myrows[0]->language] = ' selected="selected"';
    ?>
    <div id="test-popup" class="wptb_white-popup wptb_mfp-with-anim wptb_ mfp-hide"></div>
    <div class="wptb_container">
        <div class="wptb_row">
            <div class="wptb_plugin-wrap wptb_col-md-12">
                <div class="wptb_plugin-notify">
                    <div class="wptb_forms-wrap">
                        <div class="wptb_colmain">
                            <div class="wptb_what">
                                <div class="wptb_form-types-wrap">
                                    <input type="hidden" name="wptb" value="<?php echo $notify; ?>">
                                    <div class="wptb_clearfix"></div>
                                </div>
                            </div>
                        </div>
                        <div class="wptb_col" style="width:67%; ">
                            <div class="wptb_where">
                                <form class="wptb_inline-form wptb_form-inline" method="post">
                                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>">
                                    <input type="hidden" name="site_url" value="<?php echo get_site_url(); ?>" id="site_url">
                                    <div class="wptb_control-group">
                                        <label class="wptb_control-label">Tweet</label>
                                        <table border="0" width="100%">
                                            <tr>
                                                <td style="width: 160px; text-align: right; padding-right: 15px;">
                                                    <label>Display as </label>
                                                </td>
                                                <td>
                                                    <div class="wptb_form-group">
                                                        <input onchange="wptb_preview()" type="radio" id="none" name="display_as" <?php echo @$display_as['none']; ?> value="none" class="wptb_form-control" style="float:left; margin-top: 1px;">
                                                        <div class="b2-widget" style="width:100px; float:left;">
                                                            <div class="b2-widget-btn">
                                                                <i></i><span class="b2-widget-label">Tweet</span>
                                                            </div>
                                                        </div>

                                                        <input onchange="wptb_preview()" type="radio" id="horizontal" name="display_as" <?php echo @$display_as['horizontal']; ?> value="horizontal" class="wptb_form-control" style="float:left; margin-top: 1px;">
                                                        <div class="b2-widget" style="width:100px; float:left;">
                                                            <div class="b2-widget-btn">
                                                                <i></i><span class="b2-widget-label">Tweet</span>
                                                            </div>
                                                            <div class="b2-widget-count">
                                                                <i></i><u></u><div class="b2-widget-val">93</div>
                                                            </div>
                                                        </div>

                                                        <input onchange="wptb_preview()" type="radio" id="vertical" name="display_as" <?php echo @$display_as['vertical']; ?> value="vertical" class="wptb_form-control" style="float:left; margin-top: 1px;">
                                                        <div class="b2-widget" style="width:100px; float:left; ">
                                                            <div class="b2-widget-count" style="float:left;margin-left: 0; width: 53%; height: 36px;">
                                                                <i style="-webkit-transform: rotate(270deg); margin-bottom: -3px; top: 35px;; left: 58%;margin: 0 0 -4px -4px;bottom: 0;"></i><u style="-webkit-transform: rotate(270deg); margin-bottom: -3px; top: 34px; left: 58%;margin: 0 0 -4px -4px;bottom: 0;"></u><div class="b2-widget-val" style="text-align:center; height: 36px; line-height: 36px;">93</div>
                                                            </div>

                                                            <div class="b2-widget-btn" style="clear:left; margin-top: 5px;">
                                                                <i></i><span class="b2-widget-label">Tweet</span>
                                                            </div>

                                                        </div>

                                                    </div>
                                                </td>
                                            </tr>
                                            <tr><td colspan="2"><hr></td></tr>
                                            <tr>
                                                <td style="width: 160px; text-align: right; padding-right: 15px;">
                                                    <label>Where to display? </label>
                                                </td>
                                                <td>
                                                    <div class="wptb_form-group">
                                                        <select class="wptb_form-control" name="display" onchange="if (this.value == 4) {
                                                                        jQuery('.wptb_exclude').show(200)
                                                                    } else {
                                                                        jQuery('.wptb_exclude').hide(200)
                                                                    }
                                                                    if (this.value == 5) {
                                                                        jQuery('.wptb_manual').show(200)
                                                                    } else {
                                                                        jQuery('.wptb_manual').hide(200)
                                                                    }">
                                                            <option value="2" <?php echo @$display[2]; ?>>Homepage only</option>
                                                            <option value="1" <?php echo @$display[1]; ?>>All other pages/posts except Homepage</option>
                                                            <option value="3" <?php echo @$display[3]; ?>>All pages/posts</option>
                                                            <option value="4" <?php echo @$display[4]; ?>>All pages/posts except followings</option>
                                                            <option value="5" <?php echo @$display[5]; ?>>Manual</option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="vertical-align: top;width: 160px; padding-top: 10px;text-align: right; padding-right: 15px;">
                                                    <label class="wptb_exclude" style="display:<?php
                                                    if ($myrows[0]->display == 4) {
                                                        echo 'block';
                                                    } else {
                                                        echo 'none';
                                                    }
                                                    ?>">Exclude Page/Post</label>
                                                </td>
                                                <td>
                                                    <div class="wptb_form-group wptb_exclude" style="display:<?php
                                                    if ($myrows[0]->display == 4) {
                                                        echo 'block';
                                                    } else {
                                                        echo 'none';
                                                    }
                                                    ?>">
                                                       <div id="magicsuggest" value="[<?php echo $myrows[0]->except_ids; ?>]" name="except_ids[]" style="width:auto !important; background: #fff; border: thin solid #cccccc;"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="wptb_manual" style="display:<?php
                                                    if ($myrows[0]->display == 5) {
                                                        echo 'table-row';
                                                    } else {
                                                        echo 'none';
                                                    }
                                                    ?>">
                                                <td style="width: 160px; text-align: right; padding-right: 15px;">
                                                    <label>Code Snippet </label>
                                                </td>
                                                <td>
                                                    <div class="wptb_form-group">
                                                        <input type="text"  onClick="this.setSelectionRange(0, this.value.length);" name="code_snippet" value="<?php echo("<?php echo wp_tweet_button(); ?>"); ?>" class="wptb_form-control">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="wptb_manual" style="display:<?php
                                                    if ($myrows[0]->display == 5) {
                                                        echo 'table-row';
                                                    } else {
                                                        echo 'none';
                                                    }
                                                    ?>">
                                                <td style="width: 160px; text-align: right; padding-right: 15px;">
                                                    <label>Shortcode </label>
                                                </td>
                                                <td>
                                                    <div class="wptb_form-group">   
                                                        Use shortcode <input style="width:80px;" type="text" value="[wptweet]" onClick="this.setSelectionRange(0, this.value.length);"> to display tweet button
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 160px;">&nbsp;</td>
                                                <td>
                                                    <div class="wptb_form-group wptb_beforeafter">
                                                        <input type="radio" id="before" name="beforeafter" <?php echo @$beforeafter['before']; ?> value="before" class="wptb_form-control" style="float:left"><label style="float:left; font-weight: normal" for="before">Before</label>
                                                        <input type="radio" id="after" name="beforeafter" <?php echo @$beforeafter['after']; ?> value="after" class="wptb_form-control" style="float:left"><label style="float:left;font-weight: normal" for="after">After</label>
                                                    </div>
                                                </td> 
                                            </tr>
                                            <tr>
                                                <td style="width: 160px; text-align: right; padding-right: 15px;"><label>Position</label></td>
                                                <td>
                                                    <div class="wptb_form-group wptb_beforeafter">
                                                        <input onchange="wptb_preview()" type="radio" id="left" name="position" <?php echo @$position['left']; ?> value="left" class="wptb_form-control" style="float:left;font-weight: normal"><label style="float:left;font-weight: normal" for="left">Left</label>
                                                        <input onchange="wptb_preview()" type="radio" id="middle" name="position" <?php echo @$position['center']; ?> value="center" class="wptb_form-control" style="float:left;font-weight: normal"><label style="float:left;font-weight: normal" for="middle">Center</label>
                                                        <input onchange="wptb_preview()" type="radio" id="right" name="position" <?php echo @$position['right']; ?> value="right" class="wptb_form-control" style="float:left"><label style="float:left;font-weight: normal" for="right">Right</label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr><td colspan="2"><hr></td></tr>
                                            <tr>
                                                <td style="width: 160px; text-align: right; padding-right: 15px;"><label>Share URL</label></td>
                                                <td>
                                                    <div class="wptb_form-group wptb_beforeafter">
                                                        <input onchange="wptb_preview()" type="radio" id="share_opt0" name="share_opt" <?php echo @$share_opt['0']; ?> value="0" class="wptb_form-control" style="float:left"><label style="float:left; width: 80%;font-weight: normal" class="wptb_form_control" for="share_opt0">Use the Page/Post URL</label><div class="wptb_clearfix"></div>
                                                        <input onchange="wptb_preview()" type="radio" id="share_opt2" name="share_opt" <?php echo @$share_opt['2']; ?> value="2" class="wptb_form-control" style="float:left"><label style="float:left; width: 80%;font-weight: normal" class="wptb_form_control" for="share_opt2">Entire Site</label><div class="wptb_clearfix"></div>
                                                        <input onchange="wptb_preview()" type="radio" id="share_opt1" name="share_opt" <?php echo @$share_opt['1']; ?> value="1" class="wptb_form-control" style="float:left; margin-top: 7px;"><input type="text"  onkeyup="jQuery('#share_opt1').prop('checked', true);
                                                                    wptb_preview()" value="<?php echo @$myrows[0]->url; ?>" name="url" id="share_url">

                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 160px; text-align: right; padding-right: 15px;"><label>Tweet Text</label></td>
                                                <td>
                                                    <div class="wptb_form-group wptb_beforeafter">
                                                        <input onchange="wptb_preview()" type="radio" id="tweet_opt0" name="tweet_opt" <?php echo @$tweet_opt['0']; ?> value="0" class="wptb_form-control" style="float:left"><label style="float:left; width: 80%;font-weight: normal" class="wptb_form_control" for="tweet_opt0">Use the title of the page</label><div class="wptb_clearfix"></div>
                                                        <input onchange="wptb_preview()" type="radio" id="tweet_opt1" name="tweet_opt" <?php echo @$tweet_opt['1']; ?> value="1" class="wptb_form-control" style="float:left; margin-top: 7px;"><input  onkeyup="jQuery('#tweet_opt1').prop('checked', true);
                                                                    wptb_preview()" type="text" value="<?php echo @$myrows[0]->tweet_text; ?>" name="tweet_text" id="tweet_text">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr><td colspan="2"><hr></td></tr>
                                            <tr>
                                                <td style="width: 160px; text-align: right; padding-right: 15px;"><label>Via</label></td>
                                                <td>
                                                    <div class="wptb_form-group wptb_beforeafter">
                                                        <div class="b2-input input-prepend">
                                                            <span class="add-on">@</span>
                                                            <input onkeyup="wptb_preview()" type="text" spellcheck="false" class="b2-text" name="via" id="via_user" placeholder="username" value="<?php echo $myrows[0]->via; ?>">
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 160px; text-align: right; padding-right: 15px;"><label>Recommend</label></td>
                                                <td>
                                                    <div class="wptb_form-group wptb_beforeafter">
                                                        <div class="b2-input input-prepend">
                                                            <span class="add-on">@</span>
                                                            <input onkeyup="wptb_preview()" type="text" spellcheck="false" class="b2-text" name="recommend" id="recommend_user" placeholder="username" value="<?php echo $myrows[0]->recommend; ?>">
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 160px; vertical-align: top; padding-top: 15px; text-align: right; padding-right: 15px;"><label>Hashtag</label></td>
                                                <td>
                                                    <div class="wptb_form-group wptb_beforeafter">
                                                        <div class="b2-input input-prepend">
                                                            <span class="add-on">#</span>
                                                            <input onkeyup="wptb_preview()" type="text" spellcheck="false" class="b2-text" name="hashtag" id="share-hashtag-value" placeholder="hashtag" value="<?php echo $myrows[0]->hashtag; ?>">
                                                        </div>    <label class="wptb_small  wptb_exclude" style="width:100%; margin-top: -6px;font-size: 10px;margin-left: 8px;">Hashtag will be seperated by comma. Example: awesome, lovely, nice</label>

                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 160px;">&nbsp;</td>
                                                <td>
                                                    <div class="wptb_form-group wptb_beforeafter">
                                                        <input onchange="wptb_preview()" type="checkbox" id="large_btn" name="large_btn" <?php echo @$large_btn['1']; ?> value="1" class="wptb_form-control" style="float:left"><label style="float:left; width: 80%;font-weight: normal" class="wptb_form_control" for="large_btn">Large button</label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 160px;">&nbsp;</td>
                                                <td>
                                                    <div class="wptb_form-group wptb_beforeafter">
                                                        <input onchange="wptb_preview()" type="checkbox" id="opt_out" name="opt_out" <?php echo @$opt_out['1']; ?> value="1" class="wptb_form-control" style="float:left"><label style="float:left; width: 80%;font-weight: normal" class="wptb_form_control" for="opt_out">Opt-out of tailoring Twitter <a href="https://support.twitter.com/articles/20169421" target="_blank">[?]</a></label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 160px; text-align: right; padding-right: 15px;"><label>Language</label></td>
                                                <td>
                                                    <div class="wptb_form-group wptb_beforeafter">
                                                        <select id="button-lang" name="language" onchange="wptb_preview()">
                                                            <option value="0" <?php echo @$language['0']; ?> >Automatic</option>
                                                            <optgroup label="Select Language">
                                                                <option <?php echo @$language['fr']; ?> value="fr">French - français</option>
                                                                <option <?php echo @$language['en']; ?> value="en">English</option>
                                                                <option <?php echo @$language['ar']; ?> value="ar">Arabic - العربية</option>
                                                                <option <?php echo @$language['ja']; ?> value="ja">Japanese - 日本語</option>
                                                                <option <?php echo @$language['es']; ?> value="es">Spanish - Español</option>
                                                                <option <?php echo @$language['de']; ?> value="de">German - Deutsch</option>
                                                                <option <?php echo @$language['it']; ?> value="it">Italian - Italiano</option>
                                                                <option <?php echo @$language['id']; ?> value="id">Indonesian - Bahasa Indonesia</option>
                                                                <option <?php echo @$language['pt']; ?> value="pt">Portuguese - Português</option>
                                                                <option <?php echo @$language['ko']; ?> value="ko">Korean - 한국어</option>
                                                                <option <?php echo @$language['tr']; ?> value="tr">Turkish - Türkçe</option>
                                                                <option <?php echo @$language['ru']; ?> value="ru">Russian - Ру�?�?кий</option>
                                                                <option <?php echo @$language['nl']; ?> value="nl">Dutch - Nederlands</option>
                                                                <option <?php echo @$language['fil']; ?> value="fil">Filipino - Filipino</option>
                                                                <option <?php echo @$language['msa']; ?> value="msa">Malay - Bahasa Melayu</option>
                                                                <option <?php echo @$language['zh-tw']; ?> value="zh-tw">Traditional Chinese - �?體中文</option>
                                                                <option <?php echo @$language['zh-cn']; ?> value="zh-cn">Simplified Chinese - 简体中文</option>
                                                                <option <?php echo @$language['hi']; ?> value="hi">Hindi - हिन�?दी</option>
                                                                <option <?php echo @$language['no']; ?> value="no">Norwegian - Norsk</option>
                                                                <option <?php echo @$language['sv']; ?> value="sv">Swedish - Svenska</option>
                                                                <option <?php echo @$language['fi']; ?> value="fi">Finnish - Suomi</option>
                                                                <option <?php echo @$language['da']; ?> value="da">Danish - Dansk</option>
                                                                <option <?php echo @$language['pl']; ?> value="pl">Polish - Polski</option>
                                                                <option <?php echo @$language['hu']; ?> value="hu">Hungarian - Magyar</option> 
                                                                <option <?php echo @$language['fa']; ?> value="fa">Farsi - �?ارسی</option>
                                                                <option <?php echo @$language['he']; ?> value="he">Hebrew - עִבְרִית</option>
                                                                <option <?php echo @$language['ur']; ?> value="ur">Urdu - اردو</option>
                                                                <option <?php echo @$language['th']; ?> value="th">Thai - ภาษาไทย</option>
                                                            </optgroup>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td colspan="4">
                                                    <hr>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4">
                                                    <div id="u_0_18" class="wptb_preview">

                                                        <div id="twtbox"></div>    

                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4">
                                                    <hr>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"><button type="submit" name="update_wptb" class="wptb_btn wptb_btn-primary">Save Settings</button></td>
                                                <td colspan="2">
                                                    <div class="wptb_form-group wptb_switch" style="float: right;">
                                                        <?php
                                                        $img = '';
                                                        if ($myrows[0]->status == 0) {
                                                            $img = 'off.png';
                                                        } else {
                                                            $img = 'on.png';
                                                        }
                                                        ?>
                                                        <img onclick="wptb_switchonoff(this)" src="<?php echo plugins_url('/images/' . $img, __FILE__); ?>"> 
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="wptb_col wptb_col-adv" style="width:25%;">
                            <div class="wptb_where">
                                <h2 style="text-align:center;">   
                                    Our Other Products
                                </h2>
                                <hr>
                                <div>
                                    <div style="font-weight: bold;font-size: 20px; margin-top: 10px;">
                                        Jazz Popups
                                    </div>
                                    <div style="margin-top:10px; margin-bottom: 8px;">
                                        Jazz Popups allow you to add special announcement, message or offers in form of text, image and video.
                                    </div>
                                    <div style="text-align: center;">
                                        <a href="https://wordpress.org/plugins/jazz-popups/" target="_blank" class="wptb_btn wptb_btn-success" style="width:90%; margin-top:5px; margin-bottom: 5px; ">Download</a>
                                    </div>
                                </div>
                                <hr>
                                <div>
                                    <div style="font-weight: bold;font-size: 20px; margin-top: 10px;">
                                        WP Like button
                                    </div>
                                    <div style="margin-top:10px; margin-bottom: 8px;">
                                        WP Like button allows you to add Facebook like button on your wordpress blog.
                                    </div>
                                    <div style="text-align: center;">
                                        <a href="https://wordpress.org/plugins/wp-like-button/" target="_blank" class="wptb_btn wptb_btn-success" style="width:90%; margin-top:5px; margin-bottom: 5px; ">Download</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wptb_clearfix"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    </form> 
    <?php
}

//-------------------------------------- database --------------------
global $jal_db_version;
$jal_db_version = '1.0';

function wptb_install() {
    global $wpdb;
    global $jal_db_version;

    $table_name = $wpdb->prefix . 'wptb';

    $charset_collate = $wpdb->get_charset_collate();

    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}$table_name");

    // status: 1=active, 0 unactive
    // display: 1=all other page, 2= home page, 3=all pages

    $sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
                display_as varchar (50),
                display int, 
                width int,
                beforeafter varchar (25),
                except_ids varchar(255),
                position varchar (50),
                share_opt int , 
                tweet_opt int,
                tweet_text varchar (50),
                url varchar (255),
                show_count int, 
                via varchar (255),
                recommend varchar (255),
                hashtag varchar (255),
                large_btn int,
                opt_out int,
                language varchar (50),
                status int, 
                user_id int,
		created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		last_modified datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);

    add_option('jal_db_version', $jal_db_version);
}

function wptb_install_data() {
    global $wpdb;

    $type = '0';
    $radio_value = 'text';
    $data = 'Congratulations, you just completed the installation. Welcome to Wp Tweet Button!';

    $table_name = $wpdb->prefix . 'wptb';

    $ul = '0';
    global $current_user;
    get_currentuserinfo();
    if (isset($current_user)) {
        $ul = $current_user->ID;
    }
    $user_id = $ul;
    $table = $wpdb->prefix . 'wptb';
    $myrows = $wpdb->get_results("SELECT * FROM $table WHERE id = 1");
    if ($myrows == NULL) {
        $wpdb->insert(
                $table_name, array(
            'created' => current_time('mysql'),
            'last_modified' => current_time('mysql'),
            'status' => 1,
            'display' => 3,
            'display_as' => 'horizontal',
            'except_ids' => '',
            'user_id' => $user_id,
            'beforeafter' => 'before',
            'position' => 'left',
            'share_opt' => 0,
            'tweet_opt' => 0,
            'show_count' => 1
                )
        );
    }
}

register_activation_hook(__FILE__, 'wptb_install');
register_activation_hook(__FILE__, 'wptb_install_data');

//--------------------------------------------------------------------
function wptb_my_enqueue($hook) {
    //only for our special plugin admin page
    wp_register_style('wptb_css', plugins_url('/css/wptb_style.css', __FILE__));
    wp_enqueue_style('wptb_css');
    wp_register_style('wptb_magicsuggest-min', plugins_url('/css/magicsuggest-min.css', __FILE__));
    wp_enqueue_style('wptb_magicsuggest-min');
}

add_action('admin_enqueue_scripts', 'wptb_my_enqueue');
add_action('admin_enqueue_scripts', 'wptb_my_admin_scripts');

function wptb_my_admin_scripts() {
    if (isset($_GET['page']) && $_GET['page'] == 'wp-tweet-button') {
        wp_enqueue_media();
        wp_register_script('my-admin-js', plugins_url('/js/custom_tweet_button.js', __FILE__), array('jquery'));
        wp_enqueue_script('my-admin-js');
        wp_register_script('wptb_magicsuggest', plugins_url('/js/magicsuggest-min.js', __FILE__), array('jquery'));
        wp_enqueue_script('wptb_magicsuggest');
    }
}
?>