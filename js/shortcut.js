$(document).keydown(function(e) {
	
	switch(e.which) {
		case 116: //F5
			if(!e.ctrlKey) {
				page_reload();
				return false;
			}

		case 82: //r
			if(e.ctrlKey) {
				page_reload();
				return false;
			}

		case 77: //m
			if(e.ctrlKey && e.shiftKey) {
				window.location = htmldir +'/mod/login';
				return false;
			}
	}
});