<?php
return [
	'api' => [
        'role'  => [
        	'url' => 'http://sandbox.api.gamebaila.online/api/getrole?username=%s&server_id=%d&time=%d&sign=%s',
            'key' => 'af9952fdca73cb06269980466eb0c198',        	
        ],
        'exchange' => [
            'url' => 'http://sandbox.api.gamebaila.online/iapexchange',
        	'key' => 'f0ba6cdb25ac253aa628bb50b225023f',
            'product' => [
                '10000' => [
                    'product_id'=>'101',
                    'gold'=>'10000',
                ],
                '20000' => [
                    'product_id'=>'102',
                    'gold'=>'20000',
                ],                
                '50000' => [
                    'product_id'=>'103',
                    'gold'=>'50000',
                ],
                '100000' => [
                    'product_id'=>'104',
                    'gold'=>'100000',
                ],
                '200000' => [
                    'product_id'=>'105',
                    'gold'=>'200000',
                ],
                '500000' => [
                    'product_id'=>'106',
                    'gold'=>'500000',
                ],                
            ],
        ],
    ],
];