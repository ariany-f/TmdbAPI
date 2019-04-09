<?php
namespace App\Controller\Component;

use Cake\Datasource\ConnectionManager;
use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Client component
 */
class ClientComponent extends Component
{
    /**
     * Procura pelo cliente na base
     * ou add cliente
     * @param $cpf
     * @param $params
     * @return bool|array
     */
    public static function clientDb($cpf, $params = [
        'name' => '',
        'gender' => '',
        'cpf' => '',
        'password' => '',
        'estimated_assets' => '',
        'monthly_income' => '',
        'politycal_exposure' => '',
        'birth' => '',
        'occupation_id' => '',
        'marital_id' => '',
        'status_code' => ''
    ])
    {
        $output = false;

        try {
            $db = TableRegistry::getTableLocator()->get('CliCustomers');
            $query = $db
                ->find()
                ->select([
                    'CliCustomers.id'
                ])
                ->where([
                    'CliCustomers.cpf' => $cpf
                ])
                ->limit(1)
                ->toArray()
            ;
            $result = UtilsComponent::objToArray($query);

            if(count($result))
            {
                $result = current($result);
                $params['id'] = $result['id'];
                $data = $db->newEntity($params);
                if($db->save($data))
                {
                    $output = [
                        'data' => [
                            'id' => $result['id']
                        ],
                        'error' => ''
                    ];
                }
            }
            else
            {
                $data = $db->newEntity($params);
                if($db->save($data))
                {
                    $output = [
                        'data' => [
                            'id' => $data->id
                        ],
                        'error' => ''
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
     * Procura pelo email do cliente na base
     * Ou add o email
     * @param array $params
     * @return array|bool
     */
    public static function clientMail($params = [
        'customer_id' => '',
        'mail_type_id' => '',
        'mail' => '',
        'status_code' => ''
    ])
    {
        $output = false;

        try {
            $db = TableRegistry::getTableLocator()->get('CliCustomerMails');
            $query = $db
                ->find()
                ->select([
                    'CliCustomerMails.id',
                    'CliCustomerMails.mail',
                    'CliCustomerMails.mail_type_id'
                ])
                ->where([
                    'CliCustomerMails.mail' => $params['mail']
                ])
                ->limit(1)
                ->toArray()
            ;
            $result = UtilsComponent::objToArray($query);

            /**
             * Email nao encontrado, cadastra
             */
            if(!count($result))
            {
                $data = $db->newEntity($params);
                if($db->save($data))
                {
                    $output = [
                        'data' => [
                            'id' => $data->id
                        ],
                        'error' => ''
                    ];
                }
            }
            else
            {
                $result = current($result);

                /**
                 * mail_type diferente
                 */
                if($result['mail_type_id'] != $params['mail_type_id'])
                {
                    /**
                     * Atualiza o tipo de email
                     */
                    $update = $db->get($result['id']);
                    $update->mail_type_id = $params['mail_type_id'];

                    if($db->save($update))
                    {
                        $output = [
                            'data' => [
                                'id' => $result['id']
                            ],
                            'error' => ''
                        ];
                    }
                }
                else
                {
                    $output = [
                        'data' => [
                            'id' => $result['id']
                        ],
                        'error' => ''
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
     * Procura pelo telefone do cliente na base
     * ou add o telefone
     * @param array $params
     * @return array|bool
     */
    public static function clientPhone($params = [
        'customer_id' => '',
        'phone_type_id' => '',
        'phone' => '',
        'status_code' => ''
    ])
    {
        $output = false;

        try {
            $db = TableRegistry::getTableLocator()->get('CliCustomerPhones');
            $query = $db
                ->find()
                ->select([
                    'CliCustomerPhones.id',
                    'CliCustomerPhones.phone',
                    'CliCustomerPhones.phone_type_id'
                ])
                ->where([
                    'CliCustomerPhones.phone' => $params['phone'],
                    'CliCustomerPhones.customer_id' => $params['customer_id']
                ])
                ->limit(1)
                ->toArray()
            ;
            $result = UtilsComponent::objToArray($query);

            /**
             * Telefone nao encontrado, cadastra
             */
            if(!count($result))
            {
                $data = $db->newEntity($params);
                if($db->save($data))
                {
                    $output = [
                        'data' => [
                            'id' => $data->id
                        ],
                        'error' => ''
                    ];
                }
            }
            else
            {
                $result = current($result);

                /**
                 * phone_type diferente
                 */
                if($result['phone_type_id'] != $params['phone_type_id'])
                {
                    /**
                     * Atualiza o tipo de telefone
                     */
                    $update = $db->get($result['id']);
                    $update->phone_type_id = $params['phone_type_id'];

                    if($db->save($update))
                    {
                        $output = [
                            'data' => [
                                'id' => $result['id']
                            ],
                            'error' => ''
                        ];
                    }
                }
                else
                {
                    $output = [
                        'data' => [
                            'id' => $result['id']
                        ],
                        'error' => ''
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
     * Procura pelo documento do cliente na base
     * ou add o documento
     * @param array $params
     * @return array|bool
     */
    public static function clientDocument($params = [
        'customer_id' => '',
        'document_type_id' => '',
        'document_emitter_id' => '',
        'issue_date' => '',
        'number' => '',
        'status_code' => ''
    ])
    {
        $output = false;

        try {
            $db = TableRegistry::getTableLocator()->get('CliCustomerDocuments');
            $query = $db
                ->find()
                ->select([
                    'CliCustomerDocuments.id',
                    'CliCustomerDocuments.number',
                    'CliCustomerDocuments.document_type_id'
                ])
                ->where([
                    'CliCustomerDocuments.number' => $params['number'],
                    'CliCustomerDocuments.customer_id' => $params['customer_id']
                ])
                ->limit(1)
                ->toArray()
            ;
            $result = UtilsComponent::objToArray($query);

            /**
             * Documento nao encontrado, cadastra
             */
            if(!count($result))
            {
                $data = $db->newEntity($params);
                if($db->save($data))
                {
                    $output = [
                        'data' => [
                            'id' => $data->id
                        ],
                        'error' => ''
                    ];
                }
            }
            else
            {
                $result = current($result);

                /**
                 * document_type diferente
                 */
                if($result['document_type_id'] != $params['document_type_id'])
                {
                    /**
                     * Atualiza o tipo de documento
                     */
                    $update = $db->get($result['id']);
                    $update->document_type_id = $params['document_type_id'];

                    if($db->save($update))
                    {
                        $output = [
                            'data' => [
                                'id' => $result['id']
                            ],
                            'error' => ''
                        ];
                    }
                }
                else
                {
                    $output = [
                        'data' => [
                            'id' => $result['id']
                        ],
                        'error' => ''
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
     * Procura pelo endereco do cliente na base
     * ou add o endereco
     * @param array $params
     * @return array|bool
     */
    public static function clientAddress($params = [
        'address_ref_id' => '',
        'address_type_id' => '',
        'public_place' => '',
        'number' => '',
        'complement' => '',
        'neighborhood' => '',
        'city' => '',
        'state' => '',
        'country' => '',
        'zip' => '',
        'status_code' => ''
    ])
    {
        $output = false;

        try {
            $db = TableRegistry::getTableLocator()->get('ApisAddress');
            $query = $db
                ->find()
                ->select([
                    'ApisAddress.id',
                    'ApisAddress.address_type_id'
                ])
                ->where([
                    'ApisAddress.address_base_id' => 1,
                    'ApisAddress.address_ref_id' => $params['address_ref_id'],
                    'ApisAddress.address_type_id' => $params['address_type_id']
                ])
                ->limit(1)
                ->toArray()
            ;
            $result = UtilsComponent::objToArray($query);

            /**
             * Endereco nao encontrado, cadastra
             */
            if(!count($result))
            {
                $data = $db->newEntity($params);
                if($db->save($data))
                {
                    $output = [
                        'data' => [
                            'id' => $data->id
                        ],
                        'error' => ''
                    ];
                }
            }
            else
            {
                $result = current($result);
                $params['id'] = $result['id'];
                $data = $db->newEntity($params);
                if($db->save($data))
                {
                    $output = [
                        'data' => [
                            'id' => $data->id
                        ],
                        'error' => ''
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
     * Procura pelo cartao do cliente na base
     * ou add o cartao
     * @param array $params
     * @return array|bool
     */
    public static function clientCreditCard($params = [
        'credit_card_ref_id' => '',
        'card_brand_id' => '',
        'name' => '',
        'number' => '',
        'expire_month' => '',
        'expire_year' => '',
        'secure_code' => '',
        'status_code' => 2
    ])
    {
        $output = false;
        try {
            $db = TableRegistry::getTableLocator()->get('ApisCreditCard');
            $query = $db
                ->find()
                ->select([
                    'ApisCreditCard.id'
                ])
                ->where([
                    'ApisCreditCard.credit_card_base_id' => 1,
                    'ApisCreditCard.credit_card_ref_id' => $params['credit_card_ref_id'],
                    'ApisCreditCard.number' => $params['number']
                ])
                ->limit(1)
                ->toArray()
            ;
            $result = UtilsComponent::objToArray($query);

            /**
             * Cartao nao encontrado, cadastra
             */
            if(!count($result))
            {
                $data = $db->newEntity($params);
                if($db->save($data))
                {
                    $output = [
                        'data' => [
                            'id' => $data->id
                        ],
                        'error' => ''
                    ];
                }
            }
            else
            {
                $result = current($result);
                $params['id'] = $result['id'];
                $data = $db->newEntity($params);
                if($db->save($data))
                {
                    $output = [
                        'data' => [
                            'id' => $result['id']
                        ],
                        'error' => ''
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
     * Retorna todos os dados do cliente
     * @param array $params
     * @return array|bool
     */
    public static function clientFullView($params = ['id' => '', 'cpf' => ''])
    {
        $output = false;
        if($params['id'] != '' and $params['cpf'] != '')
        {
            $output = [
                'data' => [],
                'error' => [
                    0 => 'Informe apenas id ou cpf'
                ]
            ];
            goto error;
        }

        /**
         * Verificar police do item
         */
        $customer_db = TableRegistry::getTableLocator()->get('CliCustomers');
        $query = $customer_db
            ->find()
            ->select([
                'CliCustomers.id',
                'CliCustomers.name',
                'CliCustomers.cpf',
                'CliCustomers.birth',
                'CliCustomers.gender',
                'CliCustomers.estimated_assets',
                'CliCustomers.monthly_income',
                'CliCustomers.politycal_exposure',
                'ApisMaritals.id',
                'ApisMaritals.name',
                'ApisOccupations.id',
                'ApisOccupations.name',
                'ApisStatus.id',
                'ApisStatus.name',
                'CliCustomers.created'
            ])
            ->join([
                'table' => 'apis_maritals',
                'alias' => 'ApisMaritals',
                'type' => 'INNER',
                'conditions' => 'ApisMaritals.id = CliCustomers.marital_id'
            ])
            ->join([
                'table' => 'apis_occupations',
                'alias' => 'ApisOccupations',
                'type' => 'INNER',
                'conditions' => 'ApisOccupations.id = CliCustomers.occupation_id'
            ])
            ->join([
                'table' => 'apis_status',
                'alias' => 'ApisStatus',
                'type' => 'INNER',
                'conditions' => [
                    'ApisStatus.status_origin_id = CliCustomers.status_origin_id',
                    'ApisStatus.status_code = CliCustomers.status_code'
                ]
            ])
            ->where([
                'OR' => [
                    'CliCustomers.id' => $params['id'],
                    'CliCustomers.cpf' => $params['cpf'],
                ]
            ])
            ->toArray()
        ;
        $customer = UtilsComponent::objToArray($query);

        /**
         * Encontrou cliente
         * segue
         */
        if(!count($customer))
        {
            goto error;
        }
        $customer = current($customer);

        $connection = ConnectionManager::get('default');
        $mail = $connection
            ->execute("CALL customer_mail(:customer_id, 1);", [
                    'customer_id' => $customer['id']
                ]
            )
            ->fetchAll('assoc');

        $phone = $connection
            ->execute("CALL customer_phone(:customer_id, 1);", [
                    'customer_id' => $customer['id']
                ]
            )
            ->fetchAll('assoc');

        $document = $connection
            ->execute("CALL customer_document(:customer_id, 1);", [
                    'customer_id' => $customer['id']
                ]
            )
            ->fetchAll('assoc');

        $address = $connection
            ->execute("CALL customer_address(:customer_id, 1);", [
                    'customer_id' => $customer['id']
                ]
            )
            ->fetchAll('assoc');

        $customer = [
            'id' => $customer['id'],
            'name' => $customer['name'],
            'mail' => $mail,
            'cpf' => $customer['cpf'],
            'estimated_assets' => $customer['estimated_assets'],
            'monthly_income' => $customer['monthly_income'],
            'politycal_exposure' => $customer['politycal_exposure'],
            'phone' => $phone,
            'birth' => $customer['birth'],
            'document' => $document,
            'gender' => $customer['gender'],
            'occupation' => [
                'id' => $customer['ApisOccupations']['id'],
                'name' => $customer['ApisOccupations']['name']
            ],
            'marital' => [
                'id' => $customer['ApisMaritals']['id'],
                'name' => $customer['ApisMaritals']['name']
            ],
            'addresses' => $address,
            'status' => [
                'id' => $customer['ApisStatus']['id'],
                'name' => $customer['ApisStatus']['name']
            ],
            'created' => $customer['created']
        ];

        $output = [
            'data' => $customer,
            'error' => []
        ];

        error:
        return $output;
    }
}