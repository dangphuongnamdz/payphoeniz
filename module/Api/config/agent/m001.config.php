<?php
return [
	'api' => [
        'game_id' => '338',
        'op_id' => '2641',
        'role'  => [
        	//'url' => 'https://user.gtarcade.com/api/payThird/checkRole',
			'url' => 'https://pay.gtarcade.com/third/vn-lod/check-role',
            'app_id' => '5ad84a5f867f5',
            'key' => 'e5bFDQnx1ZkSRBiZ',        	
        ],
        'exchange' => [
            //'url' => 'https://user.gtarcade.com/api/payThird/exchange',
			 'url' => 'https://pay.gtarcade.com//third/vn-lod/exchange',
        	'app_id' => '5ade9b4ea7aaf',
            'product' => [
                '10000' => [
                    'product_id'=>'diamond50',
                    'gold'=>'50',
                ],
                '20000' => [
                    'product_id'=>'diamond100',
                    'gold'=>'100',
                ],
                '30000' => [
                    'product_id'=>'diamond150',
                    'gold'=>'150',
                ],
                '50000' => [
                    'product_id'=>'diamond250',
                    'gold'=>'250',
                ],
                '100000' => [
                    'product_id'=>'diamond500',
                    'gold'=>'500',
                ],
                '200000' => [
                    'product_id'=>'diamond1000',
                    'gold'=>'1000',
                ],
                '300000' => [
                    'product_id'=>'diamond1500',
                    'gold'=>'1500',
                ],
                '500000' => [
                    'product_id'=>'diamond2500',
                    'gold'=>'2500',
                ],
                '1000000' => [
                    'product_id'=>'diamond5000',
                    'gold'=>'5000',
                ],
                '150000' => [
                    'product_id'=>'monthlycard',
                    'gold'=>'800',
                ],
                '400000' => [
                    'product_id'=>'diamond2000',
                    'gold'=>'2000',
                ],
                '600000' => [
                    'product_id'=>'diamond3000',
                    'gold'=>'3000',
                ],
                '800000' => [
                    'product_id'=>'diamond4000',
                    'gold'=>'4000',
                ],
                '2000000' => [
                    'product_id'=>'diamond10000',
                    'gold'=>'10000',
                ],
            ],
        ],
    ],
];