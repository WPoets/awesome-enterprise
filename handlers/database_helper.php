<?php

class awesome2_query{
	public $action=null;
	public $atts=null;
	public $content=null;
	public $status=false;
	
	function __construct($action,$atts,$content=null){
     if (method_exists($this, $action)){
		$this->action=$action;
		$this->atts=$atts;
		$this->content=trim($content);
		$this->status=true;
	 }
	}
	function run(){
     if (method_exists($this, $this->action))
		return call_user_func(array($this, $this->action));
     else
		aw2_library::set_error('Query Method does not exist'); 
	}
	
	function att($el,$default=null){
		if(array_key_exists($el,$this->atts))
			return $this->atts[$el];
		return $default;
	}

	function args(){
		if($this->content==null || $this->content==''){
			$return_value=array();	
		}
		else{
			$json=aw2_library::clean_specialchars($this->content);
			$json=aw2_library::parse_shortcode($json);		
			$return_value=json_decode($json, true);
			if(is_null($return_value)){
				aw2_library::set_error('Invalid JSON' . $this->content); 
				$return_value=array();	
			}
		}

	$arg_list = func_get_args();
	foreach($arg_list as $arg){
		if(array_key_exists($arg,$this->atts))
			$return_value[$arg]=$this->atts[$arg];
	}
		return $return_value;
	}

	
	function get_post(){
		if($this->att('post_slug')){
			aw2_library::get_post_from_slug($this->att('post_slug'),$this->att('post_type'),$post);
			return $post;			
		}
		else	
			return get_post($this->att('post_id'));
	}	

	function get_post_terms(){
		return wp_get_post_terms($this->att('post_id'), $this->att('taxonomy'), $this->args('orderby','order','fields') );
	}	
	
	function get_post_meta(){
		return get_post_meta($this->att('post_id'), $this->att('key'), $this->att('single',true) );
	}

	function all_post_meta(){
		$post_id = $this->att('post_id');
		
		if($this->att('post_slug') && $this->att('post_type')){
			aw2_library::get_post_from_slug($this->att('post_slug'),$this->att('post_type'),$post);
			$post_id = $post->ID;
		}
		
		$return_value = get_post_meta($post_id);
                if($return_value){
                    $temp = array();
                    foreach($return_value as $key=>$value){
                            if(count($value)===1)
                                    $temp[$key] = $value[0];
                            else
                                    $temp[$key] = $value;
                    }
                    $return_value = $temp;
                }
                
		
		unset($temp);
		return $return_value;
	}		

	function insert_post(){
		if($this->att('args'))
			$return_value= wp_insert_post($this->att('args'),true);
		else
			$return_value= wp_insert_post($this->args(),true);


		if(	is_object($return_value) && get_class($return_value)=='WP_Error'){
			aw2_library::set_error($return_value); 
			return;
		}
		return $return_value;	
	}	

	function update_post(){
		if($this->att('args')){
			$return_value= wp_update_post($this->att('args'),true);
		}
		else
			$return_value= wp_update_post($this->args(),true);
		
		if(	is_object($return_value) && get_class($return_value)=='WP_Error'){
			aw2_library::set_error($return_value); 
			return;
		}
		return $return_value;	
	}

	function update_post_status(){
	  $args=array("ID"=>$this->att('post_id'), "post_status"=>$this->att('post_status'));
	  $return_value= wp_update_post($args,true);
		if(	is_object($return_value) && get_class($return_value)=='WP_Error'){
			aw2_library::set_error($return_value); 
			return;
		}
		return;	
	}

