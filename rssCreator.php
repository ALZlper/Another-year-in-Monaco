<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    function generateFileName($file, $episode_Id = "0000000000000") {
        return $episode_Id . "." . pathinfo(parse_url($file)['path'], PATHINFO_EXTENSION);
    }
    
    function formatTime($seconds) {
        $hours = floor($seconds / 3600);
        $mins = floor($seconds / 60 % 60);
        $secs = floor($seconds % 60);
        return $timeFormat = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    }

    if (isset($_GET["new"])) {
        
    //Collect information
        $episode_Title = $_POST["title"];
        $episode_Subtitle = $_POST["subtitle"];
        $episode_Description = $_POST["description"];
        $episode_Duration = $_POST["duration"];
        $rss = simplexml_load_file('feed.xml');
        $rss->registerXPathNamespace('content', 'http://purl.org/rss/1.0/modules/content/');
        $rss->registerXPathNamespace('wfw', 'http://wellformedweb.org/CommentAPI/');
        $rss->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $rss->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
        $rss->registerXPathNamespace('sy', 'http://purl.org/rss/1.0/modules/syndication/');
        $rss->registerXPathNamespace('slash', 'http://purl.org/rss/1.0/modules/slash/');
        $rss->registerXPathNamespace('itunes', 'http://www.itunes.com/dtds/podcast-1.0.dtd');
        $rss->registerXPathNamespace('rawvoice', 'http://www.rawvoice.com/rawvoiceRssModule/');
        $rss->registerXPathNamespace('googleplay', 'http://www.google.com/schemas/play-podcasts/1.0');
        
        //Get episode id
        $episode_Id = str_replace(".", "", uniqid('', true));

    //Upload files
        $uploaddir = '../cdn/';
        $uploadfile_Audio        = "audios/" . generateFileName($_FILES['audio']['name'], $episode_Id);
        $uploadfile_ThumbnailWeb = "images/" . generateFileName($_FILES['thumbnailWeb']['name'], $episode_Id);
        $uploadfile_ThumbnailRSS = "images/" . generateFileName($_FILES['thumbnailRSS']['name'], $episode_Id);
        
        move_uploaded_file($_FILES['audio']['tmp_name'],        $uploaddir.$uploadfile_Audio);
        move_uploaded_file($_FILES['thumbnailWeb']['tmp_name'], $uploaddir.$uploadfile_ThumbnailWeb);
        move_uploaded_file($_FILES['thumbnailRSS']['tmp_name'], $uploaddir.$uploadfile_ThumbnailRSS);
        
        $filesAbsolut = "http://blog.alzlper.com/podcast/cdn/";
        $fileLinks = array($filesAbsolut.$uploadfile_Audio,
                           $filesAbsolut.$uploadfile_ThumbnailWeb,
                           $filesAbsolut.$uploadfile_ThumbnailRSS
                          );
        
    //Add rss item
        $rss->channel->lastBuildDate = (new DateTime())->format(DateTime::RSS);
    
        $newItem = $rss->channel->addChild('item');
        $newItem->addChild('title', $episode_Title);
        $newItem->addChild('link', "http://blog.alzlper.com/podcast/episode/?p=".$episode_Id);
        $newItem->addChild('pubDate', (new DateTime())->format(DateTime::RSS));
        $newItem->addChild('guid', "http://blog.alzlper.com/podcast/#episode".$episode_Id)->addAttribute("isPermaLink", "false");
        $newItem->addChild('thumbnail', $fileLinks[1]);
        $newItem->addChild('id', $episode_Id);
        
        /*$newItem->addChild('comments', "dadwa");
        $newItem->addChild('wfw:commentRss', "dadwa", 'http://wellformedweb.org/CommentAPI/');
        $newItem->addChild('slash:comments', "dadwa", 'http://purl.org/rss/1.0/modules/slash/');*/
        
        $newItem->addChild('category', "Comedy");
        $newItem->addChild('itumes:category', "Comedy", 'http://www.itunes.com/dtds/podcast-1.0.dtd');
        $newItem->addChild('description', $episode_Description);
        $newItem->addChild('content', $episode_Description);
        
        $enclosure = $newItem->addChild('enclosure');
        $enclosure->addAttribute('url', $fileLinks[0]);
        $enclosure->addAttribute('length', $episode_Duration);
        $enclosure->addAttribute('type', "audio/mpeg");
        
        $newItem->addChild('itunes:subtitle', substr($episode_Subtitle, 0, 225), 'http://www.itunes.com/dtds/podcast-1.0.dtd');
        $newItem->addChild('itunes:summary', $episode_Subtitle, 'http://www.itunes.com/dtds/podcast-1.0.dtd');
        $newItem->addChild('itunes:author', htmlspecialchars("Alex Zierhut & Luca Lustig"), 'http://www.itunes.com/dtds/podcast-1.0.dtd');
        $newItem->addChild('itunes:duration', formatTime($episode_Duration), 'http://www.itunes.com/dtds/podcast-1.0.dtd');

        $rss->asXML("feed.xml");

//Youtube convert
//Youtube Upload
//Soundcloud Upload
//Tweet Episode

    } else {
?>
<html>
    <head>
        <title>Neue Episode</title>
        <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.blue-deep_purple.min.css" />
        <style>
            input, textarea {
                width: 100%;
            }
        </style>
    </head>
    <body>
        <div style="margin: 0 auto; width: 70%;">
            <h2 style="padding: 10 0; margin:0;">Neue Episode</h2>
            <form method="post" enctype="multipart/form-data" action="?new">
                Title
                <input type="text" name="title">
                Subtitle
                <input type="text" name="subtitle">
                Description
                <textarea name="description"></textarea>
                Audio only mp3
                <input type="file" name="audio">
                Duration in seconds
                <input type="number" name="duration">
                Web-Thumbnail
                <input type="file" name="thumbnailWeb">
                RSS-Thumbnail
                <input type="file" name="thumbnailRSS">
                <input type="submit" value="Hochladen &amp; Veröffentlichen" style="margin-top: 10px;">
            </form>
        </div>
    </body>
</html>
<?php
    }
?>
