<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Controller;

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT');

use Cake\Core\Configure;
use Cake\Utility\Security;
use Cake\ORM\TableRegistry;

class AuthController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow([
            'index',
            'add'
        ]);
    }


    /**
     * Trata requisicao method GET auth.json
     */
    public function index()
    {
        $this->message = 'Api Tmdb';
        $this->code = 200;
        $this->success = true;
        $this->data = [
            'count' => 1,
            'result' => [
               'Para utilização contate o administrador'
            ],
            'erros' => [

            ]
        ];
        $this->generateOutput();
    }

    /**
     * Trata requisicao method GET auth/{id}.json
     */
    public function view()
    {
        $this->message = 'Api Tmdb';
        $this->code = 200;
        $this->success = true;
        $this->data = [
            'count' => 1,
            'result' => [
                'Para utilização contate o administrador'
            ],
            'erros' => [
                //
            ]
        ];
        $this->generateOutput();
    }

    /**
     * Trata requisicao method POST auth.json
     */
    public function add()
    {
        $post = $this->request->input('json_decode', true);
        if(!$post)
        {
            $this->message = 'Post inválido';
            $this->data = [
                'count' => 0,
                'result' => [
                    //
                ],
                'erros' => [
                    'Verifique os parâmetros necessários para a requisição'
                ]
            ];
            $this->generateOutput();
        }

        if(isset($post['request_id']))
        {
            $this->request_id = $post['request_id'];
        }

        $requestFields = array("username", "password");
        foreach($requestFields as $row)
        {
            $post[$row] = $this->checkFieldRequest($post, $row, true);
        }

        if ($this->request->is('post'))
        {
            $user = $this->Auth->identify();

            if ($user)
            {
                $this->Auth->setUser($user);
                $token = Security::hash($post['username'] . $post['password'] . $this->Auth->user('id') . time(), 'md5', true);
                $expire = date('Y-m-d H:i:s', time() + Configure::read('expire'));

                $users = TableRegistry::get('ApisUsers');
                $query = $users->query();
                $query->update()
                    ->set(['token' => $token])
                    ->set(['expire' => $expire])
                    ->where(['id' => $this->Auth->user('id')])
                    ->execute();

                if ($query)
                {
                    $this->success = true;
                    $this->message = 'Autenticado com sucesso';
                    $this->code = 200;
                    $this->data = [
                        'count' => 1,
                        'result' => [
                            'user_id' => $this->Auth->user('id'),
                            'token' => $token,
                            'expire' => $expire
                        ],
                        'errors' => [
                            //
                        ]
                    ];
                }
                else
                {
                    $this->mailDebug([
                        'subject' => 'Apis Auth - Falha ao criar token',
                        'error' => 'Falha ao criar token',
                    ]);

                    $this->message = 'Falha ao criar token, contate o administrador';
                    $this->data = [
                        'count' => 0,
                        'result' => [],
                        'errors' => ['Falha ao criar token, contate o administrador']
                    ];
                }
            }
            else
            {
                $this->message = 'Usuario ou senha inválido';
                $this->data = [
                    'count' => 0,
                    'result' => [],
                    'errors' => ['Usuario ou senha inválido']
                ];
            }
        }

        $this->generateOutput();
    }

    /**
     * Trata requisicao method PUT ou PATCH auth/{id}.json
     */
    public function edit($user_id)
    {
        $data = current($this->getTokenByUser($user_id));

        if(empty($data)) {
            $data = ['token' => null,'expire' => null,'user_id'=> $user_id];
        }

        $this->success = true;
        $this->message = 'Validade do token';
        $this->data = [
            'count' => 1,
            'result' => $data,
            'errors' => [
                //
            ]
        ];
        $this->code = 200;
        $this->generateOutput();
    }

    /**
     * Trata requisicao method DELETE auth/{id}.json
     */
    public function delete()
    {
        if(!$this->checkToken())
        {
            $this->success = false;
            $this->message = 'Token inválido';
            $this->data = [
                'count' => 0,
                'result' => [],
                'errors' => [
                    0 => 'Token inválido'
                ]
            ];
            $this->code = 200;
            $this->generateOutput();
        }
        else
        {
            $users = TableRegistry::get('ApisUsers');
            $query = $users->query();
            $query->update()
                ->set(['token' => null])
                ->set(['expire' => null])
                ->where([
                    'token' => $this->Auth->user('token')
                ])
                ->execute()
            ;
        }

        $this->success = true;
        $this->message = 'Logout efetuado com sucesso';
        $this->data = [
            'count' => 1,
            'result' => ['Sessão encerrada'],
            'errors' => [
                //
            ]

        ];
        $this->code = 200;
        $this->generateOutput();
    }
}