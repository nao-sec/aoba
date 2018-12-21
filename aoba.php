<?php

require_once 'vendor/autoload.php';
require_once 'starc.php';
require_once 'ektotal.php';
require_once 'tknk.php';
require_once 'Slack.php';

if ($argc < 2) {
    echo '[!] Invalid arguments' . PHP_EOL;
    exit(-1);
}
$starc_input_url = $argv[1];

$chain_id = date('Y-m-d_H-i-s');
$base_dir = '/root/aoba/' . $chain_id;
if (!file_exists($base_dir)) {
    mkdir($base_dir);
}

$starc = new StarC('starc0', 'setuped');

$starc_status = $starc->get_status();
if ($starc_status['is_running'] === true) {
    $starc->shutdown();
    sleep(10);
}

$starc->restore();

$starc_job = $starc->start($starc_input_url);
$starc_timeout = 10 * 60;
$start_time = time();
while (true) {
    $starc_status = $starc->get_status();
    if ($starc_status['is_running'] === false || time() > $start_time + $starc_timeout) {
        $starc->restore();
        break;
    }

    sleep(10);
}

$traffic_data = $starc->get_saz();
if ($traffic_data === null) {
    $traffic_data = $starc->get_pcap();
}
$traffic_data_path = $base_dir . '/traffic.bin';
file_put_contents($traffic_data_path, $traffic_data);

// ---
// ---
// ---

$ektotal = new EKTotal();
$ektotal_submit_result = $ektotal->submit($traffic_data_path);
$ektotal_result = $ektotal->get_result();
$ektotal_webui_url = $ektotal->get_webui_url();
$malware_sha256 = null;
foreach ($ektotal_result['data'] as $traffic) {
    if ($traffic['is_malicious']) {
        $malicious_url = $traffic['URL'];
        $name = $traffic['result']['name'];
        if (isset($traffic['result']['description'])) {
            $description = $traffic['result']['description'];
        } else {
            $description = [];
        }

        if (isset($description['sha256'])) {
            if (strpos($name, 'SWF') === false) {
                $malware_sha256 = $description['sha256'];
            }
        }

        $malicious_url = str_replace('://', '[:]//', $malicious_url);
        Slack::post('#dbd', "```\n[ID] " . $chain_id . "\n[Alert] " . $name . "\n[URL] " . $malicious_url . "\n[Report URL] " . $ektotal_webui_url . "\n```");
    }
}

if ($malware_sha256 === null) {
    exit(1);
}

$malware_data = $ektotal->get_malware($malware_sha256);
$malware_data_path = $base_dir . '/malware.bin';
file_put_contents($malware_data_path, $malware_data);

// ---
// ---
// ---

$tknk = new tknk();
$upload_result = $tknk->upload($malware_data_path);
$tknk_hollows_hunter_job = $tknk->analyze('hollows_hunter');

$start_time = time();
$tknk_timeout = $tknk->$timeout + 60;
while (true) {
    if ($tknk->get_status() === 0 || time() > $start_time + $tknk_timeout) {
        break;
    }

    sleep(10);
}

$tknk_result = $tknk->get_result();
$report = $tknk_result['report'];
if ($report['result']['is_success']) {
    $tknk_webui_url = $tknk->get_webui_url();

    $detect_rules = [];

    if (!isset($report['target_scan'])) {
        // エラー処理
    }
    if (count($report['target_scan']['detect_rule']) > 0) {
        foreach ($report['target_scan']['detect_rule'] as $rule) {
            if (!in_array($rule, $detect_rules)) {
                $detect_rules[] = $rule;
            }
        }
    }
    if (!isset($report['scans'])) {
        // エラー処理
    }
    if (count($report['scans']['detect_rule']) > 0) {
        foreach ($report['scans']['detect_rule'] as $rule) {
            if (!in_array($rule, $detect_rules)) {
                $detect_rules[] = $rule;
            }
        }
    }
    $detect_rules = implode(', ', $detect_rules);

    Slack::post('#malware', "```\n[ID] " . $chain_id . "\n[Alert] " . $detect_rules . "\n[SHA256] " . $malware_sha256 . "\n[Report URL] " . $tknk_webui_url . "\n```");
} else {
    $tknk_procdump_job = $tknk->analyze('procdump');

    $start_time = time();
    while (true) {
        if ($tknk->get_status() === 0 || time() > $start_time + $tknk_timeout) {
            break;
        }

        sleep(10);
    }

    $tknk_result = $tknk->get_result();
    $report = $tknk_result['report'];
    if ($report['result']['is_success']) {
        $tknk_webui_url = $tknk->get_webui_url();

        $detect_rules = [];

        if (!isset($report['target_scan'])) {
            // エラー処理
        }
        if (count($report['target_scan']['detect_rule']) > 0) {
            foreach ($report['target_scan']['detect_rule'] as $rule) {
                if (!in_array($rule, $detect_rules)) {
                    $detect_rules[] = $rule;
                }
            }
        }
        if (!isset($report['scans'])) {
            // エラー処理
        }
        if (count($report['scans']['detect_rule']) > 0) {
            foreach ($report['scans']['detect_rule'] as $rule) {
                if (!in_array($rule, $detect_rules)) {
                    $detect_rules[] = $rule;
                }
            }
        }
        $detect_rules = implode(', ', $detect_rules);

        Slack::post('#malware', "```\n[ID] " . $chain_id . "\n[Alert] " . $detect_rules . "\n[SHA256] " . $malware_sha256 . "\n[Report URL] " . $tknk_webui_url . "\n```");
    } else {
        $tknk_webui_url = $tknk->get_webui_url();
        Slack::post('#malware', "```\n[ID] " . $chain_id . "\n[Error] Failed to identify malware\n[SHA256] " . $malware_sha256 . "\n[Report URL] " . $tknk_webui_url . "\n```");
    }
}
