

jQuery(document).ready(function(){

  jQuery( '#suggestion-notice-review-botton' ).click( function() { //NO I18N
    var data = {};
    data.action = "zoho_flow_change_next_suggestion_date";
    data.days_to_increase = 1;
    data.flow_service_id = document.getElementById('flow_service_id').innerHTML.trim();
    jQuery.post( ajaxurl, data, function(response){
    } );
    jQuery("#flow-suggestion-notice").remove(); //NO I18N
  });

  jQuery( '#suggestion-notice-gallery-botton' ).click( function() { //NO I18N
    /*
    var data = {};
    data.action = "zoho_flow_change_next_suggestion_date";
    data.days_to_increase = 1;
    data.flow_service_id = document.getElementById('flow_service_id').innerHTML.trim();
    jQuery.post( ajaxurl, data, function(response){
    } );
    jQuery("#flow-suggestion-notice").remove(); //NO I18N
    */
  });

  jQuery( '#suggestion-notice-later-botton' ).click( function() { //NO I18N
    var data = {};
    data.action = "zoho_flow_change_next_suggestion_date";
    data.days_to_increase = 15;
    data.flow_service_id = document.getElementById('flow_service_id').innerHTML.trim();
    jQuery.post( ajaxurl, data, function(response){
		} );
		jQuery("#flow-suggestion-notice").remove(); //NO I18N
	});

  jQuery( '#suggestion-notice-donot-botton' ).click( function() { //NO I18N
    var data = {};
    data.action = "zoho_flow_change_next_suggestion_date";
    data.days_to_increase = 365;
    data.flow_service_id = document.getElementById('flow_service_id').innerHTML.trim();
    jQuery.post( ajaxurl, data, function(response){
		} );
		jQuery("#flow-suggestion-notice").remove(); //NO I18N
	});
});
