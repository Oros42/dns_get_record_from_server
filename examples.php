<?php
include "dns_get_record_from_server.php";

echo "ecirtam.net from 80.67.169.12 (FDN):\n";
print_r( dns_get_record_from_server('ecirtam.net', '80.67.169.12') );

echo "\n'debian.org' from 2001:910:800::12 (FDN):\n"; // If you have an IPv6 adress
print_r( dns_get_record_from_server('debian.org', '2001:910:800::12') );
?>