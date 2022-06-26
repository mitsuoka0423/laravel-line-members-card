<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use Tapp\Airtable\Facades\AirtableFacade;
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
            if ($event instanceof TextMessage) {
                if ($event->getText() === '会員カード') {
                    // 会員登録済みか確認するため、Airtableからデータを取得する
                    $member = Airtable::where('UserId', $event->getUserId())->get();

                    if ($member->isEmpty()) {
                        // Airtableに会員データがなければ、生成して登録する
                        $memberId = strval(rand(1000000000, 9999999999));
                        $member = Airtable::firstOrCreate([
                            'UserId' => $event->getUserId(),
                            'Name' => $bot->getProfile($event->getUserId())->getJSONDecodedBody()['displayName'],
                            'MemberId' => $memberId,
                        ]);
                        Log::debug('Member is created.');
                    } else {
                        // Airtableにデータがあれば、取得したデータを利用する
                        $memberId = $member->first()['fields']['MemberId'];
                    }

                    $barcodeFileName = "{$memberId}.png";
                    $barcodeFilePath = "public/{$barcodeFileName}";
                    if (!Storage::exists($barcodeFilePath)) {
                        $barcodeImage = $barcodeGenerator->getBarcode($memberId, $barcodeGenerator::TYPE_CODE_128);
                        Storage::put($barcodeFilePath, $barcodeImage);
                    } else {
                        $barcodeImage = Storage::get($barcodeFilePath);
                    }

                    $imageUrl = Config::get('app.url') . '/storage/' . $barcodeFileName;
                    $imageMessageBuilder = new ImageMessageBuilder($imageUrl, $imageUrl);
                    return $bot->replyMessage($event->getReplyToken(), $imageMessageBuilder);
                } else {
                    return $bot->replyText($event->getReplyToken(), $event->getText());
                }
            }
        }
    });

    return 'ok!';
});

Route::get('/members/{memberId}', function ($memberId) {
    // モックモードの場合、テストデータを返却する
    if ($memberId === '123456789') {
        return [
            'UserId' => '123456789',
            'Name' => 'テストデータ',
            'MemberId' => '9381274411',
        ];
    }

    // Airtableからデータを取得する
    $member = Airtable::where('UserId', $memberId)->get();

    if ($member->isEmpty()) {
        return abort(404);
    }

    return $member->first()['fields'];
});
