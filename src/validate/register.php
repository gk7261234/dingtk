<?php
use Sirius\Validation\RuleFactory;
use Sirius\Validation\ErrorMessage;
use Sirius\Validation\Validator;

class Register
{
function validate($args){
    $ruleFactory = new RuleFactory;
    $errorMessagePrototype = new ErrorMessage;
    $validator = new Validator($ruleFactory, $errorMessagePrototype);
    //添加验证规则
    $validator->add(
        array(
            'name:Name' => 'required',
            'mp_no:MpNo' => 'required | minlength(11) | number',
            'psd:password' => 'required | minlength(8)'
        )
    );
    //工厂自定义模式
//    $validator->add($selector, $name = null, $options = null, $messageTemplate = null, $label = null);
    if ($validator->validate($args)){
        return true;
    }else{
        $message = $validator->getMessages();
        if (gettype($message) == 'array'){
            $getkeys = array_keys($message)[0];
            return (string) $message[$getkeys][0];
        }else{
            return 'data validate is error!';
        }
    }
}
}