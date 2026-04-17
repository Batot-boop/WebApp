<?php
class MovieService
{
    // private function __construct()
    
    // Fetching Data from API
    private function connectAPI() 
    {
        $curl = curl_init();

        curl_setopt_array($curl, 
        [
            CURLOPT_URL => "https://imdb236.p.rapidapi.com/api/imdb/cast/nm0000190/titles",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => 
            [
                "Content-Type: application/json",
                "x-rapidapi-host: imdb236.p.rapidapi.com",
                "x-rapidapi-key: f044e9ca29msh57825161a92c247p1c2a1cjsn3cc4c2bf361c"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err)
        { 
            error_log("cURL Error :" . $err); 
            return null;    
        }
        else return json_decode($response, true);
    }

    // Data Searching and Filtering
    public function getFilterMovies() 
    {
        $Movies = $this->connectAPI();
        $cleanedData = [];
        
        if(is_array($Movies)) 
        {
            foreach($Movies as $movie) 
            {
                $cleanedData[] = 
                [
                    'id'          => $movie['id'] ?? 'N/A',
                    'title'       => $movie['originalTitle'] ?? 'No Title',
                    'description' => $movie['description'] ?? 'No Description available',
                    'image'       => $movie['primaryImage'] ?? null,
                    'releaseDate' => $movie['releaseDate'] ?? 'N/A',
                    'genre'       => $movie['genre'] ?? 'N/A'
                    ];
            }
        }
        return $cleanedData;
    }

    public function saveMoviesToDB() {}

    // private function __destruct()
}
?>