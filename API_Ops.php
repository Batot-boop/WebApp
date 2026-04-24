<?php
// config.php
define('RAPIDAPI_KEY', '105e88c40dmsha4458d8b30434eap1222b6jsn580b40576c91');
define('DB_HOST', 'localhost');
define('DB_NAME', 'movie_tracker');
define('DB_USER', 'root');
define('DB_PASS', '');

class MovieService {
    private $apiKey;

    public function __construct() {
        $this->apiKey = RAPIDAPI_KEY;
    }

    private function connectAPI($url) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "x-rapidapi-host: imdb236.p.rapidapi.com",
                "x-rapidapi-key: " . $this->apiKey
            ],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        return ($err) ? null : json_decode($response, true);
    }

    private function cleanData($movies) {
        $cleaned = [];
        if (is_array($movies)) {
            foreach ($movies as $movie) {
                if (!empty($movie['id']) && !empty($movie['originalTitle']) &&
                    !empty($movie['primaryImage']) && filter_var($movie['primaryImage'], FILTER_VALIDATE_URL)) {
                    $cleaned[] = [
                        'id'    => $movie['id'],
                        'title' => $movie['originalTitle'],
                        'image' => $movie['primaryImage']
                    ];
                }
            }
        }
        return $cleaned;
    }

    private function cleanDetails($details) {
        if (is_array($details) && !isset($details['message'])) {
            return [
                'id'              => $details['id'] ?? '',
                'title'           => $details['originalTitle'] ?? '',
                'image'           => $details['primaryImage'] ?? '',
                'genres'          => $details['genres'] ?? [],
                'releaseDate'     => $details['releaseDate'] ?? '',
                'spokenLanguages' => $details['spokenLanguages'] ?? [],
                'locations'       => $details['filmingLocations'] ?? [],
                'trailer'         => $details['trailer'] ?? '',
                'description'     => $details['description'] ?? ''
            ];
        }
        return null;
    }

    public function getMovies($query = []) {
        $params = ['type' => 'movie', 'rows' => 20];
        $params = array_merge($params, $query);
        $url = "https://imdb236.p.rapidapi.com/api/imdb/search?" . http_build_query($params);
        $response = $this->connectAPI($url);
        return $this->cleanData($response['results'] ?? []);
    }

    public function getPopularMovies() {
        $url = "https://imdb236.p.rapidapi.com/api/imdb/most-popular-movies";
        $response = $this->connectAPI($url);
        return $this->cleanData($response ?? []);
    }

    public function getMovieDetails($id) {
        $url = "https://imdb236.p.rapidapi.com/api/imdb/" . urlencode($id);
        $response = $this->connectAPI($url);
        $details = $this->cleanDetails($response);
        return $details ?? ['error' => 'Movie not found.'];
    }

    public function getMovieForDB($id) {
        return $this->getMovieDetails($id);
    }
}

// Front Controller
$action = $_GET['action'] ?? '';
$allowed = ['popular', 'search', 'details', 'retrieveToDB'];
if (!in_array($action, $allowed)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid or missing action.']);
    exit;
}

$service = new MovieService();
header('Content-Type: application/json');

switch ($action) {
    case 'popular':
        echo json_encode($service->getPopularMovies());
        break;
    case 'search':
        $filters = [];
        if (!empty($_GET['primaryTitle'])) {
            $filters['primaryTitle'] = trim($_GET['primaryTitle']);
        }
        if (!empty($_GET['genre'])) {
            $filters['genre'] = trim($_GET['genre']);
        }
        echo json_encode($service->getMovies($filters));
        break;
    case 'details':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['error' => 'Movie ID missing.']);
            exit;
        }
        echo json_encode($service->getMovieDetails($id));
        break;
    case 'retrieveToDB':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['error' => 'Movie ID missing.']);
            exit;
        }
        echo json_encode($service->getMovieForDB($id));
        break;
}