 var load_more;
 var install_module;
 var aw2_dist={};
(function ($) {
  
  var copyEl = $('<div/>').addClass('btn-group copy-button-group');
  var copyElButton = $('<button/>').attr('data-copy-type', '').attr('type', 'button').addClass('btn btn-primary btn-sm copy-button').text('Copy');
  copyElButton.appendTo(copyEl);
  copyEl.attr('style', 'display: none;');
  copyEl.appendTo('body');
  var clipboard;
  aw2_dist.setupMouseEvents=function() {
    if (!(/iPhone|iPad/i.test(navigator.userAgent))) {
      $('.shortcode-column').on('mouseenter', function (ev) {
        var cont = $(ev.currentTarget);
        copyEl.show();
        copyEl.appendTo(cont);
      });
      if (clipboard) {
        clipboard.destroy();
      }
      aw2_dist.setupCopyButton();
    }
  }
  aw2_dist.setupCopyButton=function() {
	if(typeof Clipboard != "undefined"){
		clipboard = new Clipboard('.copy-button', {
		  text: function (trigger) {
			var button = $(trigger);
			var embed = button.attr('data-copy-embed');
			var code = $('.shortcode-content', button.parents('.shortcode-column')).text();
			return code;
		  }
		});
		clipboard.on('success', function (e) {
		  var button = $(e.trigger);
		  var btContainer = button.parents('.copy-button-group').tooltip({
			trigger: 'manual',
			placement: 'bottom',
			title: 'Copied!'
		  });
		  btContainer.tooltip('show');
		  setTimeout(function () {
			btContainer.tooltip('hide');
			btContainer.tooltip('destroy');
		  }, 1000);
		  //ga('send', 'event', 'library', 'copied', button.parents('.library-column').attr('data-lib-name'), 4);
		});
		clipboard.on('error', function (e) {
		  var button = $(e.trigger);
		  var msg;
		  if (/Mac/i.test(navigator.userAgent)) {
			msg = 'Press ⌘-C to copy';
		  } 
		  else {
			msg = 'Press Ctrl-C to copy';
		  }
		  var btContainer = button.parents('.copy-button-group').tooltip({
			trigger: 'manual',
			placement: 'bottom',
			title: msg
		  });
		  btContainer.tooltip('show');
		  setTimeout(function () {
			btContainer.tooltip('hide');
			btContainer.tooltip('destroy');
		  }, 1000);
		  //ga('send', 'event', 'library', 'copied', button.parents('.library-column').attr('data-lib-name'), 4);
		});
	}
  }
  aw2_dist.setupMouseEvents();
  
	aw2_dist.get_sh_code=function(elem, shid){
		fields=$('.shortcode-wrapper .shortcode-form').find('.cmb2-metabox').find(':input').serializeArray();
		sh_code='';
		jQuery.each( fields, function( key, obj ) {
		  if(obj.name != 'aw2_trigger_when')
			sh_code +=' '+obj.name  + '="' + obj.value.replace('"','&quot;') +'"' ;
		});
		slug=$(elem).parents('.shortcode-form').find('.slug').val();
		if(jQuery.trim(slug).length > 0){
			sh_code= '[aw2.module slug="'+slug+'" '+sh_code+' shid="'+shid+'"][/aw2.module]';
		} 
		return sh_code;
	}

	aw2_dist.getUrlVars=function(){
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

	aw2_dist.aw2_get_types=function(){
		jQuery.get(ajaxurl+'?action=aw2_get_type_list',function( response ) {
			$('#aw2-loader').hide();

			var data = jQuery.parseJSON(response);//parse JSON

			if(data.error){
				var msg='<div class=error>'+data.message+'</div>';
				$('.js-modules').html(msg);  
			}
			else {
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
				aw2_dist.setPriorityNav();
				aw2_dist.aw_get_modules();
				
			}
		 
		});
	}

	aw2_dist.aw2_get_apps=function(){
		$('#aw2-loader').show();
		
		appurl =  ajaxurl+'?action=aw2_get_apps_list';

		
		$.get(appurl,function( response ) {
			$('#aw2-loader').hide();
			//load_more.stop();
			var data = $.parseJSON(response);//parse JSON
			if(data.error){
				var msg='<div class=error>'+data.message+'</div>';
				$('.js-apps').html(msg);
			}
			else {
				$.each(data.results, function(i, item) {
					$('.js-apps').append(aw2_dist.aw2_app_template(item));

					$('.age').age();
					//install_module  =Ladda.create( document.querySelector( '.js-install' ) );
					aw2_dist.ladda_install_bind('.js-install');
					aw2_dist.ladda_reinstall_bind('.js-reinstall');
					
					$(".js-modules").imagesLoaded().always( function( instance ) {
						//$.fn.matchHeight._update();
						$('.img-wrapper').matchHeight({ byRow: false });
						$('.picture-item-wrap').matchHeight({ byRow: false });
						$('.picture-item').matchHeight({ byRow: false });
					});
				});
			}		 
		});
	}

	aw2_dist.setPriorityNav=function(){
		var nav = priorityNav.init({
			//initClass: "js-priorityNav",
			mainNavWrapper: ".menu",
			mainNav: "ul.menuitems",
			navDropdownLabel: "More +",
			navDropdownBreakpointLabel: "Module Types",
			breakPoint: 100,

		});
	}

	aw2_dist.aw_get_local_modules=function(page_num){
		$('#aw2-loader').show();
		page_num = typeof page_num !== 'undefined' ? page_num : 0;
		
		if(page_num ==0)
			moduleurl =  ajaxurl+'?action=aw2_get_local_modules_list';
		else
			moduleurl =  ajaxurl+'?action=aw2_get_local_modules_list&pagenum='+page_num;
		type= $('.js-active-menu').attr('href');
		type=type.replace('#','');
		
		if(type!="all") {
			moduleurl +="&type="+type;
		}
		
		jQuery.get(moduleurl,function( response ) {
			$('#aw2-loader').hide();
			load_more.stop();
			var data = jQuery.parseJSON(response);//parse JSON
			
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
				jQuery.each(data.modules, function(i, item) {
					$('.js-modules').append(aw2_dist.aw2_local_module_template(item));
					//$('.js-modules').append('<p>Append here!!</p>');
					
				});
				$('.js-modules').append('<div class="shortcode-wrapper col-xs-12"></div>');
				if(data.total > $('.js-modules').children().length) {
					$('.js-load-more').show();
					$('.js-load-more').attr('data-page_num',parseInt(data.page_num)+1);
				}
				else
					$('.js-load-more').hide();
				
				$(".js-modules").imagesLoaded().always( function( instance ) {
					//jQuery.fn.matchHeight._update();
					$('.img-wrapper').matchHeight({ byRow: false });
					$('.picture-item-wrap').matchHeight({ byRow: false });
					$('.picture-item').matchHeight({ byRow: false });
				});
				$('.shortcode-form-wrapper').width($('.js-modules').width()-15);
			}	 
		});
			
		
		
	}

	aw2_dist.aw_get_modules=function(page_num) {
		
		$('#aw2-loader').show();
		page_num = typeof page_num !== 'undefined' ? page_num : 0;
		
		if(page_num ==0)
			moduleurl =  ajaxurl+'?action=aw2_get_modules_list';
		else
			moduleurl =  ajaxurl+'?action=aw2_get_modules_list&pagenum='+page_num;
		type= $('.js-active-menu').attr('href');
		type=type.replace('#','');

		if(type!="all") {
			moduleurl +="&type="+type;
		}	
		
		jQuery.get(moduleurl,function( response ) {
			$('#aw2-loader').hide();
			load_more.stop();
			var data = jQuery.parseJSON(response);//parse JSON

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
					$('.js-modules').append(aw2_dist.aw2_module_template(item));
					
				});
				if(data.total > $('.js-modules').children().length) {
					$('.js-load-more').show();
					$('.js-load-more').attr('data-page_num',parseInt(data.page_num)+1);
				}
				else
					$('.js-load-more').hide();
				
				$('.age').age();
				//install_module  =Ladda.create( document.querySelector( '.js-install' ) );
				aw2_dist.ladda_install_bind('.js-install');
				aw2_dist.ladda_reinstall_bind('.js-reinstall');
				
				$(".js-modules").imagesLoaded().always( function( instance ) {
					//jQuery.fn.matchHeight._update();
					$('.img-wrapper').matchHeight({ byRow: false });
					$('.picture-item-wrap').matchHeight({ byRow: false });
					$('.picture-item').matchHeight({ byRow: false });
				});
			}		 
		});
	}

	aw2_dist.ladda_install_bind=function(selector){
		aw2_dist.ladda_bind( selector,{
		   callback: function( instance ) {
				slug=this.getAttribute('data-slug');	
				url=ajaxurl+'?action=aw2_get_server_module&slug='+slug;
				jQuery.get(url, function( data ) {
					instance.stop();
					var stack_bar_top = {"dir1": "down", "dir2": "right", "push": "top", "spacing1": 0, "spacing2": 0};
					var stack_topcenter = {"dir1": "down", "dir2": "right", "push": "top", "firstpos1": 25, "firstpos2": ($(window).width() / 4)};
					if(data.indexOf('Installed') !== -1){
						var alert_type = 'success';
					}else if(data.indexOf('exists') !== -1){
						var alert_type = 'info';
					}else{
						var alert_type = 'error';
					}
					var opts = {
						text: data,
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
		});
	}

	aw2_dist.ladda_reinstall_bind=function(selector){
			
		aw2_dist.ladda_bind( selector,{
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
									url=ajaxurl+'?action=aw2_get_server_module&upgrade=yes&slug='+slug;
									jQuery.get(url, function( data ) {
										instance.stop();
										if(data.indexOf('Installed') !== -1){
											var alert_type = 'success';
										}else if(data.indexOf('exists') !== -1){
											var alert_type = 'info';
										}else{
											var alert_type = 'error';
										}
										var opts = {
											text: data,
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
								url=ajaxurl+'?action=aw2_get_server_module&reinstall=yes&slug='+slug;
								jQuery.get(url, function( data ) {
									instance.stop();
									if(data.indexOf('Installed') !== -1){
										var alert_type = 'success';
									}else if(data.indexOf('exists') !== -1){
										var alert_type = 'info';
									}else{
										var alert_type = 'error';
									}
									var opts = {
										text: data,
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
					/* callback:	function(result) {
								if(result === true){									
									url=ajaxurl+'?action=aw2_get_server_module&reinstall=yes&slug='+slug;
									jQuery.get(url, function( data ) {
										instance.stop();
										var stack_bar_top = {"dir1": "down", "dir2": "right", "push": "top", "spacing1": 0, "spacing2": 0};
										var stack_topcenter = {"dir1": "down", "dir2": "right", "push": "top", "firstpos1": 25, "firstpos2": ($(window).width() / 4)};
										if(data.indexOf('Installed') !== -1){
											var alert_type = 'success';
										}else if(data.indexOf('exists') !== -1){
											var alert_type = 'info';
										}else{
											var alert_type = 'error';
										}
										var opts = {
											text: data,
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
								}else{
									instance.stop();
								}
							} */
				});			
		   }
		});
		
	}


	aw2_dist.ladda_app_install_bind=function(selector){
		aw2_dist.ladda_bind( selector,{
		   callback: function( instance ) {
				var id=$(this).parents(".app-details").find("input[name='pack_id']").val();
				var app_name=$(this).parents(".app-details").find("input[name='app_name']").val();
				var app_slug=$(this).parents(".app-details").find("input[name='app_slug']").val();
				if(app_name == '' || app_slug == ''){
					alert('Required fields left empty');
					instance.stop();
					return;
				}
				$(".form-group").hide();
				
				var url=ajaxurl+'?action=aw2_get_server_app_starter_pack&id='+id+'&app_name='+app_name+'&app_slug='+app_slug;
				$.get(url, function( response ) {
					instance.stop();
					$(".install-app").hide();
					var data = jQuery.parseJSON(response);//parse JSON
					if(data.error){
						var msg='<div class=error>'+data.message+'</div>';
						$('.app-details').html(msg);
					}
					else {
						aw2_dist.install_app(data.results);					
					}	
				});
		   }
		});
	}

	aw2_dist.linear_promises=function(arr){
		var d = $.Deferred();
		function one_promise(){
			arr.shift();
			if(arr.length===0)
				d.resolve();
			else{
				$.when(arr[0]()).then(function(){
						one_promise();
				});
			}
		}    
		
		if (arr.length === 0)
			d.resolve();
		else{
			var result;
			$.when(arr[0]()).then(function(){
					one_promise();
			});
		}

		return d.promise();
	}

	aw2_dist.install_app_ajax=function(install_data){
		var d = $.Deferred();

		var app_url=ajaxurl+'?action=aw2_install_app';
		var request = $.ajax({
			type:"POST",
			url: app_url,
			data:{app_data:install_data}
		});
		
		request.done(function( response ) {
			$('.app-details').append(response);
			d.resolve();
		});
		 
		request.fail(function( jqXHR, textStatus ) {
			alert('Unable to load');
		});
		
		return d.promise();	
	}

	aw2_dist.install_app=function(install_data){
		var d=$.Deferred();
		var promises=[];
		promises.push(function(){
			return aw2_dist.install_app_ajax(install_data.app);
		})
		
		promises.push(function(){
			return aw2_dist.install_app_ajax(install_data.settings);
		})

		promises.push(function(){
			return aw2_dist.install_app_ajax(install_data.pages);
		})
		
		$.when(aw2_dist.linear_promises(promises)).then(function(){
			d.resolve();
			var app_slug=$("form.app-details").find("input[name='app_slug']").val();
			$('.app-actions .run-app').attr('href', homeurl+'/'+app_slug);
			$('.app-actions').show();
		})
		return d.promise();		
	}

	aw2_dist.aw2_app_template=function(item) {
		str='<div class="col-md-4 col-sm-6 col-xs-12">';
			str +='<div class="picture-item">';
				str +='<div class="img-wrapper">';
					str += '<img height="374" src="'+item.image+'">';
				str += '</div>';
				str +='<div class="picture-item-wrap">';
					str +='<h3>'+item.title+'</h3>';
					str +='<p class="excerpt">'+item.excerpt+'</p>';
					str +='<p class="age-wrapper"><span>Last Updated: </span><time datetime="'+item.last_updated+'" class="age">'+item.last_updated+'</time></p>';
				str +='</div>';
				str +='<div class="picture-item-action">';
					str +='<a href="?page=app-starter-pack&app='+item.slug+'" class="btn btn-primary btn-install" data-id="'+item.id+'">Install</a>';
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
								str +='<button type="button" class="btn btn-primary ladda-button js-app-reinstall" data-id="'+item.id+'" data-style="slide-down">Re-Install</button>';
							str +='</li>';
						}else{
							str +='<li>';
								str +='<button type="button" class="btn btn-primary ladda-button js-app-install" data-id="'+item.id+'" data-style="slide-down">Install</button>';
							str +='</li>';
						}					
					str +='</ul>';
					str +='<div class="preview-details">';
						str +='<h3>'+item.title+'</h3>';
						str +='<p class="excerpt">'+item.details+'</p>';
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

	aw2_dist.aw2_module_template=function(item) {
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
						str +='<button type="button" class="btn btn-primary ladda-button js-reinstall" data-slug="'+item.slug+'" data-id="'+item.id+'" data-style="slide-down">Re-Install</button>';
					}else{
						str +='<button type="button" class="btn btn-primary ladda-button js-install" data-slug="'+item.slug+'" data-style="slide-down">Install</button>';
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

	aw2_dist.aw2_local_module_template=function(item){
		
		var aw_current_page = aw2_dist.getUrlVars()["page"];
		var hide_insert_button = "";
		var set_trigger_button = '';
		
		if(aw_current_page != undefined){
			var hide_insert_button = 'hide-insert-button';
			var set_trigger_button = "set-trigger";
		}
		
		str='<div class="col-md-4 col-xs-6 installed-items-wrapper">';
			str +='<div class="picture-item installed-item '+set_trigger_button+'">';
				str +='<div class="img-wrapper">';
					str +='<img src="'+item.image+'">';
				str +='</div>';
				str +='<div class="picture-item-wrap">';
					str +='<h3>'+item.title+'</h3>';
					str +='<p class="excerpt">'+item.excerpt+'</p>';
				str +='</div>';
				str +='<div class="picture-item-action"><button type="button" class="btn btn-primary ladda-button js-get-shortcode" data-slug="'+item.slug+'" data-style="slide-down">Get Shortcode</button><button type="button" class="btn btn-gray-darker ladda-button set-trigger-button" data-slug="'+item.slug+'" data-trigger="yes">Set Trigger</button><button type="button" class="btn btn-gray-darker ladda-button" data-slug="'+item.slug+'">Re-install</button></div>';
			str +='</div>';
			str +='<div class="row shortcode-form-wrapper '+hide_insert_button+' '+set_trigger_button+'">';
				str +='<div class="col-xs-12 shortcode-form-container no-padding">';
					str +='<a class="back" href="#">Close</a>';
					str +='<div id="code_samples" class="col-md-10 col-xs-12 col-md-offset-1">';
						str += '<div class="gap-5"></div>';
						str += '<div class="col-md-5 col-xs-12">';
								str += '<h3>'+item.title+'</h3>';
								str += '<div class="gap-4"></div>';
								str += '<img src="'+item.image+'">';
								str += '<p class="excerpt">'+item.excerpt+'</p>';
						str +='</div>';
						str += '<div class="col-md-7 col-xs-12 shortcode-form"></div>';
					str +='</div>';
					str +='<div id="generated_shortcode" class="col-md-6 col-xs-12 col-md-offset-3 pull-right"></div> ';
				str +='</div>';
			str +='</div>';
		str +='</div>';
		
		return str;
	}

	aw2_dist.ladda_bind=function(target, options ) {
		
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

	aw2_dist.aw2FetchModuleMeta=function(slug, elem){
		var url = ajaxurl+ "?action=aw2_cmb2_form&slug="+slug;
		/* if(trigger == 'yes')
			url = url+"&trigger=yes"; */
		
		jQuery.get( url, function( html ) {
		  $(".loader").hide();	
		  $("#aw2_cmb").show();	
		  $("#aw2_cmb_insert").show();
		  if (typeof elem != "undefined") {
			$(elem).parents('.installed-items-wrapper').find('.shortcode-form').html(html);
			$('.shortcode-wrapper').html($(elem).parents('.installed-items-wrapper').find('.shortcode-form-wrapper').clone());
		  }
		});
	}

	aw2_dist.insertAtCaret=function(areaId,text) {
		var txtarea = document.getElementById(areaId);
		var strPos = 0;
		var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? "ff" : (document.selection ? "ie" : false ) );
		if (br == "ie") { 
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart ('character', -txtarea.value.length);
			strPos = range.text.length;
		}
		else if (br == "ff") strPos = txtarea.selectionStart;

		var front = (txtarea.value).substring(0,strPos);  
		var back = (txtarea.value).substring(strPos,txtarea.value.length); 
		txtarea.value=front+text+back;
		strPos = strPos + text.length;
		if (br == "ie") { 
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart ('character', -txtarea.value.length);
			range.moveStart ('character', strPos);
			range.moveEnd ('character', 0);
			range.select();
		}
		else if (br == "ff") {
			txtarea.selectionStart = strPos;
			txtarea.selectionEnd = strPos;
			txtarea.focus();
		}
	}
	
	aw2_dist.randomString=function(len, charSet) {
					charSet = charSet || 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
					var randomString = '';
					for (var i = 0; i < len; i++) {
						var randomPoz = Math.floor(Math.random() * charSet.length);
						randomString += charSet.substring(randomPoz,randomPoz+1);
					}
					return randomString;
				} 
  
}) (jQuery);


 jQuery(document).ready(function($){
	 
	$('#wpbody').height($('#adminmenuwrap').height());
	
	//get all terms
	//load_more  =$( '.js-load-more' ).ladda();
	
	
	if(jQuery('.js-load-more').length>0){
		load_more  =Ladda.create( document.querySelector( '.js-load-more' ) );
	}
	
	if(jQuery('.aw2_cmb_set_trigger').length>0){
		trigger_ladda_button  =Ladda.create( document.querySelector( '.aw2_cmb_set_trigger' ) );
	}
	
	//Ladda.bind( '.js-install');
	var aw_current_page = aw2_dist.getUrlVars()["page"];

	if(!(typeof aw_current_page == "undefined")){
		if(aw_current_page.indexOf('awesome-modules-catalogue') !== -1)
			aw2_dist.aw2_get_types();
		console.log('in/out');
		if(aw_current_page.indexOf('awesome-studio') !== -1)
			aw2_dist.aw2_get_apps();
	
	}
	
	if($('.menuitems').find('.js-menu-item').hasClass('installed-item'))
		aw2_dist.aw_get_local_modules();
	
	$('body').on('click','.js-menu-item',function(){
		$('.js-menu-item').removeClass('js-active-menu active-item');
		$(this).addClass('js-active-menu active-item');
		$('.js-modules').addClass('js-replace');
		$('.js-load-more').attr('data-page_num',0);
		
		if($(this).hasClass('installed-item'))
			aw2_dist.aw_get_local_modules();
		else
			aw2_dist.aw_get_modules();
	});
	
	$('body').on('click','a.info',function(e){
		e.preventDefault();
		$(this).parents('.preview-actions').next('.preview-details').slideToggle('slow');
	});
		
	$('body').on('click','#install_module_btn',function(){
		slug=$(this).data('slug');		
		url=ajaxurl+'?action=aw2_get_server_module&slug='+slug;
		jQuery.get(url, function( data ) {
			aw2_dist.aw2FetchModuleMeta(slug);        
		});	
	});
	
	$('body').on('click','.js-get-shortcode, .set-trigger-button',function(){
		slug=$(this).data('slug');
		$('.shortcode-wrapper').addClass('flip');
		aw2_dist.aw2FetchModuleMeta(slug, this);
		$(this).parents('.installed-items-wrapper').addClass('flip');
	});
	
	$(document).on('click','.back',function(e){
		e.preventDefault();
		$('.installed-items-wrapper').removeClass('flip');
		$('.shortcode-wrapper').removeClass('flip');
	});
	
	$('#slug_button').click(function(){
	  	slug=$('.js-aw2-slug').val();
		if(jQuery.trim(slug).length > 0){
			$(".loader").show();
			aw2_dist.aw2FetchModuleMeta(slug);  
		}  
	});
  
	$(document).on('click','.aw2_cmb,.aw2_cmb_insert',function(e){
		
		var newid = aw2_dist.randomString(8);
	
		sh_code = aw2_dist.get_sh_code(this, newid);
		$(document).data(newid,sh_code);

		if($(e.target).hasClass('aw2_cmb_insert')){
			
			var is_ace_active = typeof ace;
			var is_tinymce_active = (typeof tinyMCE != "undefined") && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden();
			var featured_img = "";
			var slug = $(this).parents('.shortcode-form').find('.slug').val();
			
			if(is_ace_active != "undefined"){
				var editor = ace.edit("ace_ui_code");
				editor.insert(sh_code);
			}
			
			if($('div.wp-editor-wrap').hasClass('html-active')){
				/*Insert the sh_code*/
				aw2_dist.insertAtCaret('content', sh_code);
				moduleurl = ajaxurl+'?action=aw2_get_featured_image&slug='+slug;
				jQuery.get(moduleurl,function( response ) {
					featured_src = response;
					featured_img = "<img id='"+newid+"' width='250' class='aw2-shortcode' data-module_slug='"+slug+"' src='"+featured_src+"' />";
					var slugid = slug+newid;
					$(document).data(slugid,featured_img);
				})
			}			
			if($('div.wp-editor-wrap').hasClass('tmce-active')){
				//get the featured image url using ajax
				
				featured_img = $(document).data(slug);
				if(featured_img == "" || typeof featured_img == "undefined"){
					moduleurl = ajaxurl+'?action=aw2_get_featured_image&slug='+slug;
					jQuery.get(moduleurl,function( response ) {
						featured_src = response;
						featured_img = "<img id='"+newid+"' width='250' class='aw2-shortcode' data-module_slug='"+slug+"' src='"+featured_src+"' />";
						var slugid = slug+newid;
						$(document).data(slugid,featured_img);
						tinyMCE.activeEditor.execCommand('mceInsertContent', false, featured_img);
					})
				}else{
					tinyMCE.activeEditor.execCommand('mceInsertContent', false, featured_img);
				}
			}
			
			$('.shortcode-wrapper #generated_shortcode').html(sh_code);
			$('.shortcode-wrapper #generated_shortcode').show();
		}else{	
			if($('.shortcode-column').length > 0){
				$('.shortcode-column').addClass('active');
			}
			$('.shortcode-wrapper #generated_shortcode').html(sh_code);
			$('.shortcode-wrapper #generated_shortcode').show();
		}
	});

	$(document).on('click','.aw2_cmb_set_trigger',function(e){
		trigger_ladda_button.start();		
		var newid = aw2_dist.randomString(8);
		sh_code = aw2_dist.get_sh_code(this, newid);
		slug=$(this).parents('.shortcode-form').find('.slug').val();
		title = slug+"-trigger";
		trigger=$('.shortcode-wrapper .shortcode-form').find('.cmb2-metabox').find('#aw2_trigger_when').val();
		trigger_url = ajaxurl+'?action=aw2_set_trigger&title='+title+'&trigger='+trigger+'&sh_code='+sh_code+'&slug='+slug;

		jQuery.get(trigger_url,function( response ) {
			$('.shortcode-wrapper #generated_shortcode').html(response);
			$('.shortcode-wrapper #generated_shortcode').show();
			trigger_ladda_button.stop();
		});

	});
	
	if(typeof tinyMCE != "undefined"){
	 tinyMCE.on('addEditor',function(editor){
		if(editor.editor.id!='content')return;
		
		tinyMCE.get('content').on('BeforeSetContent', function( event ) {
			//alert('fired visual');
			 var $html=$('<div>' + event.content + '</div>');
			 var html=$html.html();
			 var html = html.replace(/(\[aw2.module.*?slug=(?:"(.*?)"|\'(.*?)\'|([-a-zA-Z0-9_.]+)).*?shid=(?:"(.*?)"|\'(.*?)\'|([-a-zA-Z0-9_.]+))\]((?:.|[\r\n])*?)\[\/aw2.module\])/g, function replacer(match,p1,p2,p3,p4,p5,p6,p7,offset, string) {
				//var re = /([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/g;
				//var result = re.exec(p1);
				
				var slug;
				if(typeof p2 != "undefined")
					slug = p2;
				if(typeof p3 != "undefined")
					slug = p3;
				if(typeof p4 != "undefined")
					slug = p4;
				
				var shid;
				if(typeof p5 != "undefined")
					shid = p5;
				if(typeof p6 != "undefined")
					shid = p6;
				if(typeof p7 != "undefined")
					shid = p7;
				
				slugshid = slug+shid;
				$(document).data(shid,p1);
				
				var featuredImg= $(document).data(slugshid);			
			
				if(featuredImg == "" || typeof featuredImg == "undefined"){
					return p1;
				}else{
					return featuredImg;
				}
			 });
			event.content=html;
		})

		tinyMCE.get('content').on('PostProcess', function( event ) {
			//alert('fired text');
			var $html=$('<div>' + event.content + '</div>');

			$html.find('img.aw2-shortcode').each(function() {
				$(this).replaceWith(function() { 
					return $(document).data($(this).attr('id'));
				})
			});

		  event.content=$html.html();
		})
		
	 });
	}
	
	$('.js-load-more').click(function(ev){
		page_n = $(this).attr('data-page_num');
		if(page_n) {
			load_more.start();		
			if($(this).hasClass('installed-items'))
				aw2_dist.aw_get_local_modules(page_n);
			else
				aw2_dist.aw_get_modules(page_n);
		}	
	});
	
	$(document).on('click','button.priority-nav-is-visible',function(e){
		e.preventDefault();
	});
	
	aw2_dist.ladda_app_install_bind('.install-app');
	
	$('.period').on('change',function(e){
		var period = $(this).val();
		var url      = window.location.href+'&period='+period;
		window.location = url;
	});
	
	$('.interval').on('change',function(e){
		var interval = $(this).val();
		var url      = window.location.href+'&interval='+interval;
		window.location = url;
	});
	
	var getParams = aw2_dist.getUrlVars();
	if(getParams.hasOwnProperty("period") && getParams.period != "")
		$('.period').val(getParams.period);
	if(getParams.hasOwnProperty("interval") && getParams.interval != "")
		$('.interval').val(getParams.interval);

});