<?php
return [
	'api' => [
        'role'  => [
        	'url' => 'http://14.225.16.8:86/Login/GetRole?username=%s&server_id=%d&time=%d&sign=%s',
            'key' => '209d26c1cf01ea58893fee244b10426f',        	
        ],
        'exchange' => [
            'url' => 'http://14.225.16.8/charge/iapexchange',
			'urlExchange' => 'http://14.225.16.8/charge/exchange?username=%s&roleid=%d&server_id=%d&order_id=%s&item_id=%s&money=%s&gold=%s&time=%d&sign=%s',
        	'key' => '209d26c1cf01ea58893fee244b10426f',
            
        ],
    ],
];