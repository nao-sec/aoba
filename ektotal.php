<?php

class EKTotal
{
    private $base_url = 'http://xx.xx.xx.xx';
    private $id = null;

    public function __constructor()
    {
        //
    }

    public function submit(string $file_path)
    {
        if (!file_exists($file_path)) {
            // エラー処理
        }

        $cfile = curl_file_create($file_path);
        $post = ['file' => $cfile];

        $ch = curl_init();
        $options = [
            CURLOPT_URL => $this->$base_url . '/api/submit',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $post,
        ];
        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);
        $result = json_decode($result, true);

        if (!isset($result['id'])) {
            // エラー処理
        }
        $this->$id = $result['id'];

        return $result;
    }

    public function get_result()
    {
        $result = file_get_contents($this->$base_url . '/api/result/' . $this->$id);
        $result = json_decode($result, true);
    }

    public function get_webui_url()
    {
        return $this->$base_url . '/analyze/' . $this->$id;
    }

    public function get_malware(string $sha256)
    {
        return file_get_contents($this->$base_url . '/malware/' . $sha256 . '.bin');
    }
}
