<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    //config db
    'db' => array(
        'driver'         => 'Pdo',
        'dsn'            => 'mysql:dbname=pay_100d;host=localhost',
        'username'       => 'root',
        'password'       => '',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
    ),
	//domain
    'domain'                => 'http://pay100d.local',
	//social
	'social' => array(
        'facebook' => array( 
            'appId'    => '1587420864841116',
            'secret' => '7f7b9c8936a60d9513796b873b14782a',
            'graph_version' => 'v2.8',
            'fileUpload' => false,
            'allowSignedRequest' => false
        ),
        'google' => array(
            'google_client_id'    => '1080529599153-3a8r2cnlgvltucj22iupm2n1ulnd1lud.apps.googleusercontent.com',
            'google_client_secret' => 'qymc_Q8MHTZjIwpE_wjXhjqE',
        )
    ),
    //data
    'data' => array(
        'sale'             => 'NẠP HPCODE = 105% ; GATE = 95% ; VCOIN = 90% ; ATM = 100% (Thẻ HPCode có bán ở các cửa hàng của Payoo)'
    ),
	//config email
    'email' => array(
        'name'             => 'localhost.localdomain',
        'host'             => '127.0.0.1',
        'port'             => 225,
        'sender'              => 'noreply@100d.mobi',
        'timeoutSecretKey'    => 10800,  //3 hour
    ),
    //time session
    'time_session'            => 86400,
    //config connect to passport
    'passport' => array(
        'domain'            => 'http://dev.pp.100d.mobi',
        'agent'             =>  'm001', //default
        'key'               => 'gzV50jHSvMINnnOv',
        'secret'            => 'gzV50jHSvMINnnOv',
        'encryptKey'        => 'xR4IMJ(!*(X%&976@H#C',
    ),
    //config connect to payment
    'payment' => array(
        'domain'            => 'http://dev.pm.100d.mobi',
        'm001'  =>  array(
            'key'              	=> 'gzV50jHSvMINnnOv',
            'secret'            => 'gzV50jHSvMINnnOv'
        ),
        'm002'  =>  array(
            'key'              	=> 'gzV50jHSvMINnnOvss',
            'secret'            => 'gzV50jHSvMINnnOvss'
        ),
		'm003'  =>  array(
            'key'              	=> 'gzV50jHSvMINnnOv',
            'secret'            => 'gzV50jHSvMINnnOv'
        ),
		'm004'  =>  array(
            'key'              	=> 'gzV50jHSvMINnnOv',
            'secret'            => 'gzV50jHSvMINnnOv'
        ),
        'm005'  =>  array(
            'key'              	=> 'gzV50jHSvMINnnOv',
            'secret'            => 'gzV50jHSvMINnnOv',
            'rate_refund'       => 3,
            'rate' => array(
                array(
                    'name'         => 'ATM',
                    'type'         => 'ATM',
                    'rate'         => '1',
                ),
                array(
                    'name'         => 'GATE',
                    'type'         => 'GATE',
                    'rate'         => '1',
                ),
                array(
                    'name'         => 'VTT',
                    'type'         => 'VTT',
                    'rate'         => '1',
                ),
                array(
                    'name'         => 'HPC',
                    'type'         => 'HPC',
                    'rate'         => '3',
                )
            )
        )
    ),
    //config connect to ATM
    'atm' => array(
        'domain'            => 'http://dev.pm.100d.mobi',
        'WSDL_URI_reg'      => '/payment/chargeatm',
        'm001' => array(
            'key'              	=> 'gzV50jHSvMINnnOv',
            'secret'            => 'LVqNOSJlEvHvIopboI6W',
            'return'            => 0,    //cong vi
            'returnUrl'           => 'http://localhostpay.com/payment/error-charge-atm.html',
            'backUrl'         => 'http://localhostpay.com/payment/success-charge-atm.html',
            'successRedirect'   => 'http://localhostpay.com/payment/legacy-of-discord.html', 
            'errorRedirect'     => 'http://localhostpay.com'
        ),
        'm002' => array(
            'key'              	=> 'gzV50jHSvMINnnOvss',
            'secret'            => 'gzV50jHSvMINnnOvss',
            'return'            => 0,    //cong vi
            'returnUrl'           => 'http://localhostpay.com/payment/error-charge-atm.html',
            'backUrl'         => 'http://localhostpay.com/payment/success-charge-atm.html',
            'successRedirect'   => 'http://localhostpay.com/payment/phong-van-h5.html', 
            'errorRedirect'     => 'http://localhostpay.com'
        ),
		'm003' => array(
            'key'              	=> 'gzV50jHSvMINnnOv',
            'secret'            => 'gzV50jHSvMINnnOv',
            'return'            => 0,    //cong vi
            'returnUrl'           => 'http://localhostpay.com/payment/error-charge-atm.html',
            'backUrl'         => 'http://localhostpay.com/payment/success-charge-atm.html',
            'successRedirect'   => 'http://localhostpay.com/payment/m003.html', 
            'errorRedirect'     => 'http://localhostpay.com'
        ),
		'm004' => array(
            'key'              	=> 'gzV50jHSvMINnnOv',
            'secret'            => 'gzV50jHSvMINnnOv',
            'return'            => 0,    //cong vi
            'returnUrl'           => 'http://localhostpay.com/payment/error-charge-atm.html',
            'backUrl'         => 'http://localhostpay.com/payment/success-charge-atm.html',
            'successRedirect'   => 'http://localhostpay.com/payment/m004.html', 
            'errorRedirect'     => 'http://localhostpay.com'
        ),
        'm005' => array(
            'key'              	=> 'gzV50jHSvMINnnOv',
            'secret'            => 'gzV50jHSvMINnnOv',
            'return'            => 1,   
            'returnUrl'         => 'http://pay100d.local/payment/success-charge-atm.html',
            'backUrl'           => 'http://pay100d.local/payment/error-charge-atm.html',
            'successRedirect'   => 'http://pay100d.local/payment/m003.html', 
            'errorRedirect'     => 'http://pay100d.local/'
        )
        
    ),
    //game
    'game' => array(
        'm002'  => array(
            'domainCharge'            => 'http://14.225.16.8/charge/exchange?username=%s&roleid=%d&server_id=%d&order_id=%s&item_id=%s&money=%s&gold=%s&time=%d&sign=%s',
            'keyPay'          => '209d26c1cf01ea58893fee244b10426f',
            'domainRole'      => 'http://14.225.16.8:86/Login/GetRole?username=%s&server_id=%d&time=%d&sign=%s',
			'keyRole'		=> '209d26c1cf01ea58893fee244b10426f',
        ),
		'm003' => array(
			'domainCharge'	=> 'http://mhtx2-center-yn.ios.100d.mobi:8002/service/confirm/muyouweb?userid=%s&roleid=%s&server_id=%s&order_id=%s&item_id=%d&money=%d&gold=%d&time=%s&sign=%s',
			'keyPay'		=> 'NNzfRE20VODdFIuj2Kv5ReH1lioD9',
			'domainRole'	=> 'http://mhtx2-center-yn.ios.100d.mobi:8002/api/getrole?userid=%s&server_id=%d&time=%d&sign=%s',
			'keyRole'		=> 'OKV7UOOYoNlUMefPazraYhtz',
		),
		'm004' => array(
			'domainCharge'	=> 'http://sandbox.api.gamebaila.online/exchange?username=%s&server_id=%s&order_id=%s&item_id=%s&money=%d&gold=%d&time=%d&sign=%s',
			'keyPay'		=> 'f0ba6cdb25ac253aa628bb50b225023f',
			'domainRole'	=> 'http://sandbox.api.gamebaila.online/api/getrole?username=%s&server_id=%d&time=%d&sign=%s',
			'keyRole'		=> 'af9952fdca73cb06269980466eb0c198',
        ),
        'm005' => array(
            'domainCharge'	=> 'http://128.199.191.93:8182/exchange?userid=%s&roleid=%s&server_id=%s&order_id=%s&item_id=%d&money=%d&gold=%d&time=%d&sign=%s',
            'keyPay'		=> 'b4d93d6defec498c57d65ca77ef044f9',
            'domainRole'	=> 'http://128.199.191.93:8182/api/getrole?userid=%s&server_id=%d&time=%d&sign=%s',
            'keyRole'		=> 'b4d93d6defec498c57d65ca77ef044f9',
        )
    ),
    'sdk' => array(
        'domain' => array(
            // 'android'       => 'http://supersdk-tw.gtarcade.com/Api/Notify/ESDK',
            // 'ios'           => 'http://supersdk-tw.gtarcade.com/Api/Notify/ESDKI',
            'android'       => 'http://supersdk-us.gtarcade.com/Api/Notify/ESDK',            
            'ios'           => 'http://supersdk-us.gtarcade.com/Api/Notify/ESDKI',
        ),
        'secret'            => '1jks;ddv@(Nv8Cy(',
		'key' =>array(
			'm003'			=>'Wu9V+]wQ,*fLE<Y',
			'm004'			=>'Wu9V+]wQ,*fLE<Y',
            'm002'			=>'Wu9V+]wQ,*fLE<Y',
            'm005'          =>'Wu9V+]wQ,*fLE<Y'
		)
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter'
                    => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
    )
);
