<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use App\Controller\Component\UtilsComponent;
use App\Common\Mailer;
use Cake\Core\Configure;
use DateTime;


/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Recebe valor das mensagens
     * @var
     */
    public $message = 'Área exige login';

    /**
     * Recebe um hash caso o cliente envie
     * @var null
     */
    public $request_id = null;

    /**
     * Codigo do erro
     * @var int
     */
    public $code = 401;

    /**
     * Status da acao
     * @var bool
     */
    public $success = false;

    /**
     * @var string
     */
    public $data = [
        'count' => 0,
        'result' => [],
        'errors' => [
            0 => 'Acesso não autorizado'
        ]
    ];

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     * @throws \Exception
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler',[
            'enableBeforeRedirect' => false
        ]);
        $this->loadComponent('Flash');

        /*
         * Enable the following component for recommended CakePHP security settings.
         * see https://book.cakephp.org/3.0/en/controllers/components/security.html
         */
        //$this->loadComponent('Security');

        /**
         * Da aplicacao
         */
        $this->loadComponent('Utils');
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Auth', [
            'loginAction' => [
                'controller' => 'Auth',
                'action' => 'index'
            ],
            'authenticate' => [
                'Service'
            ]
        ]);
    }

    /**
     * @param Event $event
     * @return \Cake\Network\Response|null|void
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        if ($this->request->is('options')) {
            $this->setCorsHeaders();
        }
        
        if(in_array($this->request->getParam('action'), $this->Auth->allowedActions))
        {
            return;
        }
        else if ($this->getToken() && !$this->checkToken())
        {
            $this->Auth->logout();
            $this->generateOutput();
        }
    }
    
    private function setCorsHeaders() {
        $this->response->cors($this->request)
            ->allowOrigin(['*'])
            ->allowMethods(['*'])
            ->allowHeaders(['x-xsrf-token', 'Origin', 'Content-Type', 'X-Auth-Token'])
            ->allowCredentials(['true'])
            ->exposeHeaders(['Link'])
            ->maxAge(300)
            ->build();
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return void
     */
    public function beforeRender(Event $event)
    {
        $this->setCorsHeaders();
        
        $this->request_id = md5(date('YmdHis') . $this->Auth->user('id'));

        if (!array_key_exists('_serialize', $this->viewVars) && in_array($this->response->getType(), ['application/json', 'application/xml']))
        {
            $this->set('_serialize', true);
        }
    }

    /**
     * Pega o token enviado pelo header
     * @return bool
     */
    public function getToken()
    {
        $headers = $this->getHeaders();
        if (!isset($headers['Authorization']))
        {
            return false;
        }
        $token = explode(" ", $headers['Authorization']);
        return (isset($token[1])) ? UtilsComponent::escape($token[1]) : null;
    }

    /**
     * Pega todos os reades eviados
     * @return array|false
     */
    private function getHeaders()
    {
        $headers = getallheaders();
        return $headers;
    }

    /**
     * Compara o token enviado com o token da sessao do usuario
     * @return bool
     */
    public function checkToken()
    {
        /**
         * Token no header
         */
        if (!$this->getToken())
        {
            $this->mailDebug([
                'subject' => 'Apis Auth - Token não definido',
                'error' => 'Token não definido'
            ]);

            $this->message = "Token não definido!";
            return false;
        }

        /**
         * Localizar no banco
         */
        $users = TableRegistry::getTableLocator()->get('ApisUsers');
        $query = $users
            ->find()
            ->select([
                'ApisUsers.id',
                'ApisUsers.username',
                'ApisUsers.token',
                'ApisUsers.expire',
                'ApisUsers.status_code'
            ])
            ->where([
                'ApisUsers.token' => $this->getToken()
            ])
            ->limit(1)
            ->toArray()
        ;

        /**
         * Converte dados em array
         */
        $result = UtilsComponent::objToArray($query);

        /**
         * Nao encontrou o token
         */
        if (!isset($result[0]['expire']))
        {
            $this->mailDebug([
                'subject' => 'Apis Auth - Token inválido',
                'error' => 'Token inválido'
            ]);

            $this->message = "Token inválido";
            return false;
        }

        /**
         * Token expirado
         */
        if (strtotime($result[0]['expire']) < time())
        {
            $this->mailDebug([
                'subject' => 'Apis Auth - Token expirado',
                'error' => 'Token expirado'
            ]);

            $this->message = "Token expirado";
            return false;
        }

        $user = [
            'id' => $result[0]['id'],
            'username' => $result[0]['username'],
            'token' => $result[0]['token'],
            'expire' => $result[0]['expire'],
            'status_code' => $result[0]['status_code']
        ];

        /**
         * User autenticado
         */
        $this->message = "Usuario autenticado";
        $this->Auth->setUser($user);
        return true;
    }

    /**
     * Busca se há token ativo pelo id do usuário
     * @return array
     */
    public function getTokenByUser($user_id)
    {
        /**
         * Procura no banco pelo token
         */
        $users = TableRegistry::getTableLocator()->get('ApisUsers');

        $query = $users
            ->find()
            ->select([
                'ApisUsers.token',
                'ApisUsers.expire',
                'user_id' => 'ApisUsers.id'
            ])
            ->where([
                'ApisUsers.id' => $user_id,
                'ApisUsers.expire > ' => new DateTime('now')
            ])
            ->limit(1)
            ->toArray()
        ;

        /**
         * Converte dados em array
         */
        return UtilsComponent::objToArray($query);
    }

    /**
     * @param $request
     * @param $field
     * @param bool $nulo
     * @param string $type
     * @param int $size
     * @return array|mixed
     */
    public function checkFieldRequest($request, $field, $nulo = FALSE, $type = '', $size = 6, $rules = 1)
    {
        if (!isset($request[$field]))
        {
            $this->message = 'Requisição inválida, contate o administrador';
            $this->data = [
                'count' => 0,
                'result' => [
                    //
                ],
                'errors' => [
                    'Falta o parâmetro ' . $field
                ]
            ];
            $this->code = 400;
            $this->generateOutput();
        }

        /**
         * Trata vazio
         */
        if (!is_array($request[$field]) and $type != 'boolean')
        {
            $var = strlen(trim($request[$field]));
            if (empty($var) AND ($nulo))
            {
                $this->message = 'Campo vazio, contate o administrador';
                $this->data = [
                    'count' => 0,
                    'result' => [
                        //
                    ],
                    'errors' => [
                        'O parâmetro ' . $field . ' não pode ser vazio'
                    ]
                ];
                $this->code = 400;
                $this->generateOutput();
            }
        }

        /**
         * Para retorno e checagem
         */
        $var = (is_string($request[$field])) ? trim($request[$field]) : $request[$field];

        /**
         * Validacoes de tipo
         */
        switch ($type)
        {
            case 'date':
                DateTime::createFromFormat('Y-m-d', $var);
                $check = DateTime::getLastErrors()['warning_count'] + DateTime::getLastErrors()['error_count'];
                if ($check)
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            'O valor ' . $var . ', do parâmetro ' . $field . ' não segue o formato yyyy-mm-dd ou não é uma data válida'
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;

            case 'datetime':
                    DateTime::createFromFormat('Y-m-d H:i:s', $var);
                    if (DateTime::getLastErrors()['warning_count'] OR DateTime::getLastErrors()['error_count'])
                    {
                        $this->message = "Requisição inválida, contate o administrador";
                        $this->data = [
                            'count' => 0,
                            'result' => [
                                //
                            ],
                            'errors' => [
                                'O valor ' . $var . ', do parâmetro ' . $field . ' não segue o formato yyyy-mm-dd hh:mm:ss ou não é um datetime válido'
                            ]
                        ];
                        $this->code = 400;
                        $this->generateOutput();
                    }
                break;
            case 'mail':
                if (!UtilsComponent::isMail($var))
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            'O valor ' . $var . ', do parâmetro ' . $field . ' não é um e-mail válido'
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;
            case 'phone':
                    if(!UtilsComponent::isPhone(UtilsComponent::clearString($var)))
                    {
                        $this->message = "Requisição inválida, contate o administrador";
                        $this->data = [
                            'count' => 0,
                            'result' => [
                                //
                            ],
                            'errors' => [
                                'O valor ' . $var . ', do parâmetro ' . $field . ' não parece ser um telefone válido, não segue o formato (xx) x xxxx-xxxx / (xx) xxxx-xxxx'
                            ]
                        ];
                        $this->code = 400;
                        $this->generateOutput();
                    }
                break;
            case 'numeric':
                    if(!UtilsComponent::isNumeric($var))
                    {
                        $this->message = "Requisição inválida, contate o administrador";
                        $this->data = [
                            'count' => 0,
                            'result' => [
                                //
                            ],
                            'errors' => [
                                'O valor ' . $var . ', do parâmetro ' . $field . ' não é um número'
                            ]
                        ];
                        $this->code = 400;
                        $this->generateOutput();
                    }
                break;
            case 'cpf':
                if(!UtilsComponent::isCpf($var))
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            'O valor ' . $var . ', do parâmetro ' . $field . ' não é um CPF válido'
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;
            case 'document':
                if((is_null($var) or empty($var) or strlen($var) < 5))
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            'O valor ' . $var . ', do parâmetro ' . $field . ' não é um documento válido'
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;
            case 'emitter':
                if(!is_int($var) or !UtilsComponent::documentEmitter($var))
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            'O valor ' . $var . ', do parâmetro ' . $field . ' não é um emissor de documento válido'
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;
            case 'array':
                if(!is_array($var))
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            'O valor ' . $var . ', do parâmetro ' . $field . ' não é um array'
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;
            case 'password':
                $check = UtilsComponent::passwordRules($var, $size, $rules);
                if(!$check['status'])
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            "O valor do parâmetro " . $field . " deve seguir essas regras: \n" .  $check['description']
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;
            case 'integer':
                if(!UtilsComponent::isInteger($var))
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            'O valor do parâmetro ' . $field . ' não é um número inteiro'
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;
            case 'url':
                    if(!UtilsComponent::isUrl($var))
                    {
                        $this->message = "Requisição inválida, contate o administrador";
                        $this->data = [
                            'count' => 0,
                            'result' => [
                                //
                            ],
                            'errors' => [
                                'O valor do parâmetro ' . $field . ' não é uma url válida, verifique se a url está correta'
                            ]
                        ];
                        $this->code = 400;
                        $this->generateOutput();
                    }
                break;
            case 'img':
                if(!UtilsComponent::isImage($var))
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            'O valor ' . $var . ', do parâmetro ' . $field . ' não é uma url válida, verifique se a url está correta'
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;
            case 'gender':
                if(!in_array($var, ['F', 'M']))
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            'O valor ' . $var . ', do parâmetro ' . $field . ' não é válido, são aceitos [F, M]'
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;
            case 'boolean':
                if(!is_bool($var))
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            'O valor ' . $var . ', do parâmetro ' . $field . ' não é um booleano'
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;
            case 'occupation':
                if(!is_int($var) or !UtilsComponent::checkListOccupation($var))
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            'O valor ' . $var . ', do parâmetro ' . $field . ' não é uma ocupação válida'
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;
            case 'company':
                if(!is_int($var) or !UtilsComponent::checkListOccupation($var))
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            'O valor ' . $var . ', do parâmetro ' . $field . ' não é uma companhia válida'
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;
            case 'product':
                if(!is_int($var) or !UtilsComponent::checkListProduct($var))
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            'O valor ' . $var . ', do parâmetro ' . $field . ' não é um produto válido'
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;

            case 'marital':
                if(!is_int($var) or !UtilsComponent::checkListMarital($var))
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            'O valor ' . $var . ', do parâmetro ' . $field . ' não é um estado civil válido'
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;

            case 'month':
                $month = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
                if(!in_array($var, $month) or strlen($var) < 2)
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            'O valor ' . $var . ', do parâmetro ' . $field . ' não é válido, são aceitos meses com 2 digitos numéricos'
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;

            case 'year':
                if(!(is_numeric($var)) or !(strlen($var) == 4) or !($var >= 1900))
                {
                    $this->message = "Requisição inválida, contate o administrador";
                    $this->data = [
                        'count' => 0,
                        'result' => [
                            //
                        ],
                        'errors' => [
                            'O valor ' . $var . ', do parâmetro ' . $field . ' não é válido, são aceitos anos com 4 digitos numéricos maior que 1900'
                        ]
                    ];
                    $this->code = 400;
                    $this->generateOutput();
                }
                break;
        }

        return UtilsComponent::escape($var);
    }

    /**
     * Metodo de requeste não aceito
     */
    public function methodNotPermitted()
    {
        $this->mailDebug([
            'subject' => 'Apis Method - Metodo de request não permitido',
            'error' => 'Metodo de request não permitido'
        ]);

        $this->message = 'Metodo de request não permitido';
        $this->code = 405;
        $this->success = false;
        $this->data = [
            'count' => 0,
            'result' => [],
            'errors' => [
                0 => 'O metodo ' . $this->request->getMethod() . ' não é aceito neste end point'
            ]
        ];
        $this->generateOutput();
    }

    /**
     * End Point inexistente
     */
    public function endPointActionNotExists($action)
    {
        $this->mailDebug([
            'subject' => 'Api Action - Action não existe neste endpoint',
            'error' => 'Action não existe neste endpoint'
        ]);

        $this->message = 'Action não existe neste endpoint';
        $this->code = 405;
        $this->success = false;
        $this->data = [
            'count' => 0,
            'result' => [],
            'errors' => [
                0 => 'Action ' . $action . ' não existe no endpoint ' . $this->request->getParam('action')
            ]
        ];
        $this->generateOutput();
    }

    /**
     * Send mails de Debug
     */
    public function mailDebug($params)
    {
        $details = [
            'endpoint' => 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'],
            'client' => [
                'username' => $this->Auth->user('username')
            ],
            'json' => $this->request->input('json_decode', true),
            'headers' => getallheaders(),
            'post' => $_POST,
            'get' => $_GET,
            'server' => UtilsComponent::server()
        ];

        $params = array_merge($params, $details);

        $opts = [
            'to' => Configure::read('mails.debug'),
            'subject' => $params['subject'],
            'vars' => [
                'vars' => $params
            ]
        ];

        $mailer = new Mailer;
        $mailer->setOpts($opts)->send();
    }

    /**
     * Para saidas de erros nas validacoes
     * @param $params
     */
    public function errorSimpleOutput($params)
    {
        $this->message = $params['message'];
        $this->data = [
            'count' => 0,
            'result' => [
                //
            ],
            'errors' => [
                0 => $params['error']
            ]
        ];
        $this->code = 400;
        $this->generateOutput();
    }

    /**
     * Gera a saida json
     */
    public function generateOutput()
    {
        if(is_null($this->request_id))
        {
            $this->request_id = md5(date('YmdHis') . $this->Auth->user('id'));
        }

        $output = array(
            'request_id' => $this->request_id,
            'message' => $this->message,
            'code' => $this->code,
            'success' => $this->success,
            'data' => $this->data
        );
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: *');
        header('Accept: application/json');
        http_response_code($this->code);
        echo json_encode($output);
        die;
    }
}