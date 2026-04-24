<?php
// API Logic Operations for Movie Data
class MovieService
{
    private $apiKey = "105e88c40dmsha4458d8b30434eap1222b6jsn580b40576c91";
    
    // Fetching Data from API
    private function connectAPI($url) 
    {
        $curl = curl_init();
        curl_setopt_array($curl, 
        [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => 
            [
                "Content-Type: application/json",
                "x-rapidapi-host: imdb236.p.rapidapi.com",
                "x-rapidapi-key: " . $this->apiKey
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        return ($err) ? null : json_decode($response, true);
    }

    // Data Filtering 
    private function cleanData($Movies) 
    {
        $cleanedData = [];
        
        if(is_array($Movies)) 
        {
            foreach($Movies as $movie) 
            {
                if(!empty($movie['id']) && !empty($movie['originalTitle']) && !empty($movie['primaryImage']) && filter_var($movie['primaryImage'], FILTER_VALIDATE_URL)) 
                    $cleanedData[] = 
                    [
                        'id'          => $movie['id'],
                        'title'       => $movie['originalTitle'],
                        'image'       => $movie['primaryImage']
                    ];
            }
        }
        return $cleanedData;
    }

    private function cleanDetails($details) 
    {
        if(is_array($details))
        {
            return 
            [
                'id'              => $details['id'],
                'title'           => $details['originalTitle'],
                'image'           => $details['primaryImage'],
                'genres'          => $details['genres'],
                'releaseDate'     => $details['releaseDate'],
                'spokenLanguages' => $details['spokenLanguages'],
                'locations'       => $details['filmingLocations'],
                'trailer'         => $details['trailer'],
                'description'     => $details['description']
            ];
        }
        return null;
    }

    public function getMovies($query = []) 
    {
        $params = 
        [
            'type' => 'movie',
            'rows' => 20
        ];
        $params = array_merge($params, $query);

        $queryString = http_build_query($params);
        $url = "https://imdb236.p.rapidapi.com/api/imdb/search?" . $queryString;
        
        $response = $this->connectAPI($url);
        return $this->cleanData($response['results'] ?? []);
    }

    public function getMovieDetails($id) 
    {
        $url = "https://imdb236.p.rapidapi.com/api/imdb/" . $id;
        
        $response = $this->connectAPI($url);
        if($response && !isset($response['message']))
            return $this->cleanDetails($response); 
        return ['error' => 'Movie not found or API error!'];
    }

    public function getPopularMovies() 
    {
        $url = "https://imdb236.p.rapidapi.com/api/imdb/most-popular-movies";
        
        $response = $this->connectAPI($url);
        return $this->cleanData($response ?? []);
    }

    public function getMoviesToDB($id) 
    {
        $url = "https://imdb236.p.rapidapi.com/api/imdb/" . $id;
        $response = $this->connectAPI($url);
        if($response && !isset($response['message']))
            return $this->cleanData($response);
        return ['error' => 'Movie not found or API error!']; 
    }
}

// Handle Js(Ajax) Request
$action = $_GET['action'] ?? null;
$filter = $_GET;

unset($filter['action']);
$filter = array_filter($filter, function($value){ return !empty($value); }); // Remove empty values

if(!$action)
{
    echo json_encode(['error' => 'Action is required!']);
    exit;
}

$service = new MovieService();
header('Content-Type: application/json');

// Simple Routing based on Action
switch($action)
{
    case 'popular':
        echo json_encode($service->getPopularMovies());
        break;
    case 'search':
        echo json_encode($service->getMovies($filter));
        break;
    case 'details':
        $movieID = $filter['id'] ?? null;
        if(!isset($movieID)) {
            echo json_encode(['error' => 'Movie ID is missing!']);
            exit;
        }
        echo json_encode($service->getMovieDetails($movieID));
        break;
    case 'retrieveToDB':
        $movieID = $filter['id'] ?? null;
        if(!isset($movieID)) {
            echo json_encode(['error' => 'Movie ID is missing!']);
            exit;
        }
        echo json_encode($service->getMoviesToDB($movieID));
        break;
    default:
        echo json_encode(['error' => 'Invalid action specified!']);
}
?>