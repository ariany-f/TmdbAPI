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
            0 => 'Gateway Tmdb indisponível, tente novamente'
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
     * Info referente aos pagamentos da invoice
     * @param int $invoice
     * @return mixed boolean|array
     */
    public function getInvoicePayments($invoice)
    {
        $output = false;
        $parameters =  [
            'type' => 'signatures',
            'method' => 'GET',
            'endpoint' => 'invoices/' . $invoice . '/payments',
            'vars' => []
        ];

        $this->sendRequest($parameters);
        $response = $this->responseTmdb;

        if(isset($response['payments']) and count($response['payments']))
        {
            $output = $response['payments'];
        }

        return $output;
    }

    /**
     * Convert o status da fatura para o primeiro pagamento
     * @param int $invoice
     * @return int
     */
    public function convertInvoiceStatusFirstPay($invoice)
    {
        switch ($invoice)
        {
            case 1:
                $output = 3; // aguardando pagamento
            break;

            case 2:
                $output = 4; // pagamento em análise
                break;

            case 3:
                $output = 1; // pago
                break;

            case 4:
                $output = 2; // nao autorizado
                break;

            case 5:
                $output = 2; // nao autorizado
                break;

            default:
                $output = 3;
        }

        return $output;
    }

    /**
     * Convert o status da fatura para o primeiro pagamento
     * @param int $invoice
     * @return int
     */
    public function convertInvoiceStatus($invoice)
    {
        switch ($invoice)
        {
            case 1:
                $output = 3; // aguardando pagamento
                break;

            case 2:
                $output = 4; // pagamento em análise
                break;

            case 3:
                $output = 1; // pago
                break;

            case 4:
                $output = 2; // nao autorizado
                break;

            case 5:
                $output = 16; // pagamento atrasado
                break;

            default:
                $output = 3;
        }

        return $output;
    }

    /**
     * Convert o status do payment
     * @param int $payment
     * @return int
     */
    public function convertPaymentStatus($payment)
    {
        switch ($payment)
        {
            case 1:
                $output = 1; // pago
                break;

            case 2:
                $output = 4; // pagamento em análise
                break;

            case 3:
                $output = 17; // boleto impresso
                break;

            case 4:
                $output = 1; // pago
                break;

            case 5:
                $output = 2; // nao autorizado
                break;

            case 6:
                $output = 4; // pagamento em análise
                break;

            case 7:
                $output = 11; // estornado
                break;

            case 9:
                $output = 18; // reembolsado
                break;

            case 10:
                $output = 19; // aguardando pagamento do boleto
                break;

            default:
                $output = 3;
        }

        return $output;
    }

    /**
     * Convert pay_regsiter_status para order status
     * @param int $status_code
     * @return int
     */
    public function convertPayRegisterStatusToOrderStatus($status_code)
    {
        switch ($status_code)
        {
            case 1:
                $output = 4; // pago
                break;

            case 2:
                $output = 5; // nao autorizado
                break;

            case 3:
                $output = 2; // aguardando pagamento
                break;

            case 4:
                $output = 3; // aguardando confirmacao pagamento
                break;

            case 11:
                $output = 8; // cancelado
                break;

            case 16:
                $output = 6; // falha na recorrência
                break;

            case 17:
                $output = 3; // aguardando confirmacao pagamento
                break;

            case 18:
                $output = 8; // cancelado
                break;

            case 19:
                $output = 3; // aguardando confirmacao pagamento
                break;

            default:
                $output = 2;
        }

        return $output;
    }

    /**
     * Retorna informacoes de um cliente
     * Devido a estrutura da Tmdb nao permitir mais de um cartao
     * O id do cliente sera o order_id
     * @param int $order_id
     * @return mixed boolean|array
     */
    public function getClientSignatureInfo($order_id)
    {
        $output = false;
        $parameters =  [
            'type' => 'signatures',
            'method' => 'GET',
            'endpoint' => 'customers/' . $order_id,
            'vars' => []
        ];

        $this->sendRequest($parameters);
        $response = $this->responseTmdb;

        if(isset($response['code']))
        {
            $output = $response;
        }

        return $output;
    }

    /**
     * Atualiza o cliente da assinatura
     * @param array $params
     * @return boolean
     */
    public function clientSignatureUpdate($params)
    {
        $parameters = [
            'code' => $params['customer']['order_id'],
            'email' => $params['customer']['mail'],
            'fullname' => $params['customer']['name'],
            'cpf' => $params['customer']['cpf'],
            'phone_number' => substr($params['customer']['phone'], 2, 9),
            'phone_area_code' => substr($params['customer']['phone'], 0, 2),
            'birthdate_day' => substr($params['customer']['birth'], 8, 2),
            'birthdate_month' => substr($params['customer']['birth'], 5, 2),
            'birthdate_year' => substr($params['customer']['birth'], 0, 4),
            'address' => [
                'street' => $params['customer']['address']['public_place'],
                'number' => $params['customer']['address']['number'],
                'complement' => $params['customer']['address']['complement'],
                'district' => $params['customer']['address']['neighborhood'],
                'city' => $params['customer']['address']['city'],
                'state' => $params['customer']['address']['state'],
                'country' => $params['customer']['address']['country'],
                'zipcode' => $params['customer']['address']['zip']
            ]
        ];

        $output = false;
        $parameters =  [
            'type' => 'signatures',
            'method' => 'PUT',
            'endpoint' => 'customers/' . $params['customer']['order_id'],
            'vars' => $parameters
        ];

        $this->sendRequest($parameters);
        $response = $this->responseTmdb;

        if($this->response_header == 200)
        {
            $output = true;
        }
        else
        {
            $this->App->mailDebug([
                'subject' => 'Apis GatewayTmdb - Update customer',
                'error' => [
                    'params' => $params,
                    'request' => $parameters,
                    'response' => $response
                ],
            ]);
        }

        return $output;
    }

    /**
     * Atualiza o cartao do cliente da assinatura
     * @param array $params
     * @return boolean
     */
    public function clientSignatureCreditCardUpdate($params)
    {
        $parameters = [
            'credit_card' => [
                'holder_name' => $params['payment']['credit_card']['name'],
                'number' => $params['payment']['credit_card']['number'],
                'expiration_month' => $params['payment']['credit_card']['expire_month'],
                'expiration_year' => substr($params['payment']['credit_card']['expire_year'], 2, 2)
            ]
        ];

        $output = false;
        $parameters =  [
            'type' => 'signatures',
            'method' => 'PUT',
            'endpoint' => 'customers/' . $params['customer']['code'] . '/billing_infos',
            'vars' => $parameters
        ];

        $this->sendRequest($parameters);
        $response = $this->responseTmdb;

        if($this->response_header == 200)
        {
            /**
             * Salva o cartao ativo para a order
             */
            $credit_card_active_db = TableRegistry::getTableLocator()->get('CliOrderCreditCardActives');
            $credit_card_update = $credit_card_active_db->get($params['credit_card_active']);
            $credit_card_update->gateway_id = $params['gateway_id'];
            $credit_card_update->credit_card_id = $params['payment']['credit_card']['id'];

            if(!$credit_card_active_db->save($credit_card_update))
            {
                $this->App->mailDebug([
                    'subject' => 'Apis GatewayTmdb - Save CliOrderCreditCardActives',
                    'error' => [
                        'request' => $parameters,
                        'response' => $response
                    ],
                ]);
            }

            $output = true;
        }
        else
        {
            $this->App->mailDebug([
                'subject' => 'Apis GatewayTmdb - Change credit card',
                'error' => [
                    'params' => $params,
                    'request' => $parameters,
                    'response' => $response
                ],
            ]);
        }

        return $output;
    }

    /**
     * Atualiza o status da order
     * @param null $order_id
     * @param null $payment_status
     * @return bool
     * @throws \Exception
     */
    public function orderSignatureStatusUpdate($order_id = null, $payment_status = null)
    {
        $status_code = $this->convertPayRegisterStatusToOrderStatus($payment_status);
        $output = $this->Order->updated($order_id, $status_code);

        /**
         * Aviso no mail
         */
        if(!$output)
        {
            $this->App->mailDebug([
                'subject' => 'Apis GatewayTmdbEvent - orderUpdate',
                'error' => [
                    'order_id' => $order_id,
                    'payment_status' => $payment_status
                ]
            ]);
        }

        error:
        return $output;
    }

    /**
     * Tenta um novo pagamento
     * @param array $params
     * @return boolean
     */
    public function retrySignature($params)
    {
        $output = false;
        $parameters =  [
            'type' => 'signatures',
            'method' => 'POST',
            'endpoint' => 'invoices/' . $params['invoice_id'] . '/retry',
            'vars' => []
        ];

        $this->sendRequest($parameters);
        $response = $this->responseTmdb;

        if($this->response_header == 200)
        {
            $output = true;
        }
        else
        {
            $this->App->mailDebug([
                'subject' => 'Apis GatewayTmdb - RetrySignature',
                'error' => [
                    'params' => $params,
                    'request' => $parameters,
                    'response' => $response
                ],
            ]);
        }

        return $output;
    }

    /**
     * Cancela assinatura
     * @param array $params
     * @return boolean
     */
    public function cancelSignature($params)
    {
        $output = false;
        $parameters =  [
            'type' => 'signatures',
            'method' => 'PUT',
            'endpoint' => 'subscriptions/' . $params['order_id'] . '/cancel',
            'vars' => []
        ];

        $this->sendRequest($parameters);
        $response = $this->responseTmdb;

        if($this->response_header == 200)
        {
            $output = true;
        }
        else
        {
            $this->App->mailDebug([
                'subject' => 'Apis GatewayTmdb - cancelSignature',
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