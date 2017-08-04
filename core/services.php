<?php

namespace core;

use app;

class services
{
    public function api($uri, $params)
    {
        //curl
        if (conf('data_mode') == 'model') {
            $uri = explode(".", $uri);
            $model = 'home\models\\'.$uri[0];
            $method = $uri[1];

            return app::singleton($model)->$method($params);
        }
    }

    /**
     * $request = [
     *     'uri' => 'advertise.AdApp.getApp.select',
     *     'params' => [
     *         'app_id' => 5,
     *     ],
     * ];
     * @param $request
     * @return mixed
     */
    public function call($request)
    {
        return $this->api($request['uri'], $request['params']);
    }

    /**
     * $requests = [
     *     'one' => [
     *         'uri' => 'advertise.AdApp.getApp.select',
     *         'params' => [
     *             'app_id' => 5
     *         ]
     *     ],
     *     'two' => [
     *         'uri' => 'advertise.AdApp.getApp.select',
     *         'params' => [
     *             'app_id' => 5
     *         ]
     *     ],
     * ];
     * @param $requests
     * @return array
     */
    public function multiCall($requests)
    {
        $result = [];

        foreach($requests as $k=>$request)
        {
            $result[$k] = $this->api($request['uri'], $request['params']);
        }

        return $result;
    }
}