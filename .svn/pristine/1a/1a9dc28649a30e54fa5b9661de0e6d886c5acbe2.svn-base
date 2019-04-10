<?php
namespace App\Controller;

use App\Controller\Component\UtilsComponent;
use App\Controller\Component\ProductComponent;

/**
 * Product Controller
 */
class ProductController extends AppController
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
     * Lista produtos
     * @param array $post
     * @param string $action
     */
    public function index($post = [], $action)
    {
        $products = ProductComponent::produtcList($this->Auth->user('company_id'));

        /**
         * Loop para gerar array de produtos
         * com bandeira aceitas pelo gateway ativo
         */
        $products_list = [];
        foreach ($products as $values)
        {
            /**
             * Bandeiras aceitas
             */
            $brands = UtilsComponent::checkListCreditCardProduct($values['ForProducts']['id']);

            /**
             * Loop para bandeiras de cartao
             */
            $card_brands = [];
            foreach ($brands as $values_card)
            {
                $card_brands[$values_card['PayCardBrands']['name']] = $values_card['PayCardBrands']['name_view'];
            }

            /**
             * Saida de erro falta bandeira
             */
            if(!$card_brands)
            {
                $this->mailDebug([
                    'subject' => 'Apis Store - Gateway',
                    'error' => 'Erro ao obter a lista de cartões aceitos'
                ]);

                $this->message = 'Erro de gateway';
                $this->code = 500;
                $this->data = [
                    'count' => 1,
                    'result' => [],
                    'errors' => [
                        1 => 'Erro ao obter a lista de cartões aceitos'
                    ]
                ];
                $this->generateOutput();
            }

            $products_list[] = [
                'code' => $values['ForProducts']['id'],
                'name' => $values['ForProducts']['name'],
                'description' => $values['ForProducts']['description'],
                'company' => $values['company'],
                'cnpj' => $values['cnpj'],
                'disclaimer' => $values['ForProducts']['disclaimer'],
                'susep_code' => $values['ForProducts']['susep_code'],
                'value' => $values['ForProducts']['value'],
                'installments' => $values['ForProducts']['installments'],
                'valid_period_month' => round($values['ForProducts']['valid_period_days'] / 30),
                'pay_method' => $values['PayMethods']['name'],
                'card_brands' => $card_brands
            ];
        }

        /**
         * Saida de erro lista de produtos
         */
        if(!$products_list)
        {
            $this->mailDebug([
                'subject' => 'Apis Store - Lista de produtos',
                'error' => 'Não há produto disponível',
            ]);

            $this->message = 'Lista de produtos';
            $this->code = 404;
            $this->data = [
                'count' => 1,
                'result' => [],
                'errors' => [
                    2 => 'Não há produto disponível'
                ]
            ];
            $this->generateOutput();
        }

        /**
         * Saida lista de produtos
         */
        $this->message = 'Lista de produtos';
        $this->code = 200;
        $this->success = true;
        $this->data = [
            'count' => count($products_list),
            'result' => $products_list,
            'errors' => []
        ];
        $this->generateOutput();
    }

    /**
     * View method
     */
    public function view($id = null)
    {
        die;
    }

    /**
     * Add method
     */
    public function add()
    {
        die;
    }

    /**
     * Edit method
     */
    public function edit($id = null)
    {
        die;
    }

    /**
     * Delete method
     */
    public function delete($id = null)
    {
        die;
    }
}