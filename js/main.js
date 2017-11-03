jQuery(document).ready(function(){

  // Category change func
  bindCategorySelect();

});

function bindCategorySelect(){
  jQuery('#select-ass-cat-dropdown').on("change", function(e) {
    e.preventDefault();

    prod_cat_id = jQuery(this).attr("value");

    jQuery.ajax({
      type : "post",
      url  : purchaseOrderJs.ajaxUrl,
      data : {
        action  : purchaseOrderJs.product_ajax,
        prod_cat_id  : prod_cat_id,
      },
      success: function (resp) {

        if( resp.success ) {
          jQuery("#select-product").html(resp.data);
          bindVariationSelect()
        } else {
          alert(resp.data)
        }
      }
    });
  })
}

function bindVariationSelect() {

  jQuery("#select-ass-prod-dropdown").on("change", function(e) {
    e.preventDefault();

    prod_id = jQuery(this).attr("value");

    jQuery.ajax({
      type : "post",
      url  : purchaseOrderJs.ajaxUrl,
      data : {
        action  : purchaseOrderJs.variation_ajax,
        prod_id : prod_id,
      },
      success: function (resp) {

        if( resp.success ) {
          jQuery("#select-product-variation").html(resp.data);
        } else {
          alert(resp.data)
        }
      }
    });
  })
}
