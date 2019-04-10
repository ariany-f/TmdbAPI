<?php
/*
 * Gateway Moip
 *
 * @acesso		public
 * @package     Cake.Controller.Component
 * @autor		Anderson Carlos (anderson.carlos@tecnoprog.com.br)
 * @copyright	Copyright (c) 2015, Vida Class (http://www.digi5.com.br)
 * @criado		2018-08-20
 * @versão      1.0
 *
 */

namespace App\Controller\Component;


use Cake\Controller\Component;
use App\Controller\Component\UtilsComponent;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use DateInterval;
use DateTime;

/**
 * Callback component
 */
class CallbackComponent extends Component
{
    /**
     * Default configuration.
     */
    protected $App;
    protected $responseCallback;
    protected $response_header = 0;

    /**
     * Load other component
     * @var array
     */
    public $components = [
        'Order'
    ];

    /**
     * Load tudo do controller
     */
    public function initialize(array $config)
    {
        $this->App = $this->_registry->getController();
    }

    /**
     * @param array $parameters
     */
    private function sendRequest(array $parameters = [
        'company_id' => '',
        'method' => 'POST',
        'endpoint' => '',
        'vars' => 'request'
    ])
    {
        $ambiente = Configure::read('service_mode');
        $url = Configure::read('callback')[$parameters['company_id']][$ambiente]['url'] . '/' . $parameters['endpoint'];
        $params = json_encode($parameters['vars']);

        if($parameters['method'] == 'GET')
        {
            $url .= "?" . http_build_query($parameters['vars']);
            $params = null;
        }

        $header = "Content-Type: application/json\r\n";
        $header .= "Authorization:" . Configure::read('callback')[$parameters['company_id']][$ambiente]['token'];
        $options = array(
            'http' => array(
                'header'  => $header,
                'method'  => $parameters['method'],
                'content' => $params,
                'ignore_errors' => true
            ),
            'ssl' => [
                //'cafile' => "/etc/ssl/certs/ca-certificates.crt",
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        );

        $context  = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        $this->response_header = UtilsComponent::getHttpCode($http_response_header);
        $log_file = "callback_" . $parameters['company_id'] . "_.log";
        UtilsComponent::saveLogFile($log_file, [
            'header' => $header,
            'url' => $url,
            'endpoint' => $parameters['endpoint'],
            'method' => $parameters['method'],
            'params' =>  $params,
            'response_header' => $http_response_header,
            'response' => $result
        ]);

        $result_convert =  json_decode($result, true);

        /**
         * Caso ocorra algum erro na comunicacao
         */
        $error = [
            0 => 'Callback ' . $parameters['company_id'] . ' indisponível, tente novamente'
        ];

        if($result_convert)
        {
            $result = $result_convert;
        }
        else
        {
            $result = $error;
            $this->App->mailDebug([
                'subject' => 'Apis Callback - sendRequest',
                'error' => [
                    'request' => $parameters,
                    'response_header' => $http_response_header,
                    'response' => $result
                ],
            ]);
        }

        $this->responseCallback = $result;
    }

    /**
     * Envia callback para o parceiro
     * @param array $params
     * @return bool
     */
    public function signatures($params = [])
    {
        $output = false;
        $parameters =  [
            'company_id' => $params['order']['company_id'],
            'method' => 'POST',
            'endpoint' => '',
            'vars' => $params
        ];

        $this->sendRequest($parameters);
        $response = $this->responseCallback;

        if($this->response_header == 200)
        {
            $output = true;
        }
        else
        {
            $this->App->mailDebug([
                'subject' => 'Apis Callback - signatures',
                'error' => [
                    'params' => $params,
                    'request' => $parameters,
                    'response' => $response
                ],
            ]);
        }

        return $output;
    }
}