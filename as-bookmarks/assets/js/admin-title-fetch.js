(function () {
    'use strict';

    document.addEventListener( 'DOMContentLoaded', function () {
        var button = document.getElementById( 'asbm_fetch_title' );
        if ( ! button ) {
            return;
        }

        var urlField = document.getElementById( 'asbm_url' );
        var titleField = document.getElementById( 'title' );
        var status = document.getElementById( 'asbm_fetch_status' );

        function toggleFields( disabled ) {
            button.disabled = disabled;
            if ( titleField ) {
                titleField.disabled = disabled;
            }
        }

        button.addEventListener( 'click', function () {
            var url = urlField ? urlField.value.trim() : '';

            if ( ! url ) {
                status.textContent = asbmFetch.i18n.emptyUrl;
                return;
            }

            toggleFields( true );
            status.textContent = asbmFetch.i18n.fetching;

            var body = new URLSearchParams();
            body.append( 'action', 'asbm_fetch_title' );
            body.append( 'nonce', asbmFetch.nonce );
            body.append( 'post_id', button.getAttribute( 'data-post-id' ) );
            body.append( 'url', url );

            fetch( ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: body,
            } )
                .then( function ( response ) { return response.json(); } )
                .then( function ( response ) {
                    if ( response.success ) {
                        // Reloading post-new.php would spawn a new auto-draft, so navigate to the real edit URL instead.
                        window.location.href = asbmFetch.editUrl.replace( '%d', button.getAttribute( 'data-post-id' ) );
                        return;
                    }
                    status.textContent = ( response.data && response.data.message ) || asbmFetch.i18n.genericError;
                    toggleFields( false );
                } )
                .catch( function () {
                    status.textContent = asbmFetch.i18n.genericError;
                    toggleFields( false );
                } );
        } );
    } );
})();
