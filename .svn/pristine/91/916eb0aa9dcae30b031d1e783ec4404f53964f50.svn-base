<?php
namespace App\Controller;

use Cake\Core\Configure;

/**
 * Gateway Controller
 *
 *
 */
class GatewayController extends AppController
{
    /**
     * Do Cake
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow([
            // Nenhum
        ]);
    }

    /**
     * Seleciona o Gateway ativo
     * direciona e cria assinatura
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function createSignature($params = [
        'order_id' => '',
        'total' => '',
        'itens' => [
            [
                'brand_id' => '',
                'brand_name' => '',
                'type_id' => '',
                'type_name' => '',
                'product_id' => '',
                'name' => '',
                'qtd' => '',
                'value_uni' => ''
            ]
        ],
        'customer' => [
            'id' => '',
            'name' => '',
            'mail' =>  '',
            'cpf' => '',
            'phone' => '',
            'birth' => '',
            'address' => [
                'address_ref_id' => '',
                'address_type_id' => '',
                'public_place' => '',
                'number' => '',
                'complement' => '',
                'neighborhood' => '',
                'city' => '',
                'state' => '',
                'country' => '',
                'zip' => '',
                'status_code' => ''
            ]
        ],
        'payment' => [
            'credit_card' => [
                'id' => '',
                'card_brand_id' => '',
                'card_brand_name' => '',
                'name' => '',
                'number' => '',
                'expire_month' => '',
                'expire_year' => '',
                'secure_code' => '',
                'status_code' => ''
            ]
        ]
    ])
    {
        switch (Configure::read('gateway_default'))
        {
            /**
             * Moip
             */
            case 2:
                $this->loadComponent('GatewayMoip');
                $params['gateway_id'] = 2;
                $output = $this->GatewayMoip->createSignature($params);
            break;

            default:
                $output = [
                    'data' => [],
                    'error' => [
                        11 => 'Gateway de pagamento indisponível, contate o administrador'
                    ]
                ];
        }

        return $output;
    }

    /**
     * Seleciona o Gateway ativo
     * direciona e troca o cartao
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function changeCreditCardSignature($params = [
        'order_id' => '',
        'client_id' => '',
        'credit_card_active' => '',
        'gateway_id' => '',
        'payment' => [
            'credit_card' => [
                'id' => '',
                'card_brand_id' => '',
                'card_brand_name' => '',
                'name' => '',
                'number' => '',
                'expire_month' => '',
                'expire_year' => '',
                'secure_code' => '',
                'status_code' => ''
            ]
        ]
    ])
    {
        switch ($params['gateway_id'])
        {
            /**
             * Moip
             */
            case 2:
                $this->loadComponent('GatewayMoip');

                /**
                 * Ajuste para a moip onde o id do cliente
                 * foi utilizado o id da order, assim podemos ter
                 * varios cartoes
                 */
                $params['customer']['code'] = $params['order_id'];

                $response = $this->GatewayMoip->clientSignatureCreditCardUpdate($params);
                $output = [
                    'data' => $response,
                    'error' => []
                ];
                break;

            default:
                $output = [
                    'data' => [],
                    'error' => [
                        11 => 'Gateway de pagamento indisponível, contate o administrador'
                    ]
                ];
        }

        return $output;
    }

    /**
     * Seleciona o Gateway ativo
     * direciona e tenta fazer um novo pagamento
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function retrySignature($params = [
        'order_id' => '',
        'gateway_id' => '',
        'invoice_id' => ''
    ])
    {
        switch ($params['gateway_id'])
        {
            /**
             * Moip
             */
            case 2:
                $this->loadComponent('GatewayMoip');

                /**
                 * Ajuste para a moip onde o id do cliente
                 * foi utilizado o id da order, assim podemos ter
                 * varios cartoes
                 */
                $params['customer']['code'] = $params['order_id'];

                $response = $this->GatewayMoip->retrySignature($params);
                $output = [
                    'data' => $response,
                    'error' => []
                ];
                break;

            default:
                $output = [
                    'data' => [],
                    'error' => [
                        11 => 'Gateway de pagamento indisponível, contate o administrador'
                    ]
                ];
        }

        return $output;
    }

    /**
     * Seleciona o Gateway ativo
     * direciona e tenta cancelar a assinatura
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function cancelSignature($params = [
        'order_id' => '',
        'gateway_id' => ''
    ])
    {
        switch ($params['gateway_id'])
        {
            /**
             * Moip
             */
            case 2:
                $this->loadComponent('GatewayMoip');

                /**
                 * Ajuste para a moip onde o id do cliente
                 * foi utilizado o id da order, assim podemos ter
                 * varios cartoes
                 */
                $params['customer']['code'] = $params['order_id'];

                $response = $this->GatewayMoip->cancelSignature($params);
                $output = [
                    'data' => $response,
                    'error' => []
                ];
                break;

            default:
                $output = [
                    'data' => [],
                    'error' => [
                        11 => 'Gateway de pagamento indisponível, contate o administrador'
                    ]
                ];
        }

        return $output;
    }
}