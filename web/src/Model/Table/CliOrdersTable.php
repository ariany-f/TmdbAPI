<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CliOrders Model
 *
 * @property \App\Model\Table\ApisCompanysTable|\Cake\ORM\Association\BelongsTo $ApisCompanys
 * @property \App\Model\Table\CliCustomersTable|\Cake\ORM\Association\BelongsTo $CliCustomers
 * @property \App\Model\Table\ApisStatusOriginsTable|\Cake\ORM\Association\BelongsTo $StatusOrigins
 *
 * @method \App\Model\Entity\CliOrder get($primaryKey, $options = [])
 * @method \App\Model\Entity\CliOrder newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\CliOrder[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CliOrder|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CliOrder|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CliOrder patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CliOrder[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\CliOrder findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CliOrdersTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('cli_orders');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('CliOrderItens', [
            'foreignKey' => 'order_id'
        ]);

        $this->belongsTo('ApisCompanys', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER'
        ]);

        $this->belongsTo('CliCustomers', [
            'foreignKey' => 'customer_id',
            'joinType' => 'INNER'
        ]);

        $this->belongsTo('ApisStatusOrigins', [
            'foreignKey' => 'status_origin_id',
            'joinType' => 'INNER'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('external_code')
            ->maxLength('external_code', 40)
            ->allowEmpty('external_code');

        $validator
            ->decimal('value')
            ->requirePresence('value', 'create')
            ->notEmpty('value');

        $validator
            ->scalar('paid')
            ->allowEmpty('paid');

        $validator
            ->dateTime('paid_date')
            ->allowEmpty('paid_date');

        $validator
            ->integer('status_code')
            ->requirePresence('status_code', 'create')
            ->notEmpty('status_code');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['company_id'], 'ApisCompanys'));
        $rules->add($rules->existsIn(['customer_id'], 'CliCustomers'));
        $rules->add($rules->existsIn(['status_origin_id'], 'ApisStatusOrigins'));

        return $rules;
    }
}
