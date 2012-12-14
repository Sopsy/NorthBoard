//Ajax Tooltip script: By JavaScript Kit: http://www.javascriptkit.com
//Last update (July 10th, 08'): Modified tooltip to follow mouse, added Ajax "loading" message.

var ajaxtooltip =
{
	useroffset: [15, 15], //additional x and y offset of tooltip from mouse cursor, respectively
	loadingHTML: '<p><img src="'+ htmldir +'/css/img/loading.gif" /> '+ txt_1 +'</p>',

	positiontip:function(e)
	{
		var docwidth=(window.innerWidth)? window.innerWidth-15 : ajaxtooltip.iebody.clientWidth-15;
		var docheight=(window.innerHeight)? window.innerHeight-18 : ajaxtooltip.iebody.clientHeight-15;
		var twidth=$('#tooltip').get(0).offsetWidth;
		var theight=$('#tooltip').get(0).offsetHeight;
		var tipx=e.pageX+this.useroffset[0];
		var tipy=e.pageY+this.useroffset[1];
		
		//account for right edge
		if(e.clientX + twidth > docwidth)
		{
			overflowx = docwidth - (e.clientX + twidth);
			tipx = tipx + overflowx - 30;
			if(tipx < 0)
			{
				tipx = 0;
				$('#tooltip').css({width: '99%'});
			}
		}
		//account for bottom edge
		if( docheight/2 > theight || e.clientY > docheight/2)
		{
			tipy=(e.clientY+theight>docheight)? tipy-theight-(2*this.useroffset[0]) : tipy;
		}
		
		$('#tooltip').css({left: tipx, top: tipy});
	},

	showtip:function(e)
	{
		//$('#tooltip').fadeIn(100);
		$('#tooltip').show();
	},

	hidetip:function(e)
	{
		$('#tooltip').hide();
	}

};

jQuery(document).ready(function()
{
	loadTooltips();
});

function loadTooltips()
{
	window.lastTipLoaded = false;
	$('<div id="tooltip" class="tooltip answer"></div>').appendTo('body');
	
	ajaxtooltip.iebody=(document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body;
	
	// Ajax loaded tooltips
	$('*[title^="ajax;"]').each(function(index){ //find all links with "title=ajax;" declaration
		this.titleurl=jQuery.trim(this.getAttribute('title').split(';')[1]); //get URL of external file
		$(this).removeAttr('title');
		
		$(this).hover(
			function(e)
			{ //onMouseover element
				if(window.lastTipLoaded != this.titleurl)
				{
					$('#tooltip').html('');
					var nowloading = setTimeout("$('#tooltip').html(ajaxtooltip.loadingHTML);", 1000);
					$('#tooltip').show();
					ajaxtooltip.positiontip(e);
					$('#tooltip').load(this.titleurl, '', function() {
						clearTimeout(nowloading);
					});
					window.lastTipLoaded = this.titleurl;
				}
				else
				{
					ajaxtooltip.showtip();
					ajaxtooltip.positiontip(e);
				}
			},
			function(e)
			{ //onMouseout element
				ajaxtooltip.hidetip(e);
			}
		);
		$(this).bind("mousemove", function(e)
		{
			ajaxtooltip.positiontip(e);
		});
	});
	
	// Plain text tips
	$('*[title^="tooltip;"]').each(function(index)
	{
		this.title=jQuery.trim(this.getAttribute('title').split(';')[1]);

		$(this).hover(
			function(e)
			{ //onMouseover element
				window.lastTipLoaded = false;
				ajaxtooltip.showtip(e);
				ajaxtooltip.positiontip(e);
				$('#tooltip').html(this.title);
			},
			function(e)
			{ //onMouseout element
				ajaxtooltip.hidetip(e);
			}
		);
		$(this).bind("mousemove", function(e)
		{
			ajaxtooltip.positiontip(e);
		});
	});
};
