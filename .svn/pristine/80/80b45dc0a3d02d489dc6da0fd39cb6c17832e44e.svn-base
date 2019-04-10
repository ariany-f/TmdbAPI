<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use DateInterval;
use DateTime;

/**
 * Buy component
 */
class OrderComponent extends Component
{
    /**
     * Load other component
     * @var array
     */
    public $components = [
        'PayRegister',
        'Police'
    ];

    /**
     * Default configuration.
     */
    protected $App;

    /**
     * Load tudo do controller
     */
    public function initialize(array $config)
    {
        $this->App = $this->_registry->getController();
    }

    /**
     * Add order
     * ou add cliente
     * @param $params
     * @return bool|array
     */
    public static function insuranceAdd($params = [
        'company_id' => '',
        'external_code' => '',
        'customer_id' => '',
        'cli_order_itens' => [
            [
                'brand_id' => '',
                'type_id' => '',
                'product_id' => '',
                'qtd' => '',
                'value_uni' => ''
            ]
        ]
    ])
    {
        $error = [];

        try {
            $cli_orders_db = TableRegistry::getTableLocator()->get('CliOrders');
            $cli_order_itens_db = TableRegistry::getTableLocator()->get('CliOrderItens');

            $query = $cli_orders_db
                ->find()
                ->select([
                    'CliOrders.id'
                ])
                ->where([
                    'CliOrders.company_id' => $params['company_id'],
                    'CliOrders.external_code' =>  $params['external_code']
                ])
                ->toArray()
            ;
            $external_code_check = UtilsComponent::objToArray($query);
            if(count($external_code_check))
            {
                $output = [
                    'data' => [],
                    'error' => 'A order_id ' . $params['external_code'] . ' já está em uso'
                ];
                goto error;
            }

            $cli_order_itens_data['cli_order_itens'] = [];
            $total = 0;

            $item_ok = false;
            foreach ($params['cli_order_itens'] as $key => $item)
            {
                $check = OrderComponent::checkProductSegmentBrand([
                    'customer_id' => $params['customer_id'],
                    'brand_id' => $item['brand_id'],
                    'type_id' => $item['type_id'],
                    'product_id' => $item['product_id']
                ]);

                if($check)
                {
                    $error[$key] = $check['error'];
                }
                else
                {
                    /**
                     * store/insurance/buyb2c
                     * Ajustar para receber customer no item
                     */
                    $item['customer_id'] = $params['customer_id'];
                    $item['value_total'] = $item['qtd'] * $item['value_uni'];
                    $total = $total + $item['value_total'];
                    $cli_order_itens_data['cli_order_itens'][] = $cli_order_itens_db->newEntity($item);
                    $item_ok = true;
                }
            }

            /**
             * Item passou para a venda
             */
            if($item_ok)
            {
                $params['value'] = $total;
                $params['status_code'] = 1;
                $order_new = $cli_orders_db->newEntity($params);
                $order_new['cli_order_itens'] = $cli_order_itens_data['cli_order_itens'];

                if($cli_orders_db->save($order_new))
                {
                    $output = [
                        'data' => [
                            'id' => $order_new->id,
                            'total' => $total
                        ],
                        'error' => $error
                    ];
                }
                else
                {
                    $output = [
                        'data' => [],
                        'error' => $order_new->getErrors()
                    ];
                }
            }
            else
            {
                $output = [
                    'data' => [],
                    'error' => $error
                ];
            }
        }
        catch (\PDOException $exc)
        {
            $output = [
                'data' => [],
                'error' => $exc->getMessage()
            ];
        }

        error:
        return $output;
    }

