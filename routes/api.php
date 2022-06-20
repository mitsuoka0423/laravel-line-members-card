<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use Picqer\Barcode\BarcodeGeneratorPNG;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

$httpClient = new CurlHTTPClient($_ENV['LINE_CHANNEL_ACCESS_TOKEN']);
$bot = new LINEBot($httpClient, ['channelSecret' => $_ENV['LINE_CHANNEL_SECRET']]);

$barcodeGenerator = new BarcodeGeneratorPNG();

Route::post('/webhook', function (Request $request) use ($bot, $barcodeGenerator) {
    Log::debug($request);

    $signature = $request->header(HTTPHeader::LINE_SIGNATURE);
    if (empty($signature)) {
        return abort(400);
    }

    $events = $bot->parseEventRequest($request->getContent(), $signature);
    Log::debug(['$events' => $events]);

    collect($events)->each(function ($event) use ($bot, $barcodeGenerator) {
        if ($event instanceof TextMessage) {
            if ($event->getText() === '会員カード') {
                // TODO: Airtableから`barcodeId`を取得
                $barcodeId = $event->getUserId();
                $barcodeFileName = "{$barcodeId}.png";
                $barcodeFilePath = "./public/{$barcodeFileName}";

                // バーコードが存在しなければ生成する
                if (!Storage::exists($barcodeFilePath)) {
                    $barcodeImage = $barcodeGenerator->getBarcode('barcodeId', $barcodeGenerator::TYPE_CODE_128);
                    Storage::put($barcodeFilePath, $barcodeImage);
                }

                $imageMessageBuilder = new ImageMessageBuilder($barcodeFilePath, $barcodeFilePath);
                $bot->replyMessage($event->getReplyToken(), $imageMessageBuilder);
            } else {
                $bot->replyText($event->getReplyToken(), $event->getText());
            }
        }
    });

    return 'ok!';
});
