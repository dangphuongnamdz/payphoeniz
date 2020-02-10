<?php
return [
	'api' => [
        'role'  => [
        	'url' => 'http://128.199.191.93:8182/api/getrole?userid=%s&server_id=%d&time=%d&sign=%s',
            'key' => 'b4d93d6defec498c57d65ca77ef044f9',        	
        ],
        'exchange' => [
            'url'        => 'http://128.199.191.93:8182/iapexchange',
            'url_refund' => 'http://128.199.191.93:8182/exchange?userid=%s&roleid=%s&server_id=%s&order_id=%s&item_id=%d&money=%d&gold=%d&time=%d&sign=%s',
        	'key'       => 'b4d93d6defec498c57d65ca77ef044f9',
        ],
    ],
];
