<?php
$page_data = <<<EOT
<div class="page-header">
    <h1>File Inclusion</h1>
</div>
<div class="row">
    <div class="col-md-12">
        <p>
            What do you like?
            <form class="form-inline">
		    <div class="form-group">
			<input type="radio" class="form-control" id="name" name="name" value="pages/games.txt"> Games
		    </div><br>
		    <div class="form-group">
			<input type="radio" class="form-control" id="name" name="name" value="pages/books.txt"> Books
		    </div><br>
            </form>
            <button type="submit" class="btn btn-success" id="send">Show</button>
        </p>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <p id="result">
        </p>
    </div>
</div>
EOT;

$page_script= <<<EOT
$(document).ready(function(){
//Open a WS server connection
var wsUri = "ws://dvws.local:8080/file-inclusion";
websocket = new WebSocket(wsUri);

//Connected to WS server
websocket.onopen = function(ev)
{
    console.log('Connected to server');
}

//Close WS server connection
websocket.onclose = function(ev)
{
    console.log('Disconnected from server');
};

//Message received from WS server
websocket.onmessage = function(ev)
{
    console.log('Message: '+ev.data);
    document.getElementById("result").innerHTML = "<pre>" + ev.data + "</pre>";
};

//Error
websocket.onerror = function(ev)
{
    console.log('Error: '+ev.data);
};

//Send value to WS
$('#send').click(function()
{
    var field_value = $('input[name="name"]:checked').val();
    console.log(field_value);
    websocket.send(field_value);
});
});
EOT;
?>

<?php require_once('includes/template.php'); ?>
