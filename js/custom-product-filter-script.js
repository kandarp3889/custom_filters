jQuery(document).ready(function($) {

  $('.toggle-icon').click(function(event) {
        // Prevent the click event from propagating to the document
        event.stopPropagation();
        
        // Toggle the corresponding select section
        $(this).closest('div').find('.category-select, .sold-select, .price-select').toggle(); 
        $(this).toggleClass('fa-chevron-down fa-chevron-up'); // Toggle up/down arrow icons
    });

    // Close the toggle when clicking outside the toggle sections
    $(document).click(function(event) {
        // Check if the clicked element is outside the toggle sections
        if (!$(event.target).closest('.fst_dv, .sec_dv, .third_dv').length) {
            // Close all select sections
            $('.category-select, .sold-select, .price-select').hide();
            $('.toggle-icon').addClass('fa-chevron-down').removeClass('fa-chevron-up'); // Change icon to down arrow
        }
    });

    // Prevent the click event from propagating to the document when clicking inside the toggle sections
    $('.fst_dv, .sec_dv, .third_dv').click(function(event) {
        event.stopPropagation();
    });
    
    

    $('input[name="product_cat"], input[name="product_price"], input[name="product_status"]').change(function() {
        // var category = $('input[name="product_cat"]:checked').val();
        var category = $('input[name="product_cat"]:checked').map(function() {
            return $(this).val();
        }).get();
        var price = $('input[name="product_price"]:checked').val();
        var sold = $('input[name="product_status"]:checked').val();
        console.log(sold);
        // AJAX request
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_products',
                category: category,
                sold: sold,
                price:price
            },
            success: function(response) {
                console.log(response);
                $('.elementor-jet-woo-products').html(response);
            }
        });
    });
});

