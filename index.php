<?php require('exec.php'); ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8;" />
		<title>Git Repositories Manager</title>
		<link href='favicon.png' rel='shortcut icon' type='image/png'>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.0/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<!--<script src="js/bootstrap-select.min.js"></script>-->
		<script src="js/ace.js"></script>
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/font-awesome.min.css" rel="stylesheet">
		<link href="css/style.css" rel="stylesheet">
	</head>
	<body>
	<?php if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != TRUE){
		require('login.php');	
	 }else{ ?>
	<div class="container-fluid">
		<div class="row">
			<ul id="opened_files" class="nav nav-tabs pull-left" style="width:100%"><li><a href="#">&nbsp;</a></li></ul>
			<a href="?logout" class="pull-right"><i class="fa fa-power-off"></i></a>
		</div>
	</div>
	<div id="main_content" >
		<div id="repo_list">
			<ul id="projects">
			<?php foreach($git_projects as $key => $git_project){ ?>
				<li rel="<?php echo $git_project."/".$html; ?>" ><?php echo $git_projects_texts[$key]; ?></li>
			<?php } ?>
			</ul>
		</div>
		<div id="repo_info">
			<div class="info_branches">No repository selected</div>
			<div class="actions"></div>
		</div>
	</div>

	<!-- Modal Clone Repo-->
	<div id="ModalCloneRepo" class="modal">
		<div class="modal-dialog">
	      <div class="modal-content">
	        <div class="modal-header">
	          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	          <h4 class="modal-title">Clone Repository</h4>
	        </div>
	        <div class="modal-body">
				<?php
				$ch = curl_init("https://gitlab.com/api/v3/projects?private_token=".$_SESSION['user'][1]);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
				?>
				<select id="repo_url" class="selectpicker">
				<?php
				$output = json_decode(curl_exec($ch));
				foreach($output as $key=>$value){ ?>
					<option value="<?php echo $value->ssh_url_to_repo;?>"><?php echo $value->ssh_url_to_repo;?></option>
				<?php } ?>
				</select>
				<select id="server_ip" class="selectpicker">
				<?php
				foreach($deployes AS $key=>$value){
					echo '<option value="'.$value.'">'.$key.'</option>';
				} ?>
				</select>
				<input type="text" id="deploy_url" placeholder="/path/of/remote/vhost/" class="input-xlarge" />               
			    </div>
			    <div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				<button class="btn btn-primary" id="createRepoBtn" >Clone</button>
			    </div>
			</div>
		</div>
	</div>

	<!-- Modal Clone Repo-->	    
	<div id="ModalInitRepo" class="modal">
		<div class="modal-dialog">
	      <div class="modal-content">
	        <div class="modal-header">
	          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	          <h4 class="modal-title">Init Repository</h4>
	        </div>
	        <div class="modal-body">
		<?php
		$ch = curl_init("https://gitlab.com/api/v3/projects?private_token=".$_SESSION['user'][1]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		?>
		<input type="text" id="repo" class="input-xlarge" placeholder="Name of repo" />
		<select id="server_ip" class="selectpicker">
		<?php
		foreach($deployes AS $key=>$value){
			echo '<option value="'.$value.'">'.$key.'</option>';
		} ?>
		</select>
		<input type="text" id="deploy_url" placeholder="/path/of/remote/vhost/" class="input-xlarge" /> 
	    </div>
	    <div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
		<button class="btn btn-primary" id="initRepoBtn" >Initialize</button>
			    </div>
			</div>
		</div>
	</div>

	<!-- Modal Edit File-->		
	<div id="ModalEditFile" class="modal fullscreen ModalEditFile">
		<div class="modal-dialog">
	      <div class="modal-content">
	        <div class="modal-header">
			<ul id="opened_files" class="nav nav-tabs pull-left"></ul>
			<span class="message pull-right"></span>
			<button class="btn btn-primary saveFileBtn pull-right">Save</button>
			  <button class="btn btn-primary pull-right" data-dismiss="modal" aria-hidden="true">Hide</button>
	        </div>
	        <div class="modal-body">
			<div id="editor" class="editor"></div>
			<span class="file_name" style="display:none"></span>
		</div>
	</div>
	</div>
	</div>
	<div id="output_main">
	    <div id="header" >
			<span>Commands Output</span>
			<i class="fa fa-sort up pull-right" id="fullscreen"></i>
			<span class="pull-right">Position: <strong id="path"><?php if(isset($_COOKIE['path'])){echo $_COOKIE['path'];}else{echo $base_url;} ?></strong></span>
		</div>
	    <div id="output"></div>
	    <div id="custom_commands" >
		<div class="input-prepend input-append">
		    <div class="btn-group dropup">
		        <button class="btn btn-small dropdown-toggle" data-toggle="dropdown">
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu pull-left"></ul>
			</div>
			<input class="input-xxlarge" id="custom_command" type="text" >		
			<button class="btn btn-small" type="button" id="exec_custom_command" >Exec</button>
		</div>
	</div>
</div>

	<script type="text/javascript">

	    var repo;

	    // automaticaly resize output conteiner
	    $(window).on('resize load', function() {

		$('div[id^=ModalEditFile_][aria-hidden=false]').each(function(){
				    setFullScreenModal($(this).attr('id'));
		});
		
		var body_height = $(window).height();
			
		if($('#fullscreen').hasClass('up')){
			var output_height = body_height-550;
			$('#output').css('height', output_height);
		}
		else{
			var output_height = $('#output_main').height()-$('#header').height()-$('#custom_commands').height()-18;
		}
	
		$('#output').css('height', output_height);

	    });
		
		// toggle output fullscreen
		$('#fullscreen').on('click', function(){
			if($(this).hasClass('up')){
				$('#output_main').css('height', '100%');
				$('#fullscreen').removeClass('up').addClass('down');					
			}
			else{
				$('#output_main').css('height', 'auto');
				$('#fullscreen').removeClass('down').addClass('up');
			}
			$(window).trigger('load');
		});

	    // create new repository
	    $('#createRepoBtn').on('click', function(){

		$('#ModalCloneRepo').modal('hide');
		$('.create_repo').button('loading');

		var path = $('#path').html();
		var repo_url  = $('#ModalCloneRepo #repo_url').val();
		var server_ip = $('#ModalCloneRepo #server_ip').val();
		var deploy_url  = $('#ModalCloneRepo #deploy_url').val();

		$.post('index.php', {action: 'create_repo', repo: path, repo_url: repo_url, server_ip: server_ip, deploy_url: deploy_url}, function(data){

		    $('.create_repo').button('reset');
		    $('#output').html($('#output').html()+data).trigger('change');
		    
		    $.post('index.php', function(data){
		        var project = $('#path').html();
			$('ul#projects li.active').trigger('click');                       
		    });

	      	});

	    });

	    // create new repository
	    $('#initRepoBtn').on('click', function(){

		$('#ModalInitRepo').modal('hide');
		$('.init_repo').button('loading');

		var path = $('#path').html();
		var server_ip = $('#ModalInitRepo #server_ip').val();
		var deploy_url  = $('#ModalInitRepo #deploy_url').val();

		$.post('index.php', {action: 'init_repo', repo: path, server_ip: server_ip, deploy_url: deploy_url}, function(data){

		    $('.init_repo').button('reset');
		    $('#output').html($('#output').html()+data).trigger('change');
		    
		    $.post('index.php', function(data){
		        var project = $('#path').html();
			$('ul#projects li.active').trigger('click');                       
		    });

	      	});

	    });

	    // load repository info
	    $('ul#projects li').on('click', function(){
		$('ul#projects li').removeClass('active');
		$(this).addClass('active');
		repo = $(this).attr('rel');

		$('#repo_info .info_branches').html('');
		//$('#repo_info .info').html('');
		$('#repo_info .actions').html('');

		$('#repo_info').addClass('loading');
		$('#repo_info .info_branches').html('Loading please wait...');

		//$(this).attr('disabled', 'disabled');
		$.post('index.php', {action: 'info',repo: repo}, function(data){
		    $('#repo_info').removeClass('loading');
		    data = JSON.parse(data);
		    $('#output').html($('#output').html()+data['output']).trigger('change');
		    $('#repo_info .info_branches').html('<div class="title">Info</div><div>'+data['info_branches']+'</div>');
		    //$('#repo_info .info').html('<span>Info</span><div>'+data['info']+'</div>');
			if(data['info']==""){
			    $('#repo_info .actions').append('<div class="title">Actions</div><p class="text-center"><a href="#ModalInitRepo" role="button" data-tooltip="Initialize" class="btn btn-primary init_repo" data-toggle="modal" data-loading-text="..." ><i class="fa fa-git-square"></i></a><a href="#ModalCloneRepo" role="button" data-tooltip="Clone" class="btn btn-primary create_repo" data-toggle="modal" data-loading-text="..."><i class="fa fa-clone"></i></a></p>');
			}else{
		            // create list with remote branches
		            $('#repo_info .actions').append('<div class="title">Actions</div><select id="remote_branch" class="selectpicker"></select>');
		            $(data['branches']).each(function(index){
console.log(data['branches'][index]);
		                if(data['branches'][index].search(/remotes/) != -1){
		                    $('#remote_branch').append('<option value="'+data['branches'][index]+'" >'+data['branches'][index]+'</option>');
		                }
		            });

		            // add pull from branch button 
		            $('#repo_info .actions').append('<button id="pull" data-tooltip="Pull" class="btn btn-warning btn-small" data-loading-text="..." ><i class="fa fa-download"></i></button>');
		            $('#pull').on('click', function(){
		                $(this).button('loading');
		                $.post('index.php', {action: 'pull', repo: repo, branch: $('#remote_branch').val()}, function(data){
		                    $('#pull').button('reset');		              	    
		                    $('#output').html($('#output').html()+data).trigger('change');

		                });
		            });
		            // add push from branch button 
		            $('#repo_info .actions').append('<button id="push" data-tooltip="Push" class="btn btn-warning btn-small" data-loading-text="..."><i class="fa fa-upload"></i></button><br /><br />');
		            $('#push').on('click', function(){
		      			
		                $(this).button('loading');
		      			
		                $.post('index.php', {action: 'push', repo: repo, branch: $('#remote_branch').val()}, function(data){

		                    $('#push').button('reset');		              	    
		                    $('#output').html($('#output').html()+data).trigger('change');

		                });
			      	
		            });
		            // create list with all branches
		            $('#repo_info .actions').append('<select id="branch" class="selectpicker" ></select>');                  
		            $(data['branches']).each(function(index){
			if(data['branches'][index].search(/remotes/) != -1){
		            		$('#branch').append('<option value="'+data['branches'][index]+'" >'+data['branches'][index]+'</option>');
				}
		            });	            	
		            
		            // add switch to branch button 
		            $('#repo_info .actions').append('<button id="switch" data-tooltip="Checkout" class="btn btn-danger btn-small" data-tooltip="Checkout" data-loading-text="..."><i class="fa fa-code-fork"></i></button>');
		            $('#switch').on('click', function(){
		                $(this).button('loading');
		                $.post('index.php', {action: 'switch', repo: repo, branch: $('#branch').val()}, function(data){
		                    $('#switch').button('reset');
		                    $('#output').html($('#output').html()+data).trigger('change');
		                    //$('#projects').trigger('change');
					$('ul#projects li.active').trigger('click');
		                });
		            });

		            // add fetch branches button 
		            $('#repo_info .actions').append('<span style="margin-left:5px;">|</span><button id="fetch" data-tooltip="Fetch remote branches" class="btn btn-info btn-small" data-loading-text="..." ><i class="fa fa-hdd-o"></i></button>');
		            $('#fetch').on('click', function(){
		                $(this).button('loading');
		                $.post('index.php', {action: 'fetch', repo: repo}, function(data){
		                    $('#fetch').button('reset');
		                    $('#output').html($('#output').html()+data).trigger('change');
		                    $('#projects').trigger('change');
		                });
		            });
		            
		            // add deploy branches button 
		            $('#repo_info .actions').append('<button id="deploy" data-tooltip="Deploy on production" class="btn btn-info btn-small" data-loading-text="Deploy to production..." ><i class="fa fa-cloud-upload"></i></button>');
		            $('#deploy').on('click', function(){
		                $(this).button('loading');
		                $.post('index.php', {action: 'deploy', repo: repo}, function(data){
		                    $('#deploy').button('reset');
		                    $('#output').html($('#output').html()+data).trigger('change');
		                    $('#projects').trigger('change');
		                });
		            });
		    }
		    $('#path').html(getCookie('path'));
		});
	    });
	
			// exec custom commands
			var cmd_history = new Array();
			var current_cmd;

	    var editors = {};            

			$('#exec_custom_command').on('click', function(){
			
				var cmd = $('#custom_command').val();
			
				if(cmd == ""){
					return;
				}
			
				for(var i in cmd_history){
					if(cmd_history[i] == cmd){
						cmd_history.splice(i, 1);
						$('.dropdown-menu li a[href="'+cmd+'"]').parent().remove();
					}
				}
			
				cmd_history.unshift(cmd);
				$('.dropdown-menu').append('<li><a href="'+cmd+'" >'+cmd+'</a></li>');
				current_cmd = -1;
			
				$.post('index.php', {action: 'custom_command', repo: repo, command: cmd}, function(data){

					$('#custom_command').val('');

					try{

				        var file = $.parseJSON(data);
							
				        var file_modal = $('#ModalEditFile').clone();
				        var count_file_modals = $('div[id^=ModalEditFile_]').length;
		
				        var modal_id = 'ModalEditFile_'+count_file_modals;
				        var editor_id = 'editor_'+count_file_modals;
		
				        $(file_modal).attr('id', modal_id);
				        $(file_modal).find('#editor').attr('id', editor_id);
		
				        $(file_modal).find('.file_name').html(file['name']);
				        $(file_modal).find('#message').html('');
		
				        $(file_modal).modal('show');
				        
				        editors[editor_id] = createAceEditor(editor_id);
				        editors[editor_id].setValue(file['data'], -1);
				        
				        var mode = cmd.split('.');
				        mode = mode[mode.length-1];
				        if(mode == 'js'){ mode = 'javascript'; }                        
				        editors[editor_id].getSession().setMode('ace/mode/'+mode);
				        
						setFullScreenModal(modal_id);
				        createFileTabs();
					}catch(err){
						$('#output').html($('#output').html()+data).trigger('change');
					}
				
					$('#path').html(getCookie('path'));
			
				});
			
			});
		
	    $(document).on('click', '.saveFileBtn', function(){
		
		var editor_id = $(this).parents('.modal').find('div[id^=editor]').attr('id');

				var name = $(this).parents('.modal').find('.file_name').html();
				var data = editors[editor_id].getValue();
			
		var message = $(this).parents('.modal').find('.message');
				$(message).html('');
			
				$.post('index.php', {action: 'save_file', name: name, data: data}, function(data){

					if(data > 0){
						$(message).html('&nbsp;-&nbsp;<span class="alert-success" >File successfully saved!</span>');					
					}
					else{
						$(message).html('&nbsp;-&nbsp;<span class="alert-error" >File could not be saved!</span>');						
					}
				});
			
			});
		
			$('#custom_command').on('keyup', function(e){
				if(e.keyCode == 13){
					$('#exec_custom_command').trigger('click');
				}
				else if(e.keyCode == 38){					
					if(cmd_history[current_cmd+1]){
						current_cmd++;
						$(this).val(cmd_history[current_cmd]);
					}
				}
				else if(e.keyCode == 40){					
					if(cmd_history[current_cmd-1]){
						current_cmd--;			
						$(this).val(cmd_history[current_cmd]);
					}
					else{
						current_cmd = -1;
						$(this).val('');
					}
				}
			});
	
			$('.dropdown-menu').on('click', 'a', function(e){			
				e.preventDefault();
				$('#custom_command').val($(this).html()).focus();
			});
		
	    $('#output').on('change', function(){
			$(this).scrollTop(1000000);
	    });

	$(document).on('click', 'ul#opened_files li a', function(){
		var file = $(this).attr('class');
		$('ul#opened_files li').removeClass('active');
		$('.'+file).parent().addClass('active');
	});
	    
	    $(document).on('dblclick', '#output .dir', function(){   
			var cmd = 'cd '+$(this).data('path')+'/'+$(this).html()+'; ls -l';
			$('#custom_command').val(cmd);
			$('#exec_custom_command').trigger('click');
	    });
		
		$(document).on('dblclick', '#output .file', function(){   
			var cmd = 'edit '+$(this).data('path')+'/'+$(this).html();
			$('#custom_command').val(cmd);
			$('#exec_custom_command').trigger('click');
	    });
		
	    function createAceEditor(editor_id){
		
		var editor = ace.edit(editor_id);
		editor.setTheme('ace/theme/tomorrow_night');
		editor.setShowPrintMargin(false);
		editor.commands.addCommand({
		    name: 'save',
		    bindKey: {
		        win: 'Ctrl-S',
		        mac: 'Command-S',
		        sender: 'editor|cli'
		    },
		    exec: function(env, args, request) {
		        $('#'+editor_id).parents('.modal').find('.saveFileBtn').trigger('click');
		    }
		});

		return editor;

	    }

		function setFullScreenModal(modal_id){
			$('#'+modal_id).css('width', '100%').css('height', '100%').css('margin', 0).css('top', 0).css('left', 0);
			$('#'+modal_id+' .modal-body').css('height', '100%').css('max-height', '100%');
			$('#'+modal_id+' .modal-body').height($('#'+modal_id).height());
		}

	    function createFileTabs(){
			$('ul#opened_files').html('');
			$('div[id^=ModalEditFile_]').each(function(){
			    var full_file_name = $(this).find('.file_name').html();
			    var file = full_file_name.split('/');
			    var short_file_name = file[file.length-1];
			    $('ul#opened_files').append('<li><a href="#'+$(this).attr('id')+'" data-toggle="modal" class="'+$(this).attr('id')+'" title="'+full_file_name+'" >'+short_file_name+'</a></li>');
			});

	    }
		
		function getCookie(c_name){
			//$('select').selectpicker();
			var c_value = document.cookie;
			var c_start = c_value.indexOf(" " + c_name + "=");
			if (c_start == -1)
			  {
			  c_start = c_value.indexOf(c_name + "=");
			  }
			if (c_start == -1)
			  {
			  c_value = null;
			  }
			else
			  {
			  c_start = c_value.indexOf("=", c_start) + 1;
			  var c_end = c_value.indexOf(";", c_start);
			  if (c_end == -1)
			  {
			c_end = c_value.length;
			}
			c_value = unescape(c_value.substring(c_start,c_end));
			}
			return c_value;
	    }	
	</script>
	<?php } ?>
	</body>
</html>
