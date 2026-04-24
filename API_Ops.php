<?php
// API_Ops.php

class MovieService
{ 
    private $apiKey = "3e673b78"; 
    
    private function connectAPI($url) 
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        return ($err) ? null : json_decode($response, true);
    }

    private function cleanData($Movies) 
    {
        $cleanedData = [];
        if(is_array($Movies)) 
        {
            foreach($Movies as $movie) 
            {
                
                if(!empty($movie['imdbID']) && !empty($movie['Title'])) 
                {
                    $cleanedData[] = 
                    [
                        'id'    => $movie['imdbID'],
                        'title' => $movie['Title'],
                        'image' => ($movie['Poster'] !== 'N/A') ? $movie['Poster'] : 'placeholder.png',
                        'releaseDate' => $movie['Year'] ?? ''
                    ];
                }
            }
        }
        return $cleanedData;
    }

    public function getMovies($query = []) 
    {
        //  
        $search = $query['title'] ?? ($query['s'] ?? 'movie');
        $url = "http://www.omdbapi.com/?apikey=" . $this->apiKey . "&s=" . urlencode($search);
        
        $response = $this->connectAPI($url);
        // OMDb يعيد النتائج في مصفوفة تسمى Search
        return $this->cleanData($response['Search'] ?? []);
    }

    public function getMovieDetails($id) 
    {
        $url = "http://www.omdbapi.com/?apikey=" . $this->apiKey . "&i=" . $id . "&plot=full";
        $response = $this->connectAPI($url);
        
        if($response && $response['Response'] !== "False") {
            return [
                'id'          => $response['imdbID'],
                'title'       => $response['Title'],
                'image'       => $response['Poster'],
                'genres'      => $response['Genre'],
                'releaseDate' => $response['Released'],
                'description' => $response['Plot'],
                'rating'      => $response['imdbRating']
            ];
        }
        return ['error' => 'Movie not found!'];
    }

    public function getPopularMovies() 
    {
        // OMDb 
        return $this->getMovies(['title' => '2026']);
    }
}

// for processing 
$action = $_GET['action'] ?? null;
$filter = $_GET;
unset($filter['action']);

if(!$action) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Action is required!']);
    exit;
}

$service = new MovieService();
header('Content-Type: application/json');

switch($action) {
    case 'popular': echo json_encode($service->getPopularMovies()); break;
    case 'search':  echo json_encode($service->getMovies($filter)); break;
    case 'details': echo json_encode($service->getMovieDetails($filter['id'] ?? '')); break;
    default:        echo json_encode(['error' => 'Invalid action!']);
}
?>
