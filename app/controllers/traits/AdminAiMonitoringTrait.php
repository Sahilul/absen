<?php
/**
 * File: app/controllers/traits/AdminAiMonitoringTrait.php
 * Trait untuk halaman Monitoring AI di admin panel.
 */
trait AdminAiMonitoringTrait
{
    /**
     * GET /admin/aiMonitoring
     * Halaman dashboard monitoring AI.
     */
    public function aiMonitoring()
    {
        $this->data['judul'] = 'Monitoring AI';
        $this->data['load_chartjs'] = true;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/ai_monitoring', $this->data);
        $this->view('templates/footer');
    }

    /**
     * GET /admin/aiMonitoringData
     * AJAX endpoint: return semua data monitoring sebagai JSON.
     */
    public function aiMonitoringData()
    {
        header('Content-Type: application/json');

        $startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate   = $_GET['end'] ?? date('Y-m-d');

        // Validasi format tanggal
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            echo json_encode(['success' => false, 'error' => 'Format tanggal tidak valid']);
            exit;
        }

        $model = $this->model('AiMonitoring_model');

        // Harga token dari pengaturan
        $priceInput  = (float) getSystemSetting('ai_chat_price_input_per_1k', '0');
        $priceOutput = (float) getSystemSetting('ai_chat_price_output_per_1k', '0');

        $summary = $model->getSummary($startDate, $endDate);

        // Hitung estimasi biaya
        $estimatedCost = 0;
        if ($priceInput > 0 || $priceOutput > 0) {
            $estimatedCost = (($summary['total_prompt_tokens'] / 1000) * $priceInput)
                           + (($summary['total_completion_tokens'] / 1000) * $priceOutput);
        }
        $summary['estimated_cost'] = round($estimatedCost, 2);
        $summary['price_input_per_1k'] = $priceInput;
        $summary['price_output_per_1k'] = $priceOutput;

        $data = [
            'success'       => true,
            'period'        => ['start' => $startDate, 'end' => $endDate],
            'summary'       => $summary,
            'daily_trend'   => $model->getDailyTrend($startDate, $endDate),
            'top_users'     => $model->getUsagePerUser($startDate, $endDate, 20),
            'top_keywords'  => $model->getTopKeywords($startDate, $endDate, 30),
            'conversations' => $model->getRecentConversations($startDate, $endDate, 50),
            'latency_stats' => $model->getLatencyStats($startDate, $endDate),
            'model_breakdown' => $model->getModelBreakdown($startDate, $endDate),
            'hourly_trend'  => $model->getHourlyTrend($startDate, $endDate),
            'file_usage'    => $model->getFileUsage($startDate, $endDate),
            'recent_errors' => $model->getRecentErrors($startDate, $endDate, 20),
        ];

        echo json_encode($data);
        exit;
    }

    /**
     * GET /admin/aiMonitoringConversation/{id}
     * AJAX endpoint: detail satu percakapan.
     */
    public function aiMonitoringConversation($id = null)
    {
        header('Content-Type: application/json');

        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'error' => 'ID tidak valid']);
            exit;
        }

        $model = $this->model('AiMonitoring_model');
        $detail = $model->getConversationDetail((int) $id);

        if (!$detail['conversation']) {
            echo json_encode(['success' => false, 'error' => 'Percakapan tidak ditemukan']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $detail]);
        exit;
    }
}
