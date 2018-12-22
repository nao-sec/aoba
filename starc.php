<?php

class StarC
{
    private $base_url = 'http://xx.xx.xx.xx';
    private $id = null;
    private $vm_name = null;
    private $snapshot_name = null;

    public function __constructor(string $_id, string $_vm_name, string $_snapshot_name)
    {
        $this->$id = $_id;
        $this->$vm_name = $_vm_name;
        $this->$snapshot_name = $_snapshot_name;
    }

    private function get_status()
    {
        $result = file_get_contents($this->$base_url . '/api/status?id=' . $this->$id);
        $result = json_decode($result);

        return $result;
    }

    public function shutdown()
    {
        $post = [
            'vm_name' => $this->$vm_name,
            'id' => $this->$id,
        ];

        $ch = curl_init();
        $options = [
            CURLOPT_URL => $this->$base_url . '/api/shutdown',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $post,
        ];
        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);
        $result = json_decode($result);

        return $result;
    }

    public function restore()
    {
        $post = [
            'vm_name' => $this->$vm_name,
            'snapshot' => $this->$snapshot_name,
            'id' => $this->$id,
        ];

        $ch = curl_init();
        $options = [
            CURLOPT_URL => $this->$base_url . '/api/restore',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $post,
        ];
        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);
        $result = json_decode($result);

        return $result;
    }

    public function start(string $url)
    {
        $post = [
            'vm_name' => $this->$vm_name,
            'url' => $url,
        ];

        $ch = curl_init();
        $options = [
            CURLOPT_URL => $this->$base_url . '/api/start',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $post,
        ];
        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);
        $result = json_decode($result);

        if (!isset($result['id'])) {
            // エラー処理
        }

        $this->$id = $result['id'];

        return $result;
    }

    public function get_pcap()
    {
        return file_get_contents($this->$base_url . '/api/pcap?id=' . $this->$id);
    }

    public function get_saz()
    {
        return file_get_contents($this->$base_url . '/api/saz?id=' . $this->$id);
    }
}
