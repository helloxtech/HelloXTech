(function ($) {
    window.TwbbPostDuplicator = window.TwbbPostDuplicator || {};
    /**
     *  Function duplicate post
     *
     *  @params postId int
     *  @params urlType string in case of need to get edit by elementor url set 'elementor' otherwise 'edit'
     *
     *  @return mixed bool or edit url
    */
    window.TwbbPostDuplicator.duplicatePost = function (postId, urlType = 'elementor') {
        return fetch(TwbbPostDuplicator.rest_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': TwbbPostDuplicator.nonce
            },
            body: JSON.stringify({
                post_id: postId,
                url_type: urlType
            })
        })
        .then(res => {
            if (!res.ok) {
                // HTTP error, e.g. 400 or 500
                return res.json().then(err => {
                    throw new Error(err.error || 'Server error');
                });
            }
            return res.json();
        })
        .then(data => {
            if (data.new_post_url) {
                return data.new_post_url;
            } else {
                throw new Error('Missing URL in response');
            }
        });
    };
})(jQuery);

