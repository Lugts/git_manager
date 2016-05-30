<?php
require('config.php');
session_start();
if(!isset($_POST['action']) && !isset($_GET['logout'])){
    $pwd = shell_exec('pwd;');
	setcookie('path', trim($pwd));
	$_COOKIE['path'] = $pwd;
}elseif(isset($_GET['logout'])){
   	setcookie('path', '', time()-42);
	session_destroy();
	header('Location:index.php');
}

# login user
if(isset($_POST['username']) && isset($_POST['password'])){
	$user_exists = array_search($_POST['username'], array_keys($users));
    if(md5($_POST['password']) === $users[$_POST['username']][0] && $user_exists >= 0){
        $_SESSION['logged_in'] = TRUE;
        $_SESSION['user'] = array($_POST['username'], $users[$_POST['username']][1]);
        header('location: index.php');
    }else{
        $_SESSION['logged_in'] = FALSE;
        $error = "Bad Login ...";
    }
}

if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === TRUE){
    
# get all git projects
$git_projects= array();
exec('ls ../../', $git_projects);
    $git_projects_texts = $git_projects = array_reverse($git_projects);
	$used_keys = array();
	foreach($git_projects as $key1 => $git_project1){
		$git_projects[$key1] = $git_project1 = trim($git_project1);
		if(empty($git_project1)){unset($git_projects[$key1]);continue;}
		$git_project1 = str_replace('/', '\/', $git_project1);
		foreach($git_projects as $key2 => $git_project2){
			$git_project2 = trim($git_project2);
			if(empty($git_project2)){unset($git_projects[$key2]);continue;}
			if(!in_array($key2, $used_keys) && preg_match('/'.$git_project1.'\//', $git_project2)){
				$nbsp = count(explode('/', $git_project1));
				$nbsp_str = '';
				for($i = 0; $i <= $nbsp; $i++){
					$nbsp_str .= '&nbsp;';
				}
				$git_projects_texts[$key2] = preg_replace('/'.$git_project1.'\//', $nbsp_str.'-&nbsp;/', $git_project2);
				$used_keys[] = $key2;
			}
		}
	}
	$git_projects = array_reverse($git_projects);
	$git_projects_texts = array_reverse($git_projects_texts);
}


