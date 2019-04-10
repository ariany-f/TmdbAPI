<?php
namespace App\Controller;

use App\Controller\Component\UtilsComponent;
use App\Controller\Component\ClientComponent;
use App\Controller\Component\ProductComponent;
use App\Controller\Component\OrderComponent;
use Cake\Utility\Security;

/**
 * OrderBuyb2c Controller
 */
class OrderBuyb2cController extends AppController
{
    /**
     * Load other component
     * @var array
     */
    public $components = [
        'Order'
    ];

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
     * Add order
     * @param array $post
     * @throws \Exception
     */
    public function add($post = [])
    {
        /**
         * Validando o POST
         */
        $buys = $this->checkFieldRequest($post, 'buys', true, 'array');

        /**
         * Loops nas orders
         */
        $buys_ok = [];
        foreach ($buys as $key_buy => $values)
        {
            $external_code = $this->checkFieldRequest($values, 'order_id', true);
            $company_id = $this->checkFieldRequest($values, 'company_id', true, 'company');
            $product = $this->checkFieldRequest($values, 'product', true, 'array');

            /**
             * Payment, avalia agora devido ao vinculo com com produto
             */
            $payment = $this->checkFieldRequest($values, 'payment', true, 'array');
            $credit_card = $this->checkFieldRequest($payment, 'credit_card', true, 'array');
            $payment_credit_card_name = $this->checkFieldRequest($credit_card, 'name', true);
            $payment_credit_card_brand = $this->checkFieldRequest($credit_card, 'brand', true);
            $payment_credit_card_brand_id = null;

            /**
             * Valida produto
             */
            $product_ok = [];
            foreach ($product as $product_values)
            {
                $product_check = ProductComponent::productCheck($company_id, $product_values['id']);
                if(isset($product_check['data']['id']))
                {
                    $product_ok[] = [
                        'brand_id' =>  $product_check['data']['brand_id'],
                        'brand_name' => $product_check['data']['brand_name'],
                        'type_id' => $product_check['data']['type_id'],
                        'type_name' => $product_check['data']['type_name'],
                        'product_id' => $product_values['id'],
                        'name' => $product_check['data']['name'],
                        'qtd' => 1,
                        'value_uni' => $product_check['data']['value']
                    ];

                    $payment_credit_card_brand_id = UtilsComponent::checkListCreditCardProduct($product_values['id'], $payment_credit_card_brand);
                    if(!$payment_credit_card_brand_id)
                    {
                        $this->errorSimpleOutput([
                            'message' => 'Requisição inválida, contate o administrador',
                            'error' => 'O valor ' . $payment_credit_card_brand . ', do parâmetro brand não é um cartão aceito'
                        ]);
                    }
                }
                else
                {
                    $this->errorSimpleOutput([
                        'message' => 'Requisição inválida, contate o administrador',
                        'error' => 'O produto de id ' . $product_values['id'] . ', não está disponível'
                    ]);
                }
            }

            /**
             * Continua payment cartao
             */
            $payment_credit_card_number = $this->checkFieldRequest($credit_card, 'number', true, 'numeric');
            $payment_credit_card_expire_month = $this->checkFieldRequest($credit_card, 'expire_month', true, 'month');
            $payment_credit_card_expire_year = $this->checkFieldRequest($credit_card, 'expire_year', true, 'year');
            if(UtilsComponent::cardExpired($payment_credit_card_expire_month, $payment_credit_card_expire_year))
            {
                $this->errorSimpleOutput([
                    'message' => 'Requisição inválida, contate o administrador',
                    'error' => 'O cartão ' . UtilsComponent::mascara('#### #### #### ####', $payment_credit_card_number) . ' está vencido (' . $payment_credit_card_expire_month . '/' . $payment_credit_card_expire_year . ')'
                ]);
            }
            $payment_credit_card_secure_code = $this->checkFieldRequest($credit_card, 'secure_code', true, 'numeric');

            $client = $this->checkFieldRequest($values, 'client', true, 'array');
            $client_name = $this->checkFieldRequest($client, 'name', true);
            $client_mail = $this->checkFieldRequest($client, 'mail', true, 'array');

            $client_mail_ok = [];
            foreach ($client_mail as $mail)
            {
                $type = $this->checkFieldRequest($mail, 'type', true, 'integer');
                $mail = $this->checkFieldRequest($mail, 'mail', true, 'mail');
                if(!UtilsComponent::mailType($type))
                {
                    $this->errorSimpleOutput([
                        'message' => 'Tipo de e-mail não cadastro',
                        'error' => 'O tipo de e-mail ' . $type . ', não consta em nossa base'
                    ]);
                }

                $client_mail_ok[] = [
                    'type' => $type,
                    'mail' => mb_strtolower($mail, 'UTF-8')
                ];
            }

            $client_cpf = $this->checkFieldRequest($client, 'cpf', true, 'cpf');
            $client_estimated_assets = $this->checkFieldRequest($client, 'estimated_assets', true, 'integer');
            $client_monthly_income = $this->checkFieldRequest($client, 'monthly_income', true, 'integer');
            $client_politycal_exposure = $this->checkFieldRequest($client, 'politycal_exposure', true, 'boolean');
            $client_phone = $this->checkFieldRequest($client, 'phone', true, 'array');

            $client_phone_ok = [];
            foreach ($client_phone as $phone)
            {
                $type = $this->checkFieldRequest($phone, 'type', true, 'integer');
                $number = $this->checkFieldRequest($phone, 'number', true, 'phone');
                if(!UtilsComponent::phoneType($type))
                {
                    $this->errorSimpleOutput([
                        'message' => 'Tipo de telefone não cadastro',
                        'error' => 'O tipo de telefone ' . $type . ', não consta em nossa base'
                    ]);
                }

                $client_phone_ok[] = [
                    'type' => $type,
                    'phone' => $number
                ];
            }

            $client_birth = $this->checkFieldRequest($client, 'birth', true, 'date');
            $client_document = $this->checkFieldRequest($client, 'document', true, 'array');

            $client_document_ok = [];
            foreach ($client_document as $document)
            {
                $type = $this->checkFieldRequest($document, 'type', true, 'integer');
                if(!UtilsComponent::documentType($type))
                {
                    $this->errorSimpleOutput([
                        'message' => 'Tipo de documento não cadastro',
                        'error' => 'O tipo de documento ' . $type . ', não consta em nossa base'
                    ]);
                }

                $document_number = $this->checkFieldRequest($document, 'number', true, 'document');
                $document_issue_date = $this->checkFieldRequest($document, 'issue_date', true, 'date');
                $document_emitter = $this->checkFieldRequest($document, 'emitter', true, 'emitter');

                $client_document_ok[] = [
                    'type' => $type,
                    'number' => $document_number,
                    'issue_date' => $document_issue_date,
                    'emitter' => $document_emitter
                ];
            }

            $client_gender = $this->checkFieldRequest($client, 'gender', true, 'gender');
            $client_occupation_id = $this->checkFieldRequest($client, 'occupation_id', true, 'occupation');
            $client_marital_id = $this->checkFieldRequest($client, 'marital_id', true, 'marital');
            $client_password = $this->checkFieldRequest($client, 'password', true, 'password', 8, 3);
            $client_addresses = $this->checkFieldRequest($client, 'addresses', true, 'array');

            $client_addresses_ok = [];
            foreach ($client_addresses as $address)
            {
                $type = $this->checkFieldRequest($address, 'type', true, 'integer');
                if(!UtilsComponent::addressType($type))
                {
                    $this->errorSimpleOutput([
                        'message' => 'Tipo de endereço não cadastro',
                        'error' => 'O tipo de endereço ' . $type . ', não consta em nossa base'
                    ]);
                }

                $public_place = $this->checkFieldRequest($address, 'public_place', true);
                $number = $this->checkFieldRequest($address, 'number', true);
                $complement = $this->checkFieldRequest($address, 'complement', false);
                $neighborhood = $this->checkFieldRequest($address, 'neighborhood', true);
                $city = $this->checkFieldRequest($address, 'city', true);
                $state = $this->checkFieldRequest($address, 'state', true);
                $country = $this->checkFieldRequest($address, 'country', true);
                $zip = $this->checkFieldRequest($address, 'zip', true);
                $client_addresses_ok[] = [
                    'type' => $type,
                    'public_place' => UtilsComponent::capitalize($public_place),
                    'number' => $number,
                    'complement' => $complement,
                    'neighborhood' => UtilsComponent::capitalize($neighborhood),
                    'city' => UtilsComponent::capitalize($city),
                    'state' => mb_strtoupper($state, 'UTF-8'),
                    'country' => $country,
                    'zip' => $zip
                ];
            }

            /**
             * Array final validacao inicial
             */
            $buys_ok[$key_buy] = [
                'external_code' => $external_code,
                'company_id' => $company_id,
                'product' => $product_ok,
                'client' => [
                    'name' => UtilsComponent::capitalize($client_name),
                    'mail' => $client_mail_ok,
                    'cpf' => $client_cpf,
                    'estimated_assets' => $client_estimated_assets,
                    'monthly_income' => $client_monthly_income,
                    'politycal_exposure' => $client_politycal_exposure,
                    'phone' => $client_phone_ok,
                    'birth' => $client_birth,
                    'document' => $client_document_ok,
                    'gender' => $client_gender,
                    'occupation_id' => $client_occupation_id,
                    'marital_id' => $client_marital_id,
                    'password' => $client_password,
                    'addresses' => $client_addresses_ok
                ],
                'payment' => [
                    'credit_card' => [
                        'name' => UtilsComponent::capitalize($payment_credit_card_name),
                        'brand_id' => $payment_credit_card_brand_id,
                        'brand_name' => $payment_credit_card_brand,
                        'number' => $payment_credit_card_number,
                        'expire_month' => $payment_credit_card_expire_month,
                        'expire_year' => $payment_credit_card_expire_year,
                        'secure_code' => $payment_credit_card_secure_code
                    ]
                ]
            ];
        }
        /*******************************************************************************************************
         * Dados enviados validados
         * Novo loop para realizar
         * as transacoes.
         */
        $errors = [];
        $errors_ti = [];
        $output =[];
        foreach ($buys_ok as $key_buy => $buy)
        {
            /***************************************************************************************************
             * Add cliente na base ou edit se existir
             */
            $cli_customers = [
                'name' => $buy['client']['name'],
                'gender' => $buy['client']['gender'],
                'cpf' => $buy['client']['cpf'],
                'password' => $password = Security::hash($buy['client']['password'], 'md5', true),
                'estimated_assets' => $buy['client']['estimated_assets'],
                'monthly_income' => $buy['client']['monthly_income'],
                'politycal_exposure' => $buy['client']['politycal_exposure'],
                'birth' => $buy['client']['birth'],
                'occupation_id' => $buy['client']['occupation_id'],
                'marital_id' => $buy['client']['marital_id'],
                'status_code' => 2
            ];

            /**
             * Add ou atualiza e retorna o id
             * Se erro gera log
             */
            $customer = ClientComponent::clientDb($buy['client']['cpf'], $cli_customers);

            if(!isset($customer['data']['id']))
            {
                $customer_error[3] = 'Erro ao cadastrar o cliente, contate o administrador';

                /**
                 * Erro para o log de response
                 */
                $errors[$key_buy]['client_db']['cpf'] = $buy['client']['cpf'];
                $errors[$key_buy]['client_db']['error'] = $customer_error;

                /**
                 *  Erro com mais detalhes para a TI
                 */
                $errors_ti[$key_buy]['client_db']['cpf'] = $buy['client']['cpf'];
                $errors_ti[$key_buy]['client_db']['error'] = $customer_error;
                $errors_ti[$key_buy]['client_db']['exception'] = $customer;

                /**
                 * Passa para o proximo registro
                 */
                continue;
            }

            /***************************************************************************************************
             * Add email do cliente
             * Loop dos emails
             */
            $mail = false;
            $mail_final = null;
            foreach ($buy['client']['mail'] as $mails)
            {
                $cli_customer_mails = [
                    'customer_id' => $customer['data']['id'],
                    'mail_type_id' => $mails['type'],
                    'mail' => $mails['mail'],
                    'status_code' => 2
                ];

                /**
                 * Add email e retorna o id
                 * ou atualiza o email
                 */
                $customer_mail = ClientComponent::clientMail($cli_customer_mails);
                if(!isset($customer_mail['data']['id']))
                {
                    $customer_mail_error[4] = 'Erro ao cadastrar o e-mail, contate o administrador';

                    /**
                     * Erro para o log de response
                     */
                    $errors[$key_buy]['client_mail']['mail'] = $mails['mail'];
                    $errors[$key_buy]['client_mail']['error'] = $customer_mail_error;

                    /**
                     *  Mais detalhes do erro para a TI
                     */
                    $errors_ti[$key_buy]['client_mail']['mail'] = $mails['mail'];
                    $errors_ti[$key_buy]['client_mail']['error'] = $customer_mail_error;
                    $errors_ti[$key_buy]['client_mail']['exception'] = $customer_mail;
                }
                else
                {
                    $mail_final = $mails['mail'];
                    $mail = true;
                }
            }

            /**
             * Cliente ficou sem email
             * anula registro, retorna erro
             */
            if(!$mail)
            {
                continue;
            }

            /***************************************************************************************************
             * Add telefone do cliente
             * Loop dos telefone
             */
            $phone = false;
            $phone_final = null;
            foreach ($buy['client']['phone'] as $phones)
            {
                $cli_customer_phone = [
                    'customer_id' => $customer['data']['id'],
                    'phone_type_id' => $phones['type'],
                    'phone' => $phones['phone'],
                    'status_code' => 2
                ];

                /**
                 * Add phone e retorna o id
                 * ou atualiza o phone
                 */
                $customer_phone = ClientComponent::clientPhone($cli_customer_phone);
                if(!isset($customer_phone['data']['id']))
                {
                    $customer_phone_error[5] = 'Erro ao cadastrar o telefone, contate o administrador';

                    /**
                     * Erro para o log de response
                     */
                    $errors[$key_buy]['client_phone']['phone'] = $phones['phone'];
                    $errors[$key_buy]['client_phone']['error'] = $customer_phone_error;

                    /**
                     *  Mais detalhes do erro para a TI
                     */
                    $errors_ti[$key_buy]['client_phone']['phone'] = $phones['phone'];
                    $errors_ti[$key_buy]['client_phone']['error'] = $customer_phone_error;
                    $errors_ti[$key_buy]['client_phone']['exception'] = $customer_phone;
                }
                else
                {
                    $phone_final = $phones['phone'];
                    $phone = true;
                }
            }

            /**
             * Cliente ficou sem telefone
             * anula registro, retorna erro
             */
            if(!$phone)
            {
                continue;
            }

            /***************************************************************************************************
             * Add documento do cliente
             * Loop dos documento
             */
            $document = false;
            foreach ($buy['client']['document'] as $documents)
            {
                $cli_customer_document = [
                    'customer_id' => $customer['data']['id'],
                    'document_type_id' => $documents['type'],
                    'document_emitter_id' => $documents['emitter'],
                    'issue_date' => $documents['issue_date'],
                    'number' => $documents['number'],
                    'status_code' => 2
                ];

                /**
                 * Add phone e retorna o id
                 * ou atualiza o document
                 */
                $customer_document = ClientComponent::clientDocument($cli_customer_document);
                if(!isset($customer_document['data']['id']))
                {
                    $customer_document_error[6] = 'Erro ao cadastrar o documento, contate o administrador';

                    /**
                     * Erro para o log de response
                     */
                    $errors[$key_buy]['client_document']['document'] = $documents['number'];
                    $errors[$key_buy]['client_document']['error'] = $customer_document_error;

                    /**
                     *  Mais detalhes do erro para a TI
                     */
                    $errors_ti[$key_buy]['client_document']['document'] = $documents['number'];
                    $errors_ti[$key_buy]['client_document']['error'] = $customer_document_error;
                    $errors_ti[$key_buy]['client_document']['exception'] = $customer_document;
                }
                else
                {
                    $document = true;
                }
            }

            /**
             * Cliente ficou sem documento
             * anula registro, retorna erro
             */
            if(!$document)
            {
                continue;
            }

            /***************************************************************************************************
             * Add endereco do cliente
             * Loop dos endereco
             */
            $address = false;
            $address_final = null;
            foreach ($buy['client']['addresses'] as $addresss)
            {
                $cli_customer_addresses = [
                    'address_ref_id' => $customer['data']['id'],
                    'address_type_id' => $addresss['type'],
                    'public_place' => $addresss['public_place'],
                    'number' => $addresss['number'],
                    'complement' => $addresss['complement'],
                    'neighborhood' => $addresss['neighborhood'],
                    'city' => $addresss['city'],
                    'state' => $addresss['state'],
                    'country' => $addresss['country'],
                    'zip' => $addresss['zip'],
                    'status_code' => 2
                ];

                /**
                 * Add endereco e retorna o id
                 * ou atualiza o endereco
                 */
                $customer_addresses = ClientComponent::clientAddress($cli_customer_addresses);
                if(!isset($customer_addresses['data']['id']))
                {
                    $customer_address_error[7] = 'Erro ao cadastrar o endereco, contate o administrador';

                    /**
                     * Erro para o log de response
                     */
                    $errors[$key_buy]['client_address']['public_place'] = $addresss['public_place'];
                    $errors[$key_buy]['client_address']['error'] = $customer_address_error;

                    /**
                     *  Mais detalhes do erro para a TI
                     */
                    $errors_ti[$key_buy]['client_address']['public_place'] = $addresss['public_place'];
                    $errors_ti[$key_buy]['client_address']['error'] = $customer_address_error;
                    $errors_ti[$key_buy]['client_address']['exception'] = $customer_addresses;
                }
                else
                {
                    $address_final = $cli_customer_addresses;
                    $address = true;
                }
            }

            /**
             * Cliente ficou sem endereco
             * anula registro, retorna erro
             */
            if(!$address)
            {
                continue;
            }

            /***************************************************************************************************
             * Add cartao do cliente
             * Loop dos cartao
             */
            $card = false;
            $card_final = null;
            $cli_customer_payment_credit_card = [
                'credit_card_ref_id' => $customer['data']['id'],
                'card_brand_id' => $buy['payment']['credit_card']['brand_id'],
                'card_brand_name' => $buy['payment']['credit_card']['brand_name'],
                'name' => $buy['payment']['credit_card']['name'],
                'number' => $buy['payment']['credit_card']['number'],
                'expire_month' => $buy['payment']['credit_card']['expire_month'],
                'expire_year' => $buy['payment']['credit_card']['expire_year'],
                'secure_code' => $buy['payment']['credit_card']['secure_code'],
                'status_code' => 2
            ];

            $customer_creditcard = ClientComponent::clientCreditCard($cli_customer_payment_credit_card);
            if(!isset($customer_creditcard['data']['id']))
            {
                $customer_creditcard_error[8] = 'Erro ao cadastrar o cartão, contate o administrador';

                /**
                 * Erro para o log de response
                 */
                $errors[$key_buy]['client_creditcard']['creditcard'] = $buy['payment']['credit_card']['number'];
                $errors[$key_buy]['client_creditcard']['error'] = $customer_creditcard_error;

                /**
                 *  Mais detalhes do erro para a TI
                 */
                $errors_ti[$key_buy]['client_creditcard']['creditcard'] = $buy['payment']['credit_card']['number'];
                $errors_ti[$key_buy]['client_creditcard']['error'] = $customer_creditcard_error;
                $errors_ti[$key_buy]['client_creditcard']['exception'] = $customer_creditcard;
            }
            else
            {
                $cli_customer_payment_credit_card['id'] = $customer_creditcard['data']['id'];
                $card_final = $cli_customer_payment_credit_card;
                $card = true;
            }

            /**
             * Cliente ficou sem cartao
             * anula registro, retorna erro
             */
            if(!$card)
            {
                continue;
            }

            /***************************************************************************************************
             * Order add
             * Registro na base de pedidos
             */
            $buy_order = false;

            $params = [
                'company_id' => $buy['company_id'],
                'external_code' => $buy['external_code'],
                'customer_id' => $customer['data']['id'],
                'cli_order_itens' => $buy['product']
            ];

            $buy_result = OrderComponent::insuranceAdd($params);
            if(!isset($buy_result['data']['id']))
            {
                /**
                 * Erro para o log de response
                 */
                $errors[$key_buy]['buy_order']['order_id'] = $buy['external_code'];
                $errors[$key_buy]['buy_order']['error'] = $buy_result['error'];

                /**
                 *  Mais detalhes do erro para a TI
                 */
                $errors_ti[$key_buy]['buy_order']['order_id'] = $buy['external_code'];
                $errors_ti[$key_buy]['buy_order']['error'] = $buy_result;
                $errors_ti[$key_buy]['buy_order']['exception'] = $buy_result;
            }
            else
            {
                $buy_order = true;
            }

            /**
             * Cliente ficou sem order
             * anula registro, retorna erro
             */
            if(!$buy_order)
            {
                continue;
            }

            /***************************************************************************************************
             * Gerando pagamento
             * Base
             * Gateway ativo
             */
            $buy_pay = false;

            $pay_request = [
                'order_id' => $buy_result['data']['id'],
                'total' => $buy_result['data']['total'],
                'itens' => $buy['product'],
                'customer' => [
                    'id' => $customer['data']['id'],
                    'name' => $buy['client']['name'],
                    'mail' =>  $mail_final,
                    'cpf' => $buy['client']['cpf'],
                    'phone' => $phone_final,
                    'birth' => $buy['client']['birth'],
                    'address' => $address_final
                ],
                'payment' => [
                    'credit_card' => $card_final
                ]
            ];

            $payment = new GatewayController();
            $pay_result = $payment->createSignature($pay_request);

            if(count($pay_result['error']))
            {
                /**
                 * Erro para o log de response
                 */
                $errors[$key_buy]['buy_pay']['order_id'] = $buy['external_code'];
                $errors[$key_buy]['buy_pay']['error'] = $pay_result['error'];

                /**
                 *  Mais detalhes do erro para a TI
                 */
                $errors_ti[$key_buy]['buy_pay']['order_id'] = $buy['external_code'];
                $errors_ti[$key_buy]['buy_pay']['error'] = $pay_result;
                $errors_ti[$key_buy]['buy_pay']['exception'] = $pay_result;
            }
            else
            {
                $buy_pay = true;
            }

            /**
             * Order ficou sem pagamento
             * retorna erro
             */
            if(!$buy_pay)
            {
                continue;
            }
            /***************************************************************************************************
             * Fim dos inserts
             */

            /**
             * Saida de order ok
             */
            $order = $this->Order->orderView($buy_result['data']['id']);
            $order = current($order);
            $output[$key_buy] = [
                'order_id' => $order['external_code'],
                'beginning' => $order['beginning'],
                'expiration' => $order['expiration'],
                'due_monthly' => $order['due_monthly'],
                'customer_cpf' => $order['customer']['cpf'],
                'itens' => $order['itens'],
                'credit_card_active' => [
                    'id' => $order['credit_card']['id'],
                    'brand' => $order['credit_card']['brand'],
                    'name' => $order['credit_card']['name'],
                    'number' => $order['credit_card']['number'],
                    'expire_month' => $order['credit_card']['expire_month'],
                    'expire_year' => $order['credit_card']['expire_year']
                ],
                'payment' => $order['payment'],
                'status_id' => $order['status_id'],
                'status_name' => $order['status_name'],
                'created' => $order['created']
            ];
        }

        /**
         * Debug se erros na compra
         */
        if($errors_ti)
        {
            $this->mailDebug([
                'subject' => 'Apis Store - Erro na compra',
                'error' => $errors_ti
            ]);
        }

        $this->message = 'Resultado da compra';
        $this->code = 200;
        $this->success = true;
        $this->data = [
            'count' => count($output),
            'result' => $output,
            'errors' => $errors
        ];
        $this->generateOutput();
    }

