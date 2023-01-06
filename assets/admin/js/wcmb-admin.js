(function($){

  $(document).ready(()=>{
    $('[data-action="select-line-item"]').on('change', (e)=>{
      $(e.target).parent().find('[data-action="line-item-action"]').show();
    });

    $('[data-action="line-item-action"]').on('click',(e)=>{
      e.preventDefault();
      $(e.target).hide();
      var itemData = $(e.target).attr('data-item');
      console.log(JSON.parse(itemData));

      jQuery.ajax({
        type : "post",
        dataType : "json",
        url : ajaxurl,
        data : {action: "line_item_action", action_value:$(e.target).prev().val(), payload: JSON.parse(itemData)},
        success: function(response) {
           //if(response.type == "success") {
              // jQuery("#vote_counter").html(response.vote_count)
              $('<span class="line-item-action-label" style="color: green;">Saved!</span>').insertAfter($(e.target));
              setTimeout(()=>{
                $('.line-item-action-label').fadeOut();
                $('.line-item-action-label').remove();
              },5000);
           //}
           //else {
              // alert("Your vote could not be added")
           //}
        }
      })  



    });

    /**
     * Admin Top Bar click event handler
     */
    $('#wp-admin-bar-tc-materialbank-resync a').on('click', (e)=>{
      var el = e.target;
      e.preventDefault();
      
      if($(el).hasClass('syncing')) return false;

      $(el).addClass('syncing');

      var data = {
        'action'    : 'mb_inventory_sync',
      };

    jQuery.post(ajaxurl, data, function(response) {
      $(el).removeClass('syncing');
        if( response && response.success ){
          alert('Completed MaterialBank and Wooocommerce Sync!'); 
        }

        })
        .fail(()=>{
            
        })  
        .always(()=>{
            
        });
        
    });
  })


  /**
   * ADMIN
   */
    $(document).ready(()=>{
      // $('[name="carbon_fields_compact_input[_crb_materialbank_inventory]"]').attr('disabled','');
    });
  
})(jQuery);