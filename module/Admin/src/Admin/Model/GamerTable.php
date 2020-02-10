<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Model;

use Zend\Soap\Client as SoapClient;

 class GamerTable
 {
     protected $soapClientOptions;
     public function __contruct(){
        $opts = array(
            'http' => array(
                'user_agent' => 'PHPSoapClient'
            )
        );
        $context = stream_context_create($opts);
        $this->soapClientOptions = array(
            'stream_context' => $context,
            'cache_wsdl' => WSDL_CACHE_NONE
        );
     }

     //get user Passport
     public function getUserPassport($username, $arrConfig)
     {
        $parameters ['agent'] = $arrConfig['agent'];//
        $parameters ['key'] = $arrConfig['key'];//
        $parameters ['username'] = $username;//
        $parameters ['sign'] = md5 ( $parameters ['key'] . md5 ( $parameters ['username'] ) . $arrConfig['secret'] );//
        $_WSDL_URI_reg = "/passport/profile/wsdl";
        $client     = new SoapClient($arrConfig['domain'].$_WSDL_URI_reg, $this->soapClientOptions);
        $result = $client->profilePassport ( $parameters );
        return $result;
     }

     //update status Passport
     public function updateStatusUserPassport($username, $status, $arrConfig)
     {
       $parameters ['agent'] = $arrConfig['agent'];//
       $parameters ['key'] = $arrConfig['key'];//
       $parameters ['username'] = $username;//
       $parameters ['status'] = $status;//
       $parameters ['sign'] = md5 ( $parameters ['key'] . md5 ( $parameters ['username'] ) . $arrConfig['secret'] );//
       $_WSDL_URI_reg = "/passport/updateprofile/wsdl";
       $client     = new SoapClient($arrConfig['domain'].$_WSDL_URI_reg, $this->soapClientOptions);
       $result = $client->updateProfilePassport ( $parameters );
       return $result;
     }

     //update user Passport
     public function updateUserPassport($user, $arrConfig)
     {
       $parameters ['agent'] = $arrConfig['agent'];//
       $parameters ['key'] = $arrConfig['key'];//
       $parameters ['username'] = $user['username'];//
       $parameters ['fullname'] = $user['fullname'];
       $parameters ['birthday'] = $user['birthday'];//
       $parameters ['sex'] = $user['sex'];//
       $parameters ['identityNumber'] = $user['identityNumber'];
       $parameters ['identityDate'] = $user['identityDate'];
       $parameters ['identityPlace'] = $user['identityPlace'];
        if($user['email'])
            $parameters ['email'] = $user['email'];
        if($user['mobile'])
            $parameters ['mobile'] = $user['mobile'];
       $parameters ['address'] = $user['address'];
       $parameters ['city'] = $user['city'];
       $parameters ['company'] = $user['company'];
       $parameters ['companyAddress'] = $user['companyAddress'];
       $parameters ['sign'] = md5 ( $parameters ['key'] . md5 ( $parameters ['username'] ) . $arrConfig['secret'] );//
       $_WSDL_URI_reg = "/passport/updateprofile/wsdl";
       $client     = new SoapClient($arrConfig['domain'].$_WSDL_URI_reg, $this->soapClientOptions);
       $result = $client->updateProfilePassport ( $parameters );
       return $result;
     }


      //forgotPasswordPassport
      public function forgotPasswordPassport($user, $arrConfig)
      {
          $parameters ['agent'] = $arrConfig['agent'];//
          $parameters ['key'] = $arrConfig['key'];//
          $parameters ['username'] = $user->username;//
          $parameters ['sign'] = md5 ( $parameters ['key'] . md5 ( $parameters ['username'] ) . $arrConfig['secret'] );//
          $_WSDL_URI_reg = "/passport/forgotpassword/wsdl";
          $client     = new SoapClient($arrConfig['domain'].$_WSDL_URI_reg, $this->soapClientOptions);
          $result = $client->forgotPasswordPassport($parameters);
          return $result;
      }

      //resetPasswordPassport
      public function resetPasswordPassport($user, $newpassword, $arrConfig)
      {
          $password = md5($newpassword);
          $parameters ['agent'] = $arrConfig['agent'];//
          $parameters ['key'] = $arrConfig['key'];//
          $parameters ['username'] = $user->username;//
          $parameters ['password'] = $password;//
          $parameters ['resetKey'] = $user->resetKey;//
          $parameters ['sign'] = md5 ( $parameters ['key'] . md5 ( $parameters ['username']. $parameters ['password'] ) . $arrConfig['secret'] );//
          $_WSDL_URI_reg = "/passport/resetpassword/wsdl";
          $client     = new SoapClient($arrConfig['domain'].$_WSDL_URI_reg, $this->soapClientOptions);
          $result = $client->resetPasswordPassport($parameters);
          return $result;
      }
    
 }