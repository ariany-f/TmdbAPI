<?php
namespace App\Controller;
use App\Controller\Component\TmdbComponent;
use Cake\Event\Event;

/**
 * Movies Controller
 */
class MoviesController extends AppController
{
    /**
     * Do Cake
     */
    public function initialize()
    {
        $this->loadComponent('Tmdb');
        parent::initialize();
        $this->Auth->allow([
            // Nenhum
        ]);
    }

    /**
     * Lista os filmes disponiveis
     * @param $page
     */
    public function upcoming($page = 1)
    {
        /**
         * Post json decode
         */
        $post = $this->request->input('json_decode', true);
        if(isset($post['request_id']))
        {
            $this->request_id = $post['request_id'];
        }

        $result = $this->Tmdb->getUpcoming($page);
        
        $this->message = 'Filmes';
        $this->code = 200;
        $this->success = true;
        $this->data = $result;
        $this->generateOutput();
    }

    /**
     * Procurar por titulo
     * @param null $query
     * @throws \Exception
     */
    public function search($query = null)
    {
       /**
         * Post json decode
         */
        $post = $this->request->input('json_decode', true);
        if(isset($post['request_id']))
        {
            $this->request_id = $post['request_id'];
        }

        $result = $this->Tmdb->search($query);
        
        $this->message = 'Filmes';
        $this->code = 200;
        $this->success = true;
        $this->data = $result;
        $this->generateOutput();
    }
}