    /**
     * Edita o pedido
     * Por hora troca o cartao
     * @param array $post
     * @throws \Exception
     */
    public function edit($post = [])
    {
        /**
         * Para saida
         */
        $success = false;
        $result = [];
        $count = 0;

        /**
         * Valida post
         * verifica no banco
         */
        $external_code = $this->checkFieldRequest($post, 'order_id', true, 'integer');
        $payment = $this->checkFieldRequest($post, 'payment', true, 'array');
        $credit_card = $this->checkFieldRequest($payment, 'credit_card', true, 'array');
        $name = $this->checkFieldRequest($credit_card, 'name', true);
        $brand = $this->checkFieldRequest($credit_card, 'brand', true);
        $number = $this->checkFieldRequest($credit_card, 'number', true);
        $expire_month = $this->checkFieldRequest($credit_card, 'expire_month', true, 'month');
        $expire_year = $this->checkFieldRequest($credit_card, 'expire_year', true, 'year');
        $secure_code = $this->checkFieldRequest($credit_card, 'secure_code', true);

        $this->loadComponent('Order');
        $order = $this->Order->orderView(null, $external_code, $this->Auth->user('company_id'));
        if(count($order['error']))
        {
            $errors = $order['error'];
            goto error;
        }
        $order = $order['data'];

        /**
         * So e possivel trocar o cartao nos casos
         * 4 - A order foi paga
         * 6 - Falha na recorrencia
         */
        if(!in_array($order['status_id'], [4, 5, 6]))
        {
            $errors = [
                'Status da order não permite a troca do cartão'
            ];
            goto error;
        }

        /**
         * Valida se cartao e aceito para o tipo de produto
         */
        $credit_card_brand_id = UtilsComponent::checkListCreditCardProduct($order['itens'][0]['product_id'], $brand);
        if(!$credit_card_brand_id)
        {
            $errors = [
                'O produto ' . $order['itens'][0]['product_name'] . ', não pode ser pago com a bandeira ' . $brand . '.'
            ];
            goto error;
        }

        /**
         * Associa cartao ao cliente
         */
        $params = [
            'credit_card_ref_id' => $order['customer']['id'],
            'card_brand_id' => $credit_card_brand_id,
            'name' => UtilsComponent::capitalize($name),
            'number' => $number,
            'expire_month' => $expire_month,
            'expire_year' => $expire_year,
            'secure_code' => $secure_code,
            'status_code' => 2
        ];
        $credit_bd = ClientComponent::clientCreditCard($params);

        if(!isset($credit_bd['data']['id']))
        {
            $errors = [
                'Erro ao associar o cartao ao cliente'
            ];
            goto error;
        }
        $credit_card_id = $credit_bd['data']['id'];

        /**
         * Compara com cartao ja associado a order
         */
        if($credit_card_id == $order['credit_card']['id'])
        {
            $errors = [
                'Esse cartão ' . UtilsComponent::mascara('#### #### #### ####', $number) . ', já está associado a order ' . $order['customer']['id']
            ];
            goto error;
        }

        /**
         * Troca cartao no gateway
         */
        $params = [
            'order_id' => $order['id'],
            'client_id' => $order['customer']['id'],
            'credit_card_active' => $order['credit_card_active'],
            'gateway_id' => $order['gateway_id'],
            'payment' => [
                'credit_card' => [
                    'id' => $credit_card_id,
                    'card_brand_id' => $credit_card_brand_id,
                    'card_brand_name' => $brand,
                    'name' => $name,
                    'number' => $number,
                    'expire_month' => $expire_month,
                    'expire_year' => $expire_year,
                    'secure_code' => $secure_code,
                    'status_code' => 2
                ]
            ]
        ];
        $payment = new GatewayController();
        $change_result = $payment->changeCreditCardSignature($params);
        if(!$change_result['data'])
        {
            $errors = [
                'Não foi possível trocar o cartão, tente novamente'
            ];
            goto error;
        }

        /**
         * Saida de sucesso
         */
        $success = true;
        $result = [
            'Cartão alterado com sucesso'
        ];
        $errors = [];
        $count = 1;

        /**
         * Saida client
         */
        error:
        $this->message = 'Atualizar order';
        $this->code = 200;
        $this->success = $success;
        $this->data = [
            'count' => $count,
            'result' => $result,
            'errors' => $errors
        ];
        $this->generateOutput();
    }

