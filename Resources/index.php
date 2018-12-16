<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:100,400">
    <meta name="theme-color" content="#ff4500">
</head>

<navbar id="navbar">
    <div id="navbar-logo"></div>
</navbar>

<div style="display: none; position: fixed; z-index: 1000; width: 100%; height: 100%; left: 0; top: 0; background: rgba(0, 0, 0, .5)"
     id="lock">
    <input id="idinput" placeholder="Enter ID here">
    <button id="connect" onclick="btn();">Connect</button>
</div>

<img id="album">



<div id="player">
    <span id="title">Title</span>
    <span id="name">Name</span>
    <span id="curtime">00:00</span>
    <span id="wholetime">00:00</span>
    <input id="time" disabled value="0" max="100" type="range" title="time">
    <button id="left" onclick="left();"></button>
    <button id="play" onclick="play();"></button>
    <button id="pause" onclick="pause();"></button>
    <button id="right" onclick="right();"></button>
    <button id="volumeBtn" mute="false" onclick="volume();"></button>
</div>
<div id="volumediv">
    <input id="volume" type="range" value="0" max="100">
</div>
</html>

<script>
    window.addEventListener('load', function () {
        var slider = document.getElementById("volume");
        slider.oninput = function () {
            wss.send('setvolume:' + this.value);
        };
    });

    document.getElementById("idinput")
        .addEventListener("keyup", function (event) {
            event.preventDefault();
            if (event.keyCode === 13) {
                document.getElementById("connect").click();
            }
        });

    var ip = location.hostname;

    var wss;

    var connection = false;

    function btn() {
        ip = document.getElementById('idinput').value;
        connect();
    }

    function connect() {
        wss = new WebSocket('ws://' + ip + ':15768/soundcloud/mobile');
        setupWSS(wss);
    }

    function connected() {
        document.getElementById('lock').style.display = 'none';
        connection = true;
    }

    function disconnected() {
        document.getElementById('idinput').value = ip;
        document.getElementById('lock').style.display = 'initial';
        connection = false;
    }

    function state() {
        if (connection)
            wss.send('state');
    }

    setInterval(state, 1000);

    if (ip != null) {
        connect();
    } else {
        disconnected();
    }

    function setupWSS(s) {
        s.onmessage = function (message) {
            var text = message.data;
            if (text.startsWith('playing:')) {
                if (text.split(':')[1] === 'false') {
                    document.getElementById('play').style.display = 'initial';
                    document.getElementById('pause').style.display = 'none';
                } else {
                    document.getElementById('play').style.display = 'none';
                    document.getElementById('pause').style.display = 'initial';
                }
                return;
            }
            if (text.startsWith('curtime:')) {
                document.getElementById('curtime').innerHTML = text.substring(text.indexOf(':') + 1);
                return;
            }
            if (text.startsWith('wholetime:')) {
                document.getElementById('wholetime').innerHTML = text.substring(text.indexOf(':') + 1);
                return;
            }

            if (text.startsWith('valuenow:')) {
                document.getElementById('time').setAttribute('value', text.split(':')[1]);
                return;
            }

            if (text.startsWith('valuemax:')) {
                document.getElementById('time').setAttribute('max', text.split(':')[1]);
                return;
            }

            if (text.startsWith('songtitle:')) {
                document.getElementById('title').innerHTML = text.split(':')[1];
                return;
            }

            if (text.startsWith('songname:')) {
                document.getElementById('name').innerHTML = text.split(':')[1];
                return;
            }

            if (text.startsWith('picture:')) {
                document.getElementById('album').style.backgroundImage = text.substring(text.indexOf(':') + 1);
                return;
            }

            if (text.startsWith('volume:')) {
                document.getElementById('volume').value = text.split(':')[1];
                return;
            }
        };

        s.onclose = function () {
            disconnected();
        };

        s.onopen = function () {
            connected();
        };
    }

    function left() {
        wss.send('left');
    }

    function play() {
        wss.send('play');
    }

    function pause() {
        wss.send('pause');
    }

    function right() {
        wss.send('right');
    }

    var volumeshowed = false;

    function volume(){
        if(volumeshowed){
            document.getElementById('volumediv').style.bottom = 150 - document.getElementById('volumediv').offsetHeight + 'px';
            volumeshowed = false;
        } else {
            wss.send('volume');
            document.getElementById('volumediv').style.bottom = '150px';
            volumeshowed = true;
        }
    }
</script>

