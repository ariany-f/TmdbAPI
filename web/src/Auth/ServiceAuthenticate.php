<?php

/*
 * Autenticar especifico
 *
 * @acesso		public
 * @package		Novo site
 * @autor		Anderson Carlos (anderson.carlos@tecnoprog.com.br)
 * @copyright	Copyright (c) 2018, Vida Class (http://www.digi5.com.br)
 * @criado		2018-08-17
 * @versão      1.0
 * @var         $this Component
 * @return      Array
 */

namespace App\Auth;
use Cake\ORM\TableRegistry;
use Cake\Auth\BaseAuthenticate;
use Cake\Network\Request;
use Cake\Network\Response;
use App\Controller\Component\UtilsComponent;
use Cake\Utility\Security;

class ServiceAuthenticate extends BaseAuthenticate
{
    /**
     * @param Request $request
     * @param Response $response
     * @return array|bool
     */
    public function authenticate(Request $request, Response $response)
    {
        $password = $request->getData('username') . $request->getData('password');
        $password = Security::hash($password, 'md5', true);
        
        $users = TableRegistry::getTableLocator()->get('ApisUsers');
        $query = $users
            ->find()
            ->select([
                'ApisUsers.id'
            ])
            ->where([
                'ApisUsers.username' => $request->getData('username'),
                'ApisUsers.password' => $password
            ])
            ->toArray()
        ;
        return current(UtilsComponent::objToArray($query));
    }
}