    /**
     * @param array $post
     * @throws \Exception
     */
    public function delete($post = [])
    {
        /**
         * Para saida
         */
        $success = false;
        $result = [];
        $errors = [];
        $count = 0;

        /**
         * Valida post order_id
         * verifica no banco
         */
        $external_code = $this->checkFieldRequest($post, 'order_id', true, 'integer');
        $this->loadComponent('Order');
        $order = $this->Order->orderView(null, $external_code);
        if(count($order['error']))
        {
            $errors = $order['error'];
            goto error;
        }
        $order = $order['data'];

        /**
         * Order ja foi cancelada
         */
        if(in_array($order['status_id'], [7, 8]))
        {
            $errors = [
                'Order ' . $external_code . ' cancelada ou expirada'
            ];
            goto error;
        }

        /**
         * Valida cliente e produto
         */
        $client = $this->checkFieldRequest($post, 'client', true, 'array');
        $client_product = [];
        foreach ($client as $client_value)
        {
            $cpf =$this->checkFieldRequest($client_value, 'cpf', true, 'cpf');
            $client_product[$cpf] = [];
            $product = $this->checkFieldRequest($client_value, 'product', true, 'array');
            foreach ($product as $product_value)
            {
                $product_id = $this->checkFieldRequest($product_value, 'id', true, 'integer');
                $client_product[$cpf]['product'][] = $product_id;
            }
        }

        /**
         * Loop nos itens
         */
        $cpf_ok = [];
        $product_ok = [];
        foreach ($order['itens'] as $item)
        {
            /**
             * loop para cancelar as  polices
             */
            $cpf_ok = [];
            $product_ok = [];
            foreach ($item['police'] as $key => $police)
            {
                /**
                 * Consta cpf
                 */
                if(array_key_exists($police['customer']['cpf'], $client_product))
                {
                    $cpf_ok[] = $police['customer']['cpf'];

                    /**
                     * Consta product
                     */
                    if(in_array($item['product_id'], $client_product[$police['customer']['cpf']]['product']))
                    {
                        $product_ok[$police['customer']['cpf']] = $item['product_id'];
                        /**
                         * Load police
                         */
                        $this->loadComponent('Police');
                        $params = [
                            'police_id' => $police['id'],
                            'status_code' => 6
                        ];
                        $this->Police->delete($params);

                        $result[$key] = [
                            'order_id' => $order['id'],
                            'cpf' => $police['customer']['cpf'],
                            'product_id' => $item['product_id'],
                            'stataus' => 'Cancelado com sucesso'
                        ];
                        $count++;
                        $success = true;
                    }
                }
            }

            /**
             * loop para montar check de pessoa produto
             */
            $cpf_ok = [];
            $product_ok = [];
            foreach ($order['itens'] as $product)
            {
                $cpf_ok[] = $order['customer']['cpf'];
                $product_ok[$order['customer']['cpf']] = $product['product_id'];
            }
        }

        /**
         * Loop dos logs
         */
        foreach ($client_product as $key => $value)
        {
            if(!in_array($key, $cpf_ok))
            {
                $errors[] = [
                    'order_id' => $order['id'],
                    'cpf' => $key,
                    'status' => 'Este cpf não consta neste pedido'
                ];
            }
            else
            {
                foreach ($value['product'] as $product)
                {
                    if(!in_array($product, $product_ok))
                    {
                        $errors[] = [
                            'order_id' => $order['id'],
                            'cpf' => $key,
                            'product_id' => $product,
                            'status' => 'Este product_id não consta neste pedido'
                        ];
                    }
                }
            }
        }

        /**
         * Cancelar order no Gateway
         */
        $params = [
            'order_id' => $order['id'],
            'gateway_id' => $order['gateway_id']
        ];
        $payment = new GatewayController();
        $change_result = $payment->cancelSignature($params);
        if(!$change_result['data'])
        {
            $errors = [
                'Não foi possível efetuar uma nova tentativa de pagamento, tente novamente mais tarde, lembrando que são permitidas até 3 tentativas por dia'
            ];
            goto error;
        }

        /**
         * Atualiza a order
         */
        $params = [
            'order_id' => $order['id'],
            'beginning' => $order['beginning'],
            'expiration' => date('Y-m-d'),
            'due_monthly' => date('Y-m-d'),
            'status_code' => 8
        ];
        if($this->Order->updated($params))
        {
            $success = true;
            $result = [
                'Order cancelada com sucesso'
            ];
            $errors = [];
            $count = 1;
        }
        else
        {
            $errors = [
                'Não foi possível  cancelar a ' . $external_code . ', contate o adminsitrador'
            ];

            $this->App->mailDebug([
                'subject' => 'Apis OrderBuyb2c - Cancelar order',
                'error' => [
                    'request' => $post,
                    'params' => $params
                ]
            ]);
        }

        /**
         * Saida client
         */
        error:
        $this->message = 'Cancelar order';
        $this->code = 200;
        $this->success = $success;
        $this->data = [
            'count' => $count,
            'result' => $result,
            'errors' => $errors
        ];
        $this->generateOutput();
    }

