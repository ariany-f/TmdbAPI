<?php

namespace App\Controller;

use App\Controller\Component\TmdbComponent;
use Cake\Event\Event;
use Cake\Core\Configure;

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
     * Lista os próximos filmes
     * @param $page
     */
    public function upcoming($page = 1)
    {
        /** Define ambiente */
        $ambiente = Configure::read('service_mode');

        /**
         * Post json decode
         */
        $post = $this->request->input('json_decode', true);
        if(isset($post['request_id']))
        {
            $this->request_id = $post['request_id'];
        }

        $result = $this->Tmdb->getUpcoming($page);

        /** Retirar na v2 */
        /** Adicionar gênero ao filme */
        $result['genres'] = $genres = $this->Tmdb->getGenres()['genres'];

        foreach($result['results'] as $i => $movie) {
                    
            /** 
             * Alterar url do poster do filme, 
             * assim não é necessário adicionar 
             * tal url do lado CLI 
             * */
            if(!empty($movie['poster_path'])) {
                $url_original =  Configure::read('image_url')[$ambiente]['original'];
                $result['results'][$i]['poster_path'] = $url_original . $movie['poster_path'];
            }

            /** 
             * Ajustar gêneros para exibição em texto dos mesmos 
             * */
            if(!empty($movie['genre_ids'])) {
                foreach($movie['genre_ids'] as $genre_id) {
                    $result['results'][$i]['genres'][] =  $genres[array_search($genre_id, array_column($genres, 'id'))]['name'];
                }
                unset( $result['results'][$i]['genre_ids']);
            }
        }
        
        $this->message = 'Lista de Filmes';
        $this->code = 200;
        $this->success = true;
        $this->data = $result;
        $this->generateOutput();
    }

     /**
     * Lista os filmes mais bem avaliados
     * @param $page
     */
    public function topRated($page = 1)
    {
        /** Define ambiente */
        $ambiente = Configure::read('service_mode');

        /**
         * Post json decode
         */
        $post = $this->request->input('json_decode', true);
        if(isset($post['request_id']))
        {
            $this->request_id = $post['request_id'];
        }

        $result = $this->Tmdb->getTopRated($page);

        /** Retirar na v2 */
        /** Adicionar gênero ao filme */
        $result['genres'] = $genres = $this->Tmdb->getGenres()['genres'];

        foreach($result['results'] as $i => $movie) {
                    
            /** 
             * Alterar url do poster do filme, 
             * assim não é necessário adicionar 
             * tal url do lado CLI 
             * */
            if(!empty($movie['poster_path'])) {
                $url_original =  Configure::read('image_url')[$ambiente]['original'];
                $result['results'][$i]['poster_path'] = $url_original . $movie['poster_path'];
            }

            /** 
             * Ajustar gêneros para exibição em texto dos mesmos 
             * */
            if(!empty($movie['genre_ids'])) {
                foreach($movie['genre_ids'] as $genre_id) {
                    $result['results'][$i]['genres'][] =  $genres[array_search($genre_id, array_column($genres, 'id'))]['name'];
                }
                unset( $result['results'][$i]['genre_ids']);
            }
        }
        
        $this->message = 'Lista de Filmes mais bem Avaliados';
        $this->code = 200;
        $this->success = true;
        $this->data = $result;
        $this->generateOutput();
    }

     /**
     * Lista os filmes mais bem avaliados
     * @param $page
     */
    public function Popular($page = 1)
    {
        /** Define ambiente */
        $ambiente = Configure::read('service_mode');

        /**
         * Post json decode
         */
        $post = $this->request->input('json_decode', true);
        if(isset($post['request_id']))
        {
            $this->request_id = $post['request_id'];
        }

        $result = $this->Tmdb->getPopular($page);

        /** Retirar na v2 */
        /** Adicionar gênero ao filme */
        $result['genres'] = $genres = $this->Tmdb->getGenres()['genres'];

        foreach($result['results'] as $i => $movie) {
                    
            /** 
             * Alterar url do poster do filme, 
             * assim não é necessário adicionar 
             * tal url do lado CLI 
             * */
            if(!empty($movie['poster_path'])) {
                $url_original =  Configure::read('image_url')[$ambiente]['original'];
                $result['results'][$i]['poster_path'] = $url_original . $movie['poster_path'];
            }

            /** 
             * Ajustar gêneros para exibição em texto dos mesmos 
             * */
            if(!empty($movie['genre_ids'])) {
                foreach($movie['genre_ids'] as $genre_id) {
                    $result['results'][$i]['genres'][] =  $genres[array_search($genre_id, array_column($genres, 'id'))]['name'];
                }
                unset( $result['results'][$i]['genre_ids']);
            }
        }
        
        $this->message = 'Lista de Filmes mais Populares';
        $this->code = 200;
        $this->success = true;
        $this->data = $result;
        $this->generateOutput();
    }


    /**
     * Lista os generos disponiveis
     */
    public function genres()
    {
        /**
         * Post json decode
         */
        $post = $this->request->input('json_decode', true);
        if(isset($post['request_id']))
        {
            $this->request_id = $post['request_id'];
        }

        $result = $this->Tmdb->getGenres()['genres'];
        
        $this->message = 'Lista de Gêneros';
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
        
        $this->message = 'Buscar Filmes';
        $this->code = 200;
        $this->success = true;
        $this->data = $result;
        $this->generateOutput();
    }

     /**
     * Requisitar detalhes do Filme
     * @param null $id
     * @throws \Exception
     */
    public function detail($id = null)
    {
       /**
         * Post json decode
         */
        $post = $this->request->input('json_decode', true);
        if(isset($post['request_id']))
        {
            $this->request_id = $post['request_id'];
        }

        $result = $this->Tmdb->detail($id);
        
        $this->message = 'Detalhes do Filme';
        $this->code = 200;
        $this->success = true;
        $this->data = $result;
        $this->generateOutput();
    }
}