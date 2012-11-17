// What do we do when we reload the page?
function page_reload() {

	if( saveScroll == '1' )
	{
		if(!$('html').scrollTop) {
			$('html').scrollTop = 0;
		}
		var rightScrolltop = $('html').scrollTop();
		if(rightScrolltop !== false) {
			set_cookie("content_scroll", rightScrolltop, style_cookie_duration);
		}
	}

	if (location.href.indexOf("#") > -1) {
		location.assign(location.href.replace(/\/?#[a-z0-9_]*/, "/"));
	}
	document.location = document.location;
};

// Load the autoresizer for the postfield
$(document).ready(function() {
	
	$('#save_settings_button').hide();
	$('textarea#msg').elastic();
	$('#charsremaining').show();
	
	$('#msg').keyup(function() { charsremaining() });
	
	if(window.location.hash) {
		if(window.location.hash.substr(0, 3) == "#q_") {
			msgid = window.location.hash.substr(3);
			
			if(!$('#hidepost').is(":visible"))
			{
				hide_element('postform', 'show');
			}
			
			insertAtCaret("msg", ">>"+ msgid +"\r\n");
		}
		else if(window.location.hash.substr(0, 4) == "#hl_") {
			msgid = window.location.hash.substr(4);
			highlight_post(msgid);
		}
	}
	
	if(browserSucks() && noBrowserWarning == '0')
	{
		$('#wrapper').append('<div id="browserwarning">'+ txt_19 +' <a href="http://getfirefox.com/">Mozilla Firefox 5+</a>, <a href="http://www.opera.com/download/">Opera 11.5+</a>, <a href="http://windows.microsoft.com/en-US/internet-explorer/downloads/ie">Internet Explorer 9+</a> '+ txt_20 +' <a href="http://www.apple.com/safari/download/">Safari 5.1+</a>/<a href="http://www.google.com/chrome/">Chrome 13+</a> (WebKit 535+). <a class="button" href="javascript:void(hideBrowserWarning());">Hide</a></div>');
	}
	
	// Hack to fix spoilers on iDevices, http://www.quirksmode.org/blog/archives/2008/08/iphone_events.html
	$('.spoiler').hover(function() { });
});

// Sidebar scroll position should be saved for future reference to make it work like a frame, eh?
window.onunload = function() {
	if(!$('#left').scrollTop) {
		$('#left').scrollTop = 0;
	}
	var scrolltop = $('#left').scrollTop();
	if(scrolltop !== false) {
		set_cookie("sb_scroll", scrolltop, style_cookie_duration);
	}
};
$('#left').ready(function() {
	var sidebarscroll = get_cookie("sb_scroll");
	if(sidebarscroll != 0) {
		$('#left').scrollTop(sidebarscroll);
	}
});
$('html').ready(function() {
	if( saveScroll == '1' )
	{
		var contentScroll = get_cookie("content_scroll");
		if(contentScroll != 0) {
			$('html').scrollTop(contentScroll);
		}
	}
	set_cookie("content_scroll", 0, style_cookie_duration);
});

function fp_page(page) {
	$('#fp_content').html('<p><img src="'+ htmldir +'/css/img/loading.gif" /> '+ txt_1 +'</p>');
	$('#fp_content').load('scripts/fp_content.php?page='+ page);
};

// Select all posts -link
function select_posts() {
	$('.checkbox_post').click();
};

// Hiding and showing the sidebar
function hide_element(elm, bool, link) {
	if(bool == 'hide') {
		if(elm == 'sidebar')
		{
			$("#left").animate({left:'-200px'}, 200);
			$('#hide_sidebar').html('<a class="show_link" title="'+ txt_5 +'" href="javascript:void(hide_element(\''+ elm +'\', \'show\', true));"></a>');
			$('#hide_sidebar').animate({left: '0'}, 200);

			// I wonder why padding does not automatically resize the window width but margin does in Chrome...
			$('#right').animate({marginLeft : '5px'}, 200);
		}
		if(elm == 'postform')
		{
			$('#hidepost').slideUp();
			$('#hide_postform').html('<a class="show_link_vertical" title="'+ txt_7 +'" href="javascript:void(hide_element(\'postform\', \'show\'));"></a>');
		}
		
		ajaxSaveSettings(elm, "0", false);
	}
	else if(bool == 'show') {
		if(elm == 'sidebar')
		{
			$('#left').show();
			$("#left").animate({left:'-200px'}, 0);
			$("#left").animate({left:'0'}, 200);
			$('#hide_sidebar').animate({left: '200px'}, 200);
		
			$('#hide_sidebar').html('<a class="hide_link" title="'+ txt_6 +'" href="javascript:void(hide_element(\''+ elm +'\', \'hide\', true));"></a>');

			$('#right').animate({marginLeft : '205px'}, 200);
		}
		if(elm == 'postform')
		{
			$('#hidepost').slideDown();
			$('#hide_postform').html('<a class="hide_link_vertical" title="'+ txt_8 +'" href="javascript:void(hide_element(\'postform\', \'hide\'));"></a>');
		}

		ajaxSaveSettings(elm, "1", false);
	}
};

function filesChanged(elm)
{
	var count = 0;
	var empty = 0;
	
	
	$('.fileinput').each(function()
	{
		count++;
		if(this.value == '')
		{
			empty++;
		}
	});

	if(empty == 0 && count < maxfiles)
	{
		$('#fileinputs').append('<input class="fileinput" type="file" multiple="multiple" name="files[]" id="files-'+ count +'" onchange="filesChanged();" size="52" />');
	}

};


// Check that the user has allowed cookies
$('#cookiewarning').ready(function() {
	check_cookies();
});

function check_cookies() {
	if(!get_cookie("testcookie")) {
		set_cookie("testcookie", 1, style_cookie_duration);
	}
	if(!get_cookie("testcookie")) {
		$('#cookiewarning').css({'display' : 'block'});
	}
};

// Making the postfield wider
function expand_msgfield() {
	$('#msg').toggleClass("wide");
	$('#postform').toggleClass("wide");
};

// Limit message characters
function charsremaining() {
	var limit = 8000;
	var text = $('#msg').val(); 
	var textlength = text.length;
	if(textlength > limit) {
		$('#charsremaining').html(txt_9);
		$('#msg').val(text.substr(0,limit));
		return false;
	}
	else {
		$('#charsremaining').html((limit - textlength) +' '+ txt_10);
		return true;
	}
};

// Expanding an image
function expandimage(postid, id, sauce, e) {

	// Defeating WebKit bugs
	// https://bugs.webkit.org/show_bug.cgi?id=22382
	if($.browser.webkit)
	{
		var middleclick;
		if(e)
		{
			if(e.which)
			{
				middleclick = (e.which == 2);
			}
		}
		
		if(middleclick)
		{
			window.open($('a#expandlink_'+ postid + id).attr('href'));
			return true;
		}
	}
	
	id = postid + id;

	// I'unno
	if( $('#answers_'+ postid) )
	{
		$('#answers_'+ postid).toggleClass("clear");
	}

	// And for expanding
	$('#imgfile_'+ id).attr("src",sauce);

	// Had to do it like this instead of percents, as percents does not work in FF for some weird reason.
	var maxwidth = $('#right').width() - 40;
	$('#imgfile_'+ id).css({'max-width' : maxwidth +'px'});
	
	$('#thumb_'+ id).toggle();
	$('#imgfile_'+ id).toggle();
};

// Expand all images
function expand_images(thread) {
	$('#thread_'+ thread +' .expandimage').click();
};

// Playing flash files inside a post
function toggle_flash(elm, sauce) {
	$("#imgfile_"+ elm).toggle();
	$("#flash_stop_"+ elm).toggle();
	$("#flashcontainer_"+ elm).html("");
	if(sauce) {
		$("#flashcontainer_"+ elm).flash({ src: sauce });
	}
	$("#flashcontainer_"+ elm).toggle();
}

// Playing media files
function loadMedia( player, mediaid, file, width, height )
{	

	mediaid.html = txt_1;
	if( player == 'jwplayer' || player == 'niftyplayer' )
	{
		if( player == 'niftyplayer' )
		{
			playerfile = 'niftyplayer';
			file_extra = '?file='+ file;
			height = 40;
			width = 165;
		}
		else if( player == 'jwplayer' )
		{
			playerfile = 'jwplayer';
			var flashvars = {
				file: file
			};
			file_extra = '';
		}

		var params = {
			allowfullscreen: 'true',
			allowscriptaccess: 'always',
			wmode: 'opaque'
		};
		var attributes = {};

		$('#load'+ mediaid).hide();
		swfobject.embedSWF(
			htmldir +'/flash/'+ playerfile +'.swf'+ file_extra,
			mediaid,
			width,
			height,
			'9',
			'#000000',
			flashvars,
			params,
			attributes
		);
	}
	else if( player == 'javamod' )
	{
		$('.loadjavamedia').show();
		$('#load'+ mediaid).hide();
		$('.javaplayer').remove();
		$('#'+ mediaid).html('<div class="javaplayer"><applet code="de.quippy.javamod.main.applet.JavaModApplet.class" archive="'+ htmldir +'/java/javamod.jar" width="170" height="80"><param name="file" value="'+ file +'"><param name="a" value="-"></applet></div>');
	}
};

// Upload progress bar
function getbar(id) {
	$.get(htmldir +"/scripts/progressbar.php?key="+ id, function(data) {
			text = jQuery.parseJSON(data);
			
			$("#sending_bg").css({'width' : text.percent +'%'});
			if(!text.filename && text.current != text.total && text.total == '0') {
				$("#sending_text").html(txt_2);
			}
			else if(!text.filename) {
				$("#sending_text").html(txt_3);
			}
			else {
				$("#sending_text").html(text.filename_short +": "+ text.current_kb +"/"+ text.total_kb +" "+ txt_4 +" ("+ text.percent +'%)');
			}
			setTimeout("getbar('"+ id +"')", 500);
	  }
	);
};

function startbar(id) {
	$("#sending").css({'visibility':'visible'});
	$("#sending_td").css({'height':'20px'});
	$("#sending_bg").css({'height':'20px'});
	$("#sending_text").html(txt_2);
	setTimeout("getbar('"+ id +"')", 1000);
};

// Adding quotation text to the messagefield
function quote(id, field, board, thread, page) {
	
	if(!page) {
		page = 1;
	}
	
	var url = htmldir +"/"+ board +"/"+ thread;
	var re = new RegExp(url +"(-[0-9]*)?/(#q_[0-9]*)?", "i");
	if(!document.location.href.match(re)) {
		if(page == 1) { page = ''; }
		else { page = '-'+page; }
		document.location = url + page +'/#q_'+ id;
	}
	else {
		if(!$('#hidepost').is(":visible"))
		{
			hide_element('postform', 'show');
		}
		if($("#"+ field)) {
			insertAtCaret(field, ">>"+ id +"\r\n");
			window.lastScroll = $(document).scrollTop();
			$("#"+ field).focus();
			$("#"+ field).blur();
			$(document).scrollTo(window.lastScroll, 0 );
			//$("#"+ field).append(">>"+ id +"\r\n");
		}
	}
};

function insertafter(newChild, refChild) { 
	refChild.parentNode.insertBefore(newChild,refChild.nextSibling); 
};

// Handling cookies
// http://www.thesitewizard.com/javascripts/cookies.shtml
function set_cookie(cookie_name, cookie_value, lifespan_in_days, valid_domain) {
	var domain_string = valid_domain ?
		("; domain=" + valid_domain) : '';
	document.cookie = cookie_name +
		"=" + encodeURIComponent(cookie_value) +
		"; max-age=" + 60 * 60 *
		24 * lifespan_in_days +
		"; path=/" + domain_string;
};

function get_cookie(cookie_name) {
	var cookie_string = document.cookie;
	if(cookie_string.length != 0) {
		var cookie_value = cookie_string.match (
			cookie_name +
			'=([^;]*)' );
		if(cookie_value) {
			return decodeURIComponent(cookie_value[1]);
		}
	}
	return '';
};

// Insert text at cursor position
function insertAtCaret(areaId, text)
{
	var txtarea = document.getElementById(areaId); 
	var scrollPos = txtarea.scrollTop;
	var strPos = 0;
	var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? 
		"ff" : (document.selection ? "ie" : false ) );
	if (br == "ie") { 
		var range = document.selection.createRange();
		range.moveStart ('character', -txtarea.value.length);
		strPos = range.text.length;
	}
	else if (br == "ff") strPos = txtarea.selectionStart;

	var front = (txtarea.value).substring(0,strPos);  
	var back = (txtarea.value).substring(strPos,txtarea.value.length); 
	txtarea.value=front+text+back;
	strPos = strPos + text.length;
	if (br == "ie") { 
		var range = document.selection.createRange();
		range.moveStart ('character', -txtarea.value.length);
		range.moveStart ('character', strPos);
		range.moveEnd ('character', 0);
		range.select();
	}
	else if (br == "ff") {
		txtarea.selectionStart = strPos;
		txtarea.selectionEnd = strPos-1;
	}
	txtarea.scrollTop = scrollPos;
};

// Highlighting a post
function highlight_post(a, e)
{
	
	$("div").map(function ()
	{
		if($(this).hasClass("highlighted"))
			$(this).removeClass("highlighted");
	});

	var elm = $("#no"+ a);
	if(elm)
	{
		elm.addClass("highlighted");
		$(document).scrollTo(elm, 800);
	}
	else if(e)
	{
		document.location = e.href;
	}
	else
	{
		alert(txt_11 +"\r\n\r\n"+ txt_12);
	}
};

function ajaxSaveSettings(key, value, reload)
{
	$.get(htmldir +"/scripts/ajax/save_settings.php", { "ajax": key, "data": value }, function(text) {
		if(text == "OK") {
			if(reload) {
				page_reload();
			}
		}
		else {
			alert(txt_11 +"\r\n\r\n"+ text);
		}
	});
};

function unTruncate(id)
{
	$('#msg_cut_'+ id).hide();
	$.get(htmldir +"/scripts/ajax/untruncate.php", { "id": id }, function(text) {
		$('#post_'+ id).html(text);
		loadTooltips();
	});
}

function hide_thread(id, doThis)
{
	if(doThis == 'restore')
	{
		$('#thread_'+ id).html('<p>'+ txt_18 +'</p>');
	}

	$('#thread_'+ id).slideUp(function()
	{
	
		$.get(htmldir +"/scripts/ajax/hide_thread.php", { "id": id, "do": doThis }, function(text) {
			if(text == "OK")
			{	
				if(doThis == 'hide')
				{
					$('#thread_'+ id).html('<p>'+ txt_13 +' &mdash; <a href="javascript:void(hide_thread(\''+ id +'\', \'restore\'));">'+ txt_17 +'</a></p>');
					$('#thread_'+ id).slideDown(function() {
						$('#thread_'+ id).delay(3000).slideUp();
						$('#line_'+ id).delay(3000).slideUp();
					});
				}
			}
			else
			{
				alert(txt_11 +"\r\n\r\n"+ text);
				$('#thread_'+ id).slideDown();
			}
		});
	});
	
};

function restore_thread(id)
{
	
	if(id != 'all')
	{
		var elm = '#hidden_'+ id;
	}
	else
	{
		var elm = '.hiddenthread';
	}
	
	$(elm).slideUp(function()
	{
	
		$.get(htmldir +"/scripts/ajax/hide_thread.php", { "id": id, "do": "restore" }, function(text) {
			if(text != "OK")
			{
				alert(txt_11 +"\r\n\r\n"+ text);
				$('#hidden_'+ id).slideDown();
			}
		});
	});
	
};

function follow_thread(id, whatdo)
{
	$.get(htmldir +"/scripts/ajax/follow_thread.php", { "id": id, "do": whatdo }, function(text) {
		if(text == "OK")
		{
			if(whatdo == 'add')
			{
				$('#followinfo_'+ id).html("<p>"+ txt_14 +"</p>");
				$('#followlink_img_'+ id).attr('src', htmldir +'/css/img/icons/folder_delete.png');
				$('#followlink_'+ id).attr('href', 'javascript:void(follow_thread(\''+ id +'\', \'remove\'));');
			}
			else
			{
				$('#followinfo_'+ id).html("<p>"+ txt_15 +"</p>");
				$('#followlink_img_'+ id).attr('src', htmldir +'/css/img/icons/folder_add.png');
				$('#followlink_'+ id).attr('href', 'javascript:void(follow_thread(\''+ id +'\', \'add\'));');
			}
		}
		else
		{
			alert(txt_11 +"\r\n\r\n"+ text);
		}
	});
};

function expand_thread(id, whatdo)
{
	// Show loading icon if the loading takes over one second
	var loading = setTimeout("$('#answers_'+ id).html('<img src=\"'+ htmldir +'/css/img/loading.gif\" alt=\"'+ txt_1 +'\" />');", 1000);
	$.get(htmldir +"/scripts/ajax/expand_thread.php", { "id": id, "do": whatdo }, function(text) {
		clearTimeout(loading);
		$('#expand_container_'+ id).html(text);
		loadTooltips();
	});
	
	if(whatdo == 'expand')
	{
		$('#omitted_'+ id).hide();
		$('#expandlink_img_'+ id).attr('src', htmldir +'/css/img/icons/arrow_up.png');
		$('#expandlink_'+ id).attr('href', 'javascript:void(expand_thread(\''+ id +'\', \'contract\'));');
	}
	else
	{
		$('#omitted_'+ id).show();
		$('#expandlink_img_'+ id).attr('src', htmldir +'/css/img/icons/arrow_down.png');
		$('#expandlink_'+ id).attr('href', 'javascript:void(expand_thread(\''+ id +'\', \'expand\'));');
	}
};

function quickReply(clear, thread, board, boardurl)
{
	if(!clear)
	{
		if(!$('#hidepost').is(":visible"))
		{
			window.postFormWasHidden = true;
			hide_element('postform', 'show');
		}
		else
		{
			window.postFormWasHidden = false;
		}
		
		var bak = $('#replyto_board').val();
		$('#replyto_board_bak').val(bak);
		$('#replyto_board').val(board);
		$('#replyto_thread').val(thread);
		if($('#board'))
		{
			$('#board').attr('disabled', 'disabled');
			$('#board').css({'opacity': '0.3'});
		}
		
		$('#replyto_info').html(txt_16 +' /'+ boardurl +'/'+ thread +'/ &mdash; <a href="javascript:void(quickReply(true));">'+ txt_17 +'</a>');
		$('#replyto_info').slideDown();
		$(document).scrollTo($('#right'), 400 );
	}
	else
	{
		if(window.postFormWasHidden)
		{
			hide_element('postform', 'hide');
		}
		
		var bak = $('#replyto_board_bak').val()
		$('#replyto_board').val(bak);
		$('#replyto_thread').val('0');
		if($('#board'))
		{
			$('#board').attr('disabled', '');
			$('#board').css({'opacity': '1'});
		}
		$('#replyto_info').slideUp();
	}
};

function loadEmbed(msgid)
{
	$('#embed_'+ msgid).show();
	$('#loadembed_'+ msgid).hide();
};

/* Mod functions */

function showError(id)
{
	$('#errorinfo_'+ id).slideToggle();
};

/* Show a warning for outdated browsers */
function browserSucks()
{
	// IE <= 8
	if($.browser.msie && parseInt($.browser.version) <= 8)
	{
		return true;
	}

	// Opera <= 11
	if($.browser.opera && ($.browser.version *10) <= 110)
	{
		return true;
	}

	// Firefox <= 4
	if($.browser.mozilla && parseInt($.browser.version) <= 1)
	{
		return true;
	}
	
	// WebKit <= 530
	if($.browser.webkit && ($.browser.version *10) <= 5300)
	{
		return true;
	}
	
	return false;
};

function hideBrowserWarning()
{
	$('#browserwarning').hide();
	ajaxSaveSettings('browserwarning', "1", false);
}
