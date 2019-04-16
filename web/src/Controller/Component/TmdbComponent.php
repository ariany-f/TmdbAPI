<?php
/*
 * The Movies DB
 *
 * @acesso		public
 * @package     Cake.Controller.Component
 * @autor		Ariany Ferreira (ariany_f@hotmail.com)
 * @criado		2019-04-09
 * @versão      1.0
 *
 */

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;

/**
 * Tmdb component
 */
class TmdbComponent extends Component
{
    /**
     * Default configuration.
     */
    protected $App;
    protected $responseTmdb = [];
    protected $response_header = 0;

    /**
     * Load other component
     * @var array
     */
    public $components = [
        'Order',
        'Police'
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
        'method' => 'GET',
        'endpoint' => 'movie/upcoming',
        'vars' => 'page=1'
    ])
    {
        $ambiente = Configure::read('service_mode');
        $url = Configure::read('webservices')['tmdb'][$ambiente]['url'] . '/' . $parameters['endpoint'].'?api_key='.Configure::read('webservices')['tmdb'][$ambiente]['api_key'];
        $params = [];

        if($parameters['method'] == 'GET')
        {
            $url .= "&" . http_build_query($parameters['vars']);
            $params = null;
        }

        $header = "Content-Type: application/json\r\n";
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

        UtilsComponent::saveLogFile("requestTmdb.log", [
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
         * A Tmdb esta sem retorno de json em alguns casos
         */
        switch ($this->response_header)
        {
            case 404:
                if(!$result_convert)
                {
                    $result_convert = [
                        false
                    ];
                }
                break;

            case 200:
                if(!$result_convert)
                {
                    $result_convert = [
                        true
                    ];
                }
                break;
        }

        /**
         * Caso ocorra algum erro na comunicacao
         */
        $error = [
            0 => 'Tmdb indisponível, tente novamente'
        ];


        if($result_convert)
        {
            $result = $result_convert;
        }
        else
        {
            $result = $error;

            $this->App->mailDebug([
                'subject' => 'Api Tmdb - sendRequest',
                'error' => [
                    'request' => $parameters,
                    'response_header' => $http_response_header,
                    'response' => $result
                ],
            ]);
        }

        $this->responseTmdb = $result;
    }

    /**
     * Cria a assinatura
     * @return array
     * @throws \Exception
     */
    public function getUpcoming($page = 1)
    {
        $output = [
            'data' => [],
            'error' => [
                12 => 'Serviço com falha, contate o administrador'
            ]
        ];

        /**
         * Converte params para metodo Tmdb
         */
        $parameters =  [
            'method' => 'GET',
            'endpoint' => 'movie/upcoming',
            'vars' => [
                'page' => $page
            ]
        ];

        $this->sendRequest($parameters);
        $response = $this->responseTmdb;

        return $response;
    }

     /**
     * Pegar todos os gêneros disponíveis
     * @return array
     * @throws \Exception
     */
    public function getGenres()
    {
        $output = [
            'data' => [],
            'error' => [
                12 => 'Serviço com falha, contate o administrador'
            ]
        ];

        /**
         * Converte params para metodo Tmdb
         */
        $parameters =  [
            'method' => 'GET',
            'endpoint' => 'genre/movie/list'
        ];

        $this->sendRequest($parameters);
        $response = $this->responseTmdb;

        return $response;
    }

    /**
     * Retorna filmes encontrados
     * O parametro a pesquisar será o $query
     * @param String $query
     * @return mixed boolean|array
     */
    public function search($query)
    {
        $output = [
            'data' => [],
            'error' => [
                12 => 'Serviço com falha, contate o administrador'
            ]
        ];
        
        $parameters =  [
            'method' => 'GET',
            'endpoint' => 'search/movie',
            'vars' => [
                'query' => $query
            ]
        ];

        $this->sendRequest($parameters);
        $response = $this->responseTmdb;

        return $response;
    }

    /**
     * Retorna detalhes do filme
     * O parametro a pesquisar será o $id
     * @param String $id
     * @return mixed boolean|array
     */
    public function detail($id)
    {
        $output = [
            'data' => [],
            'error' => [
                12 => 'Serviço com falha, contate o administrador'
            ]
        ];
        
        $parameters =  [
            'method' => 'GET',
            'endpoint' => 'movie/'.$id,
            'vars' => []
        ];

        $this->sendRequest($parameters);
        $response = $this->responseTmdb;

        return $response;
    }
}