shell_exec('cd '.$base_url);
# actions
if(isset($_POST['action'])){

    switch($_POST['action']){
        #create repository
        case 'create_repo':
        	//print_r($_POST); exit;
            $cmd = "rm -rf * git clone ".$_POST['repo_url']." ".$_POST['repo']." && echo ".$_POST['server_ip'].":".$_POST['deploy_url']." > ".$_POST['repo']."/.deploy";
            echo '<div><p>'.date('H:i:s')."</p><p>".$cmd."</p></div>";
            echo '<div><p>'.date('H:i:s')."</p>".trim(shell_exec($cmd))."</p></div>";
            echo '<div class="end_command"><p>'.date('H:i:s')."</p><p>end command</p></div>";

        break;

        #create repository
        case 'init_repo':
            $cmd = 'git init '.$_POST['repo']." && echo ".$_POST['deploy_url']." > ".$_POST['server_ip'].":".$_POST['repo']."/.deploy";
            echo '<div><p>'.date('H:i:s')."</p><p>".$cmd."</p></div>";
            echo '<div><p>'.date('H:i:s')."</p>".trim(shell_exec($cmd))."</p></div>";
            echo '<div class="end_command"><p>'.date('H:i:s')."</p><p>end command</p></div>";

        break;

        #get repository info
        case 'info':
            $cd  = 'cd '.$base_url.$_POST['repo'].' 2>&1;';
            $pwd = shell_exec($cd.'pwd;');
			setcookie('path', trim($pwd));
				
            $cmd = 'git branch -a && git status 2>&1;';                
			$branches = trim(shell_exec($cd.$cmd));
	
            $repo_data['output']  = '<div><p>'.date('H:i:s').'</p><p>'.$cmd.'</p></div>';
            $repo_data['output'] .= '<div><p>'.date('H:i:s').'</p><p>'.$branches.'</p></div>';
            $repo_data['output'] .= '<div class="end_command"><p>'.date('H:i:s').'</p><p>end command</p></div>';

            $repo_data['branches'] = explode("\n", $branches);
            $repo_data['info_branches'] = nl2br($branches);

            $cmd = 'git status && git log --graph --abbrev-commit --decorate --date=relative --all 2>&1;';
            $repo_info = trim(shell_exec($cd.$cmd));
            $repo_data['info'] = nl2br($repo_info);

            $repo_data['output'] .= '<div><p>'.date('H:i:s').'</p><p>'.$cd.'</p></div>';
            $repo_data['output'] .= '<div><p>'.date('H:i:s').'</p><p>'.$cmd.'</p></div>';
            $repo_data['output'] .= '<div><p>'.date('H:i:s').'</p><p>'.$repo_info.'</p></div>';
            $repo_data['output'] .= '<div class="end_command"><p>'.date('H:i:s').'</p><p>end command</p></div>';

            echo json_encode($repo_data);

        break;

        #push remote branch
        case 'push':

            $cd = 'cd '.$base_url.$_POST['repo'].';';

            $branch = end(explode('/', trim($_POST['branch'])));

            $cmd = 'git push '.$branch; //.' -m '.$_POST['message'].' 2>&1;';

            echo '<div><p>'.date('H:i:s').'</p><p>'.$cd.'</p></div>';
            echo '<div><p>'.date('H:i:s').'</p><p>'.$cmd.'</p></div>';
            echo '<div><p>'.date('H:i:s').'</p><p>'.trim(shell_exec($cd.$cmd)).'</p></div>';
            echo '<div class="end_command"><p>'.date('H:i:s').'</p><p>end command</p></div>';

        break;

        #pull remote branch
        case 'pull':

            $cd = 'cd '.$base_url.$_POST['repo'].';';

            $branch = end(explode('/', trim($_POST['branch'])));

            $cmd = 'git pull origin '.$branch.' 2>&1;';

            echo '<div><p>'.date('H:i:s').'</p><p>'.$cd.'</p></div>';
            echo '<div><p>'.date('H:i:s').'</p><p>'.$cmd.'</p></div>';
            echo '<div><p>'.date('H:i:s').'</p><p>'.trim(shell_exec($cd.$cmd)).'</p></div>';
            echo '<div class="end_command"><p>'.date('H:i:s').'</p><p>end command</p></div>';

        break;

        #remote branch
        case 'switch':

            $cd = 'cd '.$base_url.$_POST['repo'].';';

            $branch = end(explode('/', trim($_POST['branch'])));

            $cmd = 'git branch 2>&1;';
            $branches = shell_exec($cd.$cmd );
            $branches = explode("\n", $branches);
            foreach($branches as $k => $v){

                $v = trim(str_replace("*", "", $v));
                if(empty($v)){
                    unset($branches[$k]);
                    continue;
                }

                $branches[$k] = $v;

            }

            if(in_array($branch, $branches)){
                $cmd = 'git checkout '.$branch.' 2>&1;';
            }
            else{
                $cmd = 'git checkout -b '.$branch.' 2>&1;';
            }

            $output = trim(shell_exec($cd.$cmd));

            echo '<div><p>'.date('H:i:s').'</p><p>'.$cd.'</p></div>';
            echo '<div><p>'.date('H:i:s').'</p><p>'.$cmd.'</p></div>';
            if(!empty($output)){
                echo '<div><p>'.date('H:i:s').'</p><p>'.$output.'</p></div>';
            }
            echo '<div class="end_command"><p>'.date('H:i:s').'</p><p>end command</p></div>';

        break;

        #fetch remote branches
        case 'deploy':

            $cd = 'cd '.$base_url.$_POST['repo'].';';
            $deploy = file_get_contents($base_url.$_POST['repo']."/.deploy");
            $cmd = 'rsync -avz -e "ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null" --progress --exclude ".git" '.$base_url.$_POST['repo'].'/* root@'.$deploy;

            echo '<div><p>'.date('H:i:s').'</p><p>'.$cd.'</p></div>';
            echo '<div><p>'.date('H:i:s').'</p><p>'.$cmd.'</p></div>';
            echo '<div><p>'.date('H:i:s').'</p><p>'.trim(shell_exec($cd.$cmd)).'</p></div>';
            echo '<div class="end_command"><p>'.date('H:i:s').'</p><span>end command</p></div>';

        break;

        #deploy on production
        case 'fetch':

            $cd = 'cd '.$base_url.$_POST['repo'].';';
            $cmd = 'git fetch 2>&1;';

            echo '<div><p>'.date('H:i:s').'</p><p>'.$cd.'</p></div>';
            echo '<div><p>'.date('H:i:s').'</p><p>'.$cmd.'</p></div>';
            echo '<div><p>'.date('H:i:s').'</p><p>'.trim(shell_exec($cd.$cmd)).'</p></div>';
            echo '<div class="end_command"><p>'.date('H:i:s').'</p><span>end command</p></div>';

        break;
        			
		# exec custom command
		case 'custom_command':
			
			$cd = '';
			if(isset($_COOKIE['path']) && !empty($_COOKIE['path'])){
				$cd = 'cd '.$_COOKIE['path'].';';
			}
			
			$cmd = $_POST['command'];
			
			preg_match('/cd (.*);|cd (.*)/', $cmd, $match);
			if(isset($match[0])){
				$match[0] = preg_replace('/;$/', '', $match[0]);
				$pwd = trim(shell_exec($cd.$match[0].';pwd;'));
				setcookie('path', $pwd);
				$_COOKIE['path'] = $pwd;
			}

			if(preg_match('/(vi |vim |cat |more |edit )/', $cmd)){
			
			    if(preg_match('/(edit )/', $cmd)){
			        $file['name'] = preg_replace('/(edit )/', '', $cmd);
			    }
			    else{
                    $file['name'] = preg_replace('/(cd )/', '', $cd).'/'.preg_replace('/(vi |vim |cat |more |cd )/', '', $cmd);
			    }
			    $file['name'] = preg_replace('/;/', '/', $file['name']);
				$file['name'] = preg_replace('/(\/\/)/', '/', $file['name']);
				
				$cmd = preg_replace('/(nano |vi |vim |edit )/', 'cat ', $cmd);
				
				$file['data'] = shell_exec($cd.$cmd);
				echo json_encode($file);
			}
			else{
                $cmd = trim($cmd, ';')." 2>&1;";
                echo '<div><p>'.date('H:i:s').'</p><p>'.$cmd.'</p></div>';
				$cmd_output = shell_exec($cd.$cmd);
				if($cmd_output){
                    
                    $cmd_output = preg_replace('/(d[rwx-]{9}.*[1-9]{2}:[1-9]{2} )(.*\n)/', '$1<span class="dir" data-path="'.$_COOKIE['path'].'" >$2</span>', $cmd_output);
                    $cmd_output = preg_replace('/(l[rwx-]{9}.*[1-9]{2}:[1-9]{2} )(.*\n)/', '$1<span class="link" data-path="'.$_COOKIE['path'].'" >$2</span>', $cmd_output);
                    $cmd_output = preg_replace('/(-[rwx-]{9}.*[1-9]{2}:[1-9]{2} )(.*\n)/', '$1<span class="file" data-path="'.$_COOKIE['path'].'" >$2</span>', $cmd_output);

                    echo '<div><p>'.date('H:i:s').'</p><p>'.$cmd_output.'</p></div>';
				}
				echo '<div class="end_command"><p>'.date('H:i:s').'</p><p>end command</p></div>'; 
			}
			
		break;
		
		# save file
		case 'save_file':
		
			$name = $_POST['name'];
			$data = $_POST['data'];
			
            if(is_writable($name)){
			    echo file_put_contents($name, $data);
            }
            else{
                echo 0;//'You do not have permission to write in this file';
            }
		break;
    }
    exit;
}
