jQuery( document ).ready( function( $ ) {
	
	var player = new CP('iframe');
	
	function timeToSeconds(str) {
		var p = str.split(':'),
			s = 0, m = 1;

		while (p.length > 0) {
			s += m * parseInt(p.pop(), 10);
			m *= 60;
		}

		return s;
	}

	$('.cp-timestamp').click( function(e) {
		var timestamp = $(this).data('timestamp');
        player.seekTo(timeToSeconds(timestamp));
		e.preventDefault();
    });

});