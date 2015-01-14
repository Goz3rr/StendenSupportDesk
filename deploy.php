<?php
    $commands = array(
        'echo $PWD',
        'whoami',
        'git reset --hard HEAD',
        'git pull',
        'git status',
        'git submodule sync',
        'git submodule update',
        'git submodule status',
    );

    $output = '';
    foreach($commands as $command){
        $tmpcommand = $command;
        if(substr($command, 0, 3) == "git") $tmpcommand = "sudo -u goz3rr " . $command;

        $tmp = shell_exec($tmpcommand . " 2>&1");
        $output .= "<span style=\"color: #6BE234;\">\$</span> <span style=\"color: #729FCF;\">{$command}\n</span>";
        $output .= htmlentities(trim($tmp)) . "\n";
    }
?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
<meta charset="UTF-8">
<title>GIT DEPLOYMENT SCRIPT</title>
</head>
<body style="background-color: #000000; color: #FFFFFF; font-weight: bold; padding: 0 10px;">
<pre>
 .  ____  .    ____________________________
 |/      \|   |                            |
[| <span style="color: #FF0000;">&hearts;    &hearts;</span> |]  | Git Deployment Script v0.1 |
 |___==___|  /              &copy; oodavid 2012 |
              |____________________________|
 
<?= $output; ?>
</pre>
</body>
</html>