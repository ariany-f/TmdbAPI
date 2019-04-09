<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Product component
 */
class ProductComponent extends Component
{
    /**
     * Lista os produtos
     * @param $company_id
     * @return mixed
     */
    public static function produtcList($company_id)
    {
        /**
         * Busca produto no banco
         */
        $products = TableRegistry::getTableLocator()->get('ForBrands');
        $query = $products
            ->find()
            ->select([
                'ForProducts.id',
                'ForProducts.name',
                'ForProducts.description',
                'ForBrands.company',
                'ForBrands.cnpj',
                'ForProducts.disclaimer',
                'ForProducts.susep_code',
                'ForProducts.value',
                'ForProducts.installments',
                'ForProducts.valid_period_days',
                'PayMethods.name'
            ])
            ->join([
                'table' => 'for_product_type_brands',
                'alias' => 'ForProductTypeBrands',
                'type' => 'INNER',
                'conditions' => 'ForProductTypeBrands.brand_id = ForBrands.id'
            ])
            ->join([
                'table' => 'for_product_types',
                'alias' => 'ForProductTypes',
                'type' => 'INNER',
                'conditions' => 'ForProductTypes.id = ForProductTypeBrands.product_type_id'
            ])
            ->join([
                'table' => 'for_products',
                'alias' => 'ForProducts',
                'type' => 'INNER',
                'conditions' => 'ForProducts.product_type_brand_id = ForProductTypeBrands.id'
            ])
            ->join([
                'table' => 'for_product_pay_methods',
                'alias' => 'ForProductPayMethods',
                'type' => 'INNER',
                'conditions' => 'ForProductPayMethods.product_id = ForProducts.id'
            ])
            ->join([
                'table' => 'pay_methods',
                'alias' => 'PayMethods',
                'type' => 'INNER',
                'conditions' => 'PayMethods.id = ForProductPayMethods.method_id'
            ])
            ->where([
                'ForBrands.status_code' => 2,
                'ForProductTypeBrands.status_code' => 2,
                'ForProductTypes.status_code' => 2,
                'ForProducts.status_code' => 2,
                'ForProductPayMethods.status_code' => 2,
                'PayMethods.status_code' => 2
            ])
            ->toArray()
        ;
        return UtilsComponent::objToArray($query);
    }

    /**
     * Procura pelo produto na base
     * e retorna seus dados
     * @param $company_id
     * @param $product_id
     * @return bool|array
     */
    public static function productCheck($company_id, $product_id)
    {
        $output = false;

        try {
            $db = TableRegistry::getTableLocator()->get('ForProducts');
            $query = $db
                ->find()
                ->select([
                    'ForBrands.id',
                    'ForBrands.name',
                    'ForProductTypeBrands.id',
                    'ForProductTypes.id',
                    'ForProductTypes.name',
                    'ForProducts.id',
                    'ForProducts.name',
                    'ForProducts.value'
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
                    'ForProducts.id' => $product_id
                ])
                ->toArray()
            ;
            $result = UtilsComponent::objToArray($query);

            if(count($result))
            {
                $result = current($result);
                $output = [
                    'data' => [
                        'brand_id' => $result['ForBrands']['id'],
                        'brand_name' => $result['ForBrands']['name'],
                        'type_brand_id' => $result['ForProductTypeBrands']['id'],
                        'type_id' => $result['ForProductTypes']['id'],
                        'type_name' => $result['ForProductTypes']['name'],
                        'id' => $result['id'],
                        'name' => $result['name'],
                        'value' => $result['value']
                    ],
                    'error' => ''
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

        return $output;
    }
}