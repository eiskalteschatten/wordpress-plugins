jQuery(document).ready(function($) {
    let isRunning = false;
    let totalProcessed = 0;
    let totalImported = 0;
    let allErrors = [];
    let totalPosts = 0; // Store total posts count

    $('#eii-start-import').on('click', function() {
        if (isRunning) return;

        startImport();
    });

    $('#eii-stop-import').on('click', function() {
        isRunning = false;
        $('#eii-start-import').show();
        $('#eii-stop-import').hide();
        updateStatus('Import stopped by user.');
    });

    function startImport() {
        isRunning = true;
        totalProcessed = 0;
        totalImported = 0;
        allErrors = [];
        totalPosts = 0; // Reset total posts

        $('#eii-start-import').hide();
        $('#eii-stop-import').show();
        $('#eii-progress').show();
        $('#eii-results').show();

        updateStatus('Starting import...');
        console.log('=== STARTING IMPORT ===');
        processNextBatch(0, 0); // Start with last_id = 0, processed_count = 0
    }

    function processNextBatch(lastId, processedCount) {
        if (!isRunning) return;

        console.log(`=== PROCESSING BATCH ===`);
        console.log(`lastId: ${lastId}, processedCount: ${processedCount}`);

        // Get selected post types
        let selectedPostTypes = [];
        $('input[name="post_types[]"]:checked').each(function() {
            selectedPostTypes.push($(this).val());
        });

        console.log('Selected post types:', selectedPostTypes);

        if (selectedPostTypes.length === 0) {
            updateStatus('Error: Please select at least one post type to process.');
            completeImport();
            return;
        }

        updateProgressText(`Processing posts (processed: ${processedCount})...`);

        const ajaxData = {
            action: 'import_external_images',
            nonce: eii_ajax.nonce,
            last_id: lastId,
            processed_count: processedCount,
            total_posts: totalPosts, // Pass total posts to maintain state
            post_types: selectedPostTypes
        };

        console.log('AJAX request data:', ajaxData);

        $.ajax({
            url: eii_ajax.url,
            method: 'POST',
            data: ajaxData,
            success: function(response) {
                console.log('=== AJAX RESPONSE ===', response);

                if (response.success) {
                    const data = response.data;
                    console.log('Response data:', data);

                    totalProcessed += data.processed || 0;
                    totalImported += data.imported || 0;
                    if (data.errors && data.errors.length > 0) {
                        allErrors = allErrors.concat(data.errors);
                        console.log('Errors found:', data.errors);
                    }

                    // Store total posts from first response
                    if (data.total_posts && data.total_posts > 0) {
                        totalPosts = data.total_posts;
                        console.log('Total posts set to:', totalPosts);
                    }

                    updateResults();

                    // Show progress information
                    const currentProcessed = data.processed_count || processedCount;
                    if (totalPosts > 0) {
                        const progress = Math.min(Math.round((currentProcessed / totalPosts) * 100), 100);
                        updateProgressText(`Processing posts... ${currentProcessed}/${totalPosts} (${progress}%)`);
                        $('#eii-progress-bar').css('width', progress + '%');
                    } else if (currentProcessed > 0) {
                        updateProgressText(`Processing posts... ${currentProcessed} processed so far`);
                    }

                    console.log('Batch result:', {
                        last_id: data.last_id,
                        processed: data.processed,
                        processed_count: data.processed_count,
                        total: totalPosts,
                        has_more: data.has_more,
                        posts_in_batch: data.posts_in_batch,
                        query_time: data.query_time
                    });                    // Additional debugging
                    if (!data.has_more) {
                        console.log('Stopping because has_more is false or no more posts found');
                        console.log('Stop conditions: has_more =', data.has_more, 'posts_in_batch =', data.posts_in_batch);
                    }

                    // Check if we should continue
                    const shouldContinue = data.has_more &&
                                         isRunning &&
                                         (data.posts_in_batch || 0) > 0 &&
                                         data.last_id !== lastId; // Ensure we're making progress

                    console.log('Should continue?', shouldContinue);
                    console.log('Continuation check:', {
                        'data.has_more': data.has_more,
                        'isRunning': isRunning,
                        'posts_in_batch > 0': (data.posts_in_batch || 0) > 0,
                        'last_id changed': data.last_id !== lastId,
                        'old lastId': lastId,
                        'new lastId': data.last_id
                    });

                    if (shouldContinue) {
                        console.log('Continuing to next batch...');
                        // Continue with next batch
                        setTimeout(() => {
                            processNextBatch(data.last_id, data.processed_count || currentProcessed);
                        }, 500); // Small delay to prevent overwhelming the server
                    } else {
                        console.log('Import complete - stopping');
                        // Import complete
                        completeImport();
                    }
                } else {
                    console.log('AJAX returned error:', response);
                    updateStatus('Error: ' + (response.data || 'Unknown error'));
                    completeImport();
                }
            },
            error: function(xhr, status, error) {
                console.log('=== AJAX ERROR ===');
                console.log('XHR:', xhr);
                console.log('Status:', status);
                console.log('Error:', error);
                console.log('Response text:', xhr.responseText);
                updateStatus('AJAX Error: ' + error);
                completeImport();
            }
        });
    }

    function completeImport() {
        isRunning = false;
        $('#eii-start-import').show();
        $('#eii-stop-import').hide();
        $('#eii-progress').hide();

        updateStatus('Import completed!');
        updateProgressText('Import finished.');
    }

    function updateResults() {
        $('#eii-processed').text(totalProcessed);
        $('#eii-imported').text(totalImported);

        if (allErrors.length > 0) {
            let errorHtml = '<h4>Errors:</h4>';
            allErrors.forEach(function(error) {
                errorHtml += '<div class="eii-error">' + error + '</div>';
            });
            $('#eii-errors').html(errorHtml);
        }
    }

    function updateStatus(message) {
        $('#eii-status').html('<div class="notice notice-info"><p>' + message + '</p></div>');
    }

    function updateProgressText(text) {
        $('#eii-progress-text').text(text);
    }
});
