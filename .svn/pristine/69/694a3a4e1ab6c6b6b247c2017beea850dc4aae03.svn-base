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
 * GatewayMoipEvent component
 */
class GatewayMoipEventComponent extends Component
{
    /**
     * Default configuration.
     */
    protected $App;

    /**
     * Load other component
     * @var array
     */
    public $components = [
        'Order',
        'GatewayMoip',
        'Callback',
        'PayRegister'
    ];

    /**
     * Load tudo do controller
     * @param array $config
     */
    public function initialize(array $config)
    {
        $this->App = $this->_registry->getController();
    }

    /**
     * Trata o callback de assinaturas
     * @param array $post
     * @return array
     */
    public function signatures($post = [])
    {
        /**
         * Check event
         */
        if(!isset($post['event']))
        {
            $data = [];
            $error = [
                0 => 'Event não está presente no request'
            ];
            goto error;
        }

        /**
         * Dados do event invalido
         */
        $event = explode('.', $post['event']);
        if(!(isset($event[0]) and isset($event[1])))
        {
            $data = [];
            $error = [
                0 => 'Event invalido'
            ];
            goto error;
        }

        /**
         * Acao por evento
         */
        switch ($event[0])
        {
            case 'invoice':
                switch ($event[1])
                {
                    /**
                     * Novo invoice enviado pela moip
                     * Verifica e cria invoice
                     */
                    case 'created':
                        invoice_created:
                        $order_id = (isset($post['resource']['subscription_code'])) ? $post['resource']['subscription_code'] : false;
                        $invoice_id = (isset($post['resource']['id'])) ? $post['resource']['id'] : false;
                        $invoice_status_id = (isset($post['resource']['status']['code'])) ? $post['resource']['status']['code'] : false;
                        $value = (isset($post['resource']['amount'])) ? $post['resource']['amount'] / 100 : false;

                        /**
                         * Verificar valores
                         */
                        if($order_id === false or $invoice_id === false or $invoice_status_id === false or $value === false)
                        {
                            $data = [];
                            $error = [
                                0 => 'Onvoice created com valor inválido'
                            ];
                            goto error;
                        }

                        /**
                         * Localizar fatura no banco
                         * Se existir aborta a missao
                         */
                        $pay_info = $this->PayRegister->payInfo(null, null, $invoice_id, null);
                        if($pay_info)
                        {
                            $data = [];
                            $error = [
                                0 => 'Invoice ' . $invoice_id . ' já existe'
                            ];
                            goto error;
                        }

                        $order = $this->Order->orderView($order_id);
                        if(!isset($order['data']['credit_card']))
                        {
                            $data = [];
                            $error = [
                                0 => 'Order não encontrada ou sem cartão definido'
                            ];
                            goto error;
                        }
                        $order = current($order);

                        /**
                         * Dados inicias para o registro de pagamento
                         */
                        $invoice_payment_id = null;
                        $invoice_payment_status_id = null;
                        $payment_status = $this->GatewayMoip->convertInvoiceStatus($invoice_status_id);

                        /**
                         * Tem registro de pagamento
                         */
                        $payments = $this->GatewayMoip->getInvoicePayments($invoice_id);
                        if(isset($payments[0]))
                        {
                            $payments = current($payments);
                            $invoice_payment_id = $payments['id'];
                            $invoice_payment_status_id = $payments['status']['code'];
                            $payment_status = $this->GatewayMoip->convertPaymentStatus($invoice_payment_status_id);
                        }

                        /**
                         * Criando a nova fatura
                         */
                        $pay_registers_db = TableRegistry::getTableLocator()->get('PayRegisters');
                        $pay_data = $pay_registers_db->newEntity([
                            'order_id' => $order_id,
                            'credit_card_id' => $order['credit_card']['id'],
                            'gateway_id' => 2,
                            'method_id' => 2,
                            'value' => $value,
                            'acquirer_code' => $order_id,
                            'order_code' => $invoice_id,
                            'payment_code' => $invoice_payment_id,
                            'status_code' => $payment_status,
                            'paid' => ($payment_status == 1) ? 1 : 0
                        ]);

                        if(!$pay_registers_db->save($pay_data))
                        {
                            $this->App->mailDebug([
                                'subject' => 'Apis GatewayMoipEvent - Save invoice.created',
                                'error' => [
                                    'request' => $post
                                ]
                            ]);

                            $data = [];
                            $error = [
                                0 => 'Não foi possível salvar o pagamento'
                            ];
                            goto error;
                        }

                        /**
                         * Atualiza a order
                         */
                        $this->GatewayMoip->orderSignatureStatusUpdate($order_id, $payment_status);

                        $data = 'Fatura criada com sucesso';
                        $error = [];

                        /**
                         * Executa o callback
                         */
                        $order = $this->Order->OrderView($order_id);
                        $params = [
                            'event' => 'invoice.created',
                            'order' => $order['data']
                        ];
                        $this->Callback->signatures($params);
                    break;

                    /**
                     * Atualiza o status do invoice
                     * Caso nao exista retorna erro
                     */
                    case 'status_updated':
                        $invoice_id = (isset($post['resource']['id'])) ? $post['resource']['id'] : false;
                        $invoice_status_id = (isset($post['resource']['status']['code'])) ? $post['resource']['status']['code'] : false;

                        /**
                         * Verificar valores
                         */
                        if($invoice_id === false or $invoice_status_id === false)
                        {
                            $data = [];
                            $error = [
                                0 => 'Invoice status_updated com valor inválido'
                            ];
                            goto error;
                        }

                        /**
                         * Localizar fatura no banco
                         */
                        $pay_info = $this->PayRegister->payInfo(null, null, $invoice_id, null);
                        if($pay_info === false)
                        {
                            goto invoice_created;
                        }
                        $payment = $pay_info[$invoice_id][0];

                        /**
                         * Dados inicias para o registro de pagamento
                         */
                        $invoice_payment_id = null;
                        $invoice_payment_status_id = null;
                        $payment_status = $this->GatewayMoip->convertInvoiceStatus($invoice_status_id);

                        /**
                         * Tem registro de pagamento
                         */
                        $payments = $this->GatewayMoip->getInvoicePayments($invoice_id);
                        if(isset($payments[0]))
                        {
                            $payments = current($payments);
                            $invoice_payment_id = $payments['id'];
                            $invoice_payment_status_id = $payments['status']['code'];
                            $payment_status = $this->GatewayMoip->convertPaymentStatus($invoice_payment_status_id);
                        }

                        /**
                         * Update fatura
                         */
                        $pay_registers_db = TableRegistry::getTableLocator()->get('PayRegisters');
                        $register = $pay_registers_db->get($payment['id']);
                        $register->status_code = $payment_status;
                        $register->paid = ($payment_status == 1) ? 1 : 0;
                        if(!$pay_registers_db->save($register))
                        {
                            $this->App->mailDebug([
                                'subject' => 'Apis GatewayMoipEvent - Save invoice status_updated',
                                'error' => [
                                    'request' => $post
                                ]
                            ]);

                            $data = [];
                            $error = [
                                0 => 'Não foi possível atualizar o pagamento'
                            ];
                            goto error;
                        }

                        /**
                         * Atualiza a order
                         */
                        $this->GatewayMoip->orderSignatureStatusUpdate($payment['order_id'], $payment_status);

                        $data = 'Fatura atualizada com sucesso';
                        $error = [];

                        /**
                         * Executa o callback
                         */
                        $order = $this->Order->OrderView($payment['order_id']);
                        $params = [
                            'event' => 'invoice.updated',
                            'order' => $order['data']
                        ];
                        $this->Callback->signatures($params);
                    break;

                    default:
                        $data = [];
                        $error = [
                            0 => 'Invoice com acao não definida (' . $event[1] . ')'
                        ];
                        goto error;
                }
            break;

            case 'payment':
                switch ($event[1])
                {
                    /**
                     * Novo pagamento enviado pela moip
                     * Verifica e cria pagamento
                     */
                    case 'created':
                        $order_id = (isset($post['resource']['subscription_code'])) ? $post['resource']['subscription_code'] : false;
                        $invoice_id = (isset($post['resource']['invoice_id'])) ? $post['resource']['invoice_id'] : false;
                        $invoice_payment_id = (isset($post['resource']['id'])) ? $post['resource']['id'] : false;
                        $invoice_payment_status_id = (isset($post['resource']['status']['code'])) ? $post['resource']['status']['code'] : false;
                        $value = (isset($post['resource']['amount'])) ? $post['resource']['amount'] / 100 : false;

                        /**
                         * Verificar valores
                         */
                        if($order_id === false or $invoice_id === false or $invoice_payment_id === false or $invoice_payment_status_id === false or $value === false)
                        {
                            $data = [];
                            $error = [
                                0 => 'Payment created com valor inválido'
                            ];
                            goto error;
                        }

                        /**
                         * Localizar pagamento no banco
                         * Se existir aborta a missao
                         */
                        $pay_info = $this->PayRegister->payInfo(null, null, null, $invoice_payment_id);
                        if($pay_info)
                        {
                            $data = [];
                            $error = [
                                0 => 'Pagamento ' . $invoice_payment_id . ' já existe'
                            ];
                            goto error;
                        }

                        /**
                         * Load detalhes da order
                         */
                        $order = $this->Order->orderView($order_id);
                        if(!isset($order['data']['credit_card']))
                        {
                            $data = [];
                            $error = [
                                0 => 'Order não encontrada ou sem cartão definido'
                            ];
                            goto error;
                        }
                        $order = current($order);
                        $payment_status = $this->GatewayMoip->convertPaymentStatus($invoice_payment_status_id);

                        /**
                         * Load Model
                         */
                        $payments_db = TableRegistry::getTableLocator()->get('PayRegisters');

                        /**
                         * Localizar invoice sem id de pagamento
                         * Se existir atualiza o mesmo caso contrario cria novo registro
                         */
                        $query = $payments_db
                            ->find()
                            ->select([
                                'PayRegisters.id'
                            ])
                            ->where([
                                'PayRegisters.order_code' => $invoice_id,
                                'PayRegisters.payment_code IS NULL'
                            ])
                            ->limit(1)
                            ->toArray()
                        ;
                        $payment = UtilsComponent::objToArray($query);

                        if(count($payment))
                        {
                            /**
                             * Atualizando pagamento de invoice nulo
                             */
                            $payment = current($payment);
                            try
                            {
                                $payment_data = $payments_db->get($payment['id']);
                                $payment_data->payment_code = $invoice_payment_id;
                                $payment_data->status_code = $payment_status;
                                $payment_data->paid = ($payment_status == 1) ? 1 : 0;
                                if(!$payments_db->save($payment_data))
                                {
                                    $this->App->mailDebug([
                                        'subject' => 'Apis GatewayMoipEvent - Save payment.created',
                                        'error' => [
                                            'request' => $post
                                        ]
                                    ]);

                                    $data = [];
                                    $error = [
                                        0 => 'Não foi possível salvar o pagamento'
                                    ];
                                    goto error;
                                }
                            }
                            catch (\Cake\Datasource\Exception\InvalidPrimaryKeyException $exc)
                            {
                                $this->App->mailDebug([
                                    'subject' => 'Apis GatewayMoipEvent - Save payment.created',
                                    'error' => [
                                        'request' => $post,
                                        'database' => $exc->getMessage()
                                    ]
                                ]);
                            }
                            catch (\Cake\Datasource\Exception\RecordNotFoundException $exc)
                            {
                                $this->App->mailDebug([
                                    'subject' => 'Apis GatewayMoipEvent - Save payment.created',
                                    'error' => [
                                        'request' => $post,
                                        'database' => $exc->getMessage()
                                    ]
                                ]);
                            }
                            catch (\PDOException $exc)
                            {
                                $this->App->mailDebug([
                                    'subject' => 'Apis GatewayMoipEvent - Save payment.created',
                                    'error' => [
                                        'request' => $post,
                                        'database' => $exc->getMessage()
                                    ]
                                ]);
                            }
                        }
                        else
                        {
                            /**
                             * Criando a novo pagamento
                             */
                            $pay_data = $payments_db->newEntity([
                                'order_id' => $order_id,
                                'credit_card_id' => $order['credit_card']['id'],
                                'gateway_id' => 2,
                                'method_id' => 2,
                                'value' => $value,
                                'acquirer_code' => $order_id,
                                'order_code' => $invoice_id,
                                'payment_code' => $invoice_payment_id,
                                'status_code' => $payment_status,
                                'paid' => ($payment_status == 1) ? 1 : 0
                            ]);

                            if(!$payments_db->save($pay_data))
                            {
                                $this->App->mailDebug([
                                    'subject' => 'Apis GatewayMoipEvent - Save payment.created',
                                    'error' => [
                                        'request' => $post
                                    ]
                                ]);

                                $data = [];
                                $error = [
                                    0 => 'Não foi possível salvar o pagamento'
                                ];
                                goto error;
                            }
                        }

                        /**
                         * Atualiza a order
                         */
                        $this->GatewayMoip->orderSignatureStatusUpdate($order_id, $payment_status);

                        $data = "Pagamento criado com sucesso";
                        $error = [];

                        /**
                         * Executa o callback
                         */
                        $order = $this->Order->OrderView($order_id);
                        $params = [
                            'event' => 'payment.created',
                            'order' => $order['data']
                        ];
                        $this->Callback->signatures($params);
                    break;

                    /**
                     * Atualiza o statos do pagamento
                     * Caso nao exista retorna erro
                     */
                    case 'status_updated':
                        $invoice_payment_id = (isset($post['resource']['id'])) ? $post['resource']['id'] : false;
                        $invoice_payment_status_id = (isset($post['resource']['status']['code'])) ? $post['resource']['status']['code'] : false;

                        /**
                         * Verificar valores
                         */
                        if($invoice_payment_id === false or $invoice_payment_status_id === false)
                        {
                            $data = [];
                            $error = [
                                0 => 'Payment status_updated com valor inválido'
                            ];
                            goto error;
                        }

                        /**
                         * Localizar payment no banco
                         */
                        $pay_info = $this->PayRegister->payInfo(null, null, null, $invoice_payment_id);
                        if($pay_info === false)
                        {
                            $data = [];
                            $error = [
                                0 => 'Payment não encontrado'
                            ];
                            goto error;
                        }

                        $payment = current($pay_info)[0];
                        $payment_status = $this->GatewayMoip->convertPaymentStatus($invoice_payment_status_id);

                        /**
                         * Update Payment
                         */
                        $pay_registers_db = TableRegistry::getTableLocator()->get('PayRegisters');
                        $register = $pay_registers_db->get($payment['id']);
                        $register->status_code = $payment_status;
                        $register->paid = ($payment_status == 1) ? 1 : 0;
                        if(!$pay_registers_db->save($register))
                        {
                            $this->App->mailDebug([
                                'subject' => 'Apis GatewayMoipEvent - Save payment status_updated',
                                'error' => [
                                    'request' => $post
                                ]
                            ]);

                            $data = [];
                            $error = [
                                0 => 'Não foi possível atualizar o pagamento'
                            ];
                            goto error;
                        }

                        /**
                         * Atualiza a order
                         */
                        $this->GatewayMoip->orderSignatureStatusUpdate($payment['order_id'], $payment_status);

                        $data = "Pagamento atualizado com sucesso";
                        $error = [];

                        /**
                         * Executa o callback
                         */
                        $order = $this->Order->OrderView($payment['order_id']);
                        $params = [
                            'event' => 'payment.updated',
                            'order' => $order['data']
                        ];
                        $this->Callback->signatures($params);
                    break;

                    default:
                        $data = [];
                        $error = [
                            0 => 'Payment com acao não definida (' . $event[1] . ')'
                        ];
                        goto error;
                }
            break;

            case 'subscription':
                switch ($event[1])
                {
                    /**
                     * Novo invoice enviado pela moip
                     * Verifica e cria invoice
                     */
                    case 'suspended':
                    case 'canceled':
                        $order_id = (isset($post['resource']['code'])) ? $post['resource']['code'] : false;

                        /**
                         * Verificar valores
                         */
                        if($order_id === false)
                        {
                            $data = [];
                            $error = [
                                0 => 'Order code inválida'
                            ];
                            goto error;
                        }

                        /**
                         * Atualiza a order
                         */
                        if(!$this->GatewayMoip->orderSignatureStatusUpdate($order_id, 11))
                        {
                            $data = [];
                            $error = [
                                0 => 'Order id ' . $order_id . ' não encontrada ou já cancelada'
                            ];
                            goto error;
                        }
                        $data = "Order cancelada com sucesso";
                        $error = [];

                        /**
                         * Executa o callback
                         */
                        $order = $this->Order->OrderView($order_id);
                        $params = [
                            'event' => 'subscription.canceled',
                            'order' => $order['data']
                        ];
                        $this->Callback->signatures($params);
                    break;

                    default:
                        $data = [];
                        $error = [
                            0 => 'Subscription com ação não definida (' . $event[1] . ')'
                        ];
                        goto error;
                }
            break;

            default:
                $data = [];
                $error = [
                    0 => 'Event não definido (' . $event[0] . ')'
                ];
                goto error;
        }

        error:
        $output = [
            'data' => $data,
            'error' => $error
        ];
        return $output;
    }
}