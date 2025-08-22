function asppConvertPostTypeAjax(event, postId, newPostType) {
    if (confirm("Are you sure you want to convert this post type? This action cannot be undone.")) {
        // Show loading state
        event.target.disabled = true;
        var originalText = event.target.textContent;
        event.target.textContent = "Converting...";

        // Make AJAX request
        jQuery.ajax({
            url: aspp_ajax.ajax_url,
            type: "POST",
            data: {
                action: "aspp_convert_post_type",
                post_id: postId,
                new_post_type: newPostType,
                nonce: aspp_ajax.nonce
            },
            success: function (response) {
                // Reload the page to show the new post type
                window.location.reload();
            },
            error: function () {
                alert("Error: Could not convert post type. Please try again.");
                // Reset button
                event.target.disabled = false;
                event.target.textContent = originalText;
            }
        });
    }
}