//
// https://gist.github.com/yangshun/9892961
//
// Few modifications made by PHP Live
//

function parseVideo (url) {
    // - Supported YouTube URL formats:
    //   - http://www.youtube.com/watch?v=My2FRPA3Gf8
    //   - http://youtu.be/My2FRPA3Gf8
    //   - https://youtube.googleapis.com/v/My2FRPA3Gf8
    // - Supported Vimeo URL formats:
    //   - http://vimeo.com/25451551
    //   - http://player.vimeo.com/video/25451551
    // - Also supports relative URLs:
    //   - //player.vimeo.com/video/25451551

	//
	// PHP Live! modify to ensure the string is a URL only
	//
    url.match(/^(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/);

    if (RegExp.$3.indexOf('youtu') > -1) {
        var type = 'youtube';
    } else if (RegExp.$3.indexOf('vimeo') > -1) {
        var type = 'vimeo';
    }

    return {
        type: type,
        id: RegExp.$6
    };
}

function createVideo (url, width, height) {
    // Returns an iframe of the video with the specified URL.
	//
	// PHP Live! modify to output boolean if not a valid source URL
	//
    var videoObj = parseVideo(url);
    if (videoObj.type == 'youtube') {
		var $iframe = $('<iframe>', { width: width, height: height });
		$iframe.attr('frameborder', 0);
        $iframe.attr('src', 'https://www.youtube.com/embed/' + videoObj.id);
		return $iframe;
    } else if (videoObj.type == 'vimeo') {
		var $iframe = $('<iframe>', { width: width, height: height });
		$iframe.attr('frameborder', 0);
        $iframe.attr('src', 'https://player.vimeo.com/video/' + videoObj.id);
		return $iframe;
    }
    return false;
}

function getVideoThumbnail (url, cb) {
    // Obtains the video's thumbnail and passed it back to a callback function.
    var videoObj = parseVideo(url);
    if (videoObj.type == 'youtube') {
        cb('https://img.youtube.com/vi/' + videoObj.id + '/maxresdefault.jpg');
    } else if (videoObj.type == 'vimeo') {
        // Requires jQuery
        $.get('https://vimeo.com/api/v2/video/' + videoObj.id + '.json', function(data) {
            cb(data[0].thumbnail_large);
        });
    }
}