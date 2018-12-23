<?php

class tknk
{
    private $base_url = 'http://xx.xx.xx.xx';
    public $timeout = 120;
    private $id = null;
    private $file_name = null;

    public function __constructor()
    {
        //
    }

    public function upload(string $malware_path)
    {
        if (!file_exists($malware_path)) {
            // エラー処理
            return null;
        }
        $this->file_name = basename($malware_path);

        $cfile = curl_file_create($malware_path);
        $post = ['file' => $cfile];

        $ch = curl_init();
        $options = [
            CURLOPT_URL => $this->base_url . '/api/upload',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $post,
        ];
        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);
        $result = json_decode($result, true);

        return $result;
    }

    public function analyze(string $mode)
    {
        $post = [
            'path' => 'target/' . $this->file_name,
            'mode' => $mode,
            'time' => $this->timeout,
        ];

        $ch = curl_init();
        $options = [
            CURLOPT_URL => $this->base_url . '/api/analyze',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_HTTPHEADER => [
                'Content-Type' => 'application/json',
            ],
        ];
        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);
        $result = json_decode($result, true);

        if (!isset($result['UUID'])) {
            // エラー処理
        }

        $this->id = $result['UUID'];

        return $result;
    }

    public function get_status()
    {
        $result = file_get_contents($this->base_url . '/api/results/' . $this->id);
        if (!isset($result['status_code'])) {
            // エラー処理
        }

        if ($result['status_code'] === 0) {
            return 0;
        }

        return 1;
    }

    public function get_result()
    {
        $result = file_get_contents($this->base_url . '/api/results/' . $this->id);
        if (!isset($result['status_code'])) {
            // エラー処理
        }

        if ($result['status_code'] !== 0) {
            // エラー処理
        }

        if (!isset($result['report'])) {
            // エラー処理
        }

        return $result['report'];
    }

    public function get_webui_url()
    {
        return $this->base_url . '/results/' . $this->id;
    }
}
