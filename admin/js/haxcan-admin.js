function haxcanLoadQ(){
	var avNonce = haxcan_settings.nonce;
	jQuery.post(
		ajaxurl,
		{
			action: 'get_ajax_response',
			_ajax_nonce: avNonce,
			_action_request: 'get_quarantined',
		},
		function(inp){
			jQuery('a#haxcan-si').hide();
			for(i=0;i<inp.length;i++){
			jQuery('div#haxcan-quarantine-list').append(inp[i]);
		}
		}
	);
}
function haxcanThemeExpand(){ 
	var elem = jQuery('div#haxcan-theme-scan img.haxcan-expander');
	jQuery(elem).parent().css('max-height','inherit');
	jQuery(elem).parent().css('padding-bottom','20px');
	jQuery(elem).hide();
}
function haxcanPluginExpand(){ 
	var elem = jQuery('div#haxcan-plugin-scan img.haxcan-expander');
	jQuery(elem).parent().css('max-height','inherit');
	jQuery(elem).parent().css('padding-bottom','20px');
	jQuery(elem).hide();
}
function haxcanLoadfile(path){
	var avNonce = haxcan_settings.nonce;
	jQuery.post(
		ajaxurl,
		{
			action: 'get_ajax_response',
			_ajax_nonce: avNonce,
			haxfile: path,
			_action_request: 'get_haxfile',
		},
		function(inp){
			//rand id for div
			var randy = Math.floor((Math.random() * 10000) + 1);
			var er = jQuery('body').append('<textarea style="width:900px;min-height:400px" class="haxcan-ta" id="cbox-'+randy+'">'+inp+'</textarea>');
			jQuery.colorbox({width:'900px',height:'500px',html:jQuery('textarea#cbox-'+randy)});
		    editAreaLoader.init({
			id: "cbox-"+randy	// id of the textarea to transform		
			,start_highlight: true	// if start with highlight
				,min_width:900
				,min_height:500
				,font_size:0
			,language: "en"
			,syntax: "php"	
		});
		}
	);
}
async function haxcansetfiles(filez){
	let i = 0;
	for(var key in filez){
					//jQuery('div#haxcan-current-tf').append(input.data.themes[0].files[key]);
					var randy = Math.floor(Math.random() * 60) + 1;
					jQuery('div#haxcan-current-tf').append('<div id="haxcan-file-'+i+'"><span style="width:'+randy+'%"></span><strong>'+key+'</strong></div>');
					i++;
				}
}
function themescan(){
	var themesscanned = 1;
	window.filesscanned = 0;
	window.totalfiles = 0;
	window.totalfiles2 = 0;
	window.haxpercentage = 1;
	var avNonce = haxcan_settings.nonce;
    const cirkl = circliful.newCircle({
        percent: 1,
        id: 'haxcan-themes-donut',
        type: 'simple',
  backgroundCircleWidth: 6,
  foregroundCircleWidth: 8,
		noPercentageSign: true,
  strokeLinecap: "round",
    });
	window.themesfound = 0;
	jQuery('a#haxcan-start-plugins-scan').text('Scan Again');
	jQuery('div#haxcan-theme-scan div.haxcan-no-scans, div.haxcan-footer').hide();
	jQuery('div#haxcan-theme-scan div.haxcan-loading-data').fadeIn(200);
	jQuery('div#haxcan-theme-scan div#haxcan-themes-donut-done').hide();
	jQuery('div#haxcan-theme-scan div#haxcan-inner-status').css('background-color','#3A86FF').html('SCANNING');
	jQuery('div#haxcan-theme-scan div#haxcan-current-tf').empty();
	jQuery.post(
		ajaxurl,
		{
			action: 'get_ajax_response',
			_ajax_nonce: avNonce,
			_action_request: 'get_all_themes',
		},
		function( input ) {
			totalfiles = input.data.total_files;
			totalfiles2 = input.data.total_files;
			themesfound = input.data.num_of_themes;
			var mwfound = 0;
			window.filevalue = 100/input.data.total_files;
			jQuery('div#haxcan-theme-scan div#haxcan-total-plugins').empty().html('1/'+themesfound);
			jQuery('div#haxcan-theme-scan span#haxcan-files-remaining').html(totalfiles);
			
			for(let totalthemes=0;totalthemes<themesfound;totalthemes++){
				if(totalthemes===0){
					haxcansetfiles(input.data.themes[totalthemes].files);
				}
						let a = 0;
						var promises = [];								
			for(var key2 in input.data.themes[totalthemes].files){
				promises.push(
			    jQuery.post(
				   ajaxurl,
				{
					action: 'get_ajax_response',
					_ajax_nonce: avNonce,
					_theme_file: input.data.themes[totalthemes].files[key2],
					_action_request: 'check_theme_file'
				},
				function( scanres ) {
					window.scarr = scanres.theme_file;
					mwfound += scanres.haveholes;
					if(scanres.haveholes){
					    jQuery.post(
						   ajaxurl,
						{
							action: 'get_ajax_response',
							_ajax_nonce: avNonce,
							line: scanres.line,
							theme_file: scanres.theme_file,
							reason: scanres.reason,
							_action_request: 'add_to_quarantine'
						}
					);
					}
					jQuery('div#haxcan-theme-scan span#haxcan-ctp').html(input.data.themes[totalthemes].name);
					haxpercentage = haxpercentage+filevalue;
					//haxpercentage=haxpercentage+1;
					cirkl.update([
					    {type: 'percent', value: haxpercentage},
					    {type: 'animation', value: false}
					]);

					var selektor = 'div#haxcan-theme-scan div#haxcan-current-tf div#haxcan-file-'+a+' span';
					//send each file to scan
					jQuery(selektor).css('width','100%').parent().fadeOut(360);
					filesscanned++;
					jQuery('div#haxcan-theme-scan span#haxcan-files-scanned').html(filesscanned);
					totalfiles--;
					jQuery('div#haxcan-theme-scan span#haxcan-files-remaining').html(totalfiles);
					var ab = a+1;
					if(filesscanned===totalfiles2){
						cirkl.update([
						    {type: 'percent', value: 100},
						    {type: 'animation', value: false}
						]);
						jQuery('div#haxcan-theme-scan div.haxcan-current-scan').hide();
						jQuery('div#haxcan-theme-scan div#haxcan-inner-status').css('background-color','#5DB30D').html('SECURE');
						jQuery('div.haxcan-footer').fadeIn(300);
						if(!mwfound){
						jQuery('h2#haxcan-general-status').html('Secure');
					}
					else {
						jQuery('h2#haxcan-general-status').html('<div style="color:#fc1303">Insecure</div>');
						jQuery('div#haxcan-theme-scan div#haxcan-inner-status').css('background-color','#fc1303').html('CHECK QUARANTINE');
						jQuery('span#haxcan-notifs').html(mwfound).fadeIn(200);
						jQuery('div#haxcan-nfiq').hide();
						jQuery('div#haxcan-content-03').html('<a href="javascript:;" onClick="haxcanLoadQ();" class="haxcan-button" id="haxcan-si">See Infected Files</a><div id="haxcan-quarantine-list"></div>');
					}
						jQuery('h2#haxcan-last-scans').html('Now');
						jQuery.post(
							ajaxurl,
							{
								action: 'get_ajax_response',
								_ajax_nonce: avNonce,
								theme_files_scanned:totalfiles2,
								_action_request: 'save_scan',
							});
					}
					if(input.data.themes[totalthemes].files_count===ab){
					jQuery('div#haxcan-theme-scan div#haxcan-current-tf').empty();
					haxcansetfiles(input.data.themes[totalthemes+1].files);
					themesscanned++;
					//fix current theme display 3/3
					if(themesscanned>input.data.num_of_themes){
						themesscanned=input.data.num_of_themes;
					}
					jQuery('div#haxcan-theme-scan div#haxcan-total-plugins').html(themesscanned+'/'+input.data.num_of_themes);
				}
					a++;
				}));
	               };
				   Promise.all(promises);
			   }
	
	
		}
	);	
}
async function haxcansetfiles2(filez){
	let i = 0;
	for(var key in filez){
					//jQuery('div#haxcan-current-tf').append(input.data.themes[0].files[key]);
					var randy = Math.floor(Math.random() * 60) + 1;
					jQuery('div#haxcan-current-tf-2').append('<div id="haxcan-file-'+i+'"><span style="width:'+randy+'%"></span><strong>'+filez[key]+'</strong></div>');
					i++;
				}
}
function pause(time) {
  // handy pause function to await
  return new Promise(resolve => setTimeout(resolve, time))
}
async function pluginscan(){
	var themesscanned = 1;
	window.filesscanned = 0;
	window.totalfiles = 0;
	window.totalfiles2 = 0;
	window.haxpercentage = 1;
	var avNonce = haxcan_settings.nonce;
    const cirkl2 = circliful.newCircle({
        percent: 1,
        id: 'haxcan-themes-donut-4',
        type: 'simple',
  backgroundCircleWidth: 6,
  foregroundCircleWidth: 8,
  noPercentageSign: true,
  text: 'a',
  textReplacesPercentage: true,
  strokeLinecap: "round",
    });
	window.themesfound = 0;
	jQuery('a#haxcan-start-plugins-scan').text('Scan Again');
	jQuery('div#haxcan-plugin-scan div.haxcan-no-scans, div.haxcan-footer').hide();
	jQuery('div#haxcan-plugin-scan div.haxcan-loading-data').fadeIn(200);
	jQuery('div#haxcan-plugin-scan div#haxcan-themes-donut-done-4').hide();
	jQuery('div#haxcan-plugin-scan div#haxcan-inner-status-2').css('background-color','#3A86FF').html('SCANNING');
	jQuery('div#haxcan-plugin-scan div#haxcan-current-tf-2').empty();
	jQuery.post(
		ajaxurl,
		{
			action: 'get_ajax_response',
			_ajax_nonce: avNonce,
			_action_request: 'get_all_plugins',
		},
		async function( input ) {
			totalfiles = input.data.total_plugin_files;
			totalfiles2 = input.data.total_plugin_files;
			themesfound = input.data.plugins.length;
			var mwfound = 0;
			window.filevalue = 100/input.data.total_plugin_files;
			jQuery('div#haxcan-plugin-scan div#haxcan-total-plugins-2').empty().html('1/'+themesfound);
			jQuery('div#haxcan-plugin-scan span#haxcan-files-remaining-2').html(totalfiles);
			
			for(let totalthemes=0;totalthemes<themesfound;totalthemes++){
				if(totalthemes===0){
					haxcansetfiles2(input.data.plugins[totalthemes].files);
				}
						let a = 0;
						var promises = [];								
			for(var key2 in input.data.plugins[totalthemes].files){
				await pause(150); // wait one second
				
				promises.push(
			    jQuery.post(
				   ajaxurl,
				{
					action: 'get_ajax_response',
					_ajax_nonce: avNonce,
					_theme_file: input.data.plugins[totalthemes].files[key2],
					_action_request: 'check_theme_file'
				},
				async function( scanres ) {
					window.scarr = scanres.theme_file;					
					mwfound += scanres.haveholes;
					if(scanres.haveholes){
					    jQuery.post(
						   ajaxurl,
						{
							action: 'get_ajax_response',
							_ajax_nonce: avNonce,
							line: scanres.line,
							theme_file: scanres.theme_file,
							reason: scanres.reason,
							_action_request: 'add_to_quarantine'
						}
					);
					}
					jQuery('div#haxcan-plugin-scan span#haxcan-ctp').html(input.data.plugins[totalthemes].name);
					haxpercentage = haxpercentage+filevalue;
					//haxpercentage=haxpercentage+1;
					cirkl2.update([
					    {type: 'percent', value: haxpercentage},
					    {type: 'animation', value: false}
					]);

					var selektor = 'div#haxcan-plugin-scan div#haxcan-current-tf-2 div#haxcan-file-'+a+' span';
					//send each file to scan
					jQuery(selektor).css('width','100%').parent().fadeOut(360);
					filesscanned++;
					jQuery('div#haxcan-plugin-scan span#haxcan-files-scanned-2').html(filesscanned);
					totalfiles--;
					jQuery('div#haxcan-plugin-scan span#haxcan-files-remaining-2').html(totalfiles);
					var ab = a+1;
					if(filesscanned===totalfiles2){
						cirkl2.update([
						    {type: 'percent', value: 100},
						    {type: 'animation', value: false}
						]);
						jQuery('div#haxcan-plugin-scan div.haxcan-current-scan').hide();
						jQuery('div#haxcan-plugin-scan div#haxcan-inner-status-2').css('background-color','#5DB30D').html('SECURE');
						jQuery('div.haxcan-footer').fadeIn(300);
						if(!mwfound){
						jQuery('h2#haxcan-general-status').html('Secure');
					}
					else {
						jQuery('h2#haxcan-general-status').html('<div style="color:#fc1303">Insecure</div>');
						jQuery('div#haxcan-plugin-scan div#haxcan-inner-status-2').css('background-color','#fc1303').html('CHECK QUARANTINE');
						jQuery('span#haxcan-notifs').html(mwfound).fadeIn(200);
						jQuery('div#haxcan-nfiq').hide();
						jQuery('div#haxcan-content-03').html('<a href="javascript:;" onClick="haxcanLoadQ();" class="haxcan-button" id="haxcan-si">See Infected Files</a><div id="haxcan-quarantine-list"></div>');
					}
						jQuery('h2#haxcan-last-scans').html('Now');
						jQuery.post(
							ajaxurl,
							{
								action: 'get_ajax_response',
								_ajax_nonce: avNonce,
								plugin_files_scanned:totalfiles2,
								_action_request: 'save_plugin_scan',
							});
					}
					if(input.data.plugins[totalthemes].files_count===ab){
					jQuery('div#haxcan-plugin-scan div#haxcan-current-tf').empty();
					haxcansetfiles2(input.data.plugins[totalthemes+1].files);
					themesscanned++;
					//fix current theme display 3/3
					if(themesscanned>input.data.plugins.length){
						themesscanned=input.data.plugins.length;
					}
					jQuery('div#haxcan-plugin-scan div#haxcan-total-plugins-2').html(themesscanned+'/'+input.data.plugins.length);
				}
					a++;					
				}
			));
	               };
				   Promise.all(promises);
			   }
		}
	);	
}
jQuery( document ).ready(
	function( $ ) {
		if(jQuery('div#haxcan-themes-donut-done-4').length){
	    const cirkl2 = circliful.newCircle({
	        percent: 1,
	        id: 'haxcan-themes-donut-done-4',
	        type: 'simple',
	  backgroundCircleWidth: 6,
	  foregroundCircleWidth: 8,
			noPercentageSign: true,
	  strokeLinecap: "round",
	    });
		cirkl2.update([
		    {type: 'percent', value: 100},
		    {type: 'animation', value: false}
		]);
	    const cirkl3 = circliful.newCircle({
	        percent: 1,
	        id: 'haxcan-themes-donut-done',
	        type: 'simple',
	  backgroundCircleWidth: 6,
	  foregroundCircleWidth: 8,
			noPercentageSign: true,
	  strokeLinecap: "round",
	    });
		cirkl3.update([
		    {type: 'percent', value: 100},
		    {type: 'animation', value: false}
		]);
	}
		$('.haxcan-dropdown-content a').on('click', function(){
			$(this).parent().css('display','none');	
			if($(this).attr('id')=='nav-01'){
				$('span#haxcan-dashboard').html('Dashboard');
			}	
			if($(this).attr('id')=='nav-02'){
				$('span#haxcan-dashboard').html('Settings');
			}	
			if($(this).attr('id')=='nav-03'){
				$('span#haxcan-dashboard').html('Quarantine');
			}	
		});
		$('div.haxcan-dropdown').on({
    mouseenter: function () {},
    mouseleave: function () { $('div.haxcan-dropdown-content').css('display','');}
});
$('img.haxcan-expander').on('click', function(){
	alert('aa');
	$(this).parent().css('max-height','inherit');
	$(this).parent().css('padding-bottom','20px');
	$(this).hide();
	
});
	    $('#haxcan-display-content').html($('#haxcan-content-01').html());
		//define animations trough haxcan
		$('#nav-02 img,#nav-03 img').on('click', function(){
			$('#nav-02 img,#nav-03 img').css('opacity','0.4');
			var currid = $(this).parent().attr('id');
			if(currid=='nav-03'){
				$('span#haxcan-dashboard').html('Quarantine');
			}
			else {
				$('span#haxcan-dashboard').html('Settings');				
			}
			$(this).css('opacity','1');
		});
		$('#nav-01').on('click', function(){
			$('#nav-02 img,#nav-03 img').css('opacity','0.4');
			$('span#haxcan-dashboard').html('Dashboard');
		});
	    $('.haxcan-panel-change').on('click', function () {
	        var navLinkId = $(this).attr('id').substr(4);
	        $('#haxcan-display-content').fadeOut('fast', function () {
	            $('#haxcan-display-content').html($('#haxcan-content-' + navLinkId).html()).fadeIn('fast');
	        });
	    });
		
		var avNonce = haxcan_settings.nonce;
		var avMsg1 = haxcan_settings.msg_1;
		var avMsg3 = haxcan_settings.msg_3;
		var avMsg4 = haxcan_settings.msg_4;
		var avFiles = [];
		var avFilesLoaded;
		
		function checkThemeFile( current ) {
			// Sanitize ID.
			var id = parseInt( current || 0 );

			// Get corresponding file.
			var file = avFiles[id];

			// Issue the request.
			$.post(
				ajaxurl,
				{
					action: 'get_ajax_response',
					_ajax_nonce: avNonce,
					_theme_file: file,
					_action_request: 'check_theme_file',
				},
				function( input ) {
					// Initialize value.
					var item = $( '#av_template_' + id );
					var i;
					var lines;
					var line;
					var md5;

					// Data present?
					if ( input ) {
						if ( ! input.nonce || input.nonce !== avNonce ) {
							return;
						}

						// Set highlighting color.
						item.addClass( 'danger' );

						// Initialize lines of current file.
						lines = input.data;

						// Loop through lines.
						for ( i = 0; i < lines.length; i = i + 3 ) {
							md5 = lines[i + 2];
							line = lines[i + 1].replace( /@span@/g, '<span>' ).replace( /@\/span@/g, '</span>' );

							item.append( '<p><a href="#" id="' + md5 + '" class="button" title="' + avMsg4 + '">' + avMsg1 + '</a> <code>' + line + '</code></p>' );

							$( '#' + md5 ).click(
								function() {
									$.post(
										ajaxurl,
										{
											action: 'get_ajax_response',
											_ajax_nonce: avNonce,
											_file_md5: $( this ).attr( 'id' ),
											_action_request: 'update_white_list',
										},
										function( res ) {
											var parent;

											// No data received?
											if ( ! res ) {
												return;
											}

											// Security check.
											if ( ! res.nonce || res.nonce !== avNonce ) {
												return;
											}

											parent = $( '#' + res.data[0] ).parent();

											if ( parent.parent().children().length <= 1 ) {
												parent.parent().hide( 'slow' ).remove();
											}
											parent.hide( 'slow' ).remove();
										}
									);

									return false;
								}
							);
						}
					} else {
						item.addClass( 'done' );
					}

					// Increment counter.
					avFilesLoaded++;

					// Output notification.
					if ( avFilesLoaded >= avFiles.length ) {
						$( '#av_manual_scan .alert' ).text( avMsg3 ).fadeIn().fadeOut().fadeIn().fadeOut().fadeIn().animate( { opacity: 1.0 }, 500 ).fadeOut(
							'slow',
							function() {
								$( this ).empty();
							}
						);
					} else {
						checkThemeFile( id + 1 );
					}
				}
			);
		}

		// Check templates.
		$( 'a#themescan' ).click(
			function() {
				// Request.
				$.post(
					ajaxurl,
					{
						action: 'get_ajax_response',
						_ajax_nonce: avNonce,
						_action_request: 'get_theme_files',
					},
					function( input ) {
						// Initialize output value.
						var output = '';

						// No data received?
						if ( ! input ) {
							return;
						}

						// Security check.
						if ( ! input.nonce || input.nonce !== avNonce ) {
							return;
						}

						// Update global values.
						avFiles = input.data;
						avFilesLoaded = 0;

						// Visualize files.
						jQuery.each(
							avFiles,
							function( i, val ) {
								output += '<div id="av_template_' + i + '">' + val + '</div>';
							}
						);

						// assign values.
						$( '#av_manual_scan .alert' ).empty();
						$( '#av_manual_scan .output' ).empty().append( output );

						// Start loop through files.
						checkThemeFile();
					}
				);

				return false;
			}
		);
		
		$( 'a#getthemesdata' ).click(
			function() {
				// Request.
				$.post(
					ajaxurl,
					{
						action: 'get_ajax_response',
						_ajax_nonce: avNonce,
						_action_request: 'get_all_themes',
					},
					function( input ) {
					}
				);

				return false;
			}
		);

	}
);