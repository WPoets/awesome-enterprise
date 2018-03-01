<?php
/***
*	Script to give generic Excel File import layer, allowing you to import large files. 
*	It fires a "row-import-data-<type>" action hook with parsed row data for you to import
**/

/*add_action('admin_menu', 'monoframe_import_data');
*/
function monoframe_import_data($value,$type){
    add_menu_page('Import Data '.$value, 'Import Data '.$value, 'administrator','import-data-'.$type, 'monoframe_import_data_page');
	
	//also check if sheets dir exists if not create one.
    $upload_dir = wp_upload_dir();
	if(!is_dir( $upload_dir['basedir'].'/sheets'))
	{
		mkdir($upload_dir['basedir'].'/sheets');
	}
}

function monoframe_import_data_page(){

	if( isset( $_FILES['file'] ) && $_FILES['file']['name'] !=''  && isset($_POST['file_type']) ) {
		
			echo '<div id="message" class="updated below-h2">';
				monoframe_show_progress();
				monoframe_import_data_func();
			
			echo '</div>';
			
			?>
		<script type="text/javascript">
		jQuery(window).load(function() {
			jQuery('#message').append('<p id="chunkprocessing">Processing......., please wait ......................</p>');
			jQuery('#pbar').attr('max',total_files);
			jQuery('#pbar').attr('value',0);
			jQuery('#pbarspan').css('width',0);
			chunk_exists();
		});
		
		var current_count = 0;

		function chunk_exists(){
			
			act ='parse_chunk_collection_sheets';
			var hook_type = "<?php echo $_GET['page']; ?>";	
			jQuery.post( ajaxurl, {
			 		action : act,
			 		hook_type: hook_type,
			 		type : 'post',
			 		dataType : 'json' },
			 		function( data ){
			 			
						var chunkcheck = jQuery.parseJSON( data );
						if( chunkcheck.chunk ){
							current_count = current_count+1; 
			 				chunk_exists();
			 				jQuery('#pbar').attr('value',current_count);
							
							jQuery('#status').html( '<p>'+ chunkcheck.message +'</p>' );
							jQuery('#status').html( '<p>Process '+ (current_count/(total_files))*100 +'% Complete!</p>' );

			 			} else {
			 				jQuery('#chunkprocessing').fadeOut().html('Import Complete.... Thank you.').fadeIn();
			 			}
			 });
		}

		</script>
			<?php
	}else{
		echo '<div id="message" class="updated below-h2">';
		echo "<p>No file or File type selected</p>";
		echo '</div>';
	}
?>
<div class="wrap">
<h2>Import Data</h2>
<form method="post" enctype="multipart/form-data" action="admin.php?page=<?php echo $_GET['page']; ?>">
		<table class="form-table">
		<tbody>
		<tr><th>Choose type of file</th></tr>
		<tr>
		<td scope="row">Choose extension of file which you are going to upload</td>
		<td>
		<input type="radio" name="file_type" value="Excel2007" id="Excel2007"/><label for="Excel2007">.xlsx</label>
		<input type="radio" name="file_type" value="Excel5" id="Excel5"/><label for="Excel5">.xls</label>
		<input type="radio" name="file_type" value="CSV" id="CSV"/><label for="CSV">.csv</label>
		</td></tr>
		</tbody>
		</table>
		<table class="form-table">
		<tbody>
		<tr><th>Upload file</th></tr>
		<tr>
		<td scope="row">Upload file Import Data to WordPress</td>
		<td>
		<input type="file" name="file"  /><input class="button button-primary" type="submit" value="Import" />
		</td></tr>
		</tbody>
		</table>

</form>

</div>
<?php }

