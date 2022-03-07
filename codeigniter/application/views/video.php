<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AngujarJS Video Player</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
</head>
<body>
    <div ng-app="myApp">
        <div ng-controller="HomeCtrl as controller" class="videogular-container">
            <videogular vg-theme="controller.config.theme.url">
                <vg-media vg-src="controller.config.sources"
                          vg-tracks="controller.config.tracks">
                </vg-media>
        
                <vg-controls>
                    <vg-play-pause-button></vg-play-pause-button>
                    <vg-time-display>{{ currentTime | date:'mm:ss':'+0000' }}</vg-time-display>
                    <vg-scrub-bar>
                        <vg-scrub-bar-current-time></vg-scrub-bar-current-time>
                    </vg-scrub-bar>
                    <vg-time-display>{{ timeLeft | date:'mm:ss':'+0000' }}</vg-time-display>
                    <vg-time-display>{{ totalTime | date:'mm:ss':'+0000' }}</vg-time-display>
                    <vg-volume>
                        <vg-mute-button></vg-mute-button>
                        <vg-volume-bar></vg-volume-bar>
                    </vg-volume>
                    <vg-fullscreen-button></vg-fullscreen-button>
                </vg-controls>
        
                <vg-overlay-play></vg-overlay-play>
                <vg-poster vg-url='controller.config.plugins.poster'></vg-poster>
            </videogular>
        </div>
    </div>
</body>
</html>

<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.26/angular.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.26/angular-sanitize.min.js"></script>
<script src="https://unpkg.com/videogular@2.1.2/dist/videogular/videogular.js"></script>
<script src="https://unpkg.com/videogular@2.1.2/dist/controls/vg-controls.js"></script>
<script src="https://unpkg.com/videogular@2.1.2/dist/overlay-play/vg-overlay-play.js"></script>
<script src="https://unpkg.com/videogular@2.1.2/dist/poster/vg-poster.js"></script>
<script type="text/javascript" src="../assets/js/script.js"></script>