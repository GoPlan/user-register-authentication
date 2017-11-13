<?php
/**
 * Created by PhpStorm.
 * User: work
 * Date: 10/11/2017
 * Time: 12:03
 */

namespace CreativeDelta\User\Application\Form;

use Zend\Form\Form;

class SignInForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->add(
            [
               'name' => 'txtUsername',
               'type' => 'Text',
               'options' => [
                   'label' => 'User name:'
               ]
            ]);

        $this->add(
            [
                'name' => 'txtPassword',
                'type' => 'Password',
                'options' => [
                    'label' => 'Password:'
                ]
            ]);

        $this->add(
            [
                'name' => 'ResultMessages',
                'type' => 'Hidden',
            ]);

        $this->add(
            [
                'name' => 'submit',
                'type' => 'submit',
                'options' => [
                    'value' => 'Sign In',
                    'id' => 'btnsubmit',
                ]
            ]
        );
    }
}