    /**
     * Para evitar a compra de um produto do mesmo seguimento e mesma marca
     * @param array $params
     * @return array|bool|mixed
     */
    public static function checkProductSegmentBrand($params =[
        'customer_id' => '',
        'brand_id' => '',
        'type_id' => '',
        'product_id' => ''
    ])
    {
        $output = false;

        try{
            $cli_orders_db = TableRegistry::getTableLocator()->get('CliOrders');
            $query = $cli_orders_db
                ->find()
                ->select([
                    'CliOrders.id',
                    'CliOrders.status_code',
                    'ForBrands.id',
                    'ForBrands.name',
                    'ForProductTypes.id',
                    'ForProductTypes.name',
                    'ForProducts.id',
                    'ForProducts.name'
                ])
                ->join([
                    'table' => 'cli_order_itens',
                    'alias' => 'CliOrderItens',
                    'type' => 'INNER',
                    'conditions' => 'CliOrderItens.order_id = CliOrders.id'
                ])
                ->join([
                    'table' => 'for_products',
                    'alias' => 'ForProducts',
                    'type' => 'INNER',
                    'conditions' => 'ForProducts.id = CliOrderItens.product_id'
                ])
                ->join([
                    'table' => 'for_product_type_brands',
                    'alias' => 'ForProductTypeBrands',
                    'type' => 'INNER',
                    'conditions' => 'ForProductTypeBrands.id = ForProducts.product_type_brand_id'
                ])
                ->join([
                    'table' => 'for_product_types',
                    'alias' => 'ForProductTypes',
                    'type' => 'INNER',
                    'conditions' => 'ForProductTypes.id = ForProductTypeBrands.product_type_id'
                ])
                ->join([
                    'table' => 'for_brands',
                    'alias' => 'ForBrands',
                    'type' => 'INNER',
                    'conditions' => 'ForBrands.id = ForProductTypeBrands.brand_id'
                ])
                ->where([
                    'ForProducts.id' => $params['product_id'],
                    'CliOrders.customer_id' => $params['customer_id']
                ])
                ->limit(1)
                ->toArray()
            ;
            $result = UtilsComponent::objToArray($query);

            if(count($result))
            {
                $result = current($result);
                $order_id = $result['id'];
                $brand_id = $result['ForBrands']['id'];
                $type_id = $result['ForProductTypes']['id'];
                $status_code = $result['status_code'];

                /**
                 * status_code, so pode compra mesmo tipo e mesma marca se status_code igual
                 * 7 - Expirado
                 * 8 - cancelado
                 */
                if($type_id == $params['type_id'] and $brand_id == $params['brand_id'] and !in_array($status_code, [7, 8]))
                {
                    $output = [
                        'data' => [],
                        'error' => [
                            9 => 'Já existe uma solicitação do mesmo produto/marca na order ' . $order_id
                        ]
                    ];
                }
            }
        }
        catch (\PDOException $exc)
        {
            $output = [
                'data' => [],
                'error' => $exc->getMessage()
            ];
        }
        return $output;
    }

    /**
     * Atualiza a order
     * @param null $order_id
     * @param null $new_status_code
     * @return bool
     * @throws \Exception
     */
    public function updated($order_id = null, $new_status_code = null)
    {
        $output = false;
        if(is_null($order_id) or is_null($new_status_code))
        {
            goto error;
        }

        /**
         * Info da order
         */
        $order_db = $this->orderView($order_id);

        /**
         * Order não encontrada
         */
        if(!empty($order_db['error']))
        {
            goto error;
        }

        $order_db = current($order_db);

        /**
         * Se o statusa da order for cancelada
         * nada sera atualizado
         */
        if($order_db['status_id'] == 8)
        {
            goto error;
        }

        /**
         * Acoes pelo status
         */
        switch ($new_status_code)
        {
            /**
             * Pago
             */
            case 4:
                $beginning_date = (empty($order_db['beginning'])) ? date('Y-m-d') : $order_db['beginning'];
                $expiration = new DateTime($beginning_date);
                $expiration->add(new DateInterval('P12M'));
                $expiration_date = (empty($order_db['beginning'])) ? $expiration->format('Y-m-d') : $order_db['expiration'];
                $due_monthly_base = (empty($order_db['due_monthly'])) ? $beginning_date : $order_db['due_monthly'];
                $due_monthly = new DateTime($due_monthly_base);
                $due_monthly->add(new DateInterval('P1M'));
                $due_monthly_date = $due_monthly->format('Y-m-d');
                $paid = true;
            break;

            /**
             * Cancelado
             */
            case 8:
                $beginning_date = (empty($order_db['beginning'])) ? null : $order_db['beginning'];
                $expiration_date = (empty($order_db['expiration'])) ? null : date('Y-m-d');
                $due_monthly_date = null;
                $paid = false;
            break;

            default:
                $beginning_date = (empty($order_db['beginning'])) ? null : $order_db['beginning'];
                $expiration_date = (empty($order_db['beginning'])) ? null : $order_db['expiration'];
                $due_monthly_date = (empty($order_db['due_monthly'])) ? null : $order_db['due_monthly'];
                $paid = false;
        }

        /**
         * No banco
         */
        try
        {
            $order_id = $order_db['id'];
            $cli_orders_db = TableRegistry::getTableLocator()->get('CliOrders');
            $order = $cli_orders_db->get($order_id);
            $order->beginning = $beginning_date;
            $order->expiration = $expiration_date;
            $order->due_monthly = $due_monthly_date;
            $order->status_code = $new_status_code;

            /**
             * Se erro manda e-mail
             */
            if($cli_orders_db->save($order))
            {
                /**
                 * Gera numero de police
                 */
                if(empty($order_db['beginning']) and $paid)
                {
                    if($this->Police->add($order_id))
                    {
                        $output = true;
                    }
                }
                else
                {
                    $output = true;
                }
            }
            else
            {
                $this->App->mailDebug([
                    'subject' => 'Apis Order - Erro update Order',
                    'params' => [
                        'order_id' => $order_id,
                        'new_status_code' => $new_status_code
                    ],
                ]);
            }
        }
        catch (\Cake\Datasource\Exception\InvalidPrimaryKeyException $exc)
        {
            $this->App->mailDebug([
                'subject' => 'Apis Order - Erro update Order',
                'params' => [
                    'order_id' => $order_id,
                    'new_status_code' => $new_status_code
                ],
                'error' => $exc->getMessage()
            ]);
        }
        catch (\Cake\Datasource\Exception\RecordNotFoundException $exc)
        {
            $this->App->mailDebug([
                'subject' => 'Apis Order - Erro update Order',
                'params' => [
                    'order_id' => $order_id,
                    'new_status_code' => $new_status_code
                ],
                'error' => $exc->getMessage()
            ]);
        }
        catch (\PDOException $exc)
        {
            $this->App->mailDebug([
                'subject' => 'Apis Order - Erro update Order',
                'params' => [
                    'order_id' => $order_id,
                    'new_status_code' => $new_status_code
                ],
                'error' => $exc->getMessage()
            ]);
        }

        error:
        return $output;
    }

