<?php


namespace App\Service;


use GuzzleHttp\Client;

class FinmindService
{
    protected $client;
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.finmindtrade.com/api/'
        ]);
    }

    public function searchStock($stockId, $startDate = null)
    {
        $stockInfo = $this->getStockInfo();
        $stockInfo = collect($stockInfo)->firstWhere('stock_id', $stockId);
        if ($stockInfo) {
            $startDate = $startDate ?: now()->subDays(3)->format('Y-m-d');
            $endDate = $startDate ?: now()->format('Y-m-d');
            $stockPrice = $this->getStockPrice($stockId, $startDate, $endDate);
            $stockPrice = collect($stockPrice)->last();
            $stockInfo['price'] = $stockPrice;
            return $stockInfo;
        } else {
            return null;
        }
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
