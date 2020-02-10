<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Model;

use Zend\Soap\Client as SoapClient;

 class UserTable
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

     //check login Passport
     public function checkLoginPassport($user, $arrConfig)
     {
        $password = md5($user->password);
        $parameters = array();
        $parameters ['agent'] = $arrConfig['agent'];//
        $parameters ['key'] = $arrConfig['key'];//
        $parameters ['username'] = $user->username;//
        $parameters ['password'] = $password;//
        $parameters ['sign'] = md5( $parameters ['key'] . md5 ( $parameters ['username'] . $parameters ['password'] ) . $arrConfig['secret'] );//	
        $_WSDL_URI_reg = "/passport/authenticate/wsdl";
        $client = new SoapClient($arrConfig['domain'].$_WSDL_URI_reg, $this->soapClientOptions);
        $result = $client->authenticatePassport($parameters);
        //save log file
        $string = "authenticatePassport #agent:".$arrConfig['agent']
        ." #username:".$parameters ['username']
        ." #status: ".$result['status'];
        $this->saveLogFile($arrConfig['agent'], $string);
        return $result;
     }
     //check login Passport
     public function checkLoginPassportNoPass($arrConfig, $info, $source, $ip)
     {
        $parameters = array(); 
        $parameters['agent'] = $arrConfig['agent'];//
        $parameters['key'] = $arrConfig['key'];//
        $parameters['info'] = $info;//
        $parameters['source'] = $source;//
        $parameters['ip'] = $ip;//
        $parameters['sign'] = md5( $parameters['key'] . md5( $parameters['info'] ) . $arrConfig['secret'] );//  
        $_WSDL_URI_reg="/passports/authenticatewithoutside";
        $link = $arrConfig['domain'].$_WSDL_URI_reg
        . '?agent='.$arrConfig['agent']  . '&key='.urlencode($arrConfig['key']) . '&info='.$parameters['info']
        . '&source='.$parameters['source'] . '&ip='.$parameters['ip'] . '&sign='.$parameters['sign'];
        $result = json_decode(file_get_contents($link));
        if($result->status==1) 
            $username = $result->result->username;
        else
            $username = '';
        //save log file
        $string = "authenticatePassportNoPassword #agent:".$arrConfig['agent']
        ." #info:".$parameters['info']
        ." #source:".$parameters['source']
        ." #ip:".$parameters['ip']
        ." return #username:".$username
        ." #status: ".$result->status;
        $this->saveLogFile($arrConfig['agent'], $string);
        return $result;
     }

    //check username exist Passport
    public function getUserExistPassport($user, $arrConfig)
    {
        $parameters ['agent'] = $arrConfig['agent'];//
        $parameters ['key'] = $arrConfig['key'];//
        $parameters ['username'] = $user->username;//
        $_WSDL_URI_reg = "/passport/checkuser/wsdl";
        $client     = new SoapClient($arrConfig['domain'].$_WSDL_URI_reg, $this->soapClientOptions);
        $result = $client->checkuserPassport($parameters);
        return $result;
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

     //register user Passport
     public function registerUserPassport($user, $arrConfig)
     {
        $password = md5( $user->password);
        $parameters ['agent'] = $arrConfig['agent'];//
        $parameters ['key'] = $arrConfig['key'];//
        $parameters ['username'] = $user->username;//
        $parameters ['password'] = $password;//
        $parameters ['email'] = $user->email;
        $parameters ['fullname'] = $user->fullname;
        $parameters ['ip'] = $user->ip;
        $parameters ['sign'] = md5 ( $parameters ['key'] . md5 ( $parameters ['username'] . $parameters ['password'] ) . $arrConfig['secret'] );//
        $_WSDL_URI_reg = "/passport/register/wsdl";
        $client     = new SoapClient($arrConfig['domain'].$_WSDL_URI_reg, $this->soapClientOptions);
        $result = $client->registerPassport($parameters);
        //save log file
        $string = "registerPassport #agent:".$arrConfig['agent']
        ." #username:".$parameters ['username']
        ." #email:".$parameters ['email']
        ." #fullname:".$parameters ['fullname']
        ." #ip:".$parameters ['ip']
        ." #status: ".$result['status'];
        $this->saveLogFile($arrConfig['agent'], $string);
        return $result;
     }

     //register user Passport
     public function registersUserPassport($user, $arrConfig)
     {
        $password = md5( $user->password);
        $parameters ['agent'] = $arrConfig['agent'];//
        $parameters ['key'] = $arrConfig['key'];//
        $parameters ['username'] = $user->username;//
        $parameters ['password'] = $password;//
        $parameters ['email'] = $user->email;
        $parameters ['fullname'] = $user->fullname;
        $parameters ['ip'] = $user->ip;
        $parameters ['birthday'] = $user->birthday;
        $parameters ['sex'] = $user->sex;
        $parameters ['identityNumber'] = $user->identityNumber;
        $parameters ['identityDate'] = $user->identityDate;
        $parameters ['identityPlace'] = $user->identityPlace;
        $parameters ['mobile'] = $user->mobile;
        $parameters ['address'] = $user->address;
        $parameters ['city'] = $user->city;
        $parameters ['company'] = $user->company;
        $parameters ['companyAddress'] = $user->companyAddress;
        $parameters ['sign'] = md5 ( $parameters ['key'] . md5 ( $parameters ['username'] . $parameters ['password'] ) . $arrConfig['secret'] );//
        
        $_WSDL_URI_reg = "/passport/register/wsdl";
        $client     = new SoapClient($arrConfig['domain'].$_WSDL_URI_reg, $this->soapClientOptions);
        $result = $client->registerPassport($parameters);
        //save log file
        $string = "registerPassport #agent:".$arrConfig['agent']
        ." #username:".$parameters ['username']
        ." #email:".$parameters ['email']
        ." #fullname:".$parameters ['fullname']
        ." #ip:".$parameters ['ip']
        ." #birthday:".$parameters ['birthday']
        ." #sex:".$parameters ['sex']
        ." #identityNumber:".$parameters ['identityNumber']
        ." #identityDate:".$parameters ['identityDate']
        ." #identityPlace:".$parameters ['identityPlace']
        ." #mobile:".$parameters ['mobile']
        ." #address:".$parameters ['address']
        ." #city:".$parameters ['city']
        ." #company:".$parameters ['company']
        ." #companyAddress:".$parameters ['companyAddress']
        ." #status: ".$result['status'];
        $this->saveLogFile($arrConfig['agent'], $string);
        return $result;
     }

      //update user Passport
      public function updateUserPassport($user, $arrConfig)
      {
        $parameters ['agent'] = $arrConfig['agent'];//
        $parameters ['key'] = $arrConfig['key'];//
        $parameters ['username'] = $user->username;//
        $parameters ['fullname'] = $user->fullname;
        $parameters ['birthday'] = $user->birthday;//
        $parameters ['sex'] = $user->sex;//
        $parameters ['identityNumber'] = $user->identityNumber;
        $parameters ['identityDate'] = $user->identityDate;
        $parameters ['identityPlace'] = $user->identityPlace;
        $parameters ['mobile'] = $user->mobile;
        $parameters ['address'] = $user->address;
        $parameters ['city'] = $user->city;
        $parameters ['company'] = $user->company;
        $parameters ['companyAddress'] = $user->companyAddress;
        $parameters ['sign'] = md5 ( $parameters ['key'] . md5 ( $parameters ['username'] ) . $arrConfig['secret'] );//
        $_WSDL_URI_reg = "/passport/updateprofile/wsdl";
        $client     = new SoapClient($arrConfig['domain'].$_WSDL_URI_reg, $this->soapClientOptions);
        $result = $client->updateProfilePassport ( $parameters );
        //save log file
        $string = "updateProfilePassport #agent:".$arrConfig['agent']
        ." #username:".$parameters ['username']
        ." #fullname:".$parameters ['fullname']
        ." #birthday:".$parameters ['birthday']
        ." #sex:".$parameters ['sex']
        ." #identityNumber:".$parameters ['identityNumber']
        ." #identityDate:".$parameters ['identityDate']
        ." #identityPlace:".$parameters ['identityPlace']
        ." #mobile:".$parameters ['mobile']
        ." #address:".$parameters ['address']
        ." #city:".$parameters ['city']
        ." #company:".$parameters ['company']
        ." #companyAddress:".$parameters ['companyAddress']
        ." #status: ".$result['status'];
        $this->saveLogFile($arrConfig['agent'], $string);
        return $result;
      }

      //update user email Passport
      public function updateUserWithEmailPassport($user, $arrConfig)
      {
        $parameters ['agent'] = $arrConfig['agent'];//
        $parameters ['key'] = $arrConfig['key'];//
        $parameters ['username'] = $user->username;//
        $parameters ['email'] = $user->email;//
        $parameters ['fullname'] = $user->fullname;
        $parameters ['birthday'] = $user->birthday;//
        $parameters ['sex'] = $user->sex;//
        $parameters ['identityNumber'] = $user->identityNumber;
        $parameters ['mobile'] = $user->mobile;
        $parameters ['address'] = $user->address;
        $parameters ['city'] = $user->city;
        $parameters ['company'] = $user->company;
        $parameters ['companyAddress'] = $user->companyAddress;
        $parameters ['sign'] = md5 ( $parameters ['key'] . md5 ( $parameters ['username'] ) . $arrConfig['secret'] );//
        $_WSDL_URI_reg = "/passport/updateprofile/wsdl";
        $client     = new SoapClient($arrConfig['domain'].$_WSDL_URI_reg, $this->soapClientOptions);
        $result = $client->updateProfilePassport ( $parameters );
        //save log file
        $string = "updateProfilePassport #agent:".$arrConfig['agent']
        ." #username:".$parameters ['username']
        ." #email:".$parameters ['email']
        ." #fullname:".$parameters ['fullname']
        ." #birthday:".$parameters ['birthday']
        ." #sex:".$parameters ['sex']
        ." #identityNumber:".$parameters ['identityNumber']
        ." #identityDate:".$parameters ['identityDate']
        ." #identityPlace:".$parameters ['identityPlace']
        ." #mobile:".$parameters ['mobile']
        ." #address:".$parameters ['address']
        ." #city:".$parameters ['city']
        ." #company:".$parameters ['company']
        ." #companyAddress:".$parameters ['companyAddress']
        ." #status: ".$result['status'];
        $this->saveLogFile($arrConfig['agent'], $string);
        return $result;
      }

     //change password Passport
     public function changePasswordPassport($user, $arrConfig)
     {
        $oldpassword = md5($user['oldpassword']);
        $newpassword = md5($user['newpassword']);
        $parameters ['agent'] = $arrConfig['agent'];//
        $parameters ['key'] = $arrConfig['key'];//
        $parameters ['username'] = $user['username'];//
        $parameters ['oldpassword'] = $oldpassword;//
        $parameters ['newpassword'] = $newpassword;//
        $parameters ['sign'] = md5 ( $parameters ['key'] . md5 ( $parameters ['username'] . $parameters ['oldpassword'] ) . $arrConfig['secret'] );//

        $_WSDL_URI_reg = "/passport/changepassword/wsdl";
        $client     = new SoapClient($arrConfig['domain'].$_WSDL_URI_reg, $this->soapClientOptions);
        $result = $client->changePasswordPassport ( $parameters );
        //save log file
        $string = "changePasswordPassport #agent:".$arrConfig['agent']
        ." #username:".$parameters ['username']
        ." #oldPassword:".$parameters ['oldpassword']
        ." #newPassword:".$parameters ['newpassword']
        ." #status: ".$result['status'];
        $this->saveLogFile($arrConfig['agent'], $string);
        return $result;
     }

     //forgotPasswordByEmailPassport
     public function forgotPasswordByEmailPassport($email, $arrConfig)
     {
         $parameters ['agent'] = $arrConfig['agent'];//
         $parameters ['key'] = $arrConfig['key'];//
         $parameters ['email'] = $email;//
         $parameters ['sign'] = md5 ( $parameters ['key'] . md5 ( $parameters ['email'] ) . $arrConfig['secret'] );//
         //SOAP
         $_WSDL_URI_reg = "/passport/forgotpassword/wsdl";
         $client     = new SoapClient($arrConfig['domain'].$_WSDL_URI_reg, $this->soapClientOptions);
         $result = $client->forgotPasswordPassport($parameters);
         //save log file
        $string = "forgotPasswordPassport #agent:".$arrConfig['agent']
        ." #email:".$parameters ['email']
        ." #status: ".$result['status'];
        $this->saveLogFile($arrConfig['agent'], $string);
        return $result;
     }

      //forgotPasswordPassport
      public function forgotPasswordPassport($username, $arrConfig)
      {
          $parameters ['agent'] = $arrConfig['agent'];//
          $parameters ['key'] = $arrConfig['key'];//
          $parameters ['username'] = $username;//
          $parameters ['sign'] = md5 ( $parameters ['key'] . md5 ( $parameters ['username'] ) . $arrConfig['secret'] );//
          $_WSDL_URI_reg = "/passport/forgotpassword/wsdl";
          $client     = new SoapClient($arrConfig['domain'].$_WSDL_URI_reg, $this->soapClientOptions);
          $result = $client->forgotPasswordPassport($parameters);
          //save log file
            $string = "forgotPasswordPassport #agent:".$arrConfig['agent']
            ." #username:".$parameters ['username']
            ." #status: ".$result['status'];
            $this->saveLogFile($arrConfig['agent'], $string);
          return $result;
      }

      //resetPasswordPassport
      public function resetPasswordPassport($data, $arrConfig)
      {
          $password = md5($data['password']);
          $parameters ['agent'] = $arrConfig['agent'];//
          $parameters ['key'] = $arrConfig['key'];//
          $parameters ['username'] = $data['username'];//
          $parameters ['password'] = $password;
          $parameters ['resetKey'] = $data['secretKey'];//
          $parameters ['sign'] = md5 ( $parameters ['key'] . md5 ( $parameters ['username']. $parameters ['password'] ) . $arrConfig['secret'] );//
          //SOAP
          $_WSDL_URI_reg = "/passport/resetpassword/wsdl";
          $client     = new SoapClient($arrConfig['domain'].$_WSDL_URI_reg, $this->soapClientOptions);
          $result = $client->resetPasswordPassport($parameters);
          //save log file
            $string = "resetPasswordPassport #agent:".$arrConfig['agent']
            ." #username:".$parameters ['username']
            ." #resetKey:".$parameters ['resetKey']
            ." #newPassword:".$parameters ['password']
            ." #status: ".$result['status'];
            $this->saveLogFile($arrConfig['agent'], $string);
          return $result;
      }

     //save log file
     public function saveLogFile($agent = "none", $message, $type = null){
        $format = '%message%';
        $nameLog = date('Y_m_d').".txt";
        $formatter = new \Zend\Log\Formatter\Simple($format);
        $writer = new \Zend\Log\Writer\Stream(dirname(__DIR__).'/../../../../public/logs/user/'.$nameLog);
        $formatter = new \Zend\Log\Formatter\Simple('%message%');
        $writer->setFormatter($formatter);
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($message);
     }
    
 }