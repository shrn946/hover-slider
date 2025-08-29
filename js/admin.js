jQuery(function ($) {
    let frame;

    // Make the images sortable
    $('#hs-images').sortable({
        items: '.hs-image-row',
        cursor: 'move',
        placeholder: 'hs-sortable-placeholder',
        update: function () {
            reindexImages();
        }
    });

    // Function to reindex hidden inputs
    function reindexImages() {
        $('#hs-images .hs-image-row').each(function (i, row) {
            $(row).find('input[type="hidden"]').attr('name', 'hs_slides[' + i + '][id]');
        });
    }

    // Add Images
    $(document).on('click', '.hs-add', function (e) {
        e.preventDefault();

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
                        '<img src="' + (att.sizes?.thumbnail?.url || att.url) + '" style="width:80px;height:auto;margin:5px 10px 5px 0;">' +
                        '<button type="button" class="button hs-remove">Remove</button>' +
                    '</div>'
                );
            });

            reindexImages();
        });

        frame.open();
    });

    // Remove Images
    $(document).on('click', '.hs-remove', function () {
        $(this).closest('.hs-image-row').remove();
        reindexImages();
    });
});
