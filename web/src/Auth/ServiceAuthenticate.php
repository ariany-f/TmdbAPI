<?php

/*
 * Autenticar especifico
 *
 * @acesso		public
 * @package		Novo site
 * @autor		Ariany Ferreira (ariany_f@hotmail.com)
 * @criado		2019-04-09
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