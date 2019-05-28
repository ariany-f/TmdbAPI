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

            if(!empty($movie['backdrop_path'])) {
                $url_original =  Configure::read('image_url')[$ambiente]['original'];
                $result['results'][$i]['backdrop_path'] = $url_original . $movie['backdrop_path'];
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

        /** Adicionar gênero ao filme */
        $genres = $this->Tmdb->getGenres()['genres'];

        /** Adicionar linguagem ao filme */
        $language = $this->Tmdb->getLanguages();

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

            if(!empty($movie['backdrop_path'])) {
                $url_original =  Configure::read('image_url')[$ambiente]['original'];
                $result['results'][$i]['backdrop_path'] = $url_original . $movie['backdrop_path'];
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

             /** 
             * Ajustar linguagem para exibição em texto da mesma
             * */
            if(!empty($movie['original_language'])) {
                
                $result['results'][$i]['original_language'] =  $language[array_search($movie['original_language'], array_column($language, 'iso_639_1'))]['name'];
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

        /** Adicionar gênero ao filme */
        $genres = $this->Tmdb->getGenres()['genres'];

        /** Adicionar linguagem ao filme */
        $language = $this->Tmdb->getLanguages();

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

            if(!empty($movie['backdrop_path'])) {
                $url_original =  Configure::read('image_url')[$ambiente]['original'];
                $result['results'][$i]['backdrop_path'] = $url_original . $movie['backdrop_path'];
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

             /** 
             * Ajustar linguagem para exibição em texto da mesma
             * */
            if(!empty($movie['original_language'])) {
                
                $result['results'][$i]['original_language'] =  $language[array_search($movie['original_language'], array_column($language, 'iso_639_1'))]['name'];
            }
        }
        
        $this->message = 'Lista de Filmes mais Populares';
        $this->code = 200;
        $this->success = true;
        $this->data = $result;
        $this->generateOutput();
    }

    /**
     * Lista os filmes em cartaz
     * @param $page
     */
    public function nowPlaying($page = 1)
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

        $result = $this->Tmdb->getNowPlaying($page);

        /** Adicionar gênero ao filme */
        $genres = $this->Tmdb->getGenres()['genres'];
        
        /** Adicionar linguagem ao filme */
        $language = $this->Tmdb->getLanguages();

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

            if(!empty($movie['backdrop_path'])) {
                $url_original =  Configure::read('image_url')[$ambiente]['original'];
                $result['results'][$i]['backdrop_path'] = $url_original . $movie['backdrop_path'];
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

            /** 
             * Ajustar linguagem para exibição em texto da mesma
             * */
            if(!empty($movie['original_language'])) {
                
                $result['results'][$i]['original_language'] =  $language[array_search($movie['original_language'], array_column($language, 'iso_639_1'))]['name'];
            }
        }
        
        $this->message = 'Lista de Filmes em Cartaz';
        $this->code = 200;
        $this->success = true;
        $this->data = $result;
        $this->generateOutput();
    }

    /**
     * Lista os filmes mais novos
     * @param $page
     */
    public function latest($page = 1)
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

        $result = $this->Tmdb->getLatest($page);
        
        $this->message = 'Lista de Filmes mais Novos';
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
     * Lista os generos disponiveis
     */
    public function languages()
    {
        /**
         * Post json decode
         */
        $post = $this->request->input('json_decode', true);
        if(isset($post['request_id']))
        {
            $this->request_id = $post['request_id'];
        }

        $result = $this->Tmdb->getLanguages();
        
        $this->message = 'Lista de Linguagens';
        $this->code = 200;
        $this->success = true;
        $this->data = $result;
        $this->generateOutput();
    }

    /**
     * Lista os filmes disponíveis
     */
    public function discover($page = 1, $genre_id = null)
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

        $result = $this->Tmdb->getMovies($page, $genre_id);
        
        /** Adicionar gênero ao filme */
       $genres = $this->Tmdb->getGenres()['genres'];
        
       /** Adicionar linguagem ao filme */
       $language = $this->Tmdb->getLanguages();

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

            if(!empty($movie['backdrop_path'])) {
                $url_original =  Configure::read('image_url')[$ambiente]['original'];
                $result['results'][$i]['backdrop_path'] = $url_original . $movie['backdrop_path'];
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

            /** 
             * Ajustar linguagem para exibição em texto da mesma
             * */
            if(!empty($movie['original_language'])) {
                
                $result['results'][$i]['original_language'] =  $language[array_search($movie['original_language'], array_column($language, 'iso_639_1'))]['name'];
            }
        }

        $this->message = 'Lista de Filmes';
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
     * Créditos por filme
     * @param null $query
     * @throws \Exception
     */
    public function movieCredits($id = null)
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

        $result = $this->Tmdb->movieCredits($id);

        /** Fotos do elenco com url */
        foreach($result['cast'] as $i => $cast) {
            if(!empty($cast['profile_path'])) {
                $url_original =  Configure::read('image_url')[$ambiente]['original'];
                $result['cast'][$i]['profile_path'] = $url_original . $cast['profile_path'];
            }
        }

        /** Fotos da produção com url */
        foreach($result['crew'] as $i => $crew) {
            if(!empty($crew['profile_path'])) {
                $url_original =  Configure::read('image_url')[$ambiente]['original'];
                $result['crew'][$i]['profile_path'] = $url_original . $crew['profile_path'];
            }
        }
        
        $this->message = 'Créditos do Filme';
        $this->code = 200;
        $this->success = true;
        $this->data = $result;
        $this->generateOutput();
    }

    /**
     * Videos por filme
     * @param null $query
     * @throws \Exception
     */
    public function movieVideos($id = null)
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

        $result = $this->Tmdb->movieVideos($id);

        foreach($result['results'] as $i => $video) {
            if($video['site'] == "YouTube") {
                $result['results'][$i]['url'] = 'https://www.youtube.com/watch?v=' . $video['key'];
            }
            else {
                $result['results'][$i]['url'] = null;
            }
        }

        $this->message = 'Videos do Filme';
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

        /** Adicionar linguagem ao filme */
        $language = $this->Tmdb->getLanguages();

        $result = $this->Tmdb->detail($id);
        
        if(!empty($result['poster_path'])) {
            $url_original =  Configure::read('image_url')[$ambiente]['original'];
            $result['poster_path'] = $url_original . $result['poster_path'];
        }

        if(!empty($result['backdrop_path'])) {
            $url_original =  Configure::read('image_url')[$ambiente]['original'];
            $result['backdrop_path'] = $url_original . $result['backdrop_path'];
        }

        /** Linguagem por extenso */
        if(!empty($result['original_language'])) {
            $result['original_language'] =  $language[array_search($result['original_language'], array_column($language, 'iso_639_1'))]['name'];
        }

        /** Empresas produtoras */
        foreach($result['production_companies'] as $i => $production_companies) {
            if(!empty($production_companies['logo_path'])) {
                $url_original =  Configure::read('image_url')[$ambiente]['original'];
                $result['production_companies'][$i]['logo_path'] = $url_original . $production_companies['logo_path'];
            }
        }

         /** Tempo total de filme por extenso */
         if(!empty($result['runtime'])) {
            $result['runtime'] = $this->Utils->convertToHoursMins($result['runtime'], '%02d hours %02d minutes'); // should output 4 hours 17 minutes;
        }

        $this->message = 'Detalhes do Filme';
        $this->code = 200;
        $this->success = true;
        $this->data = $result;
        $this->generateOutput();
    }
}