function monoframe_import_data_func( $type = '' ){
	$upload_dir = wp_upload_dir();
	
	set_time_limit(0);
	define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

	if( isset( $_FILES['file']['tmp_name'] ) && isset( $_POST['file_type'] ) ){

		$file_type = $_POST['file_type'];

		switch ( $file_type ) {
			case 'Excel2007':
				$file_ext = '.xlsx';
				break;

			case 'Excel5':
				$file_ext = '.xls';
				break;

			case 'CSV':
				$file_ext = '.csv';
				break;
			
			default:
				$file_ext = '.csv';
				break;
		}

		require_once 'PHPExcel/PHPExcel/IOFactory.php';
		require_once 'class-chunkreadfilter.php';
		$inputFileType = $file_type;
		$callStartTime = microtime(true);
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		$callEndTime = microtime(true);
		$callTime = $callEndTime - $callStartTime;
		//echo "Memory Limit".ini_get('memory_limit'), EOL;
		//echo 'time required to create object ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
		$chunkSize = 1024;
		$chunkFilter = new chunkReadFilter();
		$objReader->setReadFilter($chunkFilter);

		// Loop to read our worksheet in "chunk size" blocks

		$total_files = 0;
		for ( $startRow = 2; $startRow <= 35000 ; $startRow += $chunkSize) {

			$chunkFilter->setRows($startRow,$chunkSize);
			$callStartTime = microtime(true);
		    //  Load only the rows that match our filter
		    $objReader->setReadDataOnly(true);
		    $objPHPExcel = $objReader->load( $_FILES['file']['tmp_name'] );

		    //$objPHPExcel = $objPHPExcel->getActiveSheet();
		    $lastRow = $objPHPExcel->getActiveSheet()->getHighestRow();

		    if( $lastRow < $startRow )
		    	break;


		    $objPHPExcel->getActiveSheet()->removeRow( 1 , $startRow -1  );

		    $callStartTime = microtime(true);

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $file_type);
			$objWriter->save(  $upload_dir['basedir'].'/sheets/'.$startRow.$file_ext);
			unset( $objWriter );
			unset( $objPHPExcel );
			$callEndTime = microtime(true);
			$callTime = $callEndTime - $callStartTime;

			$total_files++;
		}

		unset( $objReader );

		echo "<script>
			var collections = true;
			var total_files = ".$total_files."
		</script>";
	}
}


function monoframe_parse_chunks_function(){
	
	global $wpdb;
	require_once 'PHPExcel/PHPExcel/IOFactory.php';
	$upload_dir = wp_upload_dir();
	if ($handle = opendir( $upload_dir['basedir'].'/sheets' )) {
		$messages = ' No chunk found.';
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
	        	$i = 0;
				$callStartTime = microtime(true);
				$objPHPExcel = PHPExcel_IOFactory::load( $upload_dir['basedir'].'/sheets/'.$entry );
				$objWorksheet = $objPHPExcel->getActiveSheet()->toArray();

				foreach ( $objWorksheet as $value) {
					if( empty( $value ) )
						exit('Parsing Done, blank row found in a object');

					if( $value[0] ){
						$i++;
						do_action( 'row-'.$_POST['hook_type'], $value );
					}
				}

				$messages = "$i records updated</br>";
				unset( $objWorksheet );
				unset( $objPHPExcel );
				$callEndTime = microtime(true);
				$callTime = $callEndTime - $callStartTime;
				$messages .= 'Call time parse ' .sprintf('%.4f',$callTime) . " seconds" ;

				unlink( $upload_dir['basedir'].'/sheets/'.$entry );
				echo json_encode( array( 'chunk' => true , 'message' => $messages , 'filename' => $entry ) );
				exit();
			}
		}
		closedir($handle);
		echo json_encode( array( 'chunk' => false, 'message' => $messages ));
		exit();
	}
}

add_action('wp_ajax_parse_chunk_collection_sheets','monoframe_parse_chunks_function' );