	function update_post_meta(){
		if(!$this->att('meta_key')){
			$args=$this->args();
			foreach ($args as $key => $value) {
				update_post_meta($this->att('post_id'), $key, $value);
			}
			return;
		}

		if(!array_key_exists('meta_value',$this->atts))
			$this->atts['meta_value']=aw2_library::parse_shortcode($this->content);
		
		
		if(!array_key_exists('prev_value',$this->atts)){
			update_post_meta($this->att('post_id'), $this->att('meta_key'), $this->att('meta_value'));
			return;
		}
		
		update_post_meta($this->att('post_id'), $this->att('meta_key'), $this->att('meta_value'),$this->att('prev_value') );
		return ;
	}
	
	function delete_post_meta(){
		if(!$this->att('meta_key')){
			$args=$this->args();
			foreach ($args as $key => $value) {
				delete_post_meta($this->att('post_id'), $key, $value);
			}
			return;
		}

		if(!$this->att('meta_value') && $this->content != "")
			$this->atts['meta_value']=aw2_library::parse_shortcode($this->content);
		
		delete_post_meta($this->att('post_id'), $this->att('meta_key'), $this->att('meta_value'));
		return ;
	}
	
	function add_non_unique_post_meta(){
		if(!$this->att('meta_key')){
			$args=$this->args();
			foreach ($args as $key => $value) {
				add_post_meta($this->att('post_id'), $key, $value, false);
			}
			return;
		}

		if(!$this->att('meta_value'))
			$this->atts['meta_value']=aw2_library::parse_shortcode($this->content);
				
		add_post_meta($this->att('post_id'), $this->att('meta_key'), $this->att('meta_value'), false);
		return ;
	}

	function delete_post(){
		wp_delete_post($this->att('post_id'), $this->att('force_delete'));
		return ;
	}
	
	function trash_post(){
		wp_trash_post($this->att('post_id'));
		return ;
	}

	function set_post_terms(){
		if(!is_array($this->att('terms')))
			$terms=explode(",", $this->att('terms'));
		
		$terms = util::array_map_deep($terms, 'intval');
		
		if($this->att('slugs')){

			if(!is_array($this->att('slugs')))
				$pieces = explode(",", $this->att('slugs'));
			else	
				$pieces = $this->att('slugs');

			$term_array=array();

			foreach ($pieces as $value){
				$value = sanitize_title($value);
				$one_term=get_term_by( 'slug', $value, $this->att('taxonomy') );
				if(empty($one_term)){
					$one_term=wp_insert_term($value, $this->att('taxonomy'));
					$term_array[]=$one_term['term_id'];
				}
				else
					$term_array[]=$one_term->term_id;
			}
			$terms =$term_array;
		}
		
		
		wp_set_object_terms( $this->att('post_id'), $terms,$this->att('taxonomy'),$this->att('append'));
		return;
		
		
	}


	function get_posts(){

		if($this->att('args')){
			return get_posts( $this->att('args'));
		}
		else
			return get_posts( $this->args('name','posts_per_page','offset','category','category_name','orderby','order','include','exclude','meta_key','meta_value','post_type','post_mime_type','post_parent','author','post_status','suppress_filters') );
	}

	function get_pages(){
		return get_pages( $this->args('sort_order','sort_column','hierarchical','exclude','include','meta_key','meta_value','authors','child_of','parent','exclude_tree','number','offset','post_type','post_status') );
	}

	function wp_query(){
		if($this->att('args')){
			$args=aw2_library::get($this->att('args'));
			$query = new WP_Query($args);
			return $query;
		}
		
		$args=$this->args();
		if(array_key_exists('meta_query',$args)){
			$list = array("EQ", "NEQ", "GT",'LT','GTE','LTE');
			$replace_by = array("=", "!=", ">","<",">=","<=");
			
			foreach ($args['meta_query'] as $key => $value) {
				if(is_array($value) and array_key_exists('compare',$value)){
					$args['meta_query'][$key]['compare']=str_replace($list, $replace_by, $args['meta_query'][$key]['compare']);		
				}
			}
		}
		$query = new WP_Query($args);
		return $query;
	}


	function get_term_by(){
		return get_term_by( $this->att('field'),$this->att('value'),$this->att('taxonomy'),$this->att('output',ARRAY_A),$this->att('filter') );	
	}
	
