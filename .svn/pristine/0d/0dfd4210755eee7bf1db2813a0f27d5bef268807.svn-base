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
 * PayRegister component
 */
class PayRegisterComponent extends Component
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
        ''
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
     * Retorna informacoes sobre o pagamento
     * @param null $order_id
     * @param null $external_code
     * @param null $invoice_id
     * @param null $payment_id
     * @return mixed bool|array
     */
    public function payInfo($order_id = null, $external_code = null, $invoice_id = null, $payment_id = null)
    {
        $find = false;
        $where = [];
        $payment_loop = false;
        $pays = [];

        if(!is_null($order_id))
        {
            $where = [];
            $where['PayRegisters.order_id'] = $order_id;
            $find = true;
        }

        if(!is_null($external_code))
        {
            $where = [];
            $where['CliOrders.external_code'] = $external_code;
            $find = true;
        }

        if(!is_null($invoice_id))
        {
            $where = [];
            $where['PayRegisters.order_code'] = $invoice_id;
            $find = true;
        }

        if(!is_null($payment_id))
        {
            $where = [];
            $where['PayRegisters.payment_code'] = $payment_id;
            $find = true;
        }

        if(!$find)
        {
            goto error;
        }

        /**
         * Localizar fatura no banco
         */
        $pay_registers = TableRegistry::getTableLocator()->get('PayRegisters');
        $query = $pay_registers
            ->find()
            ->select([
                'PayRegisters.id',
                'PayRegisters.order_id',
                'external_code' => 'CliOrders.external_code',
                'PayRegisters.value',
                'PayRegisters.paid',
                'PayRegisters.paid_date',
                'PayRegisters.order_code',
                'PayRegisters.payment_code',
                'PayRegisters.installments',
                'ApisStatus.status_code',
                'ApisStatus.name',
                'PayRegisters.created',
                'PayRegisters.updated',
                'ApisCreditCard.name',
                'PayCardBrands.name',
                'PayCardBrands.name_view',
                'ApisCreditCard.id',
                'ApisCreditCard.number',
                'ApisCreditCard.expire_month',
                'ApisCreditCard.expire_year',
                'ApisStatusCard.status_code',
                'ApisStatusCard.name'
            ])
            ->join([
                'table' => 'cli_orders',
                'alias' => 'CliOrders',
                'type' => 'INNER',
                'conditions' => [
                    'CliOrders.id = PayRegisters.order_id'
                ]
            ])
            ->join([
                'table' => 'apis_credit_card',
                'alias' => 'ApisCreditCard',
                'type' => 'INNER',
                'conditions' => [
                    'ApisCreditCard.id = PayRegisters.credit_card_id'
                ]
            ])
            ->join([
                'table' => 'pay_card_brands',
                'alias' => 'PayCardBrands',
                'type' => 'INNER',
                'conditions' => 'PayCardBrands.id = ApisCreditCard.card_brand_id'
            ])
            ->join([
                'table' => 'apis_status',
                'alias' => 'ApisStatus',
                'type' => 'INNER',
                'conditions' => [
                    'ApisStatus.status_origin_id = PayRegisters.status_origin_id',
                    'ApisStatus.status_code = PayRegisters.status_code'
                ]
            ])
            ->join([
                'table' => 'apis_status',
                'alias' => 'ApisStatusCard',
                'type' => 'INNER',
                'conditions' => [
                    'ApisStatusCard.status_origin_id = ApisCreditCard.status_origin_id',
                    'ApisStatusCard.status_code = ApisCreditCard.status_code'
                ]
            ])
            ->where($where)
            ->order([
                'PayRegisters.id DESC'
            ])
            ->toArray();
        $invoice = UtilsComponent::objToArray($query);

        /**
         * Agrupando invoices e paymets
         */
        foreach ($invoice as $payment)
        {
            $pays[$payment['order_code']][] = [
                'id' => $payment['id'],
                'order_id' => $payment['order_id'],
                'external_code' => $payment['external_code'],
                'invoice_id' => $payment['order_code'],
                'payment_code' => $payment['payment_code'],
                'installments' => $payment['installments'],
                'value' => $payment['value'],
                'paid' =>  $payment['paid'],
                'paid_date' => $payment['paid_date'],
                'status_id' => $payment['ApisStatus']['status_code'],
                'status_name' => $payment['ApisStatus']['name'],
                'created' => $payment['created'],
                'updated' => $payment['updated'],
                'credit_card' => [
                    'id' => $payment['ApisCreditCard']['id'],
                    'brand_view' => $payment['PayCardBrands']['name_view'],
                    'brand' => $payment['PayCardBrands']['name'],
                    'holder_name' => $payment['ApisCreditCard']['name'],
                    'number' => $payment['ApisCreditCard']['number'],
                    'expire_month' => $payment['ApisCreditCard']['expire_month'],
                    'expire_year' => $payment['ApisCreditCard']['expire_year'],
                    'status_id' => $payment['ApisStatusCard']['status_code'],
                    'status_name' => $payment['ApisStatusCard']['name']
                ]
            ];
            $payment_loop[$payment['order_code']] = $pays[$payment['order_code']];
        }

        error:
        return $payment_loop;
    }
}