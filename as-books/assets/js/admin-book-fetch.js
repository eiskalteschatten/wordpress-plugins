(function () {
    'use strict';

    document.addEventListener( 'DOMContentLoaded', function () {
        var button = document.getElementById( 'asbk_fetch_book_data' );
        if ( ! button ) {
            return;
        }

        function toggleFields( disabled, elements ) {
            if ( ! elements ) {
                elements = [];
                var title = document.getElementById( 'title' );
                var box = document.getElementById( 'asbk_book_details' );

                if ( title ) {
                    elements.push( title );
                }
                if ( box ) {
                    elements = elements.concat( Array.prototype.slice.call( box.querySelectorAll( 'input, select, textarea, button' ) ) );
                }
            }

            elements.forEach( function ( el ) {
                el.disabled = disabled;
            } );

            return elements;
        }

        button.addEventListener( 'click', function () {
            var isbnField = document.getElementById( 'asbk_isbn' );
            var status = document.getElementById( 'asbk_fetch_status' );
            var isbn = isbnField ? isbnField.value.trim() : '';

            if ( ! isbn ) {
                status.textContent = asbkFetch.i18n.emptyIsbn;
                return;
            }

            var fields = toggleFields( true );
            status.textContent = asbkFetch.i18n.fetching;

            var body = new URLSearchParams();
            body.append( 'action', 'asbk_fetch_book_data' );
            body.append( 'nonce', asbkFetch.nonce );
            body.append( 'post_id', button.getAttribute( 'data-post-id' ) );
            body.append( 'isbn', isbn );

            fetch( ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: body,
            } )
                .then( function ( response ) { return response.json(); } )
                .then( function ( response ) {
                    if ( response.success ) {
                        window.location.reload();
                        return;
                    }
                    status.textContent = ( response.data && response.data.message ) || asbkFetch.i18n.genericError;
                    toggleFields( false, fields );
                } )
                .catch( function () {
                    status.textContent = asbkFetch.i18n.genericError;
                    toggleFields( false, fields );
                } );
        } );
    } );
})();
