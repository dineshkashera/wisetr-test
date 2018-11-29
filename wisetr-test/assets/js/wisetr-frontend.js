jQuery(document).ready(function($){
	
	//append single block
	jQuery('#add_elements').click(function(){
		var repeatTemplate = wp.template("repeat-block" );
		var html_appent    = repeatTemplate();
		jQuery('.all_fields_block').append(html_appent);
	});
});

//remove block
jQuery(document).on('click','.remove_block',function(){
	jQuery(this).parent().remove();
});

	
	
//google place ip
jQuery(document).ready(function($) {

    var gacFields = ["billing_google_address"];

    $.each( gacFields, function( key, field ) {

		var input = document.getElementById(field);
		
		if ( input != null ) {
		
		    //basic options of Google places API. 
		    //see this page https://developers.google.com/maps/documentation/javascript/places-autocomplete
		    //for other avaliable options
		    var options = {
		    types: ['geocode'],
		  };
		
	      var address_route 	= '';
		  var str_addr_route	= '';
		  var states 			= '';
		  var country			= '';
		  var city			    = '';
		  var zipcode			= '';
		  var autocomplete = new google.maps.places.Autocomplete(input, options);
		  
		  google.maps.event.addListener(autocomplete, 'place_changed', function () {
			  		
		  	  //get place details
		      var place = autocomplete.getPlace();
			            
	          if(place.address_components.length > 0){
	        	  	console.log(place.address_components);
	        	  	for(var key = 0; key < place.address_components.length; key++){
	            		
	            		var field_index = place.address_components[key].types[0];
	            		var long_name 	= place.address_components[key].long_name;
	            		
	            		//route name
	            		if((field_index == 'route') || (field_index == 'sublocality_level_1') || (field_index == 'sublocality_level_2') || (field_index == 'sublocality_level_3') || (field_index == 'locality')){
	            			address_route += place.address_components[key].long_name +', ';
	        			}
						
	            		//states
	            		if(field_index == 'administrative_area_level_1'){
							states  = place.address_components[key].short_name;
						}
	            		
	            		//city
						if(field_index == 'administrative_area_level_2'){
							city  = place.address_components[key].long_name;
						}
						
						//get country name
						if(field_index == 'country'){
							var country = place.address_components[key].short_name;
							
	            		}
						//get post code
						if(field_index == 'postal_code'){
							zipcode = place.address_components[key].long_name;
	            		}
					}// ends address loop
	        	  	
	        	  	jQuery('#billing_address_1').val('');
	            	if(address_route != '' && address_route != null){
	            		jQuery('#billing_address_1').val(address_route);
	            	}	
	            	
	            	jQuery('#billing_postcode').val('');
	            	if(zipcode != '' && zipcode != null){
	            		jQuery('#billing_postcode').val(zipcode);
	            	}	
	            	
	            	if(country != '' && country != null){
	            	   jQuery('#billing_country').trigger('change',[country]);
	            	}   
	            	
	            	if(states != '' && states != null){
	            		jQuery('#billing_state').trigger('change',[states]);
	            	}	
	            	
	            	jQuery('#billing_city').val('');
	            	if(city != '' && city != null){
	            		jQuery('#billing_city').val(city);
	            	}	
	             }
		  });
		}
    });
    
    jQuery( "#billing_country" ).on( "change", function( event, country_name) {
    	  if(country_name != null && country_name != '')
    		  jQuery(this).val(country_name);
   	});
    
    jQuery( "#billing_state" ).on( "change", function( event, states_name) {
    	if(states_name != null && states_name != '')
    		jQuery(this).val(states_name);
 	});
   
});