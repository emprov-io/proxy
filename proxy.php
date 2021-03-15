<?php
if (empty($_GET["tool"]) || empty($_GET["url"]) || empty($_GET["token"])) {
    header('HTTP/1.1 400 Bad Request');
    die;
} else {
    $ch = curl_init();
    switch (strtolower($_GET["tool"])) {
        case 'jira':
            $headers = array(
                'Accept: application/json',
                "Authorization: Bearer " . $_GET["token"]
            );
            if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                $headers[] = "Content-Type: application/json";
                if (!($putData = file_get_contents("php://input"))) {
                    throw new Exception("Can't get PUT data.");
                } else {
                    parse_str($putData, $post_vars);
                    $data = array_map_recursive("intval", $post_vars["data"]);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $headers[] = "Content-Type: application/json";
                $data = array_map_recursive("intval", $_POST["data"]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
            curl_setopt($ch, CURLOPT_URL, $_GET["url"]);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
            header('Content-Type: application/json');
            break;
        default:
            header('HTTP/1.1 404 Not Found');
            die;
    }

    //curl_setopt($ch, CURLOPT_VERBOSE, true);
    if (curl_exec($ch) === false) {
        print(curl_error($ch));
    }
    curl_close($ch);
    die;
}

function array_map_recursive($callback, $array)
{
    $func = function ($item) use (&$func, &$callback) {
        return is_array($item) ? array_map($func, $item) : call_user_func($callback, $item);
    };

    return array_map($func, $array);
}