<style>
    html, body {
        font-family: Roboto;
        font-weight: 400;
        margin: 0;
        padding: 0;
    }

    * {
        outline: none;
    }

    #album {
        width: 100%;
        position: fixed;
        height: calc(100% - 45px - 150px);
        top: 45px;
        background: no-repeat;
        background-size: 100% 100%;
        z-index: -1;
    }

    #idinput {
        position: fixed;
        top: 50%;
        left: 50%;
        box-shadow: 0 0 5px 0 rgba(0, 0, 0, .5);
        transform: translate(-50%, -50%);
        border: none;
        font-size: 20px;
        height: calc(2% + 35px);
        width: 60%;
        background: white;
        text-align: center;
        border-radius: 5px;
        transition: 300ms all;
    }

    #idinput:focus {
        box-shadow: 0 0 10px 0 orangered;
    }

    #connect {
        position: fixed;
        border-radius: 5px;
        top: calc(50% + 10px + 35px + 2%);
        box-shadow: 0 0 5px 0 rgba(0, 0, 0, .5);
        left: 50%;
        transform: translate(-50%, -50%);
        border: none;
        height: calc(2% + 35px);
        font-size: 20px;
        background: #333;
        color: white;
        width: 60%;
        transition: 300ms all;
    }

    #connect:hover {
        background: orangered;
    }

    #navbar {
        width: 100%;
        height: 44px;
        box-shadow: 0 0 2px 0 rgba(0, 0, 0, .5);
        background: #fff;
        position: fixed;
        top: 0;
        left: 0;
    }

    #navbar-logo {
        background: url(https://mobi.sndcdn.com/assets/images/hdpi/logo-881c7ae2.png) 0 50% no-repeat;
        background-size: 128px 16px;
        width: 128px;
        height: 44px;
        margin: 0 auto;
        display: block;
    }

    #player {
        background: white;
        position: fixed;
        width: 100%;
        bottom: 0;
        left: 0;
        height: 150px;
        box-shadow: 0 0 2px 0 rgba(0, 0, 0, .5);
        z-index: 5;
    }

    #title {
        position: absolute;
        top: 5px;
        left: 50%;
        transform: translateX(-50%);
        font-weight: 500;
        white-space: nowrap;
    }

    #name {
        position: absolute;
        top: 30px;
        left: 50%;
        transform: translateX(-50%);
        font-weight: 100;
        white-space: nowrap;
    }

    #curtime, #wholetime {
        position: absolute;
        text-align: center;
        display: block;
        width: 15%;
        top: calc(25% + 25px);
    }

    #wholetime {
        right: 0;
    }

    #time {
        -webkit-appearance: none;
        position: absolute;
        left: 50%;
        top: calc(25% + 8px + 25px);
        margin: 0 auto;
        width: 70%;
        height: 3px;
        transform: translateX(-50%);
        background: #ccc;
    }

    #time::-webkit-slider-thumb {
        -webkit-appearance: none; /* Override default look */
        appearance: none;
        width: 10px; /* Set a specific slider handle width */
        height: 10px; /* Slider handle height */
        background: orangered; /* Green background */
        border-radius: 5px;
        cursor: pointer; /* Cursor on hover */
    }

    #play, #left, #right, #pause, #volumeBtn {
        position: absolute;
        width: 25%;
        height: 40%;
        bottom: 0;
        transform: translateX(-50%);
        border: none;
    }

    #play {
        left: 50%;
        background: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCI+PHBhdGggZmlsbD0iIzMzMyIgZD0iTTggNXYxNGwxMS03eiIvPjwvc3ZnPgo=) no-repeat 50% center;
    }

    #pause {
        display: none;
        left: 50%;
        background: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCI+PHBhdGggZmlsbD0iIzMzMyIgZD0iTTYgMTloNFY1SDZ2MTR6bTgtMTR2MTRoNFY1aC00eiIvPjwvc3ZnPgo=) no-repeat 50% center;
    }

    #left {
        left: 25%;
        background: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCI+PHBhdGggZmlsbD0iIzMzMyIgZD0iTTcgNmgydjEySDdWNnptMiA2bDggNlY2bC04IDZ6Ii8+PC9zdmc+Cg==) 50% center no-repeat;
    }

    #right {
        left: 75%;
        background: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCI+PHBhdGggZmlsbD0iIzMzMyIgZD0iTTcgMThsOC02LTgtNnYxMnptOC0xMnYxMmgyVjZoLTJ6Ii8+PC9zdmc+Cg==) 50% center no-repeat;
    }

    #volumeBtn{
        left: 90%;
    }

    #volumeBtn[mute="false"]{
        background: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCI+PHBhdGggZmlsbD0iIzMzMyIgZD0iTTQgOWg0LjAwMkwxMiA1djE0Yy0yLjQ0Ni0yLjY2Ny0zLjc3OC00LTMuOTk4LTRINFY5em0xMCA0YTEgMSAwIDAgMCAwLTJWOWEzIDMgMCAwIDEgMCA2di0yem0wIDRhNSA1IDAgMCAwIDAtMTBWNWE3IDcgMCAwIDEgMCAxNHYtMnoiLz48L3N2Zz4K) 50% center no-repeat;
    }

    #volumeBtn[mute="true"]{
        background: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCI+PHBhdGggZmlsbD0iIzMzMyIgZD0iTTE4IDEwLjU4NGwtMi4yOTMtMi4yOTEtMS40MTQgMS40MTQgMi4yOTMgMi4yOTEtMi4yOTEgMi4yOTEgMS40MTQgMS40MTUgMi4yOTItMi4yOTIgMi4yOTQgMi4yOTIgMS40MTQtMS40MTUtMi4yOTMtMi4yOTEgMi4yOTEtMi4yOS0xLjQxNC0xLjQxNS0yLjI5MiAyLjI5MXpNNCA5aDQuMDAyTDEyIDV2MTRjLTIuNDQ2LTIuNjY3LTMuNzc4LTQtMy45OTgtNEg0Vjl6Ii8+PC9zdmc+Cg==) 50% center no-repeat;
    }

    #volumediv{
        background: white;
        box-shadow: 0 0 2px 0 rgba(0, 0, 0, .5);
        width: 100%;
        height: 35px;
        position: fixed;
        bottom: calc(150px - 35px);
        transition: bottom 300ms;
    }

    #volume {
        -webkit-appearance: none;
        position: absolute;
        left: 5%;
        top: 50%;
        margin: 0 auto;
        width: 90%;
        height: 3px;
        transform: translateY(-50%);
        background: #ccc;
    }

    #volume::-webkit-slider-thumb {
        -webkit-appearance: none; /* Override default look */
        appearance: none;
        width: 10px; /* Set a specific slider handle width */
        height: 10px; /* Slider handle height */
        background: orangered; /* Green background */
        border-radius: 5px;
        cursor: pointer; /* Cursor on hover */
    }
</style>