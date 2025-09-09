<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use RestClient;
use RestClientException;

class SearchController extends Controller
{
    private $login;
    private $password;

    private $api_url = 'https://api.dataforseo.com/';

    public function __construct()
    {
        $this->login = env('DATAFORSEO_LOGIN');
        $this->password = env('DATAFORSEO_PASSWORD');
    }

    public function index()
    {
        return view('search');
    }

    public function search(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string',
            'site' => 'required|url',
            'location' => 'required|string',
            'language' => 'required|string',
        ]);

        require_once base_path('lib/RestClient.php');

        try {
            $client = new RestClient($this->api_url, null, $this->login, $this->password);
        } catch (RestClientException $e) {
            return back()->with('error', "API connection error: " . $e->getMessage());
        }

        try {
            $post_array = [[
                'language_name' => $request->language,
                'location_name' => $request->location,
                'keyword' => mb_convert_encoding($request->keyword, 'UTF-8'),
            ]];

            $task_response = $client->post('/v3/serp/google/organic/task_post', $post_array);

            if (!isset($task_response['tasks'][0]['id'])) {
                return back()->with('error', 'Не вдалося створити завдання.');
            }

            $task_id = $task_response['tasks'][0]['id'];
            $rank = null;
            $allItems = [];

            $collectItems = function ($items, &$collected) use (&$collectItems) {
                foreach ($items as $item) {
                    $collected[] = $item;
                    if (isset($item['items']) && is_array($item['items'])) {
                        $collectItems($item['items'], $collected);
                    }
                }
            };

            $max_tries = 15;
            $try = 0;
            while ($try < $max_tries) {
                $tasks_ready = $client->get('/v3/serp/google/organic/tasks_ready');

                foreach ($tasks_ready['tasks'] as $task) {
                    foreach ($task['result'] as $res) {
                        if ($res['id'] == $task_id) {
                            $endpoint = $res['endpoint_regular'];
                            $task_result = $client->get($endpoint);

                            if (isset($task_result['tasks'][0]['result'][0]['items'])) {
                                $items = $task_result['tasks'][0]['result'][0]['items'];

                                $collectItems($items, $allItems);

                                foreach ($items as $item) {
                                    if ($item['type'] === 'organic' && strpos($item['url'], $request->site) !== false) {
                                        $rank = $item['rank_absolute'];
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                }

                if ($rank !== null) break;
                sleep(2);
                $try++;
            }

            return view('search', [
                'rank' => $rank,
                'site' => $request->site,
                'keyword' => $request->keyword,
                'allItems' => $allItems
            ]);

        } catch (RestClientException $e) {
            return back()->with('error', "API error: " . $e->getMessage());
        }
    }
}
