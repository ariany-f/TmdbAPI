<?php
namespace App\Controller;

use App\Controller\Component\UtilsComponent;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;

/**
 * Store Controller
 */
class EventController extends AppController
{
    /**
     * Do Cake
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow([
            'moip'
        ]);
    }

    /**
     * Trata eventos da Moip
     * @param null $action
     * @throws \Exception
     */
    public function moip($action = null)
    {
        /**
         * Component de eventos moip
         */
        $this->loadComponent('GatewayMoipEvent');

        /**
         * Post do request
         */
        $post = $this->request->input('json_decode', true);

        switch ($action)
        {
            case 'signatures':

                /**
                 * Log do event
                 */
                UtilsComponent::saveLogFile('eventMoipSignatures.log', [
                    'header' => getallheaders(),
                    'json' => $post,
                    'post' => $_POST,
                    'get' => $_GET,
                    'server' => UtilsComponent::server()
                ]);

                /**
                 * salva log no banco
                 */
                $event_key = null;
                $event_type = null;
                $event_status = null;
                if(isset($post['event']))
                {
                    $event = explode(".", $post['event']);
                    $event_type = (isset($event[0])) ? $event[0] : $event_type;
                    $event_status = (isset($event[1])) ? $event[1] : $event_status;
                }
                $event_key = (isset($post['resource']['id'])) ? $post['resource']['id'] : $event_key;
                $content = json_encode($post);

                /**
                 * Checa se event
                 * ja processado se nao
                 * salva e processa
                 */
                $pay_gateway_event_db = TableRegistry::getTableLocator()->get('PayGatewayEvents');
                $query = $pay_gateway_event_db
                    ->find()
                    ->select([
                        'PayGatewayEvents.id'
                    ])
                    ->where([
                        'PayGatewayEvents.content' => $content
                    ])
                    ->toArray()
                ;
                $result = UtilsComponent::objToArray($query);
                if(count($result))
                {
                    $this->message = 'Status da chamada do Webhook';
                    $this->code = 202;
                    $this->success = false;
                    $this->data = [
                        'count' => 0,
                        'result' => [],
                        'errors' => [
                            0 => 'Evento não modificado'
                        ]
                    ];
                    $this->generateOutput();
                }

                $params = [
                    'gateway_id' => 2,
                    'event_key' => $event_key,
                    'event_type' => $event_type,
                    'event_status' => $event_status,
                    'content' => $content
                ];
                $event_new = $pay_gateway_event_db->newEntity($params);
                $pay_gateway_event_db->save($event_new);

                /**
                 * Validando autenticacao
                 */
                $request_header = getallheaders();
                if(isset($request_header['Authorization']))
                {
                    $token_check = Configure::read('event.moip.signatures.token')[Configure::read('service_mode')];
                    $token_request = $request_header['Authorization'];
                    if($token_check != $token_request)
                    {
                        $this->mailDebug([
                            'subject' => 'Apis Event - Signatures auth',
                        ]);
                        $this->generateOutput();
                    }
                }
                else
                {
                    $this->mailDebug([
                        'subject' => 'Apis Event - Signatures auth',
                    ]);
                    $this->generateOutput();
                }

                /**
                 * Metodo permitido
                 */
                if (!$this->request->is(['post']))
                {
                    $this->methodNotPermitted();
                }

                /**
                 * Trata o event
                 */
                $output = $this->GatewayMoipEvent->signatures($post);

                /**
                 * Log TI
                 */
                UtilsComponent::saveLogFile('eventMoipSignaturesTi.log', [
                    'header' => getallheaders(),
                    'json' => $post,
                    'post' => $_POST,
                    'get' => $_GET,
                    'output' => $output,
                    'server' => UtilsComponent::server()
                ]);

                $this->message = 'Status da chamada do Webhook';
                $this->code = 200;
                $this->success = true;
                $this->data = [
                    'count' => 1,
                    'result' => $output['data'],
                    'errors' => $output['error']
                ];
                $this->generateOutput();
            break;

            default;
                $this->endPointActionNotExists($action);
        }

        /**
         * Saida com erro
         */
        $this->generateOutput();
    }
}