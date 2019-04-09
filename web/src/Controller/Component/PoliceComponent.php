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
use Phinx\Util\Util;

/**
 * Police component
 */
class PoliceComponent extends Component
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
     * Create police
     * @param null $order_id
     * @return bool
     */
    public function add($order_id = null)
    {
        $output = false;
        $order = $this->Order->orderView($order_id);
        if(!$order['data']['id'])
        {
            goto error;
        }
        $order = current($order);

        /**
         * Order esta paga
         */
        if($order['status_id'] != 4)
        {
            goto error;
        }

        /**
         * Loop nos itens
         */
        foreach($order['itens'] as $item)
        {
            /**
             * Compara numero de polices
             * com o numero de itens
             * Se igual sai erro
             */
            if($item['qtd'] == count($item['police']))
            {
                goto error;
            }

            /**
             * Checa se a marca tem o produto
             */
            $marca_type_db = TableRegistry::getTableLocator()->get('ForProductTypeBrands');
            $query = $marca_type_db
                ->find()
                ->select([
                    'ForProductTypeBrands.id'
                ])
                ->where([
                    'ForProductTypeBrands.brand_id' => $item['marca_id'],
                    'ForProductTypeBrands.product_type_id' => $item['type_id']
                ])
                ->toArray()
            ;
            $marca_type = UtilsComponent::objToArray($query);
            if(!count($marca_type))
            {
                goto error;
            }
            $marca_type_id = current($marca_type)['id'];

            /**
             * Loop para gerar polices
             */
            $police_db = TableRegistry::getTableLocator()->get('ForProductTypePoliceOrderItens');
            for($a = 0; $a < $item['qtd']; $a++)
            {
                $query = $police_db
                    ->find()
                    ->select([
                        'police_last' => 'MAX(ForProductTypePoliceOrderItens.police)'
                    ])
                    ->where([
                        'ForProductTypePoliceOrderItens.product_type_brand_id' => $marca_type_id
                    ])
                    ->toArray()
                ;
                $police = UtilsComponent::objToArray($query);

                /**
                 * Como a mapfre esta no ar esse e o
                 * numero de inicio da nova versao
                 */
                if(!$police[0]['police_last'])
                {
                    $police_new = 305000;
                }
                else
                {
                    $police_new = current($police)['police_last'] + 1;
                }

                $params = [
                    'product_type_brand_id' => $marca_type_id,
                    'customer_id' => $order['customer']['id'],
                    'order_item_id' => $item['id'],
                    'police' => $police_new,
                    'status_code' => 2
                ];
                $police_insert = $police_db->newEntity($params);
                try
                {
                    $police_db->save($police_insert);
                    $output = true;
                }
                catch (\PDOException $exc)
                {
                    $this->App->mailDebug([
                        'subject' => 'Apis Police - Erro created',
                        'params' => $params,
                        'error' => $exc->getMessage()
                    ]);
                }
            }
        }

        error:
        return $output;
    }

    /**
     * Cancela police
     * @param array $params
     * @return bool
     */
    public function delete($params = [
        'police_id' => '',
        'status_code' => ''
    ])
    {
        $output = false;
        try
        {
            $police_db = TableRegistry::getTableLocator()->get('ForProductTypePoliceOrderItens');
            $police = $police_db->get($params['police_id']);
            $police->status_code = $params['status_code'];
            if($police_db->save($police))
            {
                $output = true;
            }
        }
        catch (\Cake\Datasource\Exception\InvalidPrimaryKeyException $exc)
        {
            $this->App->mailDebug([
                'subject' => 'Apis Police - Erro delete',
                'params' => [
                    'police_id' => $params['police_id'],
                    'status_code' => $params['status_code']
                ],
                'error' => $exc->getMessage()
            ]);
        }
        catch (\Cake\Datasource\Exception\RecordNotFoundException $exc)
        {
            $this->App->mailDebug([
                'subject' => 'Apis Police - Erro delete',
                'params' => [
                    'police_id' => $params['police_id'],
                    'status_code' => $params['status_code']
                ],
                'error' => $exc->getMessage()
            ]);
        }
        catch (\PDOException $exc)
        {
            $this->App->mailDebug([
                'subject' => 'Apis Police - Erro delete',
                'params' => [
                    'police_id' => $params['police_id'],
                    'status_code' => $params['status_code']
                ],
                'error' => $exc->getMessage()
            ]);
        }
        return $output;
    }
}