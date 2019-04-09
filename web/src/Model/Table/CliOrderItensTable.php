<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CliOrderItens Model
 *
 * @property \App\Model\Table\CliOrdersTable|\Cake\ORM\Association\BelongsTo $Orders
 * @property \App\Model\Table\ForProductsTable|\Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\CliOrderIten get($primaryKey, $options = [])
 * @method \App\Model\Entity\CliOrderIten newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\CliOrderIten[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CliOrderIten|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CliOrderIten|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CliOrderIten patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CliOrderIten[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\CliOrderIten findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CliOrderItensTable extends Table
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

        $this->setTable('cli_order_itens');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('CliOrders', [
            'foreignKey' => 'order_id',
            'joinType' => 'INNER'
        ]);

        $this->belongsTo('ForProducts', [
            'foreignKey' => 'product_id',
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
            ->integer('qtd')
            ->requirePresence('qtd', 'create')
            ->notEmpty('qtd');

        $validator
            ->decimal('value_uni')
            ->requirePresence('value_uni', 'create')
            ->notEmpty('value_uni');

        $validator
            ->decimal('value_total')
            ->requirePresence('value_total', 'create')
            ->notEmpty('value_total');

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
        $rules->add($rules->existsIn(['order_id'], 'CliOrders'));
        $rules->add($rules->existsIn(['product_id'], 'ForProducts'));

        return $rules;
    }
}