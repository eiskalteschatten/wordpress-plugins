jQuery(document).ready(function($) {
    let isRunning = false;
    let totalProcessed = 0;
    let totalImported = 0;
    let allErrors = [];

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

        $('#eii-start-import').hide();
        $('#eii-stop-import').show();
        $('#eii-progress').show();
        $('#eii-results').show();

        updateStatus('Starting import...');
        processNextBatch(0, 0); // Start with last_id = 0, processed_count = 0
    }

    function processNextBatch(lastId, processedCount) {
        if (!isRunning) return;

        // Get selected post types
        let selectedPostTypes = [];
        $('input[name="post_types[]"]:checked').each(function() {
            selectedPostTypes.push($(this).val());
        });

        if (selectedPostTypes.length === 0) {
            updateStatus('Error: Please select at least one post type to process.');
            completeImport();
            return;
        }

        updateProgressText(`Processing posts (processed: ${processedCount})...`);

        $.ajax({
            url: eii_ajax.url,
            method: 'POST',
            data: {
                action: 'import_external_images',
                nonce: eii_ajax.nonce,
                last_id: lastId,
                processed_count: processedCount,
                post_types: selectedPostTypes
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    totalProcessed += data.processed;
                    totalImported += data.imported;
                    allErrors = allErrors.concat(data.errors);

                    updateResults();

                    // Show progress information
                    if (data.total_posts && data.total_posts > 0) {
                        const progress = Math.round(data.processed_count / data.total_posts * 100);
                        updateProgressText(`Processing posts... ${data.processed_count}/${data.total_posts} (${progress}%)`);
                        $('#eii-progress-bar').css('width', progress + '%');
                    } else if (data.processed_count > 0) {
                        updateProgressText(`Processing posts... ${data.processed_count} processed so far`);
                    }

                    console.log('Batch result:', {
                        last_id: data.last_id,
                        processed: data.processed,
                        processed_count: data.processed_count,
                        total: data.total_posts,
                        has_more: data.has_more,
                        posts_in_batch: data.posts_in_batch,
                        query_time: data.query_time
                    });

                    // Additional debugging
                    if (!data.has_more) {
                        console.log('Stopping because has_more is false or no more posts found');
                    }

                    if (data.has_more && isRunning && data.posts_in_batch > 0) {
                        // Continue with next batch
                        setTimeout(() => {
                            processNextBatch(data.last_id, data.processed_count);
                        }, 500); // Small delay to prevent overwhelming the server
                    } else {
                        // Import complete
                        completeImport();
                    }
                } else {
                    updateStatus('Error: ' + (response.data || 'Unknown error'));
                    completeImport();
                }
            },
            error: function(xhr, status, error) {
                updateStatus('AJAX Error: ' + error);
                console.error('AJAX Error details:', {xhr, status, error});
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
