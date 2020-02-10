<?php
return [
	'api' => [
        'role'  => [
        	'url' => 'http://mhtx2-center-yn.ios.100d.mobi:8002/api/getrole?userid=%s&server_id=%d&time=%d&sign=%s',
            'key' => 'OKV7UOOYoNlUMefPazraYhtz',        	
        ],
        'exchange' => [
            'url_refund' => 'http://mhtx2-center-yn.ios.100d.mobi:8002/service/confirm/muyouweb?userid=%s&roleid=%s&server_id=%s&order_id=%s&item_id=%d&money=%d&gold=%d&time=%s&sign=%s',
            'url' => 'http://mhtx2-center-yn.ios.100d.mobi:8002/service/confirm/muyou',
        	'key' => 'NNzfRE20VODdFIuj2Kv5ReH1lioD9',
            'product' => [
                '10000' => [
                    'product_id'=>'mc.60',
                    'gold'=>'10000',
                ],                               
            ],
        ],
    ],
];