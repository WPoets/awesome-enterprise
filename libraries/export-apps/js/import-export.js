var import_export={};
(function ($){ 

	import_export.get_url_vars=function(){
		var vars = [], hash;
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		for(var i = 0; i < hashes.length; i++)
		{
			hash = hashes[i].split('=');
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
		return vars;
	}

	import_export.bulk_initialize=function(action,file_slug,format,ladda,active_btn){

		var js_action_msg =$(active_btn).siblings('.js-action-msg');
		var js_progress = $(active_btn).parents('td.actions').find('.js-progress');	
		event.preventDefault();
		selected_items='';
		if(action=='selected'){
			selected_items = jQuery(".export form").serialize();
		}	
		url=ajaxurl+'?action=awesome_export_xml&activity='+action+'&format='+format+'&file_slug='+file_slug+'&'+selected_items;
		ladda.stop();	
		window.location.href = url;
		
	}
	
	import_export.ladda_bind=function(target, options ) {
		options = options || {};

		var targets = [];
		
		if( typeof target === 'string' ) {
			nodes=document.querySelectorAll( target );
			for ( var i = 0; i < nodes.length; i++ ) {
				targets.push( nodes[ i ] );
			}
		}
		else if( typeof target === 'object' && typeof target.nodeName === 'string' ) {
			targets = [ target ];
		}
		
		for( var i = 0, len = targets.length; i < len; i++ ) {
			
			(function() {
				var element = targets[i];

				// Make sure we're working with a DOM element
				if( typeof element.addEventListener === 'function' ) {
					var instance = Ladda.create( element );
					
					element.addEventListener( 'click', function( event ) {
						instance.startAfter( 1 );
						// Invoke callbacks
						if( typeof options.callback === 'function' ) {
							options.callback.apply(element, [ instance ] );
						}
					}, false );
				}
			})();
		}
	}

	

}) (jQuery);

 jQuery(document).ready(function($){

	import_export.ladda_bind('.js-app-export-button',{
			callback: function( instance ) {
				var active_btn=this;
				
				var action = this.getAttribute('data-action');
				var file_slug = this.getAttribute('data-file-slug');
				var format = this.getAttribute('data-format');
				import_export.bulk_initialize(action,file_slug,format,instance,active_btn);
				
				
			}
		});
	
});