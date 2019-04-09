# MusicVote

MusicVote helps you to collaboratively decide which Youtube video should be played.
Party use case: Your guests open the website (root directory) and you browse to the /player endpoint on your TV or notebook. The "clients" can then vote for their desired video to play on the "player". The voted videos are inserted into a playlist and are played on your TV or notebook. The clients can influence the playlist by voting the other videos up and down.


## Compatibility

MusicVote is currently developed and tested with PHP 7.2.


## Configuration

Create config.php in root directory by copying the config-sample.php file.

    <?php
    $config['db']['host']   = "localhost";
    $config['db']['user']   = "dbuser";
    $config['db']['pass']   = "secret";
    $config['db']['dbname'] = "dbname";
    $config['db']['port'] = "3306";
    $config['api_key'] = "..."; // Youtube Developer API Key
    $config['fallback_playlist'] = "..."; // Youtube fallback playlist ID


## Youtube API

MusicVotes uses the Youtube API to search for videos, get detailed video information like the title and duration and gets items of a fallback playlist if the current playlist is empty.