    /**
     * View order
     * @param null $order_id
     * @param null $external_code
     * @param null $company_id
     * @return array
     */
    public function orderView($order_id = null, $external_code = null, $company_id = null)
    {
        $output = [
            'data' => [],
            'error' => [
                'Order ' . $order_id . $external_code . ' não encontrada'
            ]
        ];

        /**
         * company_ini nao definido
         */
        if(is_null($company_id))
        {
            $company_id = $this->App->Auth->user('company_id');
        }

        /**
         * No webhook pode nao ter company_id
         */
        if(!is_null($company_id))
        {
            $where['CliOrders.company_id'] = $company_id;
        }

        /**
         * Nos casos de busca por order ou external code
         */
        if(is_null($order_id))
        {
            $where['CliOrders.external_code'] = $external_code;

            /**
             * Sem order_id e obrigado informar o company_id
             */
            if(is_null($company_id))
            {
                $output = [
                    'data' => [],
                    'error' => [
                        'Order ' . $order_id . $external_code . ' não encontrada'
                    ]
                ];
                goto error;
            }
        }
        else
        {
            $where['CliOrders.id'] = $order_id;
        }

        /**
         * Order
         */
        $cli_orders_db = TableRegistry::getTableLocator()->get('CliOrders');
        $query = $cli_orders_db
            ->find()
            ->select([
                'CliOrders.id',
                'CliOrders.company_id',
                'CliOrders.external_code',
                'CliOrders.customer_id',
                'CliOrders.value',
                'CliOrders.beginning',
                'CliOrders.expiration',
                'CliOrders.due_monthly',
                'CliOrderCreditCardActives.id',
                'CliOrderCreditCardActives.gateway_id',
                'ApisCreditCard.id',
                'PayCardBrands.name_view',
                'ApisCreditCard.name',
                'ApisCreditCard.number',
                'ApisCreditCard.expire_month',
                'ApisCreditCard.expire_year',
                'ApisStatus.status_code',
                'ApisStatus.name',
                'CliOrders.created'
            ])
            ->join([
                'table' => 'cli_order_credit_card_actives',
                'alias' => 'CliOrderCreditCardActives',
                'type' => 'INNER',
                'conditions' => 'CliOrderCreditCardActives.order_id = CliOrders.id'
            ])
            ->join([
                'table' => 'apis_credit_card',
                'alias' => 'ApisCreditCard',
                'type' => 'INNER',
                'conditions' => [
                    'ApisCreditCard.credit_card_base_id = 1',
                    'ApisCreditCard.id = CliOrderCreditCardActives.credit_card_id'
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
                    'ApisStatus.status_origin_id = CliOrders.status_origin_id',
                    'ApisStatus.status_code = CliOrders.status_code'
                ]
            ])
            ->where($where)
            ->toArray()
        ;
        $order = UtilsComponent::objToArray($query);
        if(!count($order))
        {
            goto error;
        }
        $order = current($order);

        /**
         * Order Itens
         */
        $cli_orders_itens_db = TableRegistry::getTableLocator()->get('CliOrderItens');
        $query = $cli_orders_itens_db
            ->find()
            ->select([
                'CliOrderItens.id',
                'ForBrands.id',
                'ForBrands.name',
                'ForProductTypes.id',
                'ForProductTypes.name',
                'ForProducts.id',
                'ForProducts.name',
                'CliOrderItens.qtd',
                'CliOrderItens.value_uni',
                'CliOrderItens.value_total'
            ])
            ->join([
                'table' => 'for_products',
                'alias' => 'ForProducts',
                'type' => 'INNER',
                'conditions' => 'ForProducts.id = CliOrderItens.product_id'
            ])
            ->join([
                'table' => 'for_product_type_brands',
                'alias' => 'ForProductTypeBrands',
                'type' => 'INNER',
                'conditions' => 'ForProductTypeBrands.id = ForProducts.product_type_brand_id'
            ])
            ->join([
                'table' => 'for_product_types',
                'alias' => 'ForProductTypes',
                'type' => 'INNER',
                'conditions' => 'ForProductTypes.id = ForProductTypeBrands.product_type_id'
            ])
            ->join([
                'table' => 'for_brands',
                'alias' => 'ForBrands',
                'type' => 'INNER',
                'conditions' => 'ForBrands.id = ForProductTypeBrands.brand_id'
            ])
            ->where([
                'CliOrderItens.order_id' => $order['id']
            ])
            ->toArray()
        ;
        $itens = UtilsComponent::objToArray($query);
        if(!count($itens))
        {
            $output = [
                'data' => [],
                'error' => [
                    'Order ' . $order_id . ' sem itens'
                ]
            ];
            goto error;
        }

        /**
         * Order payments
         */
        $pay_registers_db = TableRegistry::getTableLocator()->get('PayRegisters');
        $query = $pay_registers_db
            ->find()
            ->select([
                'PayRegisters.id',
                'ApisCreditCard.id',
                'PayCardBrands.name_view',
                'ApisCreditCard.name',
                'ApisCreditCard.number',
                'ApisCreditCard.expire_month',
                'ApisCreditCard.expire_year',
                'ApisCreditCard.status_code',
                'PayRegisters.order_code',
                'PayRegisters.value',
                'PayRegisters.paid',
                'PayRegisters.paid_date',
                'PayRegisters.created'
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
            ->where([
                'PayRegisters.order_id' => $order['id']
            ])
            ->toArray()
        ;
        $pays = UtilsComponent::objToArray($query);
        if(!count($pays))
        {
            $output = [
                'data' => [],
                'error' => [
                    'Order ' . $order_id . ' sem pagamento'
                ]
            ];
            goto error;
        }

        /**
         * Monta saida da order
         * com todos os dados
         */
        $itens_loop = [];
        foreach ($itens as $item)
        {
            /**
             * Verificar police do item
             */
            $police_db = TableRegistry::getTableLocator()->get('ForProductTypePoliceOrderItens');
            $query = $police_db
                ->find()
                ->select([
                    'ForProductTypePoliceOrderItens.id',
                    'ForProductTypePoliceOrderItens.police',
                    'ForProductTypePoliceOrderItens.customer_id',
                    'ApisStatus.status_code',
                    'ApisStatus.name',
                    'ForProductTypePoliceOrderItens.created'
                ])
                ->join([
                    'table' => 'apis_status',
                    'alias' => 'ApisStatus',
                    'type' => 'INNER',
                    'conditions' => [
                        'ApisStatus.status_origin_id = ForProductTypePoliceOrderItens.status_origin_id',
                        'ApisStatus.status_code = ForProductTypePoliceOrderItens.status_code'
                    ]
                ])
                ->where([
                    'ForProductTypePoliceOrderItens.order_item_id' => $item['id']
                ])
                ->toArray()
            ;
            $polices = UtilsComponent::objToArray($query);
            $police_item = [];
            foreach ($polices as $police)
            {
                $customer = ClientComponent::clientFullView([
                    'id' => $police['customer_id'],
                    'cpf' => ''
                ]);

                $police_item[] = [
                    'id' => $police['id'],
                    'police' => $police['police'],
                    'customer' => $customer['data'],
                    'status' => [
                        'id' => $police['ApisStatus']['status_code'],
                        'name' => $police['ApisStatus']['name']
                    ],
                    'created' => $police['created']
                ];
            }

            $itens_loop[] = [
                'id' => $item['id'],
                'marca_id' => $item['ForBrands']['id'],
                'marca_name' => $item['ForBrands']['name'],
                'type_id' => $item['ForProductTypes']['id'],
                'type_name' => $item['ForProductTypes']['name'],
                'product_id' => $item['ForProducts']['id'],
                'product_name' => $item['ForProducts']['name'],
                'qtd' => $item['qtd'],
                'value_uni' => $item['value_uni'],
                'value_total' => $item['value_total'],
                'police' => $police_item
            ];
        }

        $customer = ClientComponent::clientFullView([
            'id' => $order['customer_id'],
            'cpf' => ''
        ]);

        /**
         * Lista de invoices/payments
         */
        $payment_loop = $this->PayRegister->payInfo($order['id']);

        $resume = [
            'id' => $order['id'],
            'company_id' => $order['company_id'],
            'external_code' => $order['external_code'],
            'value' => $order['value'],
            'beginning' => $order['beginning'],
            'expiration' => $order['expiration'],
            'due_monthly' => $order['due_monthly'],
            'customer' => $customer['data'],
            'itens' => $itens_loop,
            'credit_card_active' => $order['CliOrderCreditCardActives']['id'],
            'gateway_id' => $order['CliOrderCreditCardActives']['gateway_id'],
            'credit_card' => [
                'id' => $order['ApisCreditCard']['id'],
                'brand' => $order['PayCardBrands']['name_view'],
                'name' => $order['ApisCreditCard']['name'],
                'number' => $order['ApisCreditCard']['number'],
                'expire_month' => $order['ApisCreditCard']['expire_month'],
                'expire_year' => $order['ApisCreditCard']['expire_year']
            ],
            'payment' => $payment_loop,
            'status_id' => $order['ApisStatus']['status_code'],
            'status_name' => $order['ApisStatus']['name'],
            'created' => $order['created']
        ];

        $output = [
            'data' => $resume,
            'error' => []
        ];

        error:
        return $output;
    }

    /**
     * Status da order
     * @param null $order_id
     * @param null $external_code
     * @param null $company_id
     * @return array
     */
    public function orderStatus($order_id = null, $external_code = null, $company_id = null)
    {
        $output = [
            'data' => [],
            'error' => [
                'Order ' . $order_id . $external_code . ' não encontrada'
            ]
        ];

        /**
         * company_ini nao definido
         */
        if(is_null($company_id))
        {
            $company_id = $this->App->Auth->user('company_id');
        }

        /**
         * No webhook pode nao ter company_id
         */
        if(!is_null($company_id))
        {
            $where['CliOrders.company_id'] = $company_id;
        }

        /**
         * Nos casos de busca por order ou external code
         */
        if(is_null($order_id))
        {
            $where['CliOrders.external_code'] = $external_code;

            /**
             * Sem order_id e obrigado informar o company_id
             */
            if(is_null($company_id))
            {
                $output = [
                    'data' => [],
                    'error' => [
                        'Order ' . $order_id . $external_code . ' não encontrada'
                    ]
                ];
                goto error;
            }
        }
        else
        {
            $where['CliOrders.id'] = $order_id;
        }

        /**
         * Order
         */
        $cli_orders_db = TableRegistry::getTableLocator()->get('CliOrders');
        $query = $cli_orders_db
            ->find()
            ->select([
                'CliOrders.status_code'
            ])
            ->where($where)
            ->toArray()
        ;
        $order = UtilsComponent::objToArray($query);
        if(!count($order))
        {
            goto error;
        }
        $order = current($order);

        $output = [
            'data' => $order,
            'error' => []
        ];

        error:
        return $output;
    }
}