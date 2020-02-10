<?php
namespace Admin\Form;
 
use Zend\Form\Annotation;
 
/**
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("User")
 */
class LoginForm
{
    /**
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true" })
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Attributes({"class":"form-control"})
     * @Annotation\Attributes({"id":"input-email"})
     * @Annotation\Attributes({"placeholder":"Username"})
     */
    public $username;
     
    /**
     * @Annotation\Type("Zend\Form\Element\Password")
     * @Annotation\Required({"required":"true" })
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Attributes({"class":"form-control"})
     * @Annotation\Attributes({"id":"input-email"})
     * @Annotation\Attributes({"placeholder":"Password"})
     */
    public $password;
     
    /**
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Attributes({"label":""})
     */
    public $rememberme;
     
    /**
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"Đăng nhập"})
     * @Annotation\Attributes({"class":"btn btn-lg btn-primary btn-block"})
     */
    public $submit;
}