	function get_term_meta(){
		if($this->att('single')=="false")
			$single= false;
		else
			$single= true;
		
		return get_term_meta( $this->att('term_id'),$this->att('key'),$single );	
	}

	function insert_term(){
		wp_insert_term( $this->att('term'),$this->att('taxonomy'),$this->args('alias_of','description','parent','slug'));
		return;	
	}

	function delete_term(){
		wp_delete_term( $this->att('term_id'),$this->att('taxonomy'),$this->args());
		return;	
	}

	function get_terms(){
		$return_value=get_terms( $this->att('taxonomies'),$this->args('orderby','order','hide_empty','include','exclude','exclude_tree','number','offset','fields','name','slug','hierarchical','search','name__like','description__like','pad_counts','get','child_of','parent','childless','cache_domain','update_term_meta_cache','meta_query'));
		if(	is_object($return_value) && get_class($return_value)=='WP_Error'){
			aw2_library::set_error($return_value); 
			return;
		}
		return $return_value;
	}	

	function get_comment(){
		return get_comment($this->att('id'), $this->att('output',ARRAY_A));
	}
	
	function get_comments(){
		return get_comments($this->args());
	}

	function get_results(){
		global $wpdb;
		
		$str='/*' . PHP_EOL;
		$str.='query:get_results' . PHP_EOL;
		$str.='user:	' . \aw2_library::get('app.user.email') . PHP_EOL;
		$str.='module:	' . \aw2_library::get('module.slug') . PHP_EOL;
		$str.='post_type:	' . \aw2_library::get('module.collection.post_type') . PHP_EOL;
		$str.='template:	' . \aw2_library::get('template.name') . PHP_EOL;
		$str.='*/' . PHP_EOL;

		$sql=$str . aw2_library::parse_shortcode($this->content);

		
		$results = $wpdb->get_results($sql,ARRAY_A);
		return $results;
	}

	function get_row(){
		global $wpdb;
		$sql=aw2_library::parse_shortcode($this->content);
		$results = $wpdb->get_row($sql,ARRAY_A);
		return $results;
	}

	function get_col(){
		global $wpdb;
		$sql=aw2_library::parse_shortcode($this->content);
		$results = $wpdb->get_col($sql);
		return $results;
	}

	function get_var(){
		global $wpdb;
		$sql=aw2_library::parse_shortcode($this->content);
		$results = $wpdb->get_var($sql);
		return $results;
	}
	
	function query(){
		global $wpdb;
		$sql=aw2_library::parse_shortcode($this->content);
		$results = $wpdb->query($sql);	
		return ;
	}

	function get_user_by(){
		return get_user_by($this->att('field'),$this->att('value'));
	}
	
	function update_user_meta(){
		if(!$this->att('meta_key')){
			$args=$this->args();
			foreach ($args as $key => $value) {
				update_user_meta($this->att('user_id'), $key, $value);
			}
			return;
		}

		if(!$this->att('meta_value'))
			$this->atts['meta_value']=aw2_library::parse_shortcode($this->content);
		
		update_user_meta($this->att('user_id'), $this->att('meta_key'), $this->att('meta_value'),$this->att('prev_value') );
		return ;
	}	
	
	function get_user_meta(){
		return get_user_meta($this->att('user_id'), $this->att('key'), $this->att('single',true) );
	}	
	
	function get_users(){
		return get_users( $this->args('blog_id','role','meta_key','meta_value','meta_compare','meta_query','date_query','include','exclude','orderby','order','offset','search','number','count_total','fields','who') );
	}

