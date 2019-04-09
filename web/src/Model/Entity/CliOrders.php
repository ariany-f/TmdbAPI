<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CliOrder Entity
 *
 * @property int $id
 * @property int $company_id
 * @property string $external_code
 * @property int $customer_id
 * @property float $value
 * @property string $paid
 * @property \Cake\I18n\FrozenTime $paid_date
 * @property int $status_origin_id
 * @property int $status_code
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $updated
 *
 * @property \App\Model\Entity\ApisCompany $apis_company
 * @property \App\Model\Entity\CliCustomer $cli_customer
 * @property \App\Model\Entity\StatusOrigin $status_origin
 */
class CliOrders extends Entity
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
        'company_id' => true,
        'external_code' => true,
        'customer_id' => true,
        'value' => true,
        'paid' => true,
        'paid_date' => true,
        'status_origin_id' => true,
        'status_code' => true,
        'created' => true,
        'updated' => true,
        'apis_company' => true,
        'cli_customer' => true,
        'status_origin' => true
    ];
}
