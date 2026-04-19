<?php
// API Logic Operations for Movie Data
class MovieService
{
    private $apiKey = "401c37c95fmsh2b84bfb51ee27a6p1c11bajsna23f2aba724c";
    
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

        // error_log("cURL Error :" . $err); 
        return ($err) ? null : json_decode($response, true);
    }

    // Data Filtering 
    private function cleanData($Movies) 
    {
        // $Movies = $this->connectAPI();
        $cleanedData = [];
        
        if(is_array($Movies)) 
        {
            foreach($Movies as $movie) 
            {
                $cleanedData[] = 
                [
                    'id'          => $movie['id'] ?? 'N/A',
                    'title'       => $movie['originalTitle'] ?? 'No Title',
                    'image'       => $movie['primaryImage'] ?? null,
                ];
            }
        }
        return $cleanedData;
    }

    public function cleanDetails($details) 
    {
        if(is_array($details))
        {
            return 
            [
                'id'              => $details['id'] ?? 'N/A',
                'title'           => $details['originalTitle'] ?? 'No Title',
                'image'           => $details['primaryImage'] ?? null,
                'genres'          => $details['genres'] ?? [],
                'releaseDate'     => $details['releaseDate'] ?? 'Unknown',
                'spokenLanguages' => $details['spokenLanguages'] ?? 'Unknown',
                'locations'       => $details['filmingLocations'] ?? 'Unknown',
                'trailer'         => $details['trailer'] ?? 'No Trailer Available',
                'description'     => $details['description'] ?? 'No Description Available',
            ];
        }
        return null;
    }

    public function getAnyData($query = []) 
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
    case 'search':
        echo json_encode($service->getAnyData($filter));
        break;
    case 'details':
        $movieID = $filter['id'] ?? null;
        if(!isset($movieID)) {
            echo json_encode(['error' => 'Movie ID is missing!']);
            exit;
        }
        echo json_encode($service->getMovieDetails($movieID));
        break;
    default:
        echo json_encode(['error' => 'Invalid action specified!']);
}
?>