	function users_builder(){
		$ref=&aw2_library::get_array_ref('users_builder');
		$part=$this->att('part','start');
		if($part=='run'){
			$return_value= new WP_User_Query($ref);

			$return_value->query_array=$ref;
			$ref=&aw2_library::get_array_ref();
			unset($ref['users_builder']);
			return $return_value;
		}

		$args=aw2_library::get_clean_args($this->content,$atts);

		if($part=='start'){
			$ref=$this->args('blog_id','role','role__in','role__not_in','meta_key','meta_value','meta_compare','include','exclude','orderby','order','offset','search','search_columns','number','count_total','fields','who','paged');
		}	
		
		if($part=='meta_query'){
			if(!array_key_exists('meta_query', $ref))$ref['meta_query']=array();
			$ref['meta_query'][]=$this->args('key','value','compare','type');		
		
			if(count($ref['meta_query'])>1 && !array_key_exists('relation', $ref['meta_query']))
				$ref['meta_query']['relation']=$this->att('relation','AND');	
			
		}

		if($part=='date_query'){
			if(!array_key_exists('date_query', $ref))$ref['date_query']=array();
			$ref['date_query'][]=$this->args();		
		}
		
		return ;

	}
	
	function posts_builder(){
		$ref=&aw2_library::get_array_ref('posts_builder');
		$part=$this->att('part','start');
		if($part=='run'){
			$return_value= new WP_Query($ref);
			//if(function_exists('relevanssi_do_query')){
			//	relevanssi_do_query($return_value);
			//}
			$return_value->query_array=$ref;
			$ref=&aw2_library::get_array_ref();
			unset($ref['posts_builder']);
			return $return_value;
		}

		$args=aw2_library::get_clean_args($this->content,$atts);

		if($part=='start'){
			$ref=$this->args('posts_per_page','offset','category','category_name','orderby','order','include','exclude','meta_key','meta_value','post_type','post_mime_type','post_parent','author','post_status','suppress_filters','title');
		}	

		if($part=='tax_query'){
			if(!array_key_exists('tax_query', $ref))$ref['tax_query']=array();
			
			$ref['tax_query'][]=$this->args('taxonomy','field','terms','include_children','operator');	
		
			if(count($ref['tax_query'])>1 && !array_key_exists('relation', $ref['tax_query']))
				$ref['tax_query']['relation']=$this->att('relation','AND');	
			
		}
		
		if($part=='meta_query'){
			if(!array_key_exists('meta_query', $ref))$ref['meta_query']=array();
			$ref['meta_query'][]=$this->args('key','value','compare','type');		
		
			if(count($ref['meta_query'])>1 && !array_key_exists('relation', $ref['meta_query']))
				$ref['meta_query']['relation']=$this->att('relation','AND');	
			
		}

		if($part=='date_query'){
			if(!array_key_exists('date_query', $ref))$ref['date_query']=array();
			$ref['date_query'][]=$this->args();		
		}
		
		return ;

	}
	
	function insert_comment(){
		$args=$this->args();
		$retrun_value='';
		if(isset($args['comment_post_ID'])){
			$return_value= wp_insert_comment($this->args());
		}
		
		if(!is_null($this->att('post_id'))){
			$comment= trim(aw2_library::parse_shortcode($this->content));
			$args=array(
					"comment_post_ID"=>$this->att('post_id'),
					"comment_author"=>$this->att('author_name'),
					"comment_author_email"=>$this->att('author_email'),
					"comment_author_url"=>$this->att('author_url'),
					"comment_content"=>$comment,
					'comment_type' => $this->att('type'),
					'comment_parent' => $this->att('parent'),
					'user_id' =>$this->att('user_id'),
					"comment_approved"=>$this->att('approved')
			);
			
			$return_value= wp_insert_comment($args);
		}
		
		return $return_value;	
	}	

	function delete_revisions(){
		global $wpdb;
		
		$this->content="delete FROM ".$wpdb->posts." where post_type='revision' and post_parent=" . $this->att['post_id'];
		return $this->get_results();
	}
	
	function term_exists(){
		return(term_exists( $this->att('term'),$this->att('taxonomy'),0));			
	}

}





