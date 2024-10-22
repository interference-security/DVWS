#!/bin/sh

php ws-socket.php --heartbeat-interval ${HEARTBEAT_INTERVAL:-10} &
php -S 0.0.0.0:8888

