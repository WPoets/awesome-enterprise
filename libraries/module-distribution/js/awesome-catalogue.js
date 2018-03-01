 var load_more;
 var install_module;
 var awesome_catalogue={};
 var aw2_dist={};
(function ($) {
  
 

	awesome_catalogue.get_url_vars=function(){
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

	awesome_catalogue.get_types=function(){
		
		active_object = $('.master-nav a.selected').data('value');
	
		jQuery.get(ajaxurl+'?action=aw2_get_types&object_type='+active_object,function( response ) {
			$('#aw2-loader').hide();

			var data = jQuery.parseJSON(response);//parse JSON

			if(data.error){
				var msg='<div class=error>'+data.message+'</div>';
				$('.js-modules').html(msg);  
			} else {
				if(data.results.length){
					$menu=$('<ul class="menuitems"></ul>');			
					jQuery.each(data.results, function(i, item) {
						//console.log(item);
						if(item.slug!='ignore'){
							$menu.append('<li><a href="#'+item.slug+'" class="js-menu-item"><span>'+item.name+'</span><span>'+item.count+' available</span></a></li>');
						}				
					});
					
					
					if(window.location.hash.length > 0)
					{
						$menu.find('[href^="'+window.location.hash+'"]').addClass('js-active-menu').addClass('active-item')
					}	
					else{
						$menu.find('.js-menu-item').first().addClass('js-active-menu').addClass('active-item');
					}
					
					
					$menu.appendTo('.js-menu');
					$('.aw2_navbar').show();
					awesome_catalogue.set_priority_nav();
				}	
				
				awesome_catalogue.get_catalogue_items();
				
			}
		 
		});
	}

	awesome_catalogue.get_catalogue_items=function(page_num){
		$('#aw2-loader').show();
		active_object = $('.master-nav a.selected').data('value');
		
		active_object_type='';
		if($('.js-active-menu').length){
			active_object_type = $('.js-active-menu').attr('href').substr(1);
		}
		
		page_num = typeof page_num !== 'undefined' ? page_num : 1;
		
		appurl =  ajaxurl+'?action=aw2_distribution_list&active_object='+active_object+'&active_object_type='+active_object_type+'&pagenum='+page_num;
		
		$.get(appurl,function( response ) {
			$('#aw2-loader').hide();
			load_more.stop();
			var data = $.parseJSON(response);
			if(data.error){
				var msg='<div class=error>'+data.message+'</div>';
				$('.js-modules').html(msg);  
			}
			else {
				if($('.js-modules').hasClass('js-replace'))
				{
					$('.js-modules').removeClass('js-replace');
					$('.js-modules').html('');
				}	
				jQuery.each(data.results, function(i, item) {
					$('.js-modules').append(awesome_catalogue.item_template(item));
					//$('.js-modules').append('<p>Append here!!</p>');
					
				});
				$('.js-modules').append('<div class="shortcode-wrapper col-xs-12"></div>');
				if(data.total > $('.js-modules').children().length) {
					$('.js-load-more').show();
					$('.js-load-more').attr('data-page_num',parseInt(data.page_num)+1);
				}
				else
					$('.js-load-more').hide();
				
				$('.age').age();
				awesome_catalogue.ladda_install_bind('.js-install');
				awesome_catalogue.ladda_reinstall_bind('.js-reinstall');
				$(".js-modules").imagesLoaded().always( function( instance ) {
					//jQuery.fn.matchHeight._update();
					$('.img-wrapper').matchHeight({ byRow: false });
					$('.picture-item-wrap').matchHeight({ byRow: false });
					$('.picture-item').matchHeight({ byRow: false });
				});
				$('.shortcode-form-wrapper').width($('.js-modules').width()-15);
			}
			
					//install_module  =Ladda.create( document.querySelector( '.js-install' ) );
			
			
		});
	}
	
	awesome_catalogue.item_template=function(item) {
		types='';
		types_slug='default';
		jQuery.each(item.type, function(i, item) {
				types +='<span class="category">'+item.name+'</span>';
				types_slug =item.slug;
				
		});
		str='<div class="col-md-4 col-sm-6 col-xs-12">';
			str +='<div class="picture-item">';
				str +='<div class="img-wrapper">';
					str += '<img height="374" src="'+item.image+'">';
					if(item.installed == "yes"){
						str +='<p><span>Already Installed</span></p>';
					}
				str += '</div>';
				str +='<div class="picture-item-wrap">';
					str +='<h3>'+item.title+'</h3>';
					str +='<p class="excerpt">'+item.excerpt+'</p>';
					str +='<p class="types">'+types+'</p>';	
					str +='<p class="age-wrapper"><span>Last Updated: </span><time datetime="'+item.last_updated+'" class="age">'+item.last_updated+'</time></p>';
				str +='</div>';
				str +='<div class="picture-item-action">';
					if(item.installed == "yes"){
						str +='<button type="button" class="btn btn-primary ladda-button js-reinstall" data-title="'+item.title+'" data-slug="'+item.slug+'" data-id="'+item.id+'" data-style="slide-down">Re-Install</button>';
					}else{
						str +='<button type="button" class="btn btn-primary ladda-button js-install" data-title="'+item.title+'" data-slug="'+item.slug+'" data-style="slide-down">Install</button>';
					}
					str +='<button type="button" class="btn btn-gray-darker ladda-button btnflip gray-darker-bg">Details</button>';
				str +='</div>';
			str +='</div>';
			str +='<div class="more-details">';
				str +='<div class="col-md-3 col-xs-12 picture-item-flip no-padding">';
					str +='<ul class="preview-actions no-padding no-margin gray-lighter-bg">';
						str +='<li><button class="btnflipback"></button></li>';
						str +='<li><a href="#" class="info visible-xs visible-sm"></a></li>';
						if(item.installed == "yes"){
							str +='<li>';
								str +='<button type="button" class="btn btn-primary ladda-button js-reinstall" data-slug="'+item.slug+'" data-style="slide-down">Re-Install</button>';
							str +='</li>';
						}else{
							str +='<li>';
								str +='<button type="button" class="btn btn-primary ladda-button js-install" data-slug="'+item.slug+'" data-style="slide-down">Install</button>';
							str +='</li>';
						}					
					str +='</ul>';
					str +='<div class="preview-details">';
						str +='<h3>'+item.title+'</h3>';
						str +='<p class="excerpt">'+item.details+'</p>';
						str +='<p class="types">'+types+'</p>';
						if(item.installed == "yes"){
							str +='<p>Already Installed</p>';
						}
						str +='<p class="age-wrapper"><span>Last updated: </span><time datetime="'+item.last_updated+'" class="age">'+item.last_updated+'</time></p>';
					str +='</div>';
				str +='</div>';
				str +='<div class="col-md-9 col-xs-12 brand-sixth-bg preview-img-wrapper">';
					str +='<img src="'+item.image+'" />';
				str +='</div>';
			str +='</div>';
		str +='</div>';
		
		return str;
	}
	awesome_catalogue.set_priority_nav=function(){
		var nav = priorityNav.init({
			//initClass: "js-priorityNav",
			mainNavWrapper: ".menu",
			mainNav: "ul.menuitems",
			navDropdownLabel: "More +",
			navDropdownBreakpointLabel: "Types",
			breakPoint: 100,

		});
	}
	awesome_catalogue.ladda_bind=function(target, options ) {
		
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
	
	awesome_catalogue.ladda_install_bind=function(selector){
		awesome_catalogue.ladda_bind( selector,{
		    callback: function( instance ) {
				slug=this.getAttribute('data-slug');
				title=this.getAttribute('data-title');
				active_object = $('.master-nav a.selected').data('value');				
				var active_btn=this;
				if(active_object=='apps')
					awesome_catalogue.initate_app_install_flow(title,slug,active_object,active_btn,instance);
				else
					awesome_catalogue.initate_install_flow(slug,active_object,active_btn,instance);
		    }
		});
	}

	awesome_catalogue.ladda_reinstall_bind=function(selector){
			
		awesome_catalogue.ladda_bind( selector,{
		   callback: function( instance ) {
				var slug = this.getAttribute('data-slug');
				var stack_bar_top = {"dir1": "down", "dir2": "right", "push": "top", "spacing1": 0, "spacing2": 0};
				var stack_topcenter = {"dir1": "down", "dir2": "right", "push": "top", "firstpos1": 25, "firstpos2": ($(window).width() / 4)};
				bootbox.dialog({
					title: "Actions on Module",
					message: "If you 'Upgrage' this module, it will only affect the current module. If you 'Re-Install' this module, it will also re-install its dependencies. Proceed with Caution.",
					closeButton: true,
					onEscape: function() {
						instance.stop();
					},
					buttons: {
						success: {
							label: "Upgrade",
							className: "btn-success",
							callback: function() {
								url=ajaxurl+'?action=aw2_install_obj&upgrade=yes&slug='+slug+'&active_object='+active_object;
								jQuery.get(url, function( response ) {
									instance.stop();
									var data = jQuery.parseJSON(response);
									var alert_type = 'error';
									if(data.success ){
					
										alert_type = 'success';
										jQuery(active_btn).parent().html('<p class="brand-fifth-bg brand-gray-darker text-center" style="height: 35px;line-height: 35px;margin-bottom: 0;margin-top: 0;">Installed.</p>');
									}
									var opts = {
										text: data.msg,
										addclass: "stack-bar-top",
										cornerclass: "",
										width: "50%",
										stack: stack_topcenter,
										type: alert_type,
										icon: false,
										animation: "slide"
									};
									
									new PNotify(opts);
								});
							}
						},
						danger: {
							label: "Re-Install",
							className: "btn-danger",
							callback: function() {
								url=ajaxurl+'?action=aw2_install_obj&reinstall=yes&slug='+slug+'&active_object='+active_object;
								jQuery.get(url, function( data ) {
									instance.stop();
									var data = jQuery.parseJSON(response);
									var alert_type = 'error';
									if(data.success ){
					
										alert_type = 'success';
										jQuery(active_btn).parent().html('<p class="brand-fifth-bg brand-gray-darker text-center" style="height: 35px;line-height: 35px;margin-bottom: 0;margin-top: 0;">Installed.</p>');
									}
									
									var opts = {
										text: data.msg,
										addclass: "stack-bar-top",
										cornerclass: "",
										width: "50%",
										stack: stack_topcenter,
										type: alert_type,
										icon: false,
										animation: "slide"
									};
									
									new PNotify(opts);
								});
							}
						}
					}
				});			
		   }
		});
		
	}

	awesome_catalogue.initate_install_flow = function(slug,active_object,active_btn,instance,app_name='',app_slug=''){
		
		url=ajaxurl+'?action=aw2_install_obj&slug='+slug+'&active_object='+active_object+'&app_name='+app_name+'&app_slug='+app_slug;
		jQuery.get(url, function( response ) {
			instance.stop();
			var data = jQuery.parseJSON(response);
			var alert_type = 'error';
			
			var stack_bar_top = {"dir1": "down", "dir2": "right", "push": "top", "spacing1": 0, "spacing2": 0};
			var stack_topcenter = {"dir1": "down", "dir2": "right", "push": "top", "firstpos1": 25, "firstpos2": ($(window).width() / 4)};
			if(data.success ){
				
				alert_type = 'success';
				jQuery(active_btn).parent().html('<p class="brand-fifth-bg brand-gray-darker text-center" style="height: 35px;line-height: 35px;margin-bottom: 0;margin-top: 0;">Installed.</p>');
				awesome_catalogue.flush(data.nonce);
			}
			
			var opts = {
				text: data.msg,
				addclass: "stack-bar-top",
				cornerclass: "",
				width: "50%",
				stack: stack_topcenter,
				type: alert_type,
				icon: false,
				animation: "slide"					
			};
			
			new PNotify(opts);
			
		});
	}
	
	
	awesome_catalogue.initate_app_install_flow = function(app_title,slug,active_object,active_btn,instance){
		bootbox.dialog({
                title: "Install "+app_title,
                message: '<div class="row">  ' +
                    '<div class="col-md-12"> ' +
                    '<form class="form-horizontal"> ' +
                    '<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="name">App Name</label> ' +
                    '<div class="col-md-4"> ' +
                    '<input id="name" name="name" type="text" placeholder="Display label for App" class="form-control input-md" required> ' +
                    '<span class="help-block">App will show up with this name.</span> </div> ' +
                    '</div> ' +
                    '<div class="form-group"> ' +
					'<label class="col-md-4 control-label" for="name">App Slug</label> ' +
                    '<div class="col-md-4"> ' +
                    '<input id="app_slug" name="app_slug" type="text" placeholder="Slug of your App" class="form-control input-md" required> ' +
                    '<span class="help-block">Slug for App, this will show up in the URL.</span> </div> ' +
                    '</div> ' +
                    '</form> </div>  </div>',
                buttons: {
                    success: {
                        label: "Continue",
                        className: "btn-success",
                        callback: function () {
                            var name = $('#name').val();
                            var app_slug = $('#app_slug').val();
							awesome_catalogue.initate_install_flow(slug,active_object,active_btn,instance,name,app_slug);
                        }
                    }
                },
				onEscape: function() {
					instance.stop();
				}
            }
        );
	}
	
	awesome_catalogue.flush = function(nonce){
		var data = {
                action: 'aw2_clean_up', // AJAX callback
                nonce: nonce      // Nonce field value
            };
		
		jQuery.post(ajaxurl, data, function(response) {});
	}
}) (jQuery);

 jQuery(document).ready(function($){
	 
	$('#wpbody').height($('#adminmenuwrap').height());
	
	if(jQuery('.js-load-more').length>0){
		load_more  =Ladda.create( document.querySelector( '.js-load-more' ) );
	}
	
	var aw_current_page = awesome_catalogue.get_url_vars()["page"];

	if(!(typeof aw_current_page == "undefined")){
	
		if(aw_current_page.indexOf('awesome-studio') !== -1)
			awesome_catalogue.get_types();
	
	}
	
	$('.js-load-more').click(function(ev){
		page_n = $(this).attr('data-page_num');
		if(page_n) {
			load_more.start();		
			awesome_catalogue.get_catalogue_items(page_n);
		}	
	});
	
	$('body').on('click','.js-menu-item',function(){
		$('.js-menu-item').removeClass('js-active-menu active-item');
		$(this).addClass('js-active-menu active-item');
		$('.js-modules').addClass('js-replace');
		$('.js-load-more').attr('data-page_num',0);
		
		awesome_catalogue.get_catalogue_items();
	});
});