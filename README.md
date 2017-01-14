# dns_get_record_from_server
PHP function. 
Get IPv4 and IPv6 addresses of a domain from a specific DNS server.  
  
Because in PHP you can't select the DNS server with the function [dns_get_record()](https://secure.php.net/manual/en/function.dns-get-record.php), I have made [this new function](dns_get_record_from_server.php).  
  
[Example of use](examples.php) :  
With :
```PHP
<?php
include "dns_get_record_from_server.php";

echo "ecirtam.net from 80.67.169.12 (FDN):\n";
print_r( dns_get_record_from_server('ecirtam.net', '80.67.169.12') );

echo "\n'debian.org' from 2001:910:800::12 (FDN):\n"; // If you have an IPv6 adress
print_r( dns_get_record_from_server('debian.org', '2001:910:800::12') );
?>
```
You should have :  
```
ecirtam.net from 80.67.169.12 (FDN):
Array
(
    [4] => Array
        (
            [0] => 151.236.6.249
        )

    [6] => Array
        (
            [0] => 2a03:f80:ed15:ca7:ea75:b12d:6cc:4242
        )

)

'debian.org' from 2001:910:800::12 (FDN):
Array
(
    [4] => Array
        (
            [0] => 5.153.231.4
            [1] => 128.31.0.62
            [2] => 130.89.148.14
            [3] => 140.211.15.34
        )

    [6] => Array
        (
            [0] => 2001:610:1908:b000::148:14
            [1] => 2001:41c8:1000:21::21:4
        )

)
```
