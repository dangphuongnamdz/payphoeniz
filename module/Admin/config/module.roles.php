<?php
//access allow
return array(
    'editor'=> array(
        'Application\Controller\Passport',  //[Front-end]
        'Application\Controller\Doc',       //[Front-end]
        'Application\Controller\Index',     //[Front-end]
        'Application\Controller\Payment',   //[Front-end]
        'Application\Controller\Payments',  //[Front-end]
        'Application\Controller\History',   //[Front-end]
        'Admin\Controller\Auth',            //[Admin]-Login
        'Admin\Controller\Category',        //[Admin]-Danh muc
        'Admin\Controller\Index',           //[Admin]-Dashboard
        'Admin\Controller\Posts',           //[Admin]-Bai viet
        // 'Admin\Controller\Gold',            //[Admin]-Gold
        // 'Admin\Controller\ChargeType',      //[Admin]-Gold
        //'Admin\Controller\Product',       //[Admin]-Product
        //'Admin\Controller\Server',        //[Admin]-Server list
        //'Admin\Controller\User',          //[Admin]-User
    ),
    'operation'=> array(
        'Application\Controller\Passport',  //[Front-end]
        'Application\Controller\Doc',       //[Front-end]
        'Application\Controller\Index',     //[Front-end]
        'Application\Controller\Payment',   //[Front-end]
        'Application\Controller\Payments',  //[Front-end]
        'Application\Controller\History',   //[Front-end]
        'Admin\Controller\Auth',            //[Admin]-Login
        'Admin\Controller\Category',        //[Admin]-Danh muc
        'Admin\Controller\Index',           //[Admin]-Dashboard
        'Admin\Controller\Posts',           //[Admin]-Bai viet
        'Admin\Controller\Gold',            //[Admin]-Gold
        'Admin\Controller\ChargeType',      //[Admin]-Gold
        'Admin\Controller\Server',          //[Admin]-Server list
        'Admin\Controller\Statistic',       //[Admin]-Statistic
        'Admin\Controller\Gamer',           //[Admin]-Compensation
        'Admin\Controller\Product',         //[Admin]-Product
        //'Admin\Controller\User',          //[Admin]-User
    ),
    'admin'=> array(
        'Application\Controller\Passport',
        'Application\Controller\Doc',
        'Application\Controller\Index',
        'Application\Controller\Payment',
        'Application\Controller\Payments',
        'Application\Controller\History',
        'Admin\Controller\Auth',
        'Admin\Controller\Category',
        'Admin\Controller\Index',
        'Admin\Controller\Posts',
        'Admin\Controller\Gold',       
        'Admin\Controller\ChargeType',
        'Admin\Controller\Server',
        'Admin\Controller\User',
        'Admin\Controller\Image',
        'Admin\Controller\Statistic',
        'Admin\Controller\Gamer',
        'Admin\Controller\Product',
        
    ),
);