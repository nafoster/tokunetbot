<?php
//mysql database connection
//discord vendor connection
include __DIR__.'/vendor/autoload.php';
require('token.php');

//turn on bot
$discord->on('ready', function ($discord) {
    echo "Bot is ready.", PHP_EOL;
  
    /* All commands
    
        Add assignment to database
            !add (topic) (title of news) (link) (additional link)
        Assign news to someone
            !assign (link) (user)
        Removing an assignment from database
            !remove (link)
        Viewing all assignments
            !news all
        Viewing assignments from one user
            !news (user)
    
    */
    $discord->on('message', function ($message, $discord) {
        require('connect.php');
        $add = "!add";
        $remove = "!remove";
        $assign = "!assign";
        $assignments = "!news";
        
        if (strpos($message->content, $add) === 0) {
            $entireMessage = strtolower($message->content);
            $pieces = explode(" ", $entireMessage);
            $topic = $pieces[1];
            $detail = $pieces[2];
            $link = $pieces[3];
            $additionalLink = $pieces[4];
            
            if (!empty($topic) && !empty($detail) && !empty($link)) {
                if (!empty($additionalLink)) {
                   $query = "INSERT INTO news (topic, detail, link, additionalLink) VALUES ('$topic', '$detail', '$link', '$additionalLink')"; 
                    if (mysqli_query($conn, $query)) {
                        $success = true;
                        $message->reply("Assignment has been added.");
                    } else {
                        $fail = true;
                        $message->reply("Assignment was not added. Please contact an admin.");
                    }
                } else if (empty($additionalLink)) {
                    $query = "INSERT INTO news (topic, detail, link) VALUES ('$topic', '$detail', '$link')"; 
                    if (mysqli_query($conn, $query)) {
                        $success = true;
                        $message->reply("Assignment has been added.");
                    } else {
                        $fail = true;
                        $message->reply("Assignment was not added. Please contact an admin.");
                    }
                }
                
            } else {
                $message->reply("The command was not correct. The command needs to be '!add [topic] [detail] [link] [additional link]' or '!add [topic] [detail] [link]'");
            }
            
            
        } else if (strpos($message->content, $remove) === 0){
            
            $entireMessage = strtolower($message->content);
            $pieces = explode(" ", $entireMessage);
            $link = $pieces[1];
            
            if (!empty($link)) {
                $query = "DELETE FROM news WHERE link='$link'";
                if (mysqli_query($conn, $query)) {
                        $success = true;
                        $message->reply("Assignment has been removed.");
                    } else {
                        $fail = true;
                        $message->reply("Assignment was not removed. Please contact an admin.");
                    }
            } else {
                $message->reply("The command was not correct. The command needs to be '!remove [link]'");
            }
            
        } else if (strpos($message->content, $assign) === 0) {
            
            $entireMessage = strtolower($message->content);
            $pieces = explode(" ", $entireMessage);
            $link = $pieces[1];
            $user = $pieces[2];
            $date = date('m/d/Y');
            
            if (!empty($link) && !empty($user)) {
                $query = "UPDATE news SET assigned='$user', assignedDate='$date' WHERE link='$link'";
                if (mysqli_query($conn, $query)) {
                        $success = true;
                        $message->reply($user." has been assigned to that news.");
                    } else {
                        $fail = true;
                        $message->reply($user." was not assigned to that news. Please contact an administrator.");
                    }
            } else {
                $message->reply("The command was not correct. The command needs to be '!assign [link] [user]'");
            }
            
        } else if (strpos($message->content, $assignments) === 0) {
            $entireMessage = strtolower($message->content);
            $pieces = explode(" ", $entireMessage);
            $user = $pieces[1];
            if (!empty($user)) {
                    $query = "SELECT * FROM news WHERE assigned='$user' ORDER BY id DESC";
                    $result = mysqli_query($conn, $query);
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $message->reply('
                            Topic: '.$row['topic'].' 
                            Detail: '.$row['detail'].' 
                            Link: `'.$row['link'].'` 
                            Additional link: `'.$row['additionalLink'].'` 
                            Assigned to: '.$row['assigned'].' 
                            Assigned date: '.$row['assignedDate']);
                        }
                    } else {
                        $message->reply("This user does not exist or does not have an assignment");
                    }
                    
            } else {
                $message->reply("The command was not correct. The command needs to be '!news [user]'");
            }
        }
         
              
    });
});

//run functions
$discord->run();

?>