    /**
     * Para uma nova tentativa de pagamento
     * @param $post
     * @throws \Exception
     */
    public function retry($post)
    {
        /**
         * Para saida
         */
        $success = false;
        $result = [];
        $count = 0;

        /**
         * Valida post
         * verifica no banco
         */
        $external_code = $this->checkFieldRequest($post, 'order_id', true, 'integer');
        $this->loadComponent('Order');
        $order = $this->Order->orderView(null, $external_code, $this->Auth->user('company_id'));
        if(count($order['error']))
        {
            $errors = $order['error'];
            goto error;
        }
        $order = $order['data'];

        /**
         * So e possivel fazer um novo pagamento nos casos
         * 5 - pagamento nao autorizado
         * 6 - Falha na recorrencia
         */
        if(!in_array($order['status_id'], [5, 6]))
        {
            $errors = [
                'Status da order não permite nova tentativa de pagamento'
            ];
            goto error;
        }
        $invoice = current(current($order['payment']));
        $invoice_id = $invoice['invoice_id'];
        $payment_id = $invoice['payment_code'];

        /**
         * Invoice acabou de ser criado e ainda nao tem pagamento
         * aguardar alguns instantes
         */
        if($invoice_id and !$payment_id)
        {
            $errors = [
                'Por favor, aguarde alguns instantes antes de fazer outra tentativa de pagamento'
            ];
            goto error;
        }

        /**
         * Tenta novo pagamento
         */
        $params = [
            'order_id' => $order['id'],
            'gateway_id' => $order['gateway_id'],
            'invoice_id' => $invoice_id
        ];
        $payment = new GatewayController();
        $change_result = $payment->retrySignature($params);
        if(!$change_result['data'])
        {
            $errors = [
                'Não foi possível efetuar uma nova tentativa de pagamento, tente novamente mais tarde, lembrando que são permitidas até 3 tentativas por dia'
            ];
            goto error;
        }

        /**
         * Saida de sucesso
         */
        $success = true;
        $result = [
            'Retentativa efetuada com sucesso'
        ];
        $errors = [];
        $count = 1;

        /**
         * Saida client
         */
        error:
        $this->message = 'Retentativa de pagamento';
        $this->code = 200;
        $this->success = $success;
        $this->data = [
            'count' => $count,
            'result' => $result,
            'errors' => $errors
        ];
        $this->generateOutput();
    }


    /**
     * Exibe todos os dados da order
     * @param $post
     * @param $id
     */
    public function view($post, $id)
    {
        if(!is_numeric($id))
        {
            $this->errorSimpleOutput([
                'message' => 'Número de order inválido',
                'error' => 'Verifique o tipo do dado, deve ser um numérico'

            ]);
        }

        $order = $this->Order->orderView(null, $id);

        /**
         * Saida default
         */
        $message = 'Order ' . $id . ' não encontrada';
        $code = 404;
        $success = false;
        $count = 0;
        $result = [];

        /**
         * Order encontrada
         */
        if(count($order['data']))
        {
            $message = 'Exibindo dados da order ' . $id;
            $code = 200;
            $success = true;
            $count = 1;
            $result = $order['data'];
        }

        /**
         * Saida client
         */
        $this->message = $message;
        $this->code = $code;
        $this->success = $success;
        $this->data = [
            'count' => $count,
            'result' => $result,
            'errors' => []
        ];
        $this->generateOutput();
    }
}