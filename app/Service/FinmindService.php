<?php
namespace App\Service;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FinmindService
{
    protected Client $client;
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.finmindtrade.com/api/'
        ]);
    }

    public function searchStock($stockId, $targetDate = null)
    {
        $allStockInfo = $this->getStockInfo();
        $specificStockInfo = collect($allStockInfo)->firstWhere('stock_id', $stockId);

        if (!$specificStockInfo) {
            return null;
        }

        $startDate = $targetDate ?: now()->subDays(3)->format('Y-m-d');
        $endDate = $targetDate ?: now()->format('Y-m-d');
        $stockPrice = $this->getStockPrice($stockId, $startDate, $endDate);
        Log::info('Fetching stock price', compact('startDate', 'endDate', 'stockPrice'));

        $priceData = collect($stockPrice);
        $selectedPrice = $targetDate ? $priceData->firstWhere('date', $targetDate) : $priceData->last();
        $specificStockInfo['price'] = $selectedPrice;

        return $specificStockInfo;
    }

    protected function getStockInfo()
    {
        $result = $this->finmind('v4/data', [
            'dataset' => 'TaiwanStockInfo',
            'token' => config('finance.finmind_token'),
        ]);
        return $result['data'];
    }

    protected function getStockPrice($stockId, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: now()->format('Y-m-d');
        $endDate = $endDate ?: now()->format('Y-m-d');

        $result = $this->finmind('v4/data', [
            'dataset' => 'TaiwanStockPrice',
            'data_id' => $stockId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'token' => config('finance.finmind_token'),
        ]);
        return $result['data'];
    }

    public function getCurrentStockPrice($stockId)
    {
        $result = $this->finmind('v4/taiwan_stock_tick_snapshot', [
            'data_id' => $stockId,
            'token' => config('finance.finmind_token'),
        ]);
        return $result['data'];
    }

    protected function finmind($uri, $query)
    {
        $response = $this->client->request('GET', $uri, ['query' => $query]);

        $result = $response->getBody()->getContents();
        return json_decode($result, true);
    }
}
