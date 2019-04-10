<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CliOrderIten Entity
 *
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $qtd
 * @property float $value_unit
 * @property float $value_total
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $updated
 *
 * @property \App\Model\Entity\Order $order
 * @property \App\Model\Entity\Product $product
 */
class CliOrderItens extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'order_id' => true,
        'product_id' => true,
        'qtd' => true,
        'value_uni' => true,
        'value_total' => true,
        'created' => true,
        'updated' => true,
        'order' => true,
        'product' => true
    ];
}
