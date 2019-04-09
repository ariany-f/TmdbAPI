<?php
namespace App\Controller;

use Cake\Datasource\ConnectionManager;


/**
 * Occupation Controller
 */
class OccupationController extends AppController
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
     * Index
     */
    public function index()
    {
        die;
    }

    /**
     * View method
     */
    public function view($id = null)
    {
        die;
    }

    /**
     * Lista Ocupacoes
     * @param array $post
     * @param string $action
     */
    public function add($post = [], $action)
    {
        $message = 'Lista de ocupações';
        $errors = [];
        $termo = $this->checkFieldRequest($post, 'termo', true);
        $connection = ConnectionManager::get('default');
        $occupations = $connection
            ->execute("CALL search_occupation(:company_id, :occupation, 1);", [
                    'company_id' => $this->Auth->user('company_id'),
                    'occupation' => $termo
                ]
            )
            ->fetchAll('assoc');

        if(isset($occupations[0]['proc_status_id']))
        {
            $code = 400;
            $success = false;
            $errors = [
                $occupations[0]['proc_status_id'] => $occupations[0]['proc_status']
            ];
            $occupations = [];
            goto error;
        }

        /**
         * Tem resultado
         */
        if(count($occupations))
        {
            $code = 200;
            $success = true;
        }
        else
        {
            $message = 'O termo ' . $termo . ' não foi encontrado na lista de ocupações';
            $code = 404;
            $success = true;
        }

        /**
         * Saida lista de ocupacoes
         */
        error:
        $this->message = $message;
        $this->code = $code;
        $this->success = $success;
        $this->data = [
            'count' => count($occupations),
            'result' => $occupations,
            'errors' => $errors
        ];
        $this->generateOutput();
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