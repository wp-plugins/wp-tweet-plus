jQuery(document).ready(function ($) {
    var current_url = window.location;
    var loc = window.location.href,
            index = loc.indexOf('#');

    if (index > 0) {
        current_url = loc.substring(0, index);
    }
    var magic_url = current_url + '&magic_data=1';
    console.log(magic_url);
    $('#magicsuggest').magicSuggest({
        data: magic_url ,
        ajaxConfig: {
            xhrFields: {
                withCredentials: true,
            }
        }
    });
    wptb_preview();

});

jQuery(function () {
    $('#magicsuggest').magicSuggest({
        // [...] // configuration options
    });
});

function wptb_switchonoff(val) {
    var path = jQuery(val).attr("src");
    var file = path.split('/').pop();
    var file2 = path.split(file);
    console.log(file2[0]);
    var on = '';
    var off = '';
    if (file == 'on.png') {
        on = true;
    } else {
        off = true;
    }
    if (off)
    {
        jQuery.post('', {'wptb_switchonoff': 1}, function (e) {
            if (e == 'error') {
                error('error');
            } else {
                jQuery('#wptb_circ').css("background", "#0f0");
                jQuery(val).attr("src", file2[0] + 'on.png');
            }
        });
    }
    if (on) {
        jQuery.post('', {'wptb_switchonoff': 0}, function (e) {
            if (e == 'error') {
                error('error');
            } else {
                jQuery('#wptb_circ').css("background", "#f00");
                jQuery(val).attr("src", file2[0] + 'off.png');
            }
        });
    }
    //alert(val);
}


window.twttr = (function (d, s, id) {
    var t, js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id))
        return;
    js = d.createElement(s);
    js.id = id;
    js.src = "https://platform.twitter.com/widgets.js";
    fjs.parentNode.insertBefore(js, fjs);
    return window.twttr || (t = {
        _e: [],
        ready: function (f) {
            t._e.push(f)
        }
    });
}(document, "script", "twitter-wjs"));

function wptb_preview() {
    document.getElementById('twtbox').innerHTML = '';
    var display_as = '';
    var position = '';
    var large = '';
    var opt = 'false';
    var title = 'Page title';
    var share_url = 'Page/Post URL';
    var msg = document.getElementById('tweet_text').value;
    if (jQuery('#none').is(':checked')) {
        display_as = 'none';
    }
    else if (jQuery('#horizontal').is(':checked')) {
        display_as = 'horizontal';
    }
    else if (jQuery('#vertical').is(':checked')) {
        display_as = 'vertical';
    }
    if (jQuery('#left').is(':checked')) {
        position = 'left';
    }
    else if (jQuery('#center').is(':checked')) {
        position = 'center';
    }
    else if (jQuery('#right').is(':checked')) {
        position = 'right';
    }
    if (jQuery('#large_btn').is(':checked') && display_as != 'vertical') {
        large = 'large';
    }
    if (jQuery('#opt_out').is(':checked')) {
        opt = 'true';
    }
    if (jQuery('#tweet_opt1').is(':checked')) {
        title = msg;
    }
    if (jQuery('#share_opt1').is(':checked')) {
        share_url = jQuery('#share_url').val();
    }

    var via = jQuery('#via_user').val();
    var recommend = jQuery('#recommend_user').val();
    var hashtags = jQuery('#share-hashtag-value').val();
    var language = jQuery('#button-lang').val();

    console.log(position);
    document.getElementById('twtbox').style.textAlign = position;
    // Create a New Tweet Element
    var link = document.createElement('a');
    link.setAttribute('data-count', display_as);
    link.setAttribute('href', share_url);
    link.setAttribute('class', 'twitter-share-button');
    link.setAttribute('style', 'margin-top:5px;');
    link.setAttribute('id', 'twitterbutton');
    link.setAttribute("data-text", "" + title + "");
    link.setAttribute("data-via", via);
    link.setAttribute("data-related", recommend);
    link.setAttribute("data-hashtags", hashtags);
    link.setAttribute("data-lang", language);
    link.setAttribute("data-size", large);
    link.setAttribute("data-dnt", opt);
    link.setAttribute("data-url", share_url);

    // Put it inside the twtbox div
    tweetdiv = document.getElementById('twtbox');
    tweetdiv.appendChild(link);

    twttr.widgets.load(); //very important
    return false;
}

 