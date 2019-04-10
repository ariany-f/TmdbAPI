<?php
namespace App\Controller;

use Cake\ORM\TableRegistry;
use Cake\ORM\Query;
use App\Controller\Component\UtilsComponent;

/**
 * Status Controller
 */
class StatusController extends AppController
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
     * Lista os status
     * @param null $post
     * @param null $action
     */
    public function index($post = null, $action = null)
    {
        $db = TableRegistry::getTableLocator()->get('ApisStatusOrigins');
        $query = $db
            ->find()
            ->select([
                'ApisStatusOrigins.id',
                'ApisStatusOrigins.name',
                'ApisStatus.status_code',
                'ApisStatus.name'
            ])
            ->join([
                'table' => 'apis_status',
                'alias' => 'ApisStatus',
                'type' => 'INNER',
                'conditions' => 'ApisStatus.status_origin_id = ApisStatusOrigins.id'
            ])
            ->order([
                'ApisStatusOrigins.name',
                'ApisStatus.status_code'
            ])
            ->toArray()
        ;
        $result = UtilsComponent::objToArray($query);
        $output = [];
        foreach ($result as $value)
        {
            $output[] = [
                'origin_id' => $value['id'],
                'origin_name' => $value['name'],
                'status_code' => (integer) $value['ApisStatus']['status_code'],
                'status_name' => $value['ApisStatus']['name']
            ];
        }

        /**
         * Saida lista de Status
         */
        $this->message = 'Lista de status';
        $this->code = 200;
        $this->success = true;
        $this->data = [
            'count' => count($output),
            'result' => $output,
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
     * Add
     * @param array $post
     */
    public function add($post)
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