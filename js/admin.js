jQuery(document).ready(function($) {
    // Make the images sortable
    $('#hs-images').sortable({
        items: '.hs-image-row',
        cursor: 'move',
        update: function(event, ui) {
            // Reindex hidden inputs after sort
            $('#hs-images .hs-image-row').each(function(index) {
                $(this).find('input[type="hidden"]').attr('name', 'hs_slides['+index+'][id]');
            });
        }
    });

    // Remove image
    $(document).on('click', '.hs-remove', function() {
        $(this).closest('.hs-image-row').remove();
        $('#hs-images .hs-image-row').each(function(index) {
            $(this).find('input[type="hidden"]').attr('name', 'hs_slides['+index+'][id]');
        });
    });
});