function monoframe_show_progress(){

		if( isset( $_FILES['file'] ) && $_FILES['file']['name'] !=''  && isset($_POST['file_type']) ) {
	?>
			<style>
		.progress-bar {
		  background-color: whiteSmoke;
		  border-radius: 2px;
		  box-shadow: 0 2px 3px rgba(0, 0, 0, 0.25) inset;

		  width: 250px;
		  height: 20px;
		  
		  position: relative;
		  display: block;
		}
		  
		.progress-bar > span {
		  background-color: blue;
		  border-radius: 2px;

		  display: block;
		  text-indent: -9999px;
		}
		progress[value] {
			/* Get rid of the default appearance */
			appearance: none;
			
			/* This unfortunately leaves a trail of border behind in Firefox and Opera. We can remove that by setting the border to none. */
			border: none;
			
			/* Add dimensions */
			width: 100%; height: 20px;
			
			/* Although firefox doesn't provide any additional pseudo class to style the progress element container, any style applied here works on the container. */
			  background-color: whiteSmoke;
			  border-radius: 3px;
			  box-shadow: 0 2px 3px rgba(0,0,0,.5) inset;
			
			/* Of all IE, only IE10 supports progress element that too partially. It only allows to change the background-color of the progress value using the 'color' attribute. */
			color: royalblue;
			
			position: relative;
			margin: 0 0 1.5em; 
		}

		/*
		Webkit browsers provide two pseudo classes that can be use to style HTML5 progress element.
		-webkit-progress-bar -> To style the progress element container
		-webkit-progress-value -> To style the progress element value.
		*/

		progress[value]::-webkit-progress-bar {
			background-color: whiteSmoke;
			border-radius: 3px;
			box-shadow: 0 2px 3px rgba(0,0,0,.5) inset;
		}

		progress[value]::-webkit-progress-value {
			position: relative;
			
			background-size: 35px 20px, 100% 100%, 100% 100%;
			border-radius:3px;
			
			/* Let's animate this */
			animation: animate-stripes 5s linear infinite;
		}

		@keyframes animate-stripes { 100% { background-position: -100px 0; } }

		/* Let's spice up things little bit by using pseudo elements. */

		progress[value]::-webkit-progress-value:after {
			/* Only webkit/blink browsers understand pseudo elements on pseudo classes. A rare phenomenon! */
			content: '';
			position: absolute;
			
			width:5px; height:5px;
			top:7px; right:7px;
			
			background-color: white;
			border-radius: 100%;
		}

		/* Firefox provides a single pseudo class to style the progress element value and not for container. -moz-progress-bar */

		progress[value]::-moz-progress-bar {
			/* Gradient background with Stripes */
			background-image:
			-moz-linear-gradient( 135deg,
															 transparent,
															 transparent 33%,
															 rgba(0,0,0,.1) 33%,
															 rgba(0,0,0,.1) 66%,
															 transparent 66%),
			-moz-linear-gradient( top,
																rgba(255, 255, 255, .25),
																rgba(0,0,0,.2)),
			 -moz-linear-gradient( left, #09c, #f44);
			
			background-size: 35px 20px, 100% 100%, 100% 100%;
			border-radius:3px;
			
			/* Firefox doesn't support CSS3 keyframe animations on progress element. Hence, we did not include animate-stripes in this code block */
		}

		/* Fallback technique styles */
		.progress-bar {
			background-color: whiteSmoke;
			border-radius: 3px;
			box-shadow: 0 2px 3px rgba(0,0,0,.5) inset;

			/* Dimensions should be similar to the parent progress element. */
			width: 100%; height:20px;
		}
		
		.progress-bar span {
			background-color: royalblue;
			border-radius: 3px;
			
			display: block;
			text-indent: -9999px;
		}
		
		

		p[data-value] { 
		  
		  position: relative; 
		}

		/* The percentage will automatically fall in place as soon as we make the width fluid. Now making widths fluid. */

		p[data-value]:after {
			content: attr(data-value) '%';
			position: absolute; right:0;
		}
		progress {
			color: #FF5722;
		}
		 
		/* Firefox */
		progress::-moz-progress-bar { 
			background: #FF5722;   
		}
		#pbar::-webkit-progress-value{
			background:#FF5722;
		}
		
		#my-list{
			background: #f7f7f7; 
			padding: 8px; /* Give the items some air to breathe */
		}

		#my-list > li {
			display: inline-block;
			zoom:1;
			*display:inline;
			/* this fix is needed for IE7- */
			color: #666666;
			padding: 3px 8px;
		}
		.btn {
		  -webkit-border-radius: 5;
		  -moz-border-radius: 5;
		  border-radius: 5px;
		  font-family: Arial;
		  color: #ffffff;
		  font-size: 20px;
		  background: #FFAB40;
		  padding: 10px 20px 10px 20px;
		  text-decoration: none;
		}

		.btn:hover {
		  background: #FF9100;
		  text-decoration: none;
		}
		#status{
			
		}
	</style>
		<div>
		<br/>
		<progress id='pbar' max="100" value="0">
			<div class="progress-bar">
				<span id='pbarspan' style="width: 80%;">Progress: 80%</span>
			</div>
		</progress>
		<div id='status'></div>
		</div>
	<?php	
		}
}