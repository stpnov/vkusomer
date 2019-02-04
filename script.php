<?php
$inc = new inc();
$inc->run();

class inc
{
    public $web = 0;
    public $industrial = 0;
    public $font = 0;
    public $graphical = 0;

    public $value = 500;
    public $user_id = 55365;

    public function run() {
        $user = $this->send('GET',"https://vkusomer.artlebedev.ru/api/users/{$this->user_id}/");
        $this->updateUser($user);

        $repeat = true;
        $refs = null;
        do {
            if ($refs === null) {
                $refs = $this->send('GET', "https://vkusomer.artlebedev.ru/api/refs/?format=json&offset=210");
            } else {
                $refs = $this->send('GET', $refs->next);
            }
            foreach ($refs->results as $i => $result) {

                if (
                    ($result->tags[0]->slug === 'web' && $this->web === 100) ||
                    ($result->tags[0]->slug === 'industrial' && $this->industrial === 100) ||
                    ($result->tags[0]->slug === 'font' && $this->font === 100) ||
                    ($result->tags[0]->slug === 'graphical' && $this->graphical === 100)
                ) {

                } else {

                    $this->value = 100;
                    echo $result->id . ': ';
                    $lvl = 0;
                    do {
                        $request = $this->send('POST', "https://vkusomer.artlebedev.ru/api/refs/" . $result->id . "/estimate/", "value=" . $this->value);
                        if (isset($request->detail) && $request->detail === "Не найдено.") {
                            echo ' -> ERROR' . PHP_EOL;
                            $repeat = false;
                        } else {
                            $user = $this->send('GET', "https://vkusomer.artlebedev.ru/api/users/{$this->user_id}/");
                            foreach ($user->compare_tags as $item) {
                                if ($item->tag === 'web') {
                                    if ($this->web < 100) {
                                        if ($this->web >= $item->value) {
                                            //var_dump($this->web-$item->value);
                                            if ($this->web - $item->value > 0)
                                                echo ' -> web DOWN (' . ($this->web - $item->value) . ')';
                                            $repeat = true;
                                        } else if ($this->web < $item->value) {
                                            echo ' -> web UP (' . ($item->value - $this->web) . ')';
                                            $repeat = false;
                                            break;
                                        }
                                    } else {
                                        $repeat = false;
                                    }
                                }
                                if ($item->tag === 'industrial') {
                                    if ($this->industrial < 100) {
                                        if ($this->industrial >= $item->value) {
                                            //var_dump($this->industrial-$item->value);
                                            if ($this->industrial - $item->value > 0)
                                                echo ' -> industrial DOWN (' . ($this->industrial - $item->value) . ')';
                                            $repeat = true;
                                        } else if ($this->industrial < $item->value) {
                                            echo ' -> industrial UP (' . ($item->value - $this->industrial) . ')';
                                            $repeat = false;
                                            break;
                                        }
                                    } else {
                                        $repeat = false;
                                    }
                                }
                                if ($item->tag === 'font') {
                                    if ($this->font < 100) {
                                        if ($this->font >= $item->value) {
                                            //var_dump($this->font-$item->value);
                                            if ($this->font - $item->value > 0)
                                                echo ' -> font DOWN (' . ($this->font - $item->value) . ')';
                                            $repeat = true;
                                        } else if ($this->font < $item->value) {
                                            echo ' -> font UP (' . ($item->value - $this->font) . ')';
                                            $repeat = false;
                                            break;
                                        }
                                    } else {
                                        $repeat = false;
                                    }
                                }
                                if ($item->tag === 'graphical') {
                                    if ($this->graphical < 100) {
                                        if ($this->graphical >= $item->value) {
                                            //var_dump($this->graphical-$item->value);
                                            if ($this->graphical - $item->value > 0)
                                                echo ' -> graphical DOWN (' . ($this->graphical - $item->value) . ')';
                                            $repeat = true;
                                        } else if ($this->graphical < $item->value) {
                                            echo ' -> graphical UP (' . ($item->value - $this->graphical) . ')';
                                            $repeat = false;
                                            break;
                                        }
                                    } else {
                                        $repeat = false;
                                    }
                                }
                            }
                            if ($lvl >= 5) {
                                $repeat = false;
                            }
                            $this->updateUser($user);
                            if ($repeat === true) {
                                if ($this->value === 100) {
                                    $this->value = 500;
                                } elseif ($this->value === 500) {
                                    $this->value = 100;
                                }
                                echo ' -> REPEAT ' . $this->value;
                                $lvl++;
                            } else {
                                echo ' -> UPDATE web: ' . $this->web . ' industrial: ' . $this->industrial . ' font: ' . $this->font . ' graphical: ' . $this->graphical . PHP_EOL;
                            }
                        }
                    } while ($repeat === true);
                }
            }
        } while ($refs->next !== null);
    }

    public function updateUser($user){

        foreach ($user->compare_tags as $item) {
            switch ($item->tag) {
                case 'web':
                    $this->web = $item->value;
                    break;
                case 'industrial':
                    $this->industrial = $item->value;
                    break;
                case 'font':
                    $this->font = $item->value;
                    break;
                case 'graphical':
                    $this->graphical = $item->value;
                    break;
            }
        }
    }


    private function send($request,$url,$data = null) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $request,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json, text/plain, */*",
                "Accept-Encoding: gzip, deflate, br",
                "Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Content-Type: application/x-www-form-urlencoded",
                "Cookie: sessionid=xxxxxxxxxxxx; csrftoken=xxxxxxxxxxxxxxxxxx; first-visit=true",
                "DNT: 1",
                "Host: vkusomer.artlebedev.ru",
                "Referer: https://vkusomer.artlebedev.ru/",
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:62.0) Gecko/20100101 Firefox/62.0",
                "X-CSRFToken: xxxxxxxxxxxxxxx",
                "X-Requested-With: XMLHttpRequest"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return json_decode($response);
        }
    }
}