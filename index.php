<?php
$page_data = <<<EOT
<div class="page-header">
    <h1>OWASP Damn Vulnerable Web Sockets (DVWS)</h1>
</div>
<div class="row">
    <div class="col-md-12">
        <p>
            <a href="https://owasp.org/www-project-damn-vulnerable-web-sockets/">OWASP Damn Vulnerable Web Sockets (DVWS)</a> is a vulnerable web application which works on web sockets for client-server communication. The flow of the application is similar to <a href="https://github.com/ethicalhack3r/DVWA" target="_blank">DVWA</a>.<br><br>
            In the "hosts" file of your attacker machine create an entry for "dvws.local" to point at the IP address hosting the DVWS application.
Location of the "hosts" file:<br><br>
Windows: C:\windows\System32\drivers\etc\hosts<br>
Linux: /etc/hosts<br><br>
Sample entry for hosts file:<br>
192.168.100.199&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dvws.local<br><br>
            The application requires the following:
            <ol>
                <li>Apache + PHP + MySQL</li>
                <li>PHP with MySQLi support</li>
                <li><a href="https://github.com/ratchetphp/Ratchet" target="_blank">Ratchet</a></li>
                <li><a href="https://github.com/bixuehujin/reactphp-mysql/" target="_blank">ReactPHP-MySQL</a></li>
            </ol><br>
            Set the MySQL hostname, username, password and an existing database name in the "<u><i>includes/connect-db.php</i></u>" file then go to <a href="setup.php">Setup</a> to finish setting up DVWS.<br><br>
            On the host running this application, run the following command from DVWS directory:<br>
            <br><pre>composer install
php ws-socket.php --heartbeat-interval 10</pre><br>
            This open-source project is hosted here <a href="https://github.com/interference-security/DVWS/" target="_blank">https://github.com/interference-security/DVWS/</a>.<br><br>
            DVWS created by <a href="https://twitter.com/xploresec">@xploresec</a><br><br>
            <a href="https://twitter.com/xploresec"><img src="img/twitter.png" style="width:48px; height:48px;"></a> &nbsp;&nbsp;&nbsp; 
            <a href="https://github.com/interference-security/DVWS/"><img src="img/github.png" style="width:48px; height:48px;"></a> &nbsp;&nbsp;&nbsp; 
            <a href="https://owasp.org/www-project-damn-vulnerable-web-sockets/"><img src="img/owasp.png" style="width:48px; height:48px;"></a>

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

EOT;
?>

<?php require_once('includes/template.php'); ?>
