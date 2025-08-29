jQuery(function ($) {
    let frame;

    // Add Images
    $(document).on('click', '.hs-add', function (e) {
        e.preventDefault();

        // Always create a new frame (safer than caching)
        frame = wp.media({
            title: 'Select Images',
            button: { text: 'Use these images' },
            multiple: true
        });

        frame.on('select', function () {
            const attachments = frame.state().get('selection').toJSON();

            attachments.forEach(function (att) {
                const index = $('#hs-images .hs-image-row').length;

                $('#hs-images').append(
                    '<div class="hs-image-row">' +
                        '<input type="hidden" name="hs_slides[' + index + '][id]" value="' + att.id + '">' +
                        '<img src="' + att.url + '" style="width:80px;height:auto;margin:5px 10px 5px 0;">' +
                        '<button type="button" class="button hs-remove">Remove</button>' +
                    '</div>'
                );
            });
        });

        frame.open();
    });

    // Remove Images
    $(document).on('click', '.hs-remove', function () {
        $(this).closest('.hs-image-row').remove();

        // Reindex hidden fields
        $('#hs-images .hs-image-row').each(function (i, row) {
            $(row).find('input[type="hidden"]').attr('name', 'hs_slides[' + i + '][id]');
        